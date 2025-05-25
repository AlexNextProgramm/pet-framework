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
    public function delete():Model
    {
        $this->strQuery =  "DELETE {$this->table}";
        $this->SUB = "DELETE";
        if ($this->isInfo()){
            $this->whereId($this->get('id'));
            $this->fetch();
        }
        return $this;
    }

}
