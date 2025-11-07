<?php

namespace ACPT\Core\Repository;

use ACPT\Core\Models\DynamicBlock\DynamicBlockControlModel;
use ACPT\Core\Models\DynamicBlock\DynamicBlockModel;
use ACPT\Includes\ACPT_DB;

class DynamicBlockRepository extends AbstractRepository
{
    /**
     * @return int
     */
    public static function count(): int
    {
        $baseQuery = "
            SELECT 
                count(id) as count
            FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK)."`
            WHERE 1 = 1
            ";

        $results = ACPT_DB::getResults($baseQuery);

        return (int)$results[0]->count;
    }

    /**
     * @param $id
     *
     * @throws \Exception
     */
    public static function delete($id)
    {
        ACPT_DB::executeQueryOrThrowException("DELETE FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK)."` WHERE id = %s;", [$id]);
        ACPT_DB::executeQueryOrThrowException("DELETE FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK_CONTROL)."` WHERE block_id = %s;", [$id]);
        ACPT_DB::invalidateCacheTag(self::class);
    }

    /**
     * @param $name
     * @return bool
     */
    public static function exists($name)
    {
        $baseQuery = "
            SELECT 
                id
            FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK)."`
            WHERE block_name = %s
            ";

        $blocks = ACPT_DB::getResults($baseQuery, [$name]);

        return count($blocks) === 1;
    }

    /**
     * @return string[]
     */
    public static function getNames()
    {
        $names = [];
        $query = "
	        SELECT 
                b.id, 
                b.block_name as name
            FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK)."` b
	    ";

        $elements = ACPT_DB::getResults($query, []);

        foreach ($elements as $element){
            $names[] = $element->name;
        }

        return $names;
    }

    /**
     * @return string[]
     */
    public static function getControlNames()
    {
        $names = [];
        $query = "
	        SELECT 
                f.id, 
                f.control_name as name
            FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK_CONTROL)."` f
	    ";

        $elements = ACPT_DB::getResults($query, []);

        foreach ($elements as $element){
            $names[] = $element->name;
        }

        return $names;
    }

    /**
     * @param $args
     *
     * @return DynamicBlockModel[]
     * @throws \Exception
     */
    public static function get($args): array
    {
        $mandatoryKeys = [
            'id' => [
                'required' => false,
                'type' => 'integer|string',
            ],
            'page' => [
                'required' => false,
                'type' => 'integer|string',
            ],
            'perPage' => [
                'required' => false,
                'type' => 'integer|string',
            ],
            'sortedBy' => [
                'required' => false,
                'type' => 'string',
            ],
            'lazy' => [
                'required' => false,
                'type' => 'boolean',
            ],
        ];

        self::validateArgs($mandatoryKeys, $args);

        $id = isset($args['id']) ? $args['id'] : null;
        $lazy = isset($args['lazy']) ? $args['lazy'] : false;
        $page = isset($args['page']) ? $args['page'] : false;
        $perPage = isset($args['perPage']) ? $args['perPage'] : null;

        $blockQueryArgs = [];
        $blockQuery = "
	        SELECT 
                b.id, 
                b.title,
                b.block_name as name,
                b.category,
                b.icon,
                b.css,
                b.callback,
                b.keywords,
                b.post_types,
                b.supports
            FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK)."` b
            WHERE 1 = 1
	    ";

        if($id !== null){
            $blockQuery .= " AND b.id = %s";
            $blockQueryArgs[] = $id;
        }

        $blockQuery .= ' GROUP BY b.id ORDER BY b.block_name ASC';

        if(isset($page) and isset($perPage)){
            $blockQuery .= " LIMIT ".$perPage." OFFSET " . ($perPage * ($page - 1));
        }

        $blocks = ACPT_DB::getResults($blockQuery, $blockQueryArgs);
        $blockModels = [];

        foreach ($blocks as $block){
            $blockModels[] = self::hydrateBlock($block);
        }

        return $blockModels;
    }

    /**
     * @param $id
     *
     * @return DynamicBlockModel|null
     * @throws \Exception
     */
    public static function getById($id): ?DynamicBlockModel
    {
        $blockQuery = "
	        SELECT 
                b.id, 
                b.title,
                b.block_name as name,
                b.category,
                b.icon,
                b.css,
                b.callback,
                b.keywords,
                b.post_types,
                b.supports
            FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK)."` b
            WHERE b.id = %s
	    ";

        $blocks = ACPT_DB::getResults($blockQuery, [$id]);

        if(count($blocks) !== 1){
            return null;
        }

        return self::hydrateBlock($blocks[0]);
    }

    /**
     * @param $name
     *
     * @return DynamicBlockModel|null
     * @throws \Exception
     */
    public static function getByName($name): ?DynamicBlockModel
    {
        $blockQuery = "
	        SELECT 
                b.id, 
                b.title,
                b.block_name as name,
                b.category,
                b.icon,
                b.css,
                b.callback,
                b.keywords,
                b.post_types,
                b.supports
            FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK)."` b
            WHERE b.block_name = %s
	    ";

        $blocks = ACPT_DB::getResults($blockQuery, [$name]);

        if(count($blocks) !== 1){
            return null;
        }

        return self::hydrateBlock($blocks[0]);
    }

    /**
     * @param $block
     *
     * @return DynamicBlockModel
     * @throws \Exception
     */
    private static function hydrateBlock($block)
    {
        $blockModel = DynamicBlockModel::hydrateFromArray([
            'id'        => $block->id,
            'name'      => $block->name,
            'title'     => $block->title,
            'category'  => $block->category,
            'icon'      => is_serialized($block->icon) ? unserialize($block->icon) : $block->icon,
            'css'       => $block->css,
            'callback'  => $block->callback,
            'keywords'  => is_serialized($block->keywords) ? unserialize($block->keywords) : [],
            'postTypes' => is_serialized($block->post_types) ? unserialize($block->post_types) : [],
            'supports'  => is_serialized($block->supports) ? unserialize($block->supports) : [],
        ]);

        $controlsQuery = "
		        SELECT 
	                c.id, 
	                c.control_name as `name`,
	                c.label as `label`,
	                c.control_type as `type`,
	                c.description as `description`,
	                c.default_value as `default`,
	                c.options as `options`,
	                c.settings as `settings`,
	                c.sort as `sort`
	            FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK_CONTROL)."` c
	            WHERE block_id = %s 
	            ORDER BY c.sort 
		    ";

        $items = ACPT_DB::getResults($controlsQuery, [$block->id]);

        foreach ($items as $index => $item){
            $blockControlModel = DynamicBlockControlModel::hydrateFromArray([
                'id'          => $item->id,
                'block'       => $blockModel,
                'name'        => $item->name,
                'label'       => $item->label,
                'type'        => $item->type,
                'sort'        => $item->sort,
                'description' => $item->description ?? null,
                'default'     => $item->default ?? null,
                'settings'    => $item->settings ? json_decode($item->settings) : [],
            ]);

            if($item->options){
                $options = json_decode($item->options, true);
                $blockControlModel->setOptions($options);
            }

            $blockModel->addControl($blockControlModel);
        }

        return $blockModel;
    }

    /**
     * @param DynamicBlockModel $blockModel
     *
     * @throws \Exception
     */
    public static function save(DynamicBlockModel $blockModel): void
    {
        ACPT_DB::startTransaction();

        try {
            $itemIds = [];

            $sql = "
	            INSERT INTO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK)."` 
	            (`id`,
	            `title`,
	            `block_name`,
	            `category`,
	            `icon`,
	            `css`,
	            `callback`,
	            `keywords`,
	            `post_types`,
	            `supports`
	            ) VALUES (
	                %s,
	                %s,
	                %s,
	                %s,
	                %s,
	                %s,
	                %s,
	                %s,
	                %s,
	                %s
	            ) ON DUPLICATE KEY UPDATE 
	                `title` = %s,
	                `block_name` = %s,
	                `category` = %s,
	                `icon` = %s,
	                `css` = %s,
	                `callback` = %s,
	                `keywords` = %s,
	                `post_types` = %s,
	                `supports` = %s
	        ;";

            ACPT_DB::executeQueryOrThrowException($sql, [
                $blockModel->getId(),
                $blockModel->getTitle(),
                $blockModel->getName(),
                $blockModel->getCategory(),
                (is_array($blockModel->getIcon())) ? serialize($blockModel->getIcon()) : $blockModel->getIcon(),
                $blockModel->getCSS(),
                $blockModel->getCallback(),
                serialize($blockModel->getKeywords()),
                serialize($blockModel->getPostTypes()),
                serialize($blockModel->getSupports()),
                $blockModel->getTitle(),
                $blockModel->getName(),
                $blockModel->getCategory(),
                (is_array($blockModel->getIcon())) ? serialize($blockModel->getIcon()) : $blockModel->getIcon(),
                $blockModel->getCSS(),
                $blockModel->getCallback(),
                serialize($blockModel->getKeywords()),
                serialize($blockModel->getPostTypes()),
                serialize($blockModel->getSupports()),
            ]);

            // controls
            foreach ($blockModel->getControls() as $itemIndex => $controlModel){

                $sql = "
                    INSERT INTO `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK_CONTROL)."` 
                        (`id`,
                        `block_id`,
                        `control_name`,
                        `label`,
                        `control_type`,
                        `sort`,
                        `description`,
                        `default_value`,
                        `options`,
                        `settings`
                        ) VALUES (
                            %s,
                            %s,
                            %s,
                            %s,
                            %s,
                            %d,
                            %s,
                            %s,
                            %s,
                            %s
                        ) ON DUPLICATE KEY UPDATE 
                            `block_id` = %s,
                            `control_name` = %s,
                            `label` = %s,
                            `control_type` = %s,
                            `sort` = %d,
                            `description` = %s,
                            `default_value` = %s,
                            `options` = %s,
                            `settings` = %s
                    ;";

                ACPT_DB::executeQueryOrThrowException($sql, [
                    $controlModel->getId(),
                    $blockModel->getId(),
                    $controlModel->getName(),
                    $controlModel->getLabel(),
                    $controlModel->getType(),
                    $controlModel->getSort(),
                    $controlModel->getDescription(),
                    $controlModel->getDefault(),
                    json_encode($controlModel->getOptions()),
                    json_encode($controlModel->getSettings()),
                    $blockModel->getId(),
                    $controlModel->getName(),
                    $controlModel->getLabel(),
                    $controlModel->getType(),
                    $controlModel->getSort(),
                    $controlModel->getDescription(),
                    $controlModel->getDefault(),
                    json_encode($controlModel->getOptions()),
                    json_encode($controlModel->getSettings()),
                ]);

                $itemIds[] = $controlModel->getId();
            }

            self::removeOrphans($blockModel->getId(), $itemIds);

        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
            ACPT_DB::rollbackTransaction();
        }

        ACPT_DB::commitTransaction();
        ACPT_DB::invalidateCacheTag(self::class);
    }

    /**
     * @param $blockId
     * @param $ids
     *
     * @throws \Exception
     */
    private static function removeOrphans($blockId, $ids)
    {
        $deleteQuery = "
	    	DELETE i
			FROM `".ACPT_DB::prefixedTableName(ACPT_DB::TABLE_BLOCK_CONTROL)."` i
			WHERE block_id = %s
	    ";

        if(!empty($ids)){
            $deleteQuery .= " AND i.id NOT IN ('".implode("','",$ids)."')";
            $deleteQuery .= ";";
        }

        ACPT_DB::executeQueryOrThrowException($deleteQuery, [$blockId]);
    }
}