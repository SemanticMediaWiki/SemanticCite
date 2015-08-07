<?php

namespace SCI\Tests\Bibtex;

use SCI\Bibtex\BibtexParser;

/**
 * @covers \SCI\Bibtex\BibtexParser
 * @group semantic-cite
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class BibtexParserTest extends \PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'\SCI\Bibtex\BibtexParser',
			new BibtexParser()
		);
	}

	/**
	 * @dataProvider bibtextProvider
	 */
	public function testParse( $bibtex, $expected ) {

		$instance = new BibtexParser();

		$this->assertEquals(
			$expected,
			$instance->parse( $bibtex )
		);
	}

	public function bibtextProvider() {

		$provider[] = array(
			"@article{einstein1935can,
			  title={Can quantum-mechanical description of physical reality be considered complete?},
			  author={Einstein, Albert and Podolsky, Boris and Rosen, Nathan},
			  journal={Physical review},
			  volume={47},
			  number={10},
			  pages={777},
			  year={1935},
			  publisher={APS}
			}",
			array(
				'type'      => 'article',
				'reference' => 'einstein1935can',
				'title'     => 'Can quantum-mechanical description of physical reality be considered complete?',
				'author'    => 'Einstein, Albert and Podolsky, Boris and Rosen, Nathan',
				'journal'   => 'Physical review',
				'volume'    => '47',
				'number'    => '10',
				'pages'     => '777',
				'year'      => '1935',
				'publisher' => 'APS'
			)
		);

		$provider[] = array(
			"@book{marx2004capital,
			  title={Capital (Volume 1: A Critique of Political Economy): A Critique of Political Economy},
			  author={Marx, Karl},
			  year={2004},
			  publisher={Digireads. com Publishing}
			}",
			array(
				'type'      => 'book',
				'reference' => 'marx2004capital',
				'title'     => 'Capital (Volume 1: A Critique of Political Economy): A Critique of Political Economy',
				'author'    => 'Marx, Karl',
				'year'      => '2004',
				'publisher' => 'Digireads. com Publishing'
			)
		);

		#2 No reference
		$provider[] = array(
			"@article{,
			  title={Vascular endothelial growth factor is a secreted angiogenic mitogen},
			  author={Leung, David W and Cachianes, George and Kuang, Wun-Jing and Goeddel, David V and Ferrara, Napoleone},
			  journal={Science},
			  volume={246},
			  number={4935},
			  pages={1306--1309},
			  year={1989},
			  publisher={American Association for the Advancement of Science}
			}",
			array(
				'type'      => 'article',
				'reference' => '',
				'title'     => 'Vascular endothelial growth factor is a secreted angiogenic mitogen',
				'author'    => 'Leung, David W and Cachianes, George and Kuang, Wun-Jing and Goeddel, David V and Ferrara, Napoleone',
				'journal'   => 'Science',
				'volume'    => '246',
				'number'    => '4935',
				'pages'     => '1306--1309',
				'year'      => '1989',
				'publisher' => 'American Association for the Advancement of Science'
			)
		);

		#3 No reference
		$provider[] = array(
			"@inproceedings{clean,
			  author = {First Author and Author, Second},
			  title = {Pr{\"a}diktive Teilbandcodierung mit Vektorquantisierung f{\"u}r hochqualitative Audiosignale},
			  booktitle = {8. ITG-Fachtagung H{\"o}rrundfunk},
			  year = {1988},
			  month = nov,
			  pages = {252--256},
			  abstract = {Some Abstract, across
			two lines},
			}",
			array(
				'type'      => 'inproceedings',
				'reference' => 'clean',
				'author'    => 'First Author and Author, Second',
				'title'     => 'Pr{"a}diktive Teilbandcodierung mit Vektorquantisierung f{"u}r hochqualitative Audiosignale',
				'booktitle' => '8. ITG-Fachtagung H{"o}rrundfunk',
				'year'      => '1988',
				'month'     => 'nov',
				'pages'     => '252--256',
				'abstract'  => "Some Abstract, across
			two lines"
			)
		);

		#4 invalid/unprocessable content format
		$provider[] = array(
			"foo",
			array()
		);

		$provider[] = array(
			"@article{}",
			array()
		);

		return $provider;
	}

}
