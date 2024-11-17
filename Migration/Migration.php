<?php

namespace Pet\Migration;

abstract class Migration {

    abstract function up(): void;
    abstract function back(): void;

    static function createTableMigrate() {
        Schema::create('migration', function (Table $table) {
            $table->string('name')->null();
            $table->int('status', 1)->default(false);
            $table->string('error')->null();
        });
    }
}
