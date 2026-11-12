<?php

namespace SCI\Tests\Integration\Specials;

use MediaWiki\User\User;
use PermissionsError;
use SCI\Specials\SpecialFindCitableMetadata;
use SpecialPageTestBase;

/**
 * Integration, for MediaWiki's permission check on the special page, and the
 * database that holds the test users.
 *
 * @covers \SCI\Specials\SpecialFindCitableMetadata
 * @group semantic-cite
 * @group Database
 *
 * @license GPL-2.0-or-later
 */
class FindCitableMetadataAccessTest extends SpecialPageTestBase {

	protected function newSpecialPage(): SpecialFindCitableMetadata {
		return new SpecialFindCitableMetadata();
	}

	public function testShowsTheSearchFormToAUserWithTheMetadataSearchRight(): void {
		[ $html ] = $this->executeSpecialPage( '', null, null, $this->userWithRights( [ 'sci-metadatasearch' ] ) );

		$this->assertStringContainsString( '(sci-metadata-search-form-submit)', $html );
	}

	public function testRefusesAUserWithoutTheMetadataSearchRight(): void {
		$user = $this->userWithRights( [] );

		$this->expectException( PermissionsError::class );
		$this->executeSpecialPage( '', null, null, $user );
	}

	/**
	 * @param string[] $rights
	 */
	private function userWithRights( array $rights ): User {
		$user = $this->getTestUser()->getUser();
		$this->overrideUserPermissions( $user, $rights );

		return $user;
	}

}
