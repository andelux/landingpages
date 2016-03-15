<?php
/**
 * Created by PhpStorm.
 * User: javier
 * Date: 15/03/16
 * Time: 10:15
 */

namespace LandingPages\Mvc;


class Collection extends \ArrayIterator
{
    public function __construct(array $array = array(), $flags = 0)
    {
        parent::__construct($array, $flags);
    }
}