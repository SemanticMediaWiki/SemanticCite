<?php

namespace SCI;

use SMW\Subobject;
use SMW\DIWikiPage;
use SMW\DIProperty;
use SMWDIContainer as DIContainer;
use SMWContainerSemanticData as ContainerSemanticData;
use SMW\DataValueFactory;
use SMW\SemanticData;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CitationMeta {

	/**
	 * @var CitationReferencePositionJournal
	 */
	private $citationReferencePositionJournal = null;

	/**
	 * @var boolean
	 */
	private $isEnabled = false;

	/**
	 * @since 1.1
	 *
	 * @param CitationReferencePositionJournal $citationReferencePositionJournal
	 */
	public function __construct( CitationReferencePositionJournal $citationReferencePositionJournal ) {
		$this->citationReferencePositionJournal = $citationReferencePositionJournal;
	}

	/**
	 * @since 1.1
	 *
	 * @param $isEnabled boolean
	 */
	public function setEnabledState( $isEnabled ) {
		$this->isEnabled = (bool)$isEnabled;
	}

	/**
	 * @since 1.1
	 *
	 * @param SemanticData $semanticData
	 *
	 * @return boolean
	 */
	public function addMetaRecordToSemanticData( SemanticData $semanticData ) {

		if ( !$this->isEnabled ) {
			return false;
		}

		$containerSemanticData = $this->tryToCollectCitationFrequency(
			$semanticData->getSubject()
		);

		if ( $containerSemanticData === null || $containerSemanticData->isEmpty() ) {
			return false;
		}

		$semanticData->addPropertyObjectValue(
			new DIProperty( PropertyRegistry::SCI_CITE_META ),
			new DIContainer( $containerSemanticData )
		);

		return true;
	}

	private function tryToCollectCitationFrequency( DIWikiPage $subject ) {

		$journal = $this->citationReferencePositionJournal->getJournalBySubject( $subject );

		if ( $journal === array() || !isset( $journal['reference-list'] ) ) {
			return null;
		}

		$subWikiPage = new DIWikiPage(
			$subject->getDBkey(),
			$subject->getNamespace(),
			$subject->getInterwiki(),
			'sci.meta'
		);

		$containerSemanticData = new ContainerSemanticData( $subWikiPage );

		foreach ( $journal['reference-list'] as $hash => $citationKey ) {

			if ( !isset( $journal['reference-pos'][$hash] ) ) {
				continue;
			}

			$this->addFrequencyRecord(
				$containerSemanticData,
				$subject,
				$citationKey,
				count( $journal['reference-pos'][$hash] )
			);
		}

		return $containerSemanticData;
	}

	private function addFrequencyRecord( $containerSemanticData, $subject, $citationKey, $count ) {

		$dataValue = DataValueFactory::getInstance()->newPropertyObjectValue(
			new DIProperty( PropertyRegistry::SCI_CITE_FREQUENCY ),
			$citationKey . ';' . $count,
			false,
			$subject
		);

		$containerSemanticData->addDataValue( $dataValue );
	}

}
