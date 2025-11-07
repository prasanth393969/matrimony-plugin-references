<?php

namespace ACPT\Tests;

use ACPT\Core\Helper\Uuid;
use ACPT\Integrations\WPML\Helper\WPMLConfig;

class WPMLConfigTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_generate_config_file()
	{
		$array = [
			'custom-fields' => [
				'meta_text' => [
					'id' => Uuid::v4(),
					'action' => "translate",
					'style' => "line",
					'label' => "Title"
				],
				'meta_email' => [
					'id' => Uuid::v4(),
					'action' => "translate",
					'style' => "line",
					'label' => "Title"
				],
			],
			'custom-term-fields' => [
				'meta_text' => [
					'id' => Uuid::v4(),
					'action' => "translate",
					'style' => "line",
					'label' => "Title"
				],
				'meta_email' => [
					'id' => Uuid::v4(),
					'action' => "translate",
					'style' => "line",
					'label' => "Title"
				],
			],
			'admin-texts' => [
				'option_page_meta_text' => [
					'id' => Uuid::v4(),
					'label' => "Title"
				]
			],
			'not-allowed' => []
		];

		$xml = WPMLConfig::xml($array);
		$expected = '<?xml version="1.0"?>
<wpml-config>
  <custom-fields>
    <custom-field action="translate" style="line" label="Title">meta_text</custom-field>
    <custom-field action="translate" style="line" label="Title">meta_email</custom-field>
  </custom-fields>
  <custom-term-fields>
    <custom-term-field action="translate" style="line" label="Title">meta_text</custom-term-field>
    <custom-term-field action="translate" style="line" label="Title">meta_email</custom-term-field>
  </custom-term-fields>
  <admin-texts>
    <key name="option_page_meta_text" label="Title"/>
  </admin-texts>
</wpml-config>
';

		$this->assertEquals($xml, $expected);
	}
}

