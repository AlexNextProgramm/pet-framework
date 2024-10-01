<?php

namespace Pet\DataBase;

use Pet\DataBase\Delete;
use Pet\Tools\Tools;

class Update extends Delete {    
    /**
     * update
     *
     * @param array $arrayKeyAndValue
     * @return Update
     */
    function update(array $arrayKeyAndValue): Update {
        $value = Tools::array_implode(',', $arrayKeyAndValue, "`[key]`='[val]'");
        $this->strQuery  = "UPDATE `{$this->table}` SET $value";
        return $this;
    }
}
