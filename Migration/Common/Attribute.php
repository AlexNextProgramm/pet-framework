<?php

namespace Pet\Migration\Common;

use Pet\Migration\Table;

class Attribute {
    public Table $table;

    public function __construct(Table $Table) {
        $this->table = $Table;
    }

    public function default($default): Attribute {

        $i = array_key_last($this->table->param);
        $attr =  $this->table->param[$i];

        if (gettype($default) == 'boolean') {
            $default ? $attr .= " DEFAULT TRUE "
                : $attr .= " DEFAULT FALSE ";
        }

        if (gettype($default) == 'string') $attr .= " DEFAULT '$default' ";
        $this->table->param[$i] = $attr;
        return new Attribute($this->table);
    }

    public function null(): Attribute {
        $i = array_key_last($this->table->param);
        $str = $this->table->param[$i];
        $str  = str_replace("NOT", "", $str);
        $this->table->param[$i] = str_replace("NULL", "NULL DEFAULT NULL", $str);
        return new Attribute($this->table);;
    }

    public function character($name = 'utf8mb4', $collate = 'utf8mb4_0900_ai_ci'): Attribute {
        $i = array_key_last($this->table->param);
        $this->table->param[$i] = str_replace("{charset}", " CHARACTER SET $name COLLATE $collate", $this->table->param[$i]);
        return new Attribute($this->table);
    }
}
