<?php

namespace SCI;

use SMW\ApplicationFactory;
use SMW\NamespaceExaminer;
use SMW\ParameterProcessorFactory;
use SCI\Bibtex\BibtexProcessor;
use SCI\Bibtex\BibtexParser;
use SCI\Bibtex\BibtexAuthorListParser;

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
	 * @param Options $options
	 *
	 * @return array
	 */
	public function newSciteParserFunctionDefinition( NamespaceExaminer $namespaceExaminer, Options $options ) {

		$sciteParserFunctionDefinition = function( $parser ) use( $namespaceExaminer, $options ) {

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
				$mediaWikiNsContentMapper,
				new BibtexProcessor( new BibtexParser(), new BibtexAuthorListParser() )
			);

			$sciteParserFunction->setStrictParserValidationState(
				$options->get( 'strictParserValidationEnabled' )
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

			$parserData = ApplicationFactory::getInstance()->newParserData(
				$parser->getTitle(),
				$parser->getOutput()
			);

			$referenceListParserFunction = new ReferenceListParserFunction( $parserData );

			return $referenceListParserFunction->doProcess(
				ParameterProcessorFactory::newFromArray( func_get_args() )
			);
		};

		return array( 'referencelist', $referenceListParserFunctionDefinition, 0 );
	}

}
