<?php

namespace Pet\DataBase;

use Pet\DataBase\Delete;
use Pet\Model\Model;
use Pet\Tools\Tools;

trait Update {    
    /**
     * update
     *
     * @param array $arrayKeyAndValue
     * @return Update
     */
    function update(array $arrayKeyAndValue): Model {
        $value = Tools::array_implode(',', $arrayKeyAndValue, "`[key]`='[val]'");
        $this->strQuery  = "UPDATE `{$this->table}` SET $value";
        return $this;
    }
}
