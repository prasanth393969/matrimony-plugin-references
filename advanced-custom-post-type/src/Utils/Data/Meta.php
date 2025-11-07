<?php

namespace ACPT\Utils\Data;

use ACPT\Constants\MetaTypes;
use ACPT\Core\Models\Meta\MetaFieldModel;
use ACPT\Includes\ACPT_DB;
use ACPT\Includes\ACPT_Plugin;
use Psr\Cache\InvalidArgumentException;

class Meta
{
    const CACHE_KEY_PREFIX = "Meta_";
    const CACHE_TTL = 3600; // 1 hour

    /**
     * @param $id
     * @param $belongsTo
     * @param $key
     */
    public static function delete($id, $belongsTo, $key)
    {
        switch($belongsTo){
            case MetaTypes::MEDIA:
            case MetaTypes::CUSTOM_POST_TYPE:
                delete_post_meta((int)$id, $key);
                break;

            case MetaTypes::OPTION_PAGE:
                delete_option($key);
                break;

            case MetaTypes::TAXONOMY:
                delete_term_meta((int)$id, $key);
                break;

            case MetaTypes::USER:
                delete_user_meta((int)$id, $key);
                break;

            case MetaTypes::COMMENT:
                delete_comment_meta((int)$id, $key);
                break;
        }

        self::deleteFromCache($id, $belongsTo, $key);
    }

    /**
     * @param $belongsTo
     * @param array $args
     *
     * @throws \Exception
     */
    public static function deleteBy($belongsTo, $args = [])
    {
        global $wpdb;

        $metaId = (isset($args['meta_id']) and !empty($args['meta_id'])) ? $args['meta_id'] : null;
        $id = (isset($args['id']) and !empty($args['id'])) ? $args['id'] : null;
        $key = (isset($args['key']) and !empty($args['key'])) ? $args['key'] : null;
        $value = (isset($args['value']) and !empty($args['value'])) ? $args['value'] : null;
        $queryArgs = [];

        switch ($belongsTo){
            case MetaTypes::MEDIA:
            case MetaTypes::CUSTOM_POST_TYPE:
                $sql = "DELETE FROM `{$wpdb->prefix}postmeta` WHERE 1=1";

                if($metaId){
                    $sql .= ' AND meta_id = %d';
                    $queryArgs[] = (int)$metaId;
                }

                if($key){
                    $sql .= ' AND meta_key = %s';
                    $queryArgs[] = $key;
                }

                if($value){
                    $sql .= ' AND meta_value = %s';
                    $queryArgs[] = $value;
                }

                if($id){
                    $sql .= ' AND post_id = %d';
                    $queryArgs[] = (int)$id;
                }

                break;

            case MetaTypes::TAXONOMY:
                $sql = "DELETE FROM `{$wpdb->prefix}termmeta` WHERE 1=1";

                if($metaId){
                    $sql .= ' AND meta_id = %d';
                    $queryArgs[] = (int)$metaId;
                }

                if($key){
                    $sql .= ' AND meta_key = %s';
                    $queryArgs[] = $key;
                }

                if($value){
                    $sql .= ' AND meta_value = %s';
                    $queryArgs[] = $value;
                }

                if($id){
                    $sql .= ' AND term_id = %d';
                    $queryArgs[] = (int)$id;
                }

                break;

            case MetaTypes::USER:
                $sql = "DELETE FROM `{$wpdb->prefix}usermeta` WHERE 1=1";

                if($metaId){
                    $sql .= ' AND meta_id = %d';
                    $queryArgs[] = (int)$metaId;
                }

                if($key){
                    $sql .= ' AND meta_key = %s';
                    $queryArgs[] = $key;
                }

                if($value){
                    $sql .= ' AND meta_value = %s';
                    $queryArgs[] = $value;
                }

                if($id){
                    $sql .= ' AND user_id = %d';
                    $queryArgs[] = (int)$id;
                }

                break;

            case MetaTypes::OPTION_PAGE:
                $sql = "DELETE FROM `{$wpdb->prefix}options` WHERE 1=1";

                if($metaId){
                    $sql .= ' AND option_id = %d';
                    $queryArgs[] = (int)$metaId;
                }

                if($key){
                    $sql .= ' AND option_name = %s';
                    $queryArgs[] = $key;
                }

                if($value){
                    $sql .= ' AND option_value = %s';
                    $queryArgs[] = $value;
                }

                break;
        }

        if(isset($sql)){
            ACPT_DB::executeQueryOrThrowException($sql, $queryArgs);
        }
    }

