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

		$provider = array();

		// http://artis.imag.fr/~Xavier.Decoret/resources/xdkbibtex/bibtex_summary.html
		$provider[] = array(
			 'AA BB',
			array(
				'AA BB'
			)
		);

		$provider[] = array(
			 'AA',
			array(
				'AA'
			)
		);

		$provider[] = array(
			 'AA bb',
			array(
				'AA bb'
			)
		);

		$provider[] = array(
			 'AA bb CC',
			array(
				'AA bb CC'
			)
		);

		$provider[] = array(
			 'AA bb CC dd EE',
			array(
				'AA CC bb dd EE'
			)
		);

		$provider[] = array(
			 'AA 1B cc dd',
			array(
				'AA 1B cc dd'
			)
		);

		$provider[] = array(
			 'AA {b}B cc dd',
			array(
				'AA b'
			)
		);

		$provider[] = array(
			 'AA \BB{b} cc dd',
			array(
				'AA \BB b'
			)
		);

		$provider[] = array(
			 'bb CC dd EE, AA',
			array(
				'AA bb CC dd EE'
			)
		);

		$provider[] = array(
			 'bb CC,XX, AA',
			array(
				'AA bb CC XX'
			)
		);

		$provider[] = array(
			 'Einstein, Albert and Podolsky, Boris and Rosen, Nathan',
			array(
				'Albert Einstein',
				'Boris Podolsky',
				'Nathan Rosen'
			)
		);

		$provider[] = array(
			 'Leung, David W and Cachianes, George and Kuang, Wun-Jing and Goeddel, David V and Ferrara, Napoleone',
			array(
				'David W Leung',
				'George Cachianes',
				'Wun-Jing Kuang',
				'David V Goeddel',
				'Napoleone Ferrara'
			)
		);

		$provider[] = array(
			 'S. Zhang and C. Zhu and J. K. O. Sin and P. K. T. Mok',
			array(
				'S Zhang',
				'C Zhu',
				'J K O Sin',
				'P K T Mok'
			)
		);

		$provider[] = array(
			 'R. M. A. Dawson and Z. Shen and D. A. Furst and
                   S. Connor and J. Hsu and M. G. Kane and R. G. Stewart and
                   A. Ipri and C. N. King and P. J. Green and R. T. Flegal
                   and S. Pearson and W. A. Barrow and E. Dickey and K. Ping
                   and C. W. Tang and S. Van. Slyke and
                   F. Chen and J. Shi and J. C. Sturm and M. H. Lu',
			array(
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
			)
		);

		// van
		$provider[] = array(
			 'van den Bout, D. E.',
			array(
				'D E van den Bout'
			)
		);

		// Jr.
		$provider[] = array(
			 'Osgood, Jr., R. M.',
			array(
				'R M Osgood Jr.'
			)
		);


		return $provider;
	}

}
