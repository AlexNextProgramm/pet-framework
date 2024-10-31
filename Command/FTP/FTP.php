<?php


namespace FTP;

use Exception;

class Ftp{
    public $host = "";
    public $port = 21;

    public $login = "";
    public $password = "";
    public $connect = null;
    public $dirHost = "";
    public $pasv = true;
    private $dirpwd = "";
    public $fileIgnore = [];
    public $dirIgnore = [];

    function connect():bool
    {
        try{
            $this->connect = @ftp_connect($this->host, $this->port);
            return $this->connect && @ftp_login($this->connect,$this->login, $this->password)? true :false;
        } catch (Exception $e) {
            return false;
        }

    }
    
    function connectCount($count):bool
    {
        for ($i = 1; $i < $count; $i++) {

            if (@$this->connect()) {
                echo "\033[92m Connection ftp \033[0m \n\r";
               return true;
            } else {
                $n = $i + 1;
                echo " \033[37m Попытка подключения ftp $n \033[0m \n ";
                sleep(5);
            }
        }

        return false;

    }

    function loadfile($dirFile, $ftpDir, $fileName):bool
    {
        if(in_array($fileName, $this->fileIgnore) || in_array("$ftpDir.'/'.$fileName", $this->fileIgnore) ){
            echo " \033[31m Игнорируемый файл $fileName \n \033[0m";
            return false;
        }
        echo "Загружаю файл $fileName \n";

        $fp = fopen($dirFile.'/'. $fileName , 'r');

        if(ftp_fput($this->connect, $ftpDir.'/'.$fileName, $fp, FTP_BINARY)){
            echo "\033[92m Файл $fileName успешно загружен \033[0m\n";
            return true;
        }else{

            if($this->connectCount(5)){
                $this->dir($this->dirHost);
                ftp_pasv($this->connect, $this->pasv);
                if(ftp_fput($this->connect, $ftpDir . '/' . $fileName, $fp, FTP_BINARY)){
                    echo " \033[92m Файл $fileName успешно загружен \033[0m\n";
                }

            }
            echo " \033[31m При загрузке $fileName произошла проблема \033[0m \n";
            return false;
        }

        fclose($fp);
    }


    function dir($dir){
      
        if (ftp_chdir($this->connect, $dir))
        {
            $this->dirpwd = $dir;
          
            echo " \033[92m Новая текущая директория: " . ftp_pwd($this->connect) . "  \033[0m \n";
            return true;
        } else {
            echo " \033[31m Не удалось сменить директорию  \033[0m \n";
            return false;
        }

    }


    function deleteDir($dir, $files = false)
    {

        if($files){

            $lists = $this->list($dir);
            // удаляем файлы
            foreach ($lists as $list) {
                $full = $dir.'/'. $list['name'];
                if($list['type'] == 'dir'){
                    if(count($this->list($full)) == 0){

                        ftp_rmdir($this->connect, $full);

                    }else{

                        $this->deleteDir($full, true);
                    }
                }else{
                    ftp_delete($this->connect, $full);
                }
            }

            ftp_rmdir($this->connect, $dir);

       }else{
           ftp_rmdir($this->connect, $dir);

       }

    }



    function list($dir)
    {

            ftp_pasv($this->connect, $this->pasv);

         $dataFilesName = ftp_nlist($this->connect, $dir);

        if($data = ftp_rawlist($this->connect, $dir)){

            $array = [];
            foreach($data as $i => $txt){
                
                $array[$i] = [];
                preg_match("(-?[a-z]{1,})",$txt, $match);
                $array[$i]['type'] = str_contains($match[0],'dr')? "dir":"file";
                $array[$i]['name'] = array_reverse(explode('/', $dataFilesName[$i]))[0];
            }

            return $array;

        }

        return [];
    }

    function is_list($list_ftp, $name):bool
    {
        foreach($list_ftp as $list){
            if($list['name'] == $name) return true;
        }
        return false;
    }



    function close(){
        ftp_close($this->connect);
    }

    function putDirFiles($dirLocal, $dirHost){
        ftp_pasv($this->connect, $this->pasv);
        $files = scandir($dirLocal);

       foreach ($files as $file) {
           if ($file == '.' || $file == '..') continue;
           if(is_file($dirLocal . '/' . $file)){
               $this->loadfile($dirLocal, $dirHost,  $file );
           }
       }

     $list  = $this->list($dirHost);

       foreach($files as $file){
            if($file == '.' || $file == '..') continue;


            if(is_dir($dirLocal.'/'.$file)){

            if(in_array($file, $this->dirIgnore)){
                echo "\033[31m Игнорируемая директория $file  \033[0m \n";
                continue;
            }
            if(!$this->is_list($list, $file)){
                $this->createDir($dirHost . '/' . $file);
            }

            $this->putDirFiles($dirLocal."/".$file, $dirHost . '/' . $file);

            }
        }
    }


    function createDir($dir):bool
    {
        if (ftp_mkdir($this->connect, $dir)) {
            echo "\033[93m Создана директория $dir \n \033[0m";
            return true;
        } else {
            echo "\033[31m Не удалось создать директорию $dir  \033[0m  \n";
            return false;
        }
    }
}

?>