    /**
     * @param $belongsTo
     * @param $id
     *
     * @throws \Exception
     */
    public static function deleteByMetaId($belongsTo, $id)
    {
        global $wpdb;

        switch ($belongsTo){
            case MetaTypes::MEDIA:
            case MetaTypes::CUSTOM_POST_TYPE:
                $sql = "DELETE FROM `{$wpdb->prefix}postmeta` WHERE meta_id = %d";
                break;

            case MetaTypes::TAXONOMY:
                $sql = "DELETE FROM `{$wpdb->prefix}termmeta` WHERE meta_id = %d";
                break;
            case MetaTypes::USER:
                $sql = "DELETE FROM `{$wpdb->prefix}usermeta` WHERE meta_id = %d";
                break;

            case MetaTypes::OPTION_PAGE:
                $sql = "DELETE FROM `{$wpdb->prefix}options` WHERE option_id = %d";
                break;
        }

        if(isset($sql)){
            ACPT_DB::executeQueryOrThrowException($sql, [$id]);
        }
    }

    /**
     * @param string $id
     * @param string $belongsTo
     * @param string $key
     * @param bool $single
     *
     * @return mixed|null
     */
    public static function fetch($id, $belongsTo, $key, $single = true)
    {
        $fromCache = self::fetchFromCache($id, $belongsTo, $key);

        if($fromCache !== null){
            return $fromCache;
        }

        $fetched = null;

        switch ($belongsTo){
            case MetaTypes::MEDIA:
            case MetaTypes::CUSTOM_POST_TYPE:
                $fetched = get_post_meta((int)$id, $key, $single);
                break;

            case MetaTypes::TAXONOMY:
                $fetched = get_term_meta((int)$id, $key, $single);
                break;

            case MetaTypes::OPTION_PAGE:
                $fetched = get_option($key);
                break;

            case MetaTypes::USER:
                $fetched = get_user_meta((int)$id, $key, $single);
                break;

            case MetaTypes::COMMENT:
                $fetched = get_comment_meta((int)$id, $key, $single);
                break;
        }

        // in case of failure, return null
        if($fetched === false or $fetched === ''){
            return null;
        }

        self::saveInCache($id, $belongsTo, $key, $fetched);

        return $fetched;
    }

    /**
     * @param $belongsTo
     * @param array $args
     *
     * @return array
     */
    public static function fetchBy($belongsTo, $args = [])
    {
        global $wpdb;

        $metaIdIn = (isset($args['meta_id_in']) and !empty($args['meta_id_in'])) ? $args['meta_id_in'] : null;
        $metaIdNotIn = (isset($args['meta_id_not_in']) and !empty($args['meta_id_not_in'])) ? $args['meta_id_not_in'] : null;
        $metaId = (isset($args['meta_id']) and !empty($args['meta_id'])) ? $args['meta_id'] : null;
        $id = (isset($args['id']) and !empty($args['id'])) ? $args['id'] : null;
        $key = (isset($args['key']) and !empty($args['key'])) ? $args['key'] : null;
        $value = (isset($args['value']) and !empty($args['value'])) ? $args['value'] : null;
        $queryArgs = [];

        switch ($belongsTo){
            case MetaTypes::MEDIA:
            case MetaTypes::CUSTOM_POST_TYPE:
                $sql = "SELECT * FROM `{$wpdb->prefix}postmeta` WHERE 1=1";

                if($metaIdIn and is_array($metaIdIn) and !empty($metaIdIn)){
                    $sql .= " AND post_id IN ('".implode("','", $metaIdIn) . "')";
                }

                if($metaIdNotIn and is_array($metaIdNotIn) and !empty($metaIdNotIn)){
                    $sql .= " AND post_id NOT IN ('".implode("','", $metaIdNotIn) . "')";
                }

                if($metaId){
                    $sql .= ' AND meta_id = %d';
                    $queryArgs[] = (int)$metaId;
                }

                if($key){
                    $sql .= ' AND meta_key = %s';
                    $queryArgs[] = $key;
                }

                if($value){
                    $sql .= ' AND meta_value = %s';
                    $queryArgs[] = $value;
                }

                if($id){
                    $sql .= ' AND post_id = %d';
                    $queryArgs[] = (int)$id;
                }

                break;

            case MetaTypes::TAXONOMY:
                $sql = "SELECT * FROM `{$wpdb->prefix}termmeta` WHERE 1=1";

                if($metaId){
                    $sql .= ' AND meta_id = %d';
                    $queryArgs[] = (int)$metaId;
                }

                if($key){
                    $sql .= ' AND meta_key = %s';
                    $queryArgs[] = $key;
                }

                if($value){
                    $sql .= ' AND meta_value = %s';
                    $queryArgs[] = $value;
                }

                if($id){
                    $sql .= ' AND term_id = %d';
                    $queryArgs[] = (int)$id;
                }

                break;

                break;

            case MetaTypes::USER:
                $sql = "SELECT * FROM `{$wpdb->prefix}usermeta` WHERE 1=1";

                if($metaId){
                    $sql .= ' AND meta_id = %d';
                    $queryArgs[] = (int)$metaId;
                }

                if($key){
                    $sql .= ' AND meta_key = %s';
                    $queryArgs[] = $key;
                }

                if($value){
                    $sql .= ' AND meta_value = %s';
                    $queryArgs[] = $value;
                }

                if($id){
                    $sql .= ' AND user_id = %d';
                    $queryArgs[] = (int)$id;
                }

                break;

            case MetaTypes::OPTION_PAGE:
                $sql = "SELECT * FROM `{$wpdb->prefix}options` WHERE 1=1";

                if($metaId){
                    $sql .= ' AND option_id = %d';
                    $queryArgs[] = (int)$metaId;
                }

                if($key){
                    $sql .= ' AND option_name = %s';
                    $queryArgs[] = $key;
                }

                if($value){
                    $sql .= ' AND option_value = %s';
                    $queryArgs[] = $value;
                }

                break;
        }

        if(isset($sql)){
            return ACPT_DB::getResults($sql, $queryArgs);
        }

        return [];
    }

    /**
     * @param $id
     * @param $belongsTo
     * @param $key
     * @param $value
     *
     * @return bool|int|\WP_Error
     */
    public static function save($id, $belongsTo, $key, $value)
    {
        $value = wp_unslash($value);

        switch($belongsTo){
            default:
            case MetaTypes::MEDIA:
            case MetaTypes::CUSTOM_POST_TYPE:
                $update = update_post_meta((int)$id, $key, $value);
                break;

            case MetaTypes::OPTION_PAGE:
                $update = update_option($key, $value);
                break;

            case MetaTypes::TAXONOMY:
                $update = update_term_meta((int)$id, $key, $value);
                break;

            case MetaTypes::USER:
                $update = update_user_meta((int)$id, $key, $value);
                break;

            case MetaTypes::COMMENT:
                $update = update_comment_meta((int)$id, $key, $value);
                break;
        }

        if($update === true){
            self::saveInCache($id, $belongsTo, $key, $value);
        }

        return $update;
    }

    /**
     * This function adds the value to field object representations
     *
     * @param $fieldObject
     * @param mixed $value
     * @param string $format
     * @return mixed
     */
    public static function format(\stdClass $fieldObject, $value, $format = 'only_value')
    {
        if($format === "complete"){
            $fieldObject->value = $value;

            // repeater
            if($fieldObject->type === MetaFieldModel::REPEATER_TYPE and count($fieldObject->children) > 0){
                foreach ($fieldObject->children as $index => $child){

                    $fieldObject->children[$index]->value = self::injectNestedValues($child->name, $fieldObject);

                    // nested repeater
                    if($fieldObject->children[$index]->type === MetaFieldModel::REPEATER_TYPE and count($fieldObject->children[$index]->children) > 0){
                        foreach ($fieldObject->children[$index]->children as $subIndex => $subChild){
                            $fieldObject->children[$index]->children[$subIndex]->value = self::injectNestedValues($subChild->name, $fieldObject->children[$index]);
                        }
                    }

                    // nested block
                    if($fieldObject->children[$index]->type === MetaFieldModel::FLEXIBLE_CONTENT_TYPE and count($fieldObject->children[$index]->blocks) > 0){
                        foreach ($fieldObject->children[$index]->blocks as $blockIndex => $block){
                            foreach ($block->fields as $bindex => $bchild){
                                $fieldObject->children[$index]->blocks[$blockIndex]->fields[$bindex]->value = self::injectNestedValues($bchild->name, $fieldObject);
                            }
                        }
                    }
                }
            }

            // block
            if($fieldObject->type === MetaFieldModel::FLEXIBLE_CONTENT_TYPE and count($fieldObject->blocks) > 0){
                foreach ($fieldObject->blocks as $blockIndex => $block){
                    foreach ($block->fields as $index => $child){
                        $fieldObject->blocks[$blockIndex]->fields[$index]->value = self::injectNestedValues($child->name, $fieldObject);

                        // nested repeater
                        if($fieldObject->blocks[$blockIndex]->fields[$index]->type === MetaFieldModel::REPEATER_TYPE and count($fieldObject->blocks[$blockIndex]->fields[$index]->children) > 0){
                            foreach ($fieldObject->blocks[$blockIndex]->fields[$index]->children as $subIndex => $subChild){
                                $fieldObject->blocks[$blockIndex]->fields[$index]->children[$subIndex]->value = self::injectNestedValues($subChild->name, $fieldObject->blocks[$blockIndex]->fields[$index]);
                            }
                        }

                        // nested block
                        if($fieldObject->blocks[$blockIndex]->fields[$index]->type === MetaFieldModel::FLEXIBLE_CONTENT_TYPE and count($fieldObject->blocks[$blockIndex]->fields[$index]->blocks) > 0){
                            foreach ($fieldObject->blocks[$blockIndex]->fields[$index]->blocks as $bblockIndex => $block){
                                foreach ($block->fields as $bindex => $bchild){
                                    $fieldObject->blocks[$blockIndex]->fields[$index]->blocks[$blockIndex]->fields[$bindex]->value = self::injectNestedValues($bchild->name, $fieldObject);
                                }
                            }
                        }
                    }
                }
            }

            return $fieldObject;
        }

        return $value;
    }

    /**
     * @param $childName
     * @param $fieldObject
     * @return array
     */
    private static function injectNestedValues($childName, $fieldObject)
    {
        $v = [];

        if(is_array($fieldObject->value)){
            foreach ($fieldObject->value as $nestedValues){
                if(is_array($nestedValues)){
                    foreach ($nestedValues as $key => $nestedValue){
                        if($childName === $key){
                            $v[] = $nestedValue;
                        }

                        // nested flexible
                        if(is_array($nestedValue)){
                            foreach ($nestedValue as $nn){
                                if(is_array($nn)){
                                    foreach ($nn as $key => $nestedValue){
                                        if($childName === $key){
                                            $v[] = $nestedValue;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $v;
    }

    /**
     * @param $id
     * @param $belongsTo
     * @param $key
     *
     * @return mixed|null
     */
    private static function fetchFromCache($id, $belongsTo, $key)
    {
        if(!ACPT_ENABLE_META_CACHE){
            return null;
        }

        $cache = ACPT_Plugin::getCache();

        if($cache === null){
            return null;
        }

        try {
            $cacheKey = self::cacheKey($id, $belongsTo, $key);
            $cachedElement = $cache->getItem($cacheKey);

            if (!$cachedElement->isHit()) {
                return null;
            }

            return $cachedElement->get();
        } catch (\Exception $exception){

            do_action("acpt/error", $exception);

            return null;
        } catch (\Psr\Cache\InvalidArgumentException $exception){

            do_action("acpt/error", $exception);

            return null;
        }
    }

    /**
     * @param $id
     * @param $belongsTo
     * @param $key
     */
    private static function deleteFromCache($id, $belongsTo, $key)
    {
        if(!ACPT_ENABLE_META_CACHE){
            return;
        }

        $cache = ACPT_Plugin::getCache();

        if($cache === null){
            return;
        }

        try {
            $cacheKey = self::cacheKey($id, $belongsTo, $key);
            $cache->deleteItem($cacheKey);
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        } catch ( InvalidArgumentException $exception ) {
            do_action("acpt/error", $exception);
        }
    }

    /**
     * @param $id
     * @param $belongsTo
     * @param $key
     * @param $value
     */
    private static function saveInCache($id, $belongsTo, $key, $value)
    {
        if(!ACPT_ENABLE_META_CACHE){
            return;
        }

        $cache = ACPT_Plugin::getCache();

        if($cache === null){
            return;
        }

        try {
            self::deleteFromCache($id, $belongsTo, $key);
            $cacheKey = self::cacheKey($id, $belongsTo, $key);
            $cachedElement = $cache->getItem($cacheKey);
            $tag = md5(static::class);
            $cachedElement->addTag($tag)->set($value)->expiresAfter(self::CACHE_TTL);
            $cache->save($cachedElement);
        } catch (\Exception $exception){
            do_action("acpt/error", $exception);
        } catch (\Psr\Cache\InvalidArgumentException $exception){
            do_action("acpt/error", $exception);
        }
    }

    /**
     * Flush meta cache
     */
    private static function flushCache()
    {
        if(!ACPT_ENABLE_META_CACHE){
            return;
        }

        $cache = ACPT_Plugin::getCache();

        if($cache === null){
            return;
        }

        $cache->deleteItemsByTag(md5(static::class));
    }

    /**
     * @param $id
     * @param $belongsTo
     * @param $key
     *
     * @return string
     */
    private static function cacheKey($id, $belongsTo, $key)
    {
        $identifier = $id . "_" . $belongsTo . "_" . $key;

        return md5(self::CACHE_KEY_PREFIX.$identifier);
    }
}