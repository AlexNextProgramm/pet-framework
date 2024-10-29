<?php

namespace Pet\DataBase;

use Pet\DataBase\Insert;
use Pet\Model\Model;
use Pet\Tools\Tools;


trait Delete {


    /**
     * delAnd
     *
     * @param  array $ArrayColumnAndValue
     * @param  bool $returnThis default false
     * @return Delete
     */
    function del(array $ArrayColumnAndValue = [], $returnThis = false): Model|bool {
        $this->strQuery =  "DELETE FROM `{$this->table}` WHERE " . Tools::array_implode(" AND ", $ArrayColumnAndValue, "`[key]` = '[val]'");
        $this->strQuery = $this->delSoft($ArrayColumnAndValue);
        return $returnThis ? $this : $this->DB->prepare($this->strQuery)->execute();
    }

    /**
     * delOr
     *
     * @param  array $ArrayColumnAndValue
     * @param  bool $returnThis default false
     * @return Delete
     */
    function delOr(array $ArrayColumnAndValue = [], $returnThis = false): Model|bool {
        $this->strQuery =  "DELETE FROM `{$this->table}` WHERE " . Tools::array_implode(" OR ", $ArrayColumnAndValue, "`[key]` = '[val]'");
        $this->strQuery = $this->delSoft($ArrayColumnAndValue);
        return $returnThis ? $this : $this->DB->prepare($this->strQuery)->execute();
    }

    /**
     * delSoft
     *
     * @param array $ArrayColumnAndValue
     * @return string
     */
    private function delSoft(array $ArrayColumnAndValue): string {
        return !$this->isSoftRemoval ?
            $this->strQuery : ""; // подумать о мягком удалении
    }
}
