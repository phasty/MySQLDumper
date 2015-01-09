<?php
namespace Phasty\MySQLDumper {
    class Factory { 
        static public function getOperation($name) {
            static $instances = [];
            if (isset($instances[ $name ])) {
                return $instances[ $name ];
            }
            $clear = preg_replace("/\W/", "", $name);
            if ($clear != $name) {
                throw new \Exception("Invalid operation name '$name'");
            }
            $clear = "\\Phasty\\MySQLDumper\\Operations\\" . ucfirst($clear);
            if (!class_exists($clear)) {
                throw new \Exception("Unknown operation '$name'");
            }
            return $instances[ $name ] = new $clear;
        }
    }
}
