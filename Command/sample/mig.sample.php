<?php

use Pet\Migration\Migration;
use Pet\Migration\Schema;
use Pet\Migration\Table;

class {name} extends Migration
{
    public function up(): void
    {
        // Создает таблицу
        Schema::create("{name}", function (Table $t) {
            $t->string("name")->null();
        });
        // Добавляет в таблицу
        Schema::add("{name}", function (Table $t) {
            $t->boolean("active")->default(true);
            $t->string("password", 500)->null();
            $t->text("tokenN")->null();
        });
        // Изменяет в таблице
        Schema::change("{name}", function (Table $t) {
            $t->string("tokenN token", 500)->null();
        });
    }
    public function back(): void
    {
        Schema::drop("{name}", "tokenN"); //удаляет колонку в таблице
        Schema::drop("{name}"); // удаляет таблицу
    }
}