<?php

namespace SCI\Tests\Integration\Parser;

use SCI\MediaWikiNsContentMapper;
use SCI\HookRegistry;
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

	protected function setUp() {
		parent::setUp();

		$this->semanticDataValidator = UtilityFactory::getInstance()->newValidatorFactory()->newSemanticDataValidator();
		$this->stringValidator = UtilityFactory::getInstance()->newValidatorFactory()->newStringValidator();

		$configuration = array(
			'numberOfReferenceListColumns'     => 1,
			'browseLinkToCitationResource'     => false,
			'showTooltipForCitationReference'  => false,
			'tooltipRequestCacheTTL'           => false,
			'citationReferenceCaptionFormat'   => 1,
			'referenceListType'                => 'ol',
			'strictParserValidationEnabled'    => true,
			'cachePrefix'                      => 'foo'
		);

		$cacheFactory = new CacheFactory();

		$hookRegistry = new HookRegistry(
			$this->getStore(),
			$cacheFactory->newFixedInMemoryLruCache(),
			$configuration
		);

		$hookRegistry->clear();
		$hookRegistry->register();
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
			'scigCitationReferenceCaptionFormat',
			'scigReferenceListType'
		);

		foreach ( $permittedSettings as $key ) {
			$this->changeGlobalSettingTo(
				$key,
				$jsonTestCaseFileHandler->getSettingsFor( $key )
			);
		}

		$this->createPagesFor(
			$jsonTestCaseFileHandler->getListOfProperties(),
			SMW_NS_PROPERTY
		);

		$this->createPagesFor(
			$jsonTestCaseFileHandler->getListOfSubjects(),
			NS_MAIN
		);

		MediaWikiNsContentMapper::clear();
		MediaWikiNsContentMapper::$skipMessageCache = true;

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

		if ( !isset( $case['expected-output'] ) || !isset( $case['expected-output']['to-contain'] ) ) {
			return;
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
	//	$context->getOutput()->addParserOutputMetadata( $parserOutput );
		$context->getOutput()->addParserOutputContent( $parserOutput );

		$this->stringValidator->assertThatStringContains(
			$case['expected-output']['to-contain'],
			$context->getOutput()->getHtml(),
			$case['about']
		);
	}

}
