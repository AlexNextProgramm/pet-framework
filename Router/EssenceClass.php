<?php

namespace Pet\Router;

use Pet\Errors\AppException;

class EssenceClass {


    public function open($class, $argm = []) {
        $argm = func_get_args();

        unset($argm[0]);
        $this->isArrayClass($class, $method);

        // // проверка сушествования класса и метода; код остановить 
        if (gettype($class) == 'string' && !class_exists($class)) {
            throw new AppException("Class not found " . $class, E_ERROR);
        }
        if ($method && !method_exists($class, $method)) die("Not method: $method in class: $class");

        if ($this->isCallable($class, $method)) {

            if ($method)  return call_user_func([$class, $method], ...$argm);
            return call_user_func($class, ...$argm);
        } else {

            $classNew  = new $class();

            if ($this->isCallable($classNew, $method)) {

                if ($method) return call_user_func([$classNew, $method], ...$argm);
                return call_user_func($classNew, ...$argm);
            }
        }

        throw new AppException('Undefintd class else function '  . $class, E_ERROR);
    }

    private function isArrayClass(&$class, &$method) {
        $value = $class;

        if (gettype($value) == 'array') {

            if (count($value) == 1) $class = $value[0];
            if (count($value) > 1) {

                $method = $value[1];
                $class = $value[0];
            }
        }
    }


    public function isCallable($class, string $method = null): bool {

        if ($method && is_callable([$class, $method])) return true;

        if (gettype($class) == 'object' && is_callable($class)) return true;

        if (gettype($class) == 'string' && is_callable($class)) return true;

        return false;
    }
}
