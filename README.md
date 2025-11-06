# Pet Framework

framework for easy use

```
{
    "name":"my/project",
    "require":{
        "php":">=8.0",
        "pet/framework":"dev-main"
    }, 
    "autoload": {
        "psr-4": {
          "Pet\\": "vendor/pet/framework/"
        }
    },
    ,
  "files":[
    "/vendor/pet/framework/function.php"
  ],
    "scripts":
}

```

## Запуск сервера PET

```
php pet serve

```

> Запускает сервер из установленных настроек в .env URLDEV должен быть установлен обязательно.

> Можете запустить стартовую (шаблон) сборку командой.

```
php pet build_sample

```

## Миграции

Создает новую миграцию;
Создавайте миграцию через консоль чтобы небыло ошибок;

```
php pet make:migrate

```

Накатить все миграции

```
php pet migrate

```
