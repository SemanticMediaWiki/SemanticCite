<?php

namespace SCI\Tests\Bibtex;

use SCI\Bibtex\BibtexAuthorListParser;

/**
 * @covers \SCI\Bibtex\BibtexAuthorListParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class BibtexAuthorListParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\Bibtex\BibtexAuthorListParser',
			new BibtexAuthorListParser()
		);
	}

	/**
	 * @dataProvider authorListProvider
	 */
	public function testParse( $authorList, $expected ) {

		$instance = new BibtexAuthorListParser();

		$this->assertEquals(
			$expected,
			$instance->parse( $authorList )
		);
	}

	public function authorListProvider() {

		$provider = [];

		// http://artis.imag.fr/~Xavier.Decoret/resources/xdkbibtex/bibtex_summary.html
		$provider[] = [
			 'AA BB',
			[
				'AA BB'
			]
		];

		$provider[] = [
			 'AA',
			[
				'AA'
			]
		];

		$provider[] = [
			 'AA bb',
			[
				'AA bb'
			]
		];

		$provider[] = [
			 'AA bb CC',
			[
				'AA bb CC'
			]
		];

		$provider[] = [
			 'AA bb CC dd EE',
			[
				'AA CC bb dd EE'
			]
		];

		$provider[] = [
			 'AA 1B cc dd',
			[
				'AA 1B cc dd'
			]
		];

		$provider[] = [
			 'AA {b}B cc dd',
			[
				'AA b'
			]
		];

		$provider[] = [
			 'AA \BB{b} cc dd',
			[
				'AA \BB b'
			]
		];

		$provider[] = [
			 'bb CC dd EE, AA',
			[
				'AA bb CC dd EE'
			]
		];

		$provider[] = [
			 'bb CC,XX, AA',
			[
				'AA bb CC XX'
			]
		];

		$provider[] = [
			 'Einstein, Albert and Podolsky, Boris and Rosen, Nathan',
			[
				'Albert Einstein',
				'Boris Podolsky',
				'Nathan Rosen'
			]
		];

		$provider[] = [
			 'Leung, David W and Cachianes, George and Kuang, Wun-Jing and Goeddel, David V and Ferrara, Napoleone',
			[
				'David W Leung',
				'George Cachianes',
				'Wun-Jing Kuang',
				'David V Goeddel',
				'Napoleone Ferrara'
			]
		];

		$provider[] = [
			 'S. Zhang and C. Zhu and J. K. O. Sin and P. K. T. Mok',
			[
				'S Zhang',
				'C Zhu',
				'J K O Sin',
				'P K T Mok'
			]
		];

		$provider[] = [
			 'R. M. A. Dawson and Z. Shen and D. A. Furst and
                   S. Connor and J. Hsu and M. G. Kane and R. G. Stewart and
                   A. Ipri and C. N. King and P. J. Green and R. T. Flegal
                   and S. Pearson and W. A. Barrow and E. Dickey and K. Ping
                   and C. W. Tang and S. Van. Slyke and
                   F. Chen and J. Shi and J. C. Sturm and M. H. Lu',
			[
				'R M A Dawson',
				'Z Shen',
				'D A Furst',
				'S Connor',
				'J Hsu',
				'M G Kane',
				'R G Stewart',
				'A Ipri',
				'C N King',
				'P J Green',
				'Flegal R T',
				'S Pearson',
				'W A Barrow',
				'E Dickey',
				'Ping K',
				'C W Tang',
				'Van. S Slyke',
				'F Chen',
				'J Shi',
				'J C Sturm',
				'M H Lu'
			]
		];

		// van
		$provider[] = [
			 'van den Bout, D. E.',
			[
				'D E van den Bout'
			]
		];

		// Jr.
		$provider[] = [
			 'Osgood, Jr., R. M.',
			[
				'R M Osgood Jr.'
			]
		];


		return $provider;
	}

}
