<?php

namespace SCI;

use SMW\DataItems\Property;
use SMW\DataItems\WikiPage;
use SMW\HashBuilder;
use SMW\Services\ServicesFactory as ApplicationFactory;
use SMW\SQLStore\ChangeOp\ChangeOp;
use SMW\Store;

/**
 * If a citation text was altered then lookup related references on pages used
 * and schedule a dispatch update job.
 *
 * @license GPL-2.0-or-later
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
	 * @var bool
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
	 * @param bool $enabledUpdateJobState
	 */
	public function setEnabledUpdateJobState( $enabledUpdateJobState ) {
		$this->enabledUpdateJobState = $enabledUpdateJobState;
	}

	/**
	 * @since  1.0
	 *
	 * @param WikiPage $subject
	 * @param ChangeOp $changeOp
	 *
	 * @return bool
	 */
	public function dispatchUpdateJobFor( WikiPage $subject, ChangeOp $changeOp ) {
		if ( !$this->enabledUpdateJobState ) {
			return false;
		}

		$tableName = $this->store->getPropertyTableInfoFetcher()->findTableIdForProperty(
			new Property( PropertyRegistry::SCI_CITE_TEXT )
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

		$dataItems = $this->store->getObjectIds()->getDataItemsFromList( $subjectIdList );
		$referenceBacklinks = [];

		foreach ( $dataItems as $dataItem ) {
			$referenceBacklinks += $this->referenceBacklinksLookup->findReferenceBacklinksFor(
				$this->referenceBacklinksLookup->tryToFindCitationKeyFor( $dataItem )
			);
		}

		$targetBatch = [];

		foreach ( $referenceBacklinks as $referenceBacklink ) {
			$targetBatch[HashBuilder::getHashIdForDiWikiPage( $referenceBacklink )] = true;
		}

		return $targetBatch;
	}

}
