<?php

namespace SCI;

use SMW\Store;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMW\Services\ServicesFactory as ApplicationFactory;
use SMW\HashBuilder;
use SMW\SQLStore\ChangeOp\ChangeOp;

/**
 * If a citation text was altered then lookup related references on pages used
 * and schedule a dispatch update job.
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CitationTextChangeUpdateJobDispatcher {

	/**
	 * @var Store
	 */
	private $store;

	/**
	 * @var ReferenceBacklinksLookup
	 */
	private $referenceBacklinksLookup;

	/**
	 * @var boolean
	 */
	private $enabledUpdateJobState = true;

	/**
	 * @since  1.0
	 *
	 * @param Store $store
	 * @param ReferenceBacklinksLookup $referenceBacklinksLookup
	 */
	public function __construct( Store $store, ReferenceBacklinksLookup $referenceBacklinksLookup ) {
		$this->store = $store;
		$this->referenceBacklinksLookup = $referenceBacklinksLookup;
	}

	/**
	 * @since  1.0
	 *
	 * @param boolean $enabledUpdateJobState
	 */
	public function setEnabledUpdateJobState( $enabledUpdateJobState ) {
		$this->enabledUpdateJobState = $enabledUpdateJobState;
	}

	/**
	 * @since  1.0
	 *
	 * @param DIWikiPage $subject
	 * @param ChangeOp $changeOp
	 *
	 * @return boolean
	 */
	public function dispatchUpdateJobFor( DIWikiPage $subject, ChangeOp $changeOp ) {

		if ( !$this->enabledUpdateJobState ) {
			return false;
		}

		$tableName = $this->store->getPropertyTableInfoFetcher()->findTableIdForProperty(
			new DIProperty( PropertyRegistry::SCI_CITE_TEXT )
		);

		$subjectIdList = $this->getSubjectListFrom(
			$changeOp->getOrderedDiffByTable( $tableName )
		);

		if ( ( $jobList = $this->getDispatchableTargetList( $subjectIdList ) ) === [] ) {
			return false;
		}

		$updateDispatcherJob = ApplicationFactory::getInstance()->newJobFactory()->newUpdateDispatcherJob(
			$subject->getTitle(),
			[
				'job-list' => $jobList
			]
		);

		$updateDispatcherJob->insert();

		return true;
	}

	private function getSubjectListFrom( array $orderedDiff ) {

		$subjectIdList = [];

		// Find out whether a cite text object was altered
		foreach ( $orderedDiff as $key => $value ) {

			if ( strpos( $key, 'sci_cite_text' ) === false ) {
				continue;
			}

			if ( !isset( $value['delete'] ) ) {
				$value['delete'] = [];
			}

			foreach ( $value['delete'] as $delete ) {
				$subjectIdList[] = $delete['s_id'];
			}

			if ( !isset( $value['insert'] ) ) {
				$value['insert'] = [];
			}

			foreach ( $value['insert'] as $insert ) {
				$subjectIdList[] = $insert['s_id'];
			}
		}

		return $subjectIdList;
	}

	private function getDispatchableTargetList( array $subjectIdList ) {

		if ( $subjectIdList === [] ) {
			return [];
		}

		$hashList = $this->store->getObjectIds()->getDataItemPoolHashListFor( $subjectIdList );
		$referenceBacklinks = [];

		foreach ( $hashList as $hash ) {
			$referenceBacklinks += $this->referenceBacklinksLookup->findReferenceBacklinksFor(
				$this->referenceBacklinksLookup->tryToFindCitationKeyFor( DIWikiPage::doUnserialize( $hash ) )
			);
		}

		$targetBatch = [];

		foreach ( $referenceBacklinks as $referenceBacklink ) {
			$targetBatch[HashBuilder::getHashIdForDiWikiPage( $referenceBacklink )] = true;
		}

		return $targetBatch;
	}

}
