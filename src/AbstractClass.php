<?php
/**
 * @author will <wizarot@gmail.com>
 * @link http://wizarot.me/
 *
 * Date: 17/4/26
 * Time: 下午4:25
 */

namespace AbstractClass;


use Pimple\Container;

class AbstractClass
{
    /**
     * @var Container Container
     */
    public $container;
    public function __construct(Container $container)
    {
        $this->container = $container;

    }

}