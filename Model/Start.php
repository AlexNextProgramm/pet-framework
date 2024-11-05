<?php

namespace Pet\Model;

use Pet\Command\Build;
use Pet\Migration\Table;

class Start
{
    public Build|null $Build = null;

    public function __construct()
    {
        $this->Build = new Build();
        $this->Build->isPetWarning = true;
    }

    public function init($name)
    {
        $this->Build->setFile('Model.php', env("PUBLIC_DIR", "dist") . "/PHP/Model/", [
            "NAME" => ucwords($name),
            "SPACE" => "PHP\\Model",
            "TABLE" => strtolower($name),
        ]);
    }
}
