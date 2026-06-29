# Консольные команды

## Стартовый файл

В корне проекта должен находиться файл `pet` — точка входа для консольных команд:

```php
<?php

include './vendor/autoload.php';
include './vendor/pet/framework/function.php';
include './config.constant.php';

use Pet\Command\Command;

if ($argc == 1) die("Not arguments console \n");

$option = $argv;
unset($option[0]);
Command::init($option);
```

## Запуск команд

```bash
php pet [команда] [аргументы]
```

## Доступные команды

| Команда | Описание |
|---------|----------|
| [`serve`](../Command/Command.php:64) | Запускает встроенный PHP-сервер |
| [`migrate`](../Command/Command.php:44) | Выполняет миграции БД |
| [`socket`](../Command/Command.php:35) | Запускает WebSocket-сервер |
| [`make:model`](../Command/Command.php:47) | Создаёт новую модель |
| [`git-monitor`](../Command/Command.php:52) | Мониторинг изменений в Git с автосборкой |
| [`git-update`](../Command/Command.php:55) | Pull изменений и сборка |
| [`load`](../Command/Command.php:38) | Загрузка на сервер по FTP |
| [`load-diff`](../Command/Command.php:41) | Выгрузка только изменённых файлов |
| [`info`](../Command/Command.php:49) | Информация о командах |