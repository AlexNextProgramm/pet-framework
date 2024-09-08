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
    "scripts":{
        "post-package-install":[
            "php ./vendor/pet/framework/Command/Build.php"
        ]
    }

}

```
 ## Запуск сервера PET

 ```
 php pts serve

 ```
 > Запускает сервер из установленных настроек в .env URLDEV
