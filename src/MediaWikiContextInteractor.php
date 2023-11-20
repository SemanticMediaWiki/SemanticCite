<?php

namespace SCI;

use IContextSource;
use MediaWiki\MediaWikiServices;

/**
 * Helper class to avoid making objects depend on the IContextSource and instead
 * provide dedicated methods to access a specific aspect of the context.
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 * @reviewer thomas-topway-it
 */
class MediaWikiContextInteractor {

	/**
	 * @var IContextSource
	 */
	private $context;
	
	/**
	 * @var revisionLookup
	 */
	private $revisionLookup;

	/**
	 * @since 1.0
	 *
	 * @param IContextSource $context
	 */
	public function __construct( IContextSource $context ) {
		$this->context = $context;
		$this->revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
	}

	/**
	 * @see RawAction::getOldId
	 *
	 * @since 1.0
	 *
	 * @return int
	 */
	public function getOldId() {
		$oldid = $this->context->getRequest()->getInt( 'oldid' );
		$title = $this->context->getTitle();

		switch ( $this->context->getRequest()->getText( 'direction' ) ) {
			case 'next':
				# output next revision, or nothing if there isn't one
				$nextid = 0;
				if ( $oldid ) {
					$revision = $this->revisionLookup->getRevisionById( $oldid );
					$nextRevision = $this->revisionLookup->getNextRevision( $revision );
					if ( $nextRevision ) {
						$nextid = $nextRevision->getId();
					}
				}
				$oldid = $nextid ?: -1;
				break;
			case 'prev':
				# output previous revision, or nothing if there isn't one
				$previd = 0;
				if ( !$oldid ) {
					# get the current revision so we can get the penultimate one
					$oldid = $title->getLatestRevID();
				}
				if ( $oldid ) {
					$revision = $this->revisionLookup->getRevisionById( $oldid );
					$previousRevision = $this->revisionLookup->getPreviousRevision( $revision );
					if ( $previousRevision ) {
						$previd = $previousRevision->getId();
					}
				}
				$oldid = $previd ?: -1;
				break;
			case 'cur':
				$oldid = 0;
				break;
		}

		return (int)$oldid;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $magicword
	 *
	 * @return boolean
	 */
	public function hasMagicWord( $magicword ) {

		$outputPage = $this->context->getOutput();

		if ( isset( $outputPage->smwmagicwords ) && in_array( $magicword, $outputPage->smwmagicwords ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $action
	 *
	 * @return boolean
	 */
	public function hasAction( $action ) {
		return \Action::getActionName( $this->context ) === $action;
	}

	/**
	 * @since 1.0
	 *
	 * @return Title
	 */
	public function getTitle() {
		return $this->context->getTitle();
	}

	/**
	 * @since 1.0
	 *
	 * @return string
	 */
	public function getLanguageCode() {
		return $this->context->getLanguage()->getCode();
	}

}
