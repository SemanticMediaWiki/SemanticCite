<?php

namespace SCI\Tests\FilteredMetadata;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use GuzzleHttpRequest;
use MediaWiki\Http\HttpRequestFactory;
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
			new MediaWikiHttpRequest( $this->newHttpRequestFactory(), new HashBagOStuff(), self::publicResolver() )
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

		$instance = new MediaWikiHttpRequest( $httpRequestFactory, new HashBagOStuff(), self::publicResolver() );

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

		$instance = new MediaWikiHttpRequest( $httpRequestFactory, new HashBagOStuff(), self::publicResolver() );

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

		$instance = new MediaWikiHttpRequest( $httpRequestFactory, new HashBagOStuff(), self::publicResolver() );

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

		$instance = new MediaWikiHttpRequest( $httpRequestFactory, new HashBagOStuff(), self::publicResolver() );

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

	/**
	 * @dataProvider redirectStatusProvider
	 */
	public function testFollowedRedirectReturnsOnlyTheFinalResponseBody( int $redirectStatus ) {
		$instance = $this->newInstanceServedBy( new MockHandler( [
			new Response( $redirectStatus, [ 'Location' => 'https://api.example.org/final' ], '<html>Redirect</html>' ),
			new Response( 200, [], '{"ok":1}' ),
		] ) );

		$instance->setOption( HttpRequest::URL, 'https://doi.example.org/10.1/x' );
		$instance->setOption( HttpRequest::FOLLOW_LOCATION, true );

		$this->assertSame( '{"ok":1}', $instance->execute() );
	}

	public static function redirectStatusProvider(): iterable {
		yield 'multiple choices' => [ 300 ];
		yield 'moved permanently' => [ 301 ];
		yield 'found' => [ 302 ];
		yield 'see other' => [ 303 ];
		yield 'temporary redirect' => [ 307 ];
		yield 'permanent redirect' => [ 308 ];
	}

	/**
	 * Uses MediaWiki core's request class, so that core's redirect and body
	 * handling is part of what is tested; the handler stands in for the network.
	 */
	private function newInstanceServedBy( MockHandler $server, ?callable $resolveHost = null ): MediaWikiHttpRequest {
		$httpRequestFactory = $this->createStub( HttpRequestFactory::class );
		$httpRequestFactory->method( 'create' )->willReturnCallback(
			static fn ( string $url, array $options ) => new GuzzleHttpRequest(
				$url,
				$options + [ 'timeout' => 1, 'connectTimeout' => 1, 'handler' => $server ]
			)
		);

		return new MediaWikiHttpRequest( $httpRequestFactory, new HashBagOStuff(), $resolveHost ?? self::publicResolver() );
	}

	private static function publicResolver(): callable {
		return static fn ( string $host ): array => [ '93.184.216.34' ];
	}

	/**
	 * @dataProvider relativeLocationProvider
	 */
	public function testFollowedRedirectResolvesARelativeLocation( string $location, string $expectedUrl ) {
		$server = new MockHandler( [
			new Response( 308, [ 'Location' => $location ] ),
			new Response( 200, [], 'OK' ),
		] );
		$instance = $this->newInstanceServedBy( $server );

		$instance->setOption( HttpRequest::URL, 'https://search.example.org:8443/oclc/42' );
		$instance->setOption( HttpRequest::FOLLOW_LOCATION, true );
		$instance->execute();

		$this->assertSame( $expectedUrl, (string)$server->getLastRequest()->getUri() );
	}

	public static function relativeLocationProvider(): iterable {
		yield 'absolute path' => [ '/title/42', 'https://search.example.org:8443/title/42' ];
		yield 'relative path' => [ 'title/42', 'https://search.example.org:8443/oclc/title/42' ];
	}

	public function testRedirectToAFailedResponseReportsTheError() {
		$instance = $this->newInstanceServedBy( new MockHandler( [
			new Response( 302, [ 'Location' => 'https://api.example.org/missing' ] ),
			new Response( 404, [], 'Not Found' ),
		] ) );

		$instance->setOption( HttpRequest::URL, 'https://doi.example.org/10.1/x' );
		$instance->setOption( HttpRequest::FOLLOW_LOCATION, true );
		$instance->execute();

		$this->assertNotSame( '', $instance->getLastError() );
	}

	public function testFollowedRedirectKeepsTheRequestHeaders() {
		$server = new MockHandler( [
			new Response( 302, [ 'Location' => 'https://api.example.org/final' ] ),
			new Response( 200, [], 'OK' ),
		] );
		$instance = $this->newInstanceServedBy( $server );

		$instance->setOption( HttpRequest::URL, 'https://doi.example.org/10.1/x' );
		$instance->setOption( HttpRequest::FOLLOW_LOCATION, true );
		$instance->setOption( HttpRequest::HEADERS, [ 'Accept: application/vnd.citationstyles.csl+json' ] );
		$instance->execute();

		$this->assertSame(
			'application/vnd.citationstyles.csl+json',
			$server->getLastRequest()->getHeaderLine( 'Accept' )
		);
	}

	public function testUnfollowedRedirectReturnsTheRedirectResponseBody() {
		$instance = $this->newInstanceServedBy( new MockHandler( [
			new Response( 302, [ 'Location' => 'https://api.example.org/final' ], 'Moved' ),
			new Response( 200, [], '{"ok":1}' ),
		] ) );

		$instance->setOption( HttpRequest::URL, 'https://doi.example.org/10.1/x' );

		$this->assertSame( 'Moved', $instance->execute() );
	}

	public function testEndlessRedirectsAreAbandonedWithAnError() {
		$server = new MockHandler(
			array_fill( 0, 50, new Response( 302, [ 'Location' => 'https://doi.example.org/again' ] ) )
		);
		$instance = $this->newInstanceServedBy( $server );

		$instance->setOption( HttpRequest::URL, 'https://doi.example.org/10.1/x' );
		$instance->setOption( HttpRequest::FOLLOW_LOCATION, true );
		$instance->execute();

		$this->assertNotSame( '', $instance->getLastError() );
		$this->assertGreaterThan( 0, $server->count(), 'The server was still redirecting when the client gave up' );
	}

	public function testRedirectToAPrivateAddressIsNotFollowed() {
		$server = new MockHandler( [
			new Response( 302, [ 'Location' => 'http://127.0.0.1:9901/internal' ], 'moved' ),
			new Response( 200, [], 'INTERNAL-ONLY' ),
		] );
		$instance = $this->newInstanceServedBy( $server );

		$instance->setOption( HttpRequest::URL, 'https://doi.example.org/10.1/x' );
		$instance->setOption( HttpRequest::FOLLOW_LOCATION, true );

		$this->assertSame( '', $instance->execute() );
		$this->assertNotSame( '', $instance->getLastError() );
	}

	public function testRedirectToAnIpv4MappedIpv6LoopbackIsNotFollowed() {
		$server = new MockHandler( [
			new Response( 302, [ 'Location' => 'http://[::ffff:7f00:1]/internal' ], 'moved' ),
			new Response( 200, [], 'INTERNAL-ONLY' ),
		] );
		$instance = $this->newInstanceServedBy( $server );

		$instance->setOption( HttpRequest::URL, 'https://doi.example.org/10.1/x' );
		$instance->setOption( HttpRequest::FOLLOW_LOCATION, true );

		$this->assertSame( '', $instance->execute() );
		$this->assertNotSame( '', $instance->getLastError() );
	}

	public function testRedirectToAHostResolvingToAPrivateAddressIsNotFollowed() {
		$server = new MockHandler( [
			new Response( 302, [ 'Location' => 'https://metadata.example.test/' ], 'moved' ),
			new Response( 200, [], 'INTERNAL-ONLY' ),
		] );
		$instance = $this->newInstanceServedBy(
			$server,
			static fn ( string $host ): array => [ '169.254.169.254' ]
		);

		$instance->setOption( HttpRequest::URL, 'https://doi.example.org/10.1/x' );
		$instance->setOption( HttpRequest::FOLLOW_LOCATION, true );

		$this->assertSame( '', $instance->execute() );
		$this->assertNotSame( '', $instance->getLastError() );
	}

	public function testRedirectToAHostResolvingToAnIpv4MappedLoopbackIsNotFollowed() {
		$server = new MockHandler( [
			new Response( 302, [ 'Location' => 'https://metadata.example.test/' ], 'moved' ),
			new Response( 200, [], 'INTERNAL-ONLY' ),
		] );
		$instance = $this->newInstanceServedBy(
			$server,
			static fn ( string $host ): array => [ '::ffff:7f00:1' ]
		);

		$instance->setOption( HttpRequest::URL, 'https://doi.example.org/10.1/x' );
		$instance->setOption( HttpRequest::FOLLOW_LOCATION, true );

		$this->assertSame( '', $instance->execute() );
		$this->assertNotSame( '', $instance->getLastError() );
	}

}
