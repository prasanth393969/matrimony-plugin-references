<?php

namespace ACPT\Tests;

use ACPT\Constants\BelongsTo;
use ACPT\Constants\Logic;
use ACPT\Constants\MetaTypes;
use ACPT\Constants\Operator;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Belong\BelongModel;
use ACPT\Core\Models\Meta\MetaGroupModel;

class BelongingTraitTest extends AbstractTestCase
{
	/**
	 * @test
	 */
	public function empty_group()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');

		$this->assertFalse($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::EQUALS, 'page'));
	}

	/**
	 * @test
	 */
	public function no_matching_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong = BelongModel::hydrateFromArray([
			'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
			'operator' => Operator::EQUALS,
			'find' => 'page',
			'sort' => 1,
		]);

		$group->addBelong($belong);

		$this->assertFalse($group->belongsTo(MetaTypes::OPTION_PAGE, Operator::EQUALS, 'option-page'));
	}

	/**
	 * @test
	 */
	public function simple_equals_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong = BelongModel::hydrateFromArray([
			'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
			'operator' => Operator::EQUALS,
			'find' => 'page',
			'sort' => 1,
		]);

		$group->addBelong($belong);

		$this->assertTrue($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE));
		$this->assertTrue($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::EQUALS, 'page'));
		$this->assertFalse($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::NOT_EQUALS, 'page'));
		$this->assertTrue($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::NOT_EQUALS, 'product'));
	}

	/**
	 * @test
	 */
	public function simple_in_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong = BelongModel::hydrateFromArray([
			'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
			'operator' => Operator::IN,
			'find' => 'page, product, post',
			'sort' => 1,
		]);

		$group->addBelong($belong);

		$this->assertTrue($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::EQUALS, 'page'));
		$this->assertTrue($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::IN, 'page'));
		$this->assertTrue($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::NOT_EQUALS, 'movie'));
	}

	/**
	 * @test
	 */
	public function simple_not_in_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong = BelongModel::hydrateFromArray([
			'belongsTo' => MetaTypes::CUSTOM_POST_TYPE,
			'operator' => Operator::NOT_IN,
			'find' => 'movie, hotel',
			'sort' => 1,
		]);

		$group->addBelong($belong);

		$this->assertFalse($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::EQUALS, 'movie'));
		$this->assertFalse($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::EQUALS, 'hotel'));
		$this->assertTrue($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::EQUALS, 'post'));
		$this->assertTrue($group->belongsTo(MetaTypes::CUSTOM_POST_TYPE, Operator::EQUALS, 'page'));
	}

	/**
	 * @test
	 */
	public function post_id_equals_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_ID,
			'operator' => Operator::EQUALS,
			'find' => '12',
			'sort' => 1,
		]);

		$group->addBelong($belong);

		$this->assertFalse($group->belongsTo(BelongsTo::POST_ID, Operator::EQUALS, '23'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::NOT_EQUALS, '434323'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::EQUALS, '12'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::IN, '12,23,54545,666565'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::NOT_IN, '54545,666565'));
	}

	/**
	 * @test
	 */
	public function post_id_in_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_ID,
			'operator' => Operator::IN,
			'find' => '12, 34, 56',
			'sort' => 1,
		]);

		$group->addBelong($belong);

		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::EQUALS, '12'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::NOT_EQUALS, '434323'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::IN, '12,23,54545,666565'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::NOT_IN, '54545,666565'));
	}

	/**
	 * @test
	 */
	public function mixed_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong1 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_ID,
			'operator' => Operator::EQUALS,
			'find' => '12',
			'logic' => Logic::AND,
			'sort' => 1,
		]);
		$belong2 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_ID,
			'operator' => Operator::NOT_IN,
			'find' => '323',
			'sort' => 2,
		]);

		$group->addBelong($belong1);
		$group->addBelong($belong2);

		$this->assertFalse($group->belongsTo(BelongsTo::POST_ID, Operator::EQUALS, '4343434323'));
		$this->assertFalse($group->belongsTo(BelongsTo::POST_ID, Operator::IN, '323'));
//		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::IN, '54354534543,12'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::NOT_IN, '44,5555,66565'));
	}

	/**
	 * @test
	 */
	public function user_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong1 = BelongModel::hydrateFromArray([
			'belongsTo' => MetaTypes::USER,
			'operator' => null,
			'find' => null,
			'sort' => 1,
		]);

		$group->addBelong($belong1);

		$this->assertTrue($group->belongsTo(MetaTypes::USER));

		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong1 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::USER_ID,
			'operator' => Operator::NOT_IN,
			'find' => '12, 34, 65, 67',
			'sort' => 1,
		]);

		$group->addBelong($belong1);

		$this->assertTrue($group->belongsTo(BelongsTo::USER_ID, Operator::NOT_IN, '44'));
	//	$this->assertFalse($group->belongsTo(BelongsTo::USER_ID, Operator::IN, '12'));
		$this->assertTrue($group->belongsTo(BelongsTo::USER_ID, Operator::EQUALS, '44'));
		$this->assertTrue($group->belongsTo(BelongsTo::USER_ID, Operator::NOT_EQUALS, '4443434343'));
	}

	/**
	 * @test
	 */
	public function mixed_logic_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong1 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_ID,
			'operator' => Operator::EQUALS,
			'find' => '12',
			'logic' => 'OR',
			'sort' => 1,
		]);
		$belong2 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_ID,
			'operator' => Operator::EQUALS,
			'find' => '323',
			'logic' => 'OR',
			'sort' => 2,
		]);
		$belong3 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_ID,
			'operator' => Operator::EQUALS,
			'find' => '666',
			'logic' => null,
			'sort' => 3,
		]);

		$group->addBelong($belong1);
		$group->addBelong($belong2);
		$group->addBelong($belong3);

		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::EQUALS, '12'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::EQUALS, '323'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::EQUALS, '666'));
	}

	/**
	 * @test
	 */
	public function other_mixed_logic_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong1 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_ID,
			'operator' => Operator::NOT_EQUALS,
			'find' => '12',
			'logic' => null,
			'sort' => 1,
		]);
		$belong2 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_ID,
			'operator' => Operator::EQUALS,
			'find' => '323',
			'logic' => 'OR',
			'sort' => 2,
		]);
		$belong3 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_ID,
			'operator' => Operator::EQUALS,
			'find' => '666',
			'logic' => null,
			'sort' => 3,
		]);

		$group->addBelong($belong1);
		$group->addBelong($belong2);
		$group->addBelong($belong3);

		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::EQUALS, '323'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_ID, Operator::EQUALS, '666'));
	}

	/**
	 * @test
	 */
	public function another_mixed_logic_query()
	{
		$group = new MetaGroupModel(Uuid::v4(), 'name', 'label');
		$belong1 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_TEMPLATE,
			'operator' => Operator::EQUALS,
			'find' => 'page-template.php',
			'logic' => 'OR',
			'sort' => 1,
		]);
		$belong2 = BelongModel::hydrateFromArray([
			'belongsTo' => BelongsTo::POST_CAT,
			'operator' => Operator::EQUALS,
			'find' => '323',
			'logic' => null,
			'sort' => 2,
		]);

		$group->addBelong($belong1);
		$group->addBelong($belong2);

		$this->assertTrue($group->belongsTo(BelongsTo::POST_TEMPLATE, Operator::EQUALS, 'page-template.php'));
		$this->assertTrue($group->belongsTo(BelongsTo::POST_CAT, Operator::EQUALS, '323'));
	}
}