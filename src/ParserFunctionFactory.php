<?php

namespace SCI;

use SMW\ApplicationFactory;
use SMW\NamespaceExaminer;
use SMW\ParameterProcessorFactory;
use Parser;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class ParserFunctionFactory {

	/**
	 * {{#scite:}}
	 *
	 * @since  1.0
	 *
	 * @param NamespaceExaminer $namespaceExaminer
	 * @param array $configuration
	 *
	 * @return array
	 */
	public function newSciteParserFunctionDefinition( NamespaceExaminer $namespaceExaminer, array $configuration ) {

		$sciteParserFunctionDefinition = function( $parser ) use( $namespaceExaminer, $configuration ) {

			$parserData = ApplicationFactory::getInstance()->newParserData(
				$parser->getTitle(),
				$parser->getOutput()
			);

			$mwCollaboratorFactory = ApplicationFactory::getInstance()->newMwCollaboratorFactory();

			$citationTextTemplateRenderer = new CitationTextTemplateRenderer(
				$mwCollaboratorFactory->newWikitextTemplateRenderer(),
				$parser
			);

			$mediaWikiNsContentMapper = new MediaWikiNsContentMapper(
				$mwCollaboratorFactory->newMediaWikiNsContentReader()
			);

			$sciteParserFunction = new SciteParserFunction(
				$parserData,
				$namespaceExaminer,
				$citationTextTemplateRenderer,
				$mediaWikiNsContentMapper
			);

			$sciteParserFunction->setStrictParserValidationState(
				$configuration['strictParserValidationEnabled']
			);

			return $sciteParserFunction->doProcess(
				ParameterProcessorFactory::newFromArray( func_get_args() )
			);
		};

		return array( 'scite', $sciteParserFunctionDefinition, 0 );
	}

	/**
	 * {{#referencelist:}}
	 *
	 * @since  1.0
	 *
	 * @return array
	 */
	public function newReferenceListParserFunctionDefinition() {

		$referenceListParserFunctionDefinition = function( $parser ) {

			$referenceListParserFunction = new ReferenceListParserFunction();

			return $referenceListParserFunction->doProcess(
				ParameterProcessorFactory::newFromArray( func_get_args() )
			);
		};

		return array( 'referencelist', $referenceListParserFunctionDefinition, 0 );
	}

}
