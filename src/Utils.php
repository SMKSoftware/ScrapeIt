<?php
/**
 * Created by PhpStorm.
 * User: Администратор
 * Date: 13.09.2016
 * Time: 10:45
 */

namespace SmkSoftware\ScrapeIt;


use ReflectionClass;

class Utils
{
    const CREATECLASS_ERROR_NOT_FOUND = -1;
    const CREATECLASS_ERROR_NOT_INSTANCE_OF = -2;

    public static function createClass($name, $args, $instanceOf)
    {
        $instance = null;

        if (!class_exists($name)) {
            $classes = get_declared_classes();
            foreach ($classes as $class) {
                if (end(explode('\\', $class)) == $name) {
                    $instance = (new ReflectionClass($class))->newInstanceArgs($args);
                }
            }
            if (!$instance) return self::CREATECLASS_ERROR_NOT_FOUND;
        } else
            $instance = (new ReflectionClass($name))->newInstanceArgs($args);

        if (!is_a($instance, $instanceOf)) {
            return self::CREATECLASS_ERROR_NOT_INSTANCE_OF;
        }
        return $instance;
    }
}