<?php

namespace Pet\Migration\Common;

use Pet\Migration\Change;
use Pet\Migration\Table;

trait Common {


    private function changeName(&$name, $isChange = false): string {
        $newName = explode(" ", $name);
        $name = $newName[0];
        $newName = !empty($newName[1]) ? $newName[1] : null;
        if ($isChange) {
            return $newName ? "`$newName`" : "`$name`";
        }

        return $newName ? "`$newName`" : "";
    }
}
