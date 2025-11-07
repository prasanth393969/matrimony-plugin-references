<?php

namespace ACPT\Tests;

use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Core\Validators\MetaDataValidator;

class MetaDataValidatorTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function can_validate_data()
	{
		$data = [
			[
				'type' => MetaFieldModel::ADDRESS_TYPE,
				'value' => 'Via Roma 23, Milano',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::COLOR_TYPE,
				'value' => '#dddddd',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::CHECKBOX_TYPE,
				'value' => [
					'this is a value',
					'this is a another value',
					'this is a last value',
				],
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::CURRENCY_TYPE,
				'value' => '345.65',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::DATE_TYPE,
				'value' => '2020-02-02',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::EDITOR_TYPE,
				'value' => '<p>This is an HTML string</p>',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::EMAIL_TYPE,
				'value' => 'maurocassani1978@gmail.com',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::EMBED_TYPE,
				'value' => 'https://youtu.be/t8CYWZ2P8l8',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::FILE_TYPE,
				'value' => 'https://acpt.io/wp-content/2020/03/sample.txt',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::HTML_TYPE,
				'value' => '<p>This is an HTML string</p>',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::GALLERY_TYPE,
				'value' => [
					'https://acpt.io/img/logo.png',
					'https://acpt.io/img/logo2.png',
					'https://acpt.io/img/logo3.png',
				],
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::IMAGE_TYPE,
				'value' => 'https://acpt.io/img/logo.png',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::LENGTH_TYPE,
				'value' => '43',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::LIST_TYPE,
				'value' => [
					'element1',
					'element2',
					'element3',
					'element4',
				],
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::NUMBER_TYPE,
				'value' => '545',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::POST_TYPE,
				'value' => '545',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::POST_TYPE,
				'value' => [
					'545',
					'45645',
					43,
				],
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::PHONE_TYPE,
				'value' => '+3978000000',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::REPEATER_TYPE,
				'value' => [
					'text' => [
						[
							'type' => [
								MetaFieldModel::TEXT_TYPE
							],
							'value' => [
								'This is a string'
							]
						],
						[
							'type' => [
								MetaFieldModel::TEXT_TYPE,
								MetaFieldModel::TEXT_TYPE,
							],
							'value' => [
								'This is a string 2',
								'This is a string 3',
							]
						],
					],
				],
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::RADIO_TYPE,
				'value' => 'this is radio value',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::SELECT_TYPE,
				'value' => 'this is select value',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::SELECT_MULTI_TYPE,
				'value' => [
					'this is a multiselect value',
					'this is a another multiselect value',
					'this is a last multiselect value',
				],
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::TEXT_TYPE,
				'value' => 'This is a text string',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::TEXTAREA_TYPE,
				'value' => 'This is a string',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::TIME_TYPE,
				'value' => '23:59',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::TOGGLE_TYPE,
				'value' => '1',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::VIDEO_TYPE,
				'value' => 'https://acpt.io/video/video.mp4',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::WEIGHT_TYPE,
				'value' => 345,
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::URL_TYPE,
				'value' => 'https://acpt.io',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::USER_TYPE,
				'value' => '23',
				'isRequired' => false
			],
			[
				'type' => MetaFieldModel::USER_MULTI_TYPE,
				'value' => [
					'23',
					434,
					'333',
				],
				'isRequired' => false
			],
		];

		foreach ($data as $datum){
			try {
				MetaDataValidator::validate($datum['type'], $datum['value']);

				$this->assertTrue(true);
			} catch (\Exception $exception){
				$this->fail('['.$datum['type'].']: ' . $exception->getMessage());
			}
		}
	}
}