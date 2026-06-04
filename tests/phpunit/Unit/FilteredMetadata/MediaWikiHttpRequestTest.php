<?php

namespace SCI\Tests\FilteredMetadata;

use MediaWiki\Status\Status;
use SCI\FilteredMetadata\HttpRequest;
use SCI\FilteredMetadata\MediaWikiHttpRequest;
use Wikimedia\ObjectCache\HashBagOStuff;

/**
 * @covers \SCI\FilteredMetadata\MediaWikiHttpRequest
 * @group semantic-cite
 *
 * @license GPL-2.0-or-later
 * @since 7.0
 *
 * @author mwjames
 */
class MediaWikiHttpRequestTest extends \PHPUnit\Framework\TestCase {

	private function newHttpRequestFactory() {
		return $this->getMockBuilder( '\MediaWiki\Http\HttpRequestFactory' )
			->disableOriginalConstructor()
			->getMock();
	}

	private function newMwHttpRequest( $status, $content, $httpCode = 200 ) {
		$mwRequest = $this->getMockBuilder( '\MWHttpRequest' )
			->disableOriginalConstructor()
			->getMock();

		$mwRequest->method( 'execute' )->willReturn( $status );
		$mwRequest->method( 'getContent' )->willReturn( $content );
		$mwRequest->method( 'getStatus' )->willReturn( $httpCode );

		return $mwRequest;
	}

	private function newStatus( $isOK ) {
		$status = $this->createMock( Status::class );
		$status->method( 'isOK' )->willReturn( $isOK );

		return $status;
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			MediaWikiHttpRequest::class,
			new MediaWikiHttpRequest( $this->newHttpRequestFactory(), new HashBagOStuff() )
		);
	}

	public function testExecuteReturnsContentAndCachesSuccessfulResponse() {
		$mwRequest = $this->newMwHttpRequest( $this->newStatus( true ), '{"ok":1}' );

		$httpRequestFactory = $this->newHttpRequestFactory();

		// create() is invoked only once: the second execute is served from the
		// response cache.
		$httpRequestFactory->expects( $this->once() )
			->method( 'create' )
			->willReturn( $mwRequest );

		$instance = new MediaWikiHttpRequest( $httpRequestFactory, new HashBagOStuff() );

		$instance->setOption( HttpRequest::URL, 'https://example.org/x' );
		$this->assertSame( '{"ok":1}', $instance->execute() );
		$this->assertFalse( $instance->isCached() );
		$this->assertSame( '', $instance->getLastError() );

		$instance->setOption( HttpRequest::URL, 'https://example.org/x' );
		$this->assertSame( '{"ok":1}', $instance->execute() );
		$this->assertTrue( $instance->isCached() );
	}

	public function testFailedResponseIsNotCachedAndReportsError() {
		$mwRequest = $this->newMwHttpRequest( $this->newStatus( false ), '', 404 );

		$httpRequestFactory = $this->newHttpRequestFactory();

		// create() is invoked on every execute because a failed response is
		// never cached.
		$httpRequestFactory->expects( $this->exactly( 2 ) )
			->method( 'create' )
			->willReturn( $mwRequest );

		$instance = new MediaWikiHttpRequest( $httpRequestFactory, new HashBagOStuff() );

		$instance->setOption( HttpRequest::URL, 'https://example.org/missing' );
		$instance->execute();
		$this->assertNotSame( '', $instance->getLastError() );
		$this->assertFalse( $instance->isCached() );

		$instance->setOption( HttpRequest::URL, 'https://example.org/missing' );
		$instance->execute();
		$this->assertFalse( $instance->isCached() );
	}

	public function testPostOptionsArePassedToTheFactory() {
		$mwRequest = $this->newMwHttpRequest( $this->newStatus( true ), 'OK' );

		$httpRequestFactory = $this->newHttpRequestFactory();

		$httpRequestFactory->expects( $this->once() )
			->method( 'create' )
			->with(
				'https://eutils.example.org/',
				$this->callback( static function ( $options ) {
					return ( $options['method'] ?? null ) === 'POST'
						&& ( $options['postData'] ?? null ) === 'db=pubmed&id=1';
				} ),
				$this->anything()
			)
			->willReturn( $mwRequest );

		$instance = new MediaWikiHttpRequest( $httpRequestFactory, new HashBagOStuff() );

		$instance->setOption( HttpRequest::URL, 'https://eutils.example.org/' );
		$instance->setOption( HttpRequest::POST, true );
		$instance->setOption( HttpRequest::POST_FIELDS, 'db=pubmed&id=1' );

		$this->assertSame( 'OK', $instance->execute() );
	}

	public function testHeadersAreParsedAndForwarded() {
		$mwRequest = $this->newMwHttpRequest( $this->newStatus( true ), 'OK' );

		$headers = [];

		// Two well-formed headers are forwarded; a header without a colon is
		// skipped, so setHeader() is called exactly twice.
		$mwRequest->expects( $this->exactly( 2 ) )
			->method( 'setHeader' )
			->willReturnCallback( static function ( $name, $value ) use ( &$headers ) {
				$headers[$name] = $value;
			} );

		$httpRequestFactory = $this->newHttpRequestFactory();
		$httpRequestFactory->method( 'create' )->willReturn( $mwRequest );

		$instance = new MediaWikiHttpRequest( $httpRequestFactory, new HashBagOStuff() );

		$instance->setOption( HttpRequest::URL, 'https://example.org/x' );
		$instance->setOption( HttpRequest::HEADERS, [
			'Accept: application/json',
			'Content-Type: text/plain; charset=UTF-8',
			'MalformedHeaderWithoutColon',
		] );

		$this->assertSame( 'OK', $instance->execute() );
		$this->assertSame( 'application/json', $headers['Accept'] );
		$this->assertSame( 'text/plain; charset=UTF-8', $headers['Content-Type'] );
	}

}
