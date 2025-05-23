<?php
namespace Pet\Model;
class MakeModel
{
    protected static $DIR = ROOT. DS . PUBLIC_DIR . DS . APP;
    protected static $nameFolder = "Model";
    public function __construct($name)
    {

        if (!is_dir(self::$DIR.DS.self::$nameFolder)) {
            mkdir(self::$DIR.DS.self::$nameFolder, 0777, true);
        }
        $name = ucfirst($name);
        $this->createModel($name);
    }

    private function createModel(string $name)
    {
        $sample = file_get_contents(__DIR__ . '/../Command/sample/Model.sample.php');
        $sample = str_replace([
            "NAME",
            "SPACE"
        ], [
            $name,
            self::$nameFolder
        ], $sample);
        $folder = self::$DIR . DS . self::$nameFolder . DS . "$name.php";
        file_put_contents("$folder", $sample);
    }
}
