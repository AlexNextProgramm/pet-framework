<?php

namespace Pet\Router;

use Pet\Errors\AppException;

class EssenceClass {


    public function open($class, $argm = []) {
        $argm = func_get_args();

        unset($argm[0]);
        $this->isArrayClass($class, $method);

        //проверка сушествования класса и метода;
        if (gettype($class) == 'string' && !class_exists($class)) {
            throw new AppException("Class not found " . $class, E_ERROR);
        }
        if ($method && !method_exists($class, $method)) {
            throw new AppException("method not found $method in class " . $class, E_ERROR);
        }

        if ($this->isCallable($class, $method)) {
            return call_user_func($method ? [$class, $method] : $class, ...$argm);
        } else {
            $classNew  = new $class();
            if ($this->isCallable($classNew, $method)) {
                return call_user_func($method ? [$classNew, $method] : $classNew, ...$argm);
            }
        }

        throw new AppException("Undefind class else function $method"  . $class, E_ERROR);
    }

    private function isArrayClass(&$class, &$method)
    {
        $value = $class;

        if (gettype($value) == 'array') {

            if (count($value) == 1) $class = $value[0];
            if (count($value) > 1) {

                $method = $value[1];
                $class = $value[0];
            }
        }
    }


    /**
     * isCallable
     *
     * @param  mixed $class
     * @param  mixed $method
     * @return bool
     */
    public function isCallable($class, string|null $method = null): bool
    {

        if ($method && is_callable([$class, $method])) return true;

        if (gettype($class) == 'object' && is_callable($class)) return true;

        if (gettype($class) == 'string' && is_callable($class)) return true;

        return false;
    }
}
