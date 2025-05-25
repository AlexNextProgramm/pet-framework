<?php

namespace Pet;

abstract class Controller
{
    /**
     * saveFile
     *
     * @param  mixed $name
     * @param  string $path
     * @param  int $access
     * @return bool
     */
    public function saveFile(string $tmp, string $name, string $path = ROOT,  int $access = 0777 ): bool
    {
        if(!is_dir($path)) mkdir($path, $access, true);
        return move_uploaded_file($tmp, $path . DS . $name);
    }
}
