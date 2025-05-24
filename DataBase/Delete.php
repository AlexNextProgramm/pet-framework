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
        $from =$this->fromTable();
        $this->strQuery =  "DELETE $from";
        $this->SUB = "DELETE";
        return $this;
    }

}
