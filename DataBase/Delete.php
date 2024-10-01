<?php

namespace Pet\DataBase;

use Pet\DataBase\Insert;
use Pet\Tools\Tools;


class Delete extends Insert {


    /**
     * delAnd
     *
     * @param  array $ArrayColumnAndValue
     * @param  bool $returnThis default false
     * @return Delete
     */
    function del(array $ArrayColumnAndValue = [], $returnThis = false): Delete|bool {
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
    function delOr(array $ArrayColumnAndValue = [], $returnThis = false): Delete|bool {
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
