<?php

namespace SCI\Tests\Integration\Parser;

use SCI\MediaWikiNsContentMapper;
use SCI\HookRegistry;
use SCI\Options;
use Onoi\Cache\CacheFactory;
use SMW\Tests\ByJsonTestCaseProvider;
use SMW\Tests\JsonTestCaseFileHandler;
use SMW\Tests\Utils\UtilityFactory;
use SMW\DIWikiPage;

/**
 * @group semantic-cite-integration
 * @group medium
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ByJsonParserTestCaseRunnerTest extends ByJsonTestCaseProvider {

	private $semanticDataValidator;
	private $stringValidator;
	private $hookRegistry;

	protected function setUp() {
		parent::setUp();

		$this->semanticDataValidator = UtilityFactory::getInstance()->newValidatorFactory()->newSemanticDataValidator();
		$this->stringValidator = UtilityFactory::getInstance()->newValidatorFactory()->newStringValidator();

		$configuration = array(
			'numberOfReferenceListColumns'       => 1,
			'browseLinkToCitationResource'       => false,
			'showTooltipForCitationReference'    => false,
			'tooltipRequestCacheTTL'             => false,
			'citationReferenceCaptionFormat'     => 1,
			'referenceListType'                  => 'ol',
			'strictParserValidationEnabled'      => true,
			'cachePrefix'                        => 'foo',
			'enabledCitationTextChangeUpdateJob' => false
		);

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
	 * @see ByJsonTestCaseProvider::getJsonTestCaseVersion
	 */
	protected function getJsonTestCaseVersion() {
		return '0.1';
	}

	/**
	 * @see ByJsonTestCaseProvider::getTestCaseLocation
	 */
	protected function getTestCaseLocation() {
		return __DIR__ . '/Fixtures';
	}

	/**
	 * @see ByJsonTestCaseProvider::runTestCaseFile
	 *
	 * @param JsonTestCaseFileHandler $jsonTestCaseFileHandler
	 */
	protected function runTestCaseFile( JsonTestCaseFileHandler $jsonTestCaseFileHandler ) {

		$this->checkEnvironmentToSkipCurrentTest( $jsonTestCaseFileHandler );

		$permittedSettings = array(
			'smwgNamespacesWithSemanticLinks',
			'smwgPageSpecialProperties',
			'wgLanguageCode',
			'wgContLang',
			'wgLang',
			'scigCitationReferenceCaptionFormat'
		);

		foreach ( $permittedSettings as $key ) {
			$this->changeGlobalSettingTo(
				$key,
				$jsonTestCaseFileHandler->getSettingsFor( $key )
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

		if ( isset( $case['errors'] ) && $case['errors'] !== array() ) {
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
			$case['expected-output']['to-contain'] = array();
		}

		if ( !isset( $case['expected-output']['to-not-contain'] ) ) {
			$case['expected-output']['to-not-contain'] = array();
		}

		$subject = DIWikiPage::newFromText(
			$case['subject'],
			isset( $case['namespace'] ) ? constant( $case['namespace'] ) : NS_MAIN
		);

		$parserOutput = UtilityFactory::getInstance()->newPageReader()->getEditInfo( $subject->getTitle() )->output;

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
