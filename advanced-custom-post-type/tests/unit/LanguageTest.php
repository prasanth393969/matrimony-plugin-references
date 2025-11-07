<?php

namespace ACPT\Tests;

use ACPT\Utils\PHP\Language;

class LanguageTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_get_list()
	{
		$this->assertIsArray(Language::list());
	}

	/**
	 * @test
	 */
	public function can_get_label()
	{
		$this->assertEquals(Language::getLabel('en_US'), 'English US');
		$this->assertEquals(Language::getLabel('fr_FR'), 'French');
		$this->assertEquals(Language::getLabel('es_ES'), 'Spanish');
		$this->assertEquals(Language::getLabel('it_IT'), 'Italian');
	}

	/**
	 * @test
	 */
	public function can_get_code()
	{
		$this->assertEquals(Language::getCode('English US'), 'en_US');
		$this->assertEquals(Language::getCode('French'), 'fr_FR');
		$this->assertEquals(Language::getCode('Spanish'), 'es_ES');
		$this->assertEquals(Language::getCode('Italian'), 'it_IT');
	}
}