<?php
namespace LandingPages\Mvc;

use LandingPages\Model\Entities;
use LandingPages\Model\EntityTypes;
use LandingPages\Object;

class Eav extends Object
{
    static public function getEntityTypeId($type)
    {
        static $types = array();

        if ( ! array_key_exists($type, $types) ) {
            $types[$type] = EntityTypes::factory()->load($type, 'name')->getId();
        }

        return $types[$type];
    }

    /**
     *
     * @example Eav::load('blog_posts', 'my-first-post', 'slug')
     *
     * @param $type
     * @param $id
     * @param null $field
     * @return $this
     */
    static public function load($type, $id, $field = null)
    {
        /** @var Entities $entity */
        $entity = Entities::factory();
        $entity->setData('type_id', self::getEntityTypeId($type));
        return $entity->load($id, $field);
    }

    /**
     *
     * @example Eav::items('blog_posts')->addFieldToFilter('post_status','publish')->addOrderBy('date_published DESC')->collection()
     *
     * @param $type
     * @return $this
     * @throws \Exception
     */
    static public function items($type)
    {
        return Entities::factory()->addFieldToFilter('type_id', self::getEntityTypeId($type));
    }
}