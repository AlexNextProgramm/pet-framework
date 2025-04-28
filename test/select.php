<?php
include "./autoload.php";

use Pet\Model\Model;

class  M extends Model {
    protected string $table = 'users';
}


// Тестирование
$data = [
    'name' => "rarЕНr",
    "surname" => 'ashahas',
    "phone" => '7977596863',
    "password"=> "ascdhcjvbcghsvd",
];
$Model = new M($data, true);
print_r($Model->max());
// $Model->find(['id'=> '1']);
// $Model->set($data);
// dd($str);
