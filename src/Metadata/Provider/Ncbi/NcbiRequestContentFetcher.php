<?php

namespace SCI\Metadata\Provider\Ncbi;

use SCI\Metadata\HttpRequestContentFetcher;
use Onoi\HttpRequest\HttpRequest;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class NcbiRequestContentFetcher extends HttpRequestContentFetcher {

	/**
	 * @see http://www.ncbi.nlm.nih.gov/books/NBK25501/
	 * @see http://www.ncbi.nlm.nih.gov/books/NBK1058/
	 */
	const SUMMARY_URL = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi?";
	const FETCH_URL = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?";

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @since 1.0
	 *
	 * @param HttpRequest $httpRequest
	 * @param string $type
	 */
	public function __construct( HttpRequest $httpRequest, $type ) {
		$this->httpRequest = $httpRequest;
		$this->type = $type;
	}

	/**
	 * @see http://www.ncbi.nlm.nih.gov/books/NBK25497/table/chapter2.T._entrez_unique_identifiers_ui/
	 */
	public function getType() {
		return $this->type;
	}

	public function fetchSummaryFor( $id ) {

		$this->httpRequest->setOption( CURLOPT_RETURNTRANSFER, true ); // put result into variable
		$this->httpRequest->setOption( CURLOPT_FAILONERROR, true );
		$this->httpRequest->setOption( CURLOPT_URL, self::SUMMARY_URL );

		$this->httpRequest->setOption( CURLOPT_HTTPHEADER, array(
			'Accept: application/sparql-results+json, application/rdf+json, application/json',
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
		) );

		$this->httpRequest->setOption( CURLOPT_POST, true );

		$this->httpRequest->setOption(
			CURLOPT_POSTFIELDS,
			"db={$this->type}&retmode=json&id=" . $id
		);

		return $this->httpRequest->execute();
	}

	public function fetchAbstractFor( $id ) {
		// http://www.fredtrotter.com/2014/11/14/hacking-on-the-pubmed-api/

		$this->httpRequest->setOption( CURLOPT_RETURNTRANSFER, true ); // put result into variable
		$this->httpRequest->setOption( CURLOPT_FAILONERROR, true );
		$this->httpRequest->setOption( CURLOPT_URL, self::FETCH_URL );

		$this->httpRequest->setOption( CURLOPT_HTTPHEADER, array(
			'Accept: application/sparql-results+xml, application/rdf+xml, application/xml',
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8'
		) );

		$this->httpRequest->setOption( CURLOPT_POST, true );

		$this->httpRequest->setOption(
			CURLOPT_POSTFIELDS,
			"db={$this->type}&rettype=abstract&retmode=xml&id=" . $id
		);

		return $this->httpRequest->execute();
	}

}
