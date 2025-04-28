<?php

namespace Pet\DataBase;

use Pet\DataBase\Insert;
use Pet\Model\Model;
use Pet\Tools\Tools;


trait Delete
{


    /**
     * delAnd
     *
     * @param  array $ArrayColumnAndValue
     * @param  bool $returnThis default false
     * @return Delete
     */
    public function delele():Model
    {
        $this->strQuery =  "DELETE FROM `{$this->table}`";
        $this->SUB = "DELETE";
        return $this;
    }

}
