<?php
namespace Pet\Command\FTP;

use Pet\Command\FTP\Ftp;
use Pet\Command\Console\Console;

class ConnectFtp {

    const ROOT = ROOT . DS;
    public static $PUBLIC_DIR;
    public static $VENDOR_DIR;
    public static $IGNORE_DIR;
    public static $IGNORE_FILE;
    public static $isInput = false;

    public function __construct() {
        self::$PUBLIC_DIR = self::ROOT . env('PUBLIC_DIR', 'dist');
        self::$VENDOR_DIR = self::ROOT . '/vendor';
        self::$IGNORE_DIR = explode("|", env('FTP_DIR_EXEPTION'));
        self::$IGNORE_FILE = explode("|", env('FTP_FILE_EXEPTION'));
    }

    public static function load() {
        new self();

        //  Запись ветки для отчетности;
        Console::cmd(
            "cd '" . __DIR__ . "' && git branch",
            function ($out) {
                if (strpos($out, "*") !== false) {
                    $file = self::$PUBLIC_DIR . '/.gitinfo';
                    $text = "DT: " . date('d.m.Y H:i:s') . " Branch: " . $out . "\n";
                    file_put_contents($file, $text, FILE_APPEND);
                }
            }
        );

        //Построить проект npm
        Console::text("Выполнить build Webpack перед загрузкой на сервер? (y/n)", 'yellow');
        $outInput = '';
        Console::input($outInput);

        if (Console::isYes($outInput)) {
            Console::cmd('cd "' . self::ROOT . '" && npm run build', fn($txt) => Console::text($txt, 'violet'));
        }

        //Загрузка vendor
        Console::text("Выполнить загрузку папки vendor? (y/n)", 'yellow');
        Console::input($outInput);
        $isVendor = Console::isYes($outInput);

        $ftp = new Sftp();
        $ftp->host = env('FTP_HOST');
        $ftp->login = env('FTP_LOGIN');
        $ftp->fileIgnore = self::$IGNORE_FILE;
        $ftp->dirIgnore = self::$IGNORE_DIR;

        if (trim(env('FTP_PASSWORD')) == '') {
            Console::text("Нет пароля  FTP (*_*)", 'red');
            return false;
        }


        $ftp->password = env('FTP_PASSWORD');
        if ($ftp->connectCount(5)) {
            $ftp->dirHost = env('FTP_HOST_DIR');
            $ftp->dir(env('FTP_HOST_DIR'));
            $ftp->putDirFiles(self::$PUBLIC_DIR, 'dist');
            if ($isVendor == 'y') {
                $ftp->putDirFiles(self::$VENDOR_DIR, 'vendor');
            }
            $ftp->close();
        } else {
            Console::text("Не удалось подключиться по  FTP...", 'red');
        }
    }
}
