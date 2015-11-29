<?php

namespace SCI;

use SCI\Bibtex\BibtexProcessor;
use SMW\NamespaceExaminer;
use SMW\ParserParameterProcessor;
use SMW\DataValueFactory;
use SMW\DIProperty;
use SMW\ParserData;
use SMW\Subobject;
use Html;

/**
 * #scite: is used the create a citation resource with a reference to be
 * accessible wiki-wide trough a unique citation key.
 *
 * [[CiteRef: ... ]] | [[Citation reference:: ...]] is used to create an in-text
 * reference link to a citation resource.
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class SciteParserFunction {

	/**
	 * @var ParserData
	 */
	private $parserData;

	/**
	 * @var NamespaceExaminer
	 */
	private $namespaceExaminer;

	/**
	 * @var CitationTextTemplateRenderer
	 */
	private $citationTextTemplateRenderer;

	/**
	 * @var MediaWikiNsContentMapper
	 */
	private $mediaWikiNsContentMapper;

	/**
	 * @var BibtexProcessor
	 */
	private $bibtexProcessor;

	/**
	 * @var DataValueFactory
	 */
	private $dataValueFactory;

	/**
	 * @var boolean
	 */
	private $strictParserValidationState = true;

	/**
	 * @var array
	 */
	private $parameters = array();

	/**
	 * @since 1.0
	 *
	 * @param ParserData $parserData
	 * @param NamespaceExaminer $namespaceExaminer
	 * @param CitationTextTemplateRenderer $citationTextTemplateRenderer
	 * @param MediaWikiNsContentMapper $mediaWikiNsContentMapper
	 * @param BibtexProcessor $bibtexProcessor
	 */
	public function __construct( ParserData $parserData, NamespaceExaminer $namespaceExaminer, CitationTextTemplateRenderer $citationTextTemplateRenderer, MediaWikiNsContentMapper $mediaWikiNsContentMapper, BibtexProcessor $bibtexProcessor ) {
		$this->parserData = $parserData;
		$this->namespaceExaminer = $namespaceExaminer;
		$this->citationTextTemplateRenderer = $citationTextTemplateRenderer;
		$this->mediaWikiNsContentMapper = $mediaWikiNsContentMapper;
		$this->bibtexProcessor = $bibtexProcessor;
		$this->dataValueFactory = DataValueFactory::getInstance();
	}

	/**
	 * @since 1.0
	 *
	 * @param boolean $strictParserValidationState
	 */
	public function setStrictParserValidationState ( $strictParserValidationState ) {
		$this->strictParserValidationState = (bool)$strictParserValidationState;
	}

	/**
	 * Identifiers that have a id-property mapping are to be annotated as semantic
	 * property. The property mapped to an identifier should be maintained
	 * accordantly with the the expected type it ought to represent
	 * (e.g. 'url' => 'Has url', with 'Has url' being of [[Has type::URL]])
	 *
	 * Only identifiers used in #scite: that have an appropriate mapping
	 * will be created as semantic annotation. For example, identifiers important
	 * for the citation output are not necessarily notable for a semantic
	 * assignment (e.g. 'pages', 'page reference', or 'available').
	 *
	 * {{#scite:Segon & Booth 2011
	 * |type=online
	 * |author=Segon, M;Booth, C|+sep=;
	 * |year=2011
	 * |title=Bribery: what do Australian managers know and what do they do?
	 * |journal=Journal of Business Systems, Governance and Ethics
	 * |volumn=vol. 6, no. 3
	 * |pages=15-29
	 * |url=http://www.jbsge.vu.edu.au/issues/vol06no3/Segon_&_Booth.pdf
	 * |available=20 October 2014
	 * |template=scite-formatter-online
	 * }}
	 *
	 * OR
	 *
	 * {{#scite:Einstein, Podolsky, and Nathan 1935
	 * |bibtex=@article{einstein1935can,
	 *  title={Can quantum-mechanical description of physical reality be considered complete?},
	 *  author={Einstein, Albert and Podolsky, Boris and Rosen, Nathan},
	 *  journal={Physical review},
	 *  volume={47},
	 *  number={10},
	 *  pages={777},
	 *  year={1935},
	 *  publisher={APS}
	 * }
	 * |authors=Albert Einstein, Boris Podolsky, Nathan Rosen|+sep=,
	 * }}
	 *
	 * The citation style is determined by the template assigned and stored in
	 * its own semantic property. To apply for individual formatting rules, use
	 * |template=
	 *
	 * For any free text assignment use,
	 * |citation text=
	 *
	 * @since 1.0
	 *
	 * @param ParserParameterProcessor $parserParameterProcessor
	 */
	public function doProcess( ParserParameterProcessor $parserParameterProcessor ) {

		if ( !$this->namespaceExaminer->isSemanticEnabled( $this->parserData->getTitle()->getNamespace() ) ) {
			return $this->createErrorMessageFor( 'sci-scite-parser-not-enabled-namespace' );
		}

		// Hopefully the bibtex processor will add all required parameters and values
		if ( $parserParameterProcessor->hasParameter( 'bibtex' ) ) {
			$this->bibtexProcessor->doProcess( $parserParameterProcessor );
		}

		$reference = str_replace( '_', ' ', $parserParameterProcessor->getFirstParameter() );
		$type = '';

		// Explicit reference precedes
		if ( $parserParameterProcessor->hasParameter( 'reference' ) ) {
			$reference = $parserParameterProcessor->getParameterValuesFor( 'reference' );
			$reference = end( $reference );
		}

		if ( $reference === '' ) {
			return $this->createErrorMessageFor( 'sci-scite-parser-no-reference' );
		}

		// Fixed identifier
		$parserParameterProcessor->addParameter(
			'reference',
			$reference
		);

		if ( $parserParameterProcessor->hasParameter( 'type' ) ) {
			$type = $parserParameterProcessor->getParameterValuesFor( 'type' );
			sort( $type );
			$type = end( $type );
		}

		$errorText = $this->tryToMatchCitationTextByPrecept(
			$parserParameterProcessor,
			$reference,
			$type
		);

		$this->addPropertyValuesFor(
			$parserParameterProcessor,
			$reference
		);

		return $errorText; //array( '', 'noparse' => true, 'isHTML' => true );
	}

	private function tryToMatchCitationTextByPrecept( ParserParameterProcessor $parserParameterProcessor, $reference, $type = '' ) {

		$template = '';

		if ( $this->strictParserValidationState && $type === '' ) {

			$parserParameterProcessor->addParameter(
				DIProperty::TYPE_ERROR,
				PropertyRegistry::SCI_CITE
			);

			return $this->createErrorMessageFor( 'sci-scite-parser-no-type', $reference );
		}

		// An injected text trumps a template
		if ( $parserParameterProcessor->hasParameter( 'citation text' ) ) {
			return '';
		}

		// An explicit template precedes the assignment found for the type
		if ( $parserParameterProcessor->hasParameter( 'template' ) ) {
			$template = $parserParameterProcessor->getParameterValuesFor( 'template' );
			$template = trim( end( $template ) );
		} elseif ( $this->mediaWikiNsContentMapper->findTemplateForType( $type ) !== null ) {
			$template = $this->mediaWikiNsContentMapper->findTemplateForType( $type );
		}

		if ( $template !== '' ) {
			$this->citationTextTemplateRenderer->packFieldsForTemplate( $template );

			// Fixed identifier
			$parserParameterProcessor->addParameter(
				'citation text',
				$this->citationTextTemplateRenderer->renderFor( $parserParameterProcessor->toArray() )
			);
		} elseif ( $this->strictParserValidationState ) {

			$parserParameterProcessor->addParameter(
				DIProperty::TYPE_ERROR,
				PropertyRegistry::SCI_CITE_TEXT
			);

			return $this->createErrorMessageFor( 'sci-scite-parser-no-citation-text', $reference, $type );
		}

		return '';
	}

	private function addPropertyValuesFor( ParserParameterProcessor $parserParameterProcessor, $reference ) {

		$subobject = new Subobject( $this->parserData->getTitle() );
		$subobject->setEmptyContainerForId( '_SCITE' . md5( $reference ) );

		$subject = $this->parserData->getSemanticData()->getSubject();

		foreach ( $parserParameterProcessor->toArray() as $key => $values ) {

			if ( $key === DIProperty::TYPE_ERROR ) {

				$value = new DIProperty( end( $values ) );

				$subobject->getSemanticData()->addPropertyObjectValue(
					new DIProperty( DIProperty::TYPE_ERROR ),
					$value->getDiWikiPage()
				);

				continue;
			}

			$property = $this->mediaWikiNsContentMapper->findPropertyForId( $key );

			// No mapping, no annotation
			if ( $property === null ) {
				continue;
			}

			foreach ( $values as $value ) {

				$dataValue = $this->dataValueFactory->newPropertyValue(
						$property,
						trim( $value ),
						false,
						$subject
					);

				$subobject->addDataValue( $dataValue );
			}
		}

		if ( $subobject->getSemanticData()->isEmpty() ) {
			return;
		}

		$this->parserData->getSemanticData()->addPropertyObjectValue(
			new DIProperty( PropertyRegistry::SCI_CITE ),
			$subobject->getContainer()
		);

		$this->parserData->getSubject()->setContextReference( 'scitep:' . uniqid() );
		$this->parserData->pushSemanticDataToParserOutput();
	}

	private function createErrorMessageFor( $messageKey, $arg1 = '',  $arg2 = '' ) {
		return \Html::rawElement(
			'div',
			array( 'class' => 'smw-callout smw-callout-error' ),
			wfMessage( $messageKey, $arg1, $arg2 )->inContentLanguage()->text()
		);
	}

}
