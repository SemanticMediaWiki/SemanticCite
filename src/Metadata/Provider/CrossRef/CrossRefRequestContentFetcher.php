<?php

namespace SCI\Metadata\Provider\CrossRef;

use SCI\Metadata\HttpRequestContentFetcher;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class CrossRefRequestContentFetcher extends HttpRequestContentFetcher {

	/**
	 * @see http://crosscite.org/cn/
	 */
	const DOI_CONTENT_API = "http://dx.doi.org/";

	/**
	 * @since 1.0
	 *
	 * @param string $doi
	 *
	 * @return string
	 */
	public function fetchBibtexFor( $doi ) {

		$this->httpRequest->setOption( CURLOPT_FOLLOWLOCATION, true );

		$this->httpRequest->setOption( CURLOPT_RETURNTRANSFER, true );
		$this->httpRequest->setOption( CURLOPT_FAILONERROR, true );
		$this->httpRequest->setOption( CURLOPT_URL, self::DOI_CONTENT_API . $doi );

		$this->httpRequest->setOption( CURLOPT_HTTPHEADER, array(
			'Accept: application/x-bibtex',
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
		) );

		return $this->httpRequest->execute();
	}

	/**
	 * @since 1.0
	 *
	 * @param string $doi
	 *
	 * @return string
	 */
	public function fetchCiteprocJsonFor( $doi ) {

		$this->httpRequest->setOption( CURLOPT_FOLLOWLOCATION, true );

		$this->httpRequest->setOption( CURLOPT_RETURNTRANSFER, true );
		$this->httpRequest->setOption( CURLOPT_FAILONERROR, true );
		$this->httpRequest->setOption( CURLOPT_URL, self::DOI_CONTENT_API . $doi );

		$this->httpRequest->setOption( CURLOPT_HTTPHEADER, array(
			'Accept: application/vnd.citationstyles.csl+json',
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
		) );

		return $this->httpRequest->execute();
	}

}
