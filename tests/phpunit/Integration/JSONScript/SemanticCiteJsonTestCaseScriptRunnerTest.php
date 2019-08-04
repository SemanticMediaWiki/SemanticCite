<?php

namespace SCI\Tests\Integration\JSONScript;

use SCI\MediaWikiNsContentMapper;
use SCI\HookRegistry;
use SCI\Options;
use Onoi\Cache\CacheFactory;
use SMW\Tests\JsonTestCaseScriptRunner;
use SMW\Tests\JsonTestCaseFileHandler;
use SMW\Tests\Utils\UtilityFactory;
use SMW\DIWikiPage;

/**
 * @group semantic-cite
 * @group medium
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class SemanticCiteJsonTestCaseScriptRunnerTest extends JsonTestCaseScriptRunner {

	private $semanticDataValidator;
	private $stringValidator;
	private $hookRegistry;

	protected function setUp() {
		parent::setUp();

		$this->testEnvironment->tearDown();

		// Make sure LocalSettings don't interfere with the default settings
		$this->testEnvironment->withConfiguration(
			[
				'smwgQueryResultCacheType' => false,
				'smwgPageSpecialProperties' => [ '_MDAT' ],
			]
		);

		$validatorFactory = $this->testEnvironment->getUtilityFactory()->newValidatorFactory();

		$this->semanticDataValidator = $validatorFactory->newSemanticDataValidator();
		$this->stringValidator = $validatorFactory->newStringValidator();

		$configuration = [
			'numberOfReferenceListColumns'       => 1,
			'browseLinkToCitationResource'       => false,
			'showTooltipForCitationReference'    => false,
			'tooltipRequestCacheTTL'             => false,
			'citationReferenceCaptionFormat'     => 1,
			'referenceListType'                  => 'ol',
			'enabledStrictParserValidation'      => true,
			'cachePrefix'                        => 'foo',
			'enabledCitationTextChangeUpdateJob' => false,
			'responsiveMonoColumnCharacterBoundLength' => 100
		];

		// This is to ensure we read from the DB when a test case
		// specifies a NS_MEDIAWIKI page
		MediaWikiNsContentMapper::clear();
		MediaWikiNsContentMapper::$skipMessageCache = true;

		$cacheFactory = new CacheFactory();

		$this->hookRegistry = new HookRegistry(
			$this->getStore(),
			$cacheFactory->newFixedInMemoryLruCache(),
			new Options( $configuration )
		);

		$this->hookRegistry->clear();
		$this->hookRegistry->register();
	}

	/**
	 * @see JsonTestCaseScriptRunner::getTestCaseLocation
	 */
	protected function getRequiredJsonTestCaseMinVersion() {
		return '0.1';
	}

	/**
	 * @see JsonTestCaseScriptRunner::getAllowedTestCaseFiles
	 */
	protected function getAllowedTestCaseFiles() {
		return [];
	}

	/**
	 * @see JsonTestCaseScriptRunner::getTestCaseLocation
	 */
	protected function getTestCaseLocation() {
		return __DIR__ . '/TestCases';
	}

	/**
	 * @see JsonTestCaseScriptRunner::getPermittedSettings
	 */
	protected function getPermittedSettings() {
		$settings = parent::getPermittedSettings();

		return array_merge( $settings, [
			'smwgNamespacesWithSemanticLinks',
			'smwgPageSpecialProperties',
			'wgLanguageCode',
			'wgContLang',
			'wgLang',
			'scigCitationReferenceCaptionFormat',
			'smwgQueryResultCacheType'
		] );
	}

	/**
	 * @see JsonTestCaseScriptRunner::runTestCaseFile
	 *
	 * @param JsonTestCaseFileHandler $jsonTestCaseFileHandler
	 */
	protected function runTestCaseFile( JsonTestCaseFileHandler $jsonTestCaseFileHandler ) {

		$this->checkEnvironmentToSkipCurrentTest( $jsonTestCaseFileHandler );

		foreach ( $this->getPermittedSettings() as $key ) {
			$this->changeGlobalSettingTo(
				$key,
				$jsonTestCaseFileHandler->getSettingsFor( $key, $this->getConfigValueCallback( $key ) )
			);
		}

		// On SQLite we don't want DB dead locks due to parallel write access
		$this->changeGlobalSettingTo(
			'smwgEnabledHttpDeferredJobRequest',
			false
		);

		$this->hookRegistry->setOption(
			'citationReferenceCaptionFormat',
			$jsonTestCaseFileHandler->getSettingsFor( 'scigCitationReferenceCaptionFormat' )
		);

		$this->hookRegistry->setOption(
			'referenceListType',
			$jsonTestCaseFileHandler->getSettingsFor( 'scigReferenceListType' )
		);

		$this->createPagesFor(
			$jsonTestCaseFileHandler->getListOfProperties(),
			SMW_NS_PROPERTY
		);

		$this->createPagesFor(
			$jsonTestCaseFileHandler->getListOfSubjects(),
			NS_MAIN
		);

		foreach ( $jsonTestCaseFileHandler->findTestCasesFor( 'parser-testcases' ) as $case ) {

			if ( !isset( $case['subject'] ) ) {
				break;
			}

			$this->assertSemanticDataForCase( $case, $jsonTestCaseFileHandler->getDebugMode() );
			$this->assertParserOutputForCase( $case );
		}
	}

	private function assertSemanticDataForCase( $case, $debug ) {

		if ( !isset( $case['store'] ) || !isset( $case['store']['semantic-data'] ) ) {
			return;
		}

		$subject = DIWikiPage::newFromText(
			$case['subject'],
			isset( $case['namespace'] ) ? constant( $case['namespace'] ) : NS_MAIN
		);

		$semanticData = $this->getStore()->getSemanticData( $subject );

		if ( $debug ) {
			print_r( $semanticData );
		}

		if ( isset( $case['errors'] ) && $case['errors'] !== [] ) {
			$this->assertNotEmpty(
				$semanticData->getErrors()
			);
		}

		$this->semanticDataValidator->assertThatPropertiesAreSet(
			$case['store']['semantic-data'],
			$semanticData,
			$case['about']
		);
	}

	private function assertParserOutputForCase( $case ) {

		if ( !isset( $case['expected-output'] ) ) {
			return;
		}

		if ( !isset( $case['expected-output']['to-contain'] ) ) {
			$case['expected-output']['to-contain'] = [];
		}

		if ( !isset( $case['expected-output']['to-not-contain'] ) ) {
			$case['expected-output']['to-not-contain'] = [];
		}

		$subject = DIWikiPage::newFromText(
			$case['subject'],
			isset( $case['namespace'] ) ? constant( $case['namespace'] ) : NS_MAIN
		);

		$pageReader = UtilityFactory::getInstance()->newPageReader();
		$parserOutput = $pageReader->getEditInfo( $subject->getTitle() )->getOutput();

		// Cheating a bit here but this is to ensure the OutputPageBeforeHTML
		// hook is run
		$context = new \RequestContext();
		$context->setTitle( $subject->getTitle() );
		$context->getOutput()->addParserOutput( $parserOutput );

		$this->stringValidator->assertThatStringContains(
			$case['expected-output']['to-contain'],
			$context->getOutput()->getHtml(),
			$case['about']
		);

		$this->stringValidator->assertThatStringNotContains(
			$case['expected-output']['to-not-contain'],
			$context->getOutput()->getHtml(),
			$case['about']
		);
	}

}
