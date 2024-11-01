<?php
namespace Pet\Migration;


class Table{
    public $param = [];
    public $engine = "ENGINE = InnoDB";

    public function string(string $name, int $length = 255): Table
    {
        $this->param[] = "`$name` VARCHAR($length) NOT NULL";
        return $this;
    }

    public function id($id = 'id'){
        $this->param[] = "`$id` INT NOT NULL AUTO_INCREMENT";
        $this->param[] = "PRIMARY KEY (`$id`)";
        return $this;
    }

    public function text(string $name): Table
    {
        $this->param[] = "`$name` TEXT NOT NULL";
        return $this;
    }

    public function date(string $name): Table
    {
        $this->param[] = "`$name` DATE NOT NULL";
        return $this;
    }

    public function datetime(string $name): Table
    {
        $this->param[] = "`$name` DATETIME NOT NULL";
        return $this;
    }

    public function int(string $name, int $length = 60):Table
    {
        $this->param[] = " `$name` INT($length) NULL"; 
        return $this;
    }

    public function boolean(string $name):Table
    {
        $this->param[] = " `$name` BOOLEAN NULL";
        return $this;
    }

    public function timestamp(string $name = "date_time"):Table
    {
        $this->param[] = " `$name` DATETIME NULL DEFAULT CURRENT_TIMESTAMP";
        return $this;
    }

    public function null(): Table
    {
        $i = array_key_last($this->param);
        $str = $this->param[$i];
        $str  = str_replace( "NOT", "", $str);
        $this->param[$i] = str_replace("NULL", "NULL DEFAULT NULL", $str);
        return $this;
    }

    public function storage(string $type = "InnoDB")
    {
        $this->engine = "ENGINE = $type";
    }

    public function default($default){

        $i = array_key_last($this->param);
        $attr =  $this->param[$i];

        if(gettype($default) == 'boolean'){
            $default? $attr.= " DEFAULT TRUE "
            : $attr .= " DEFAULT FALSE ";
        }

        if (gettype($default) == 'string') $attr.=" DEFAULT '$default' ";

        
        $this->param[$i] = $attr;

        return $this;

     }

}
?>