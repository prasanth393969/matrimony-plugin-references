<?php

namespace ACPT\Tests;

use ACPT\Constants\BelongsTo;
use ACPT\Constants\Logic;
use ACPT\Constants\Operator;
use ACPT\Core\Helper\Uuid;
use ACPT\Core\Models\Belong\BelongModel;
use ACPT\Utils\PHP\Logics;

class LogicsTest extends AbstractTestCase
{
    /**
     * @test
     */
    public function can_extract_logic_blocks_with_or_or_or()
    {
        $logicBlocks = $this->extractConditions([
            Logic::OR,
            Logic::OR,
            Logic::OR,
            Logic::OR,
        ]);

        $this->assertCount(4, $logicBlocks['blocks']);
        $this->assertEquals($logicBlocks['blocks'][0][0]->getId(), $logicBlocks['belongs'][0]->getId());
        $this->assertEquals($logicBlocks['blocks'][1][0]->getId(), $logicBlocks['belongs'][1]->getId());
        $this->assertEquals($logicBlocks['blocks'][2][0]->getId(), $logicBlocks['belongs'][2]->getId());
        $this->assertEquals($logicBlocks['blocks'][3][0]->getId(), $logicBlocks['belongs'][3]->getId());
    }

    /**
     * @test
     */
    public function can_extract_logic_blocks_with_and_and_and()
    {
        $logicBlocks = $this->extractConditions([
            Logic::AND,
            Logic::AND,
            Logic::AND,
            Logic::AND,
        ]);

        $this->assertCount(1, $logicBlocks['blocks']);
        $this->assertEquals($logicBlocks['blocks'][0][0]->getId(), $logicBlocks['belongs'][0]->getId());
        $this->assertEquals($logicBlocks['blocks'][0][1]->getId(), $logicBlocks['belongs'][1]->getId());
        $this->assertEquals($logicBlocks['blocks'][0][2]->getId(), $logicBlocks['belongs'][2]->getId());
        $this->assertEquals($logicBlocks['blocks'][0][3]->getId(), $logicBlocks['belongs'][3]->getId());
    }

    /**
     * @test
     */
    public function can_extract_logic_blocks_with_and_or_or()
    {
        $logicBlocks = $this->extractConditions([
                Logic::AND,
                Logic::OR,
                Logic::OR,
                Logic::OR,
        ]);

        $this->assertCount(3, $logicBlocks['blocks']);
        $this->assertEquals($logicBlocks['blocks'][0][0]->getId(), $logicBlocks['belongs'][0]->getId());
        $this->assertEquals($logicBlocks['blocks'][0][1]->getId(), $logicBlocks['belongs'][1]->getId());
        $this->assertEquals($logicBlocks['blocks'][1][0]->getId(), $logicBlocks['belongs'][2]->getId());
        $this->assertEquals($logicBlocks['blocks'][2][0]->getId(), $logicBlocks['belongs'][3]->getId());
    }

    /**
     * @test
     */
    public function can_extract_logic_blocks_with_and_and_or()
    {
        $logicBlocks = $this->extractConditions([
            Logic::AND,
            Logic::AND,
            Logic::OR,
            Logic::OR,
        ]);

        $this->assertCount(2, $logicBlocks['blocks']);
        $this->assertEquals($logicBlocks['blocks'][0][0]->getId(), $logicBlocks['belongs'][0]->getId());
        $this->assertEquals($logicBlocks['blocks'][0][1]->getId(), $logicBlocks['belongs'][1]->getId());
        $this->assertEquals($logicBlocks['blocks'][0][2]->getId(), $logicBlocks['belongs'][2]->getId());
        $this->assertEquals($logicBlocks['blocks'][1][0]->getId(), $logicBlocks['belongs'][3]->getId());
    }

    /**
     * @param $conditions
     *
     * @return array
     */
    private function extractConditions($conditions)
    {
        try {
            $belongs = [];

            foreach ($conditions as $index => $condition){
                $belongs[] = new BelongModel(
                    Uuid::v4(),
                    BelongsTo::POST_ID,
                    ($index+1),
                    $condition,
                    Operator::EQUALS,
                    rand(1, 999)
                );
            }

            return [
                'belongs' => $belongs,
                'blocks'  => Logics::extractLogicBlocks($belongs),
            ];
        } catch (\Exception $exception){
            return [];
        }
    }
}