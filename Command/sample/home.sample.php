<!doctype html>
<html lang="en" data-bs-theme="auto">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
  <meta name="generator" content="Hugo 0.122.0">
  <title>Pet FrameWork</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

  <link rel="stylesheet" href="/view/css/style.css">
</head>

<body class="d-flex  text-center text-bg-dark">

  <div class="cover-container d-flex w-100  p-3 mx-auto flex-column">
    <header class="mb-auto">
      <div>
        <h3 class="float-md-start mb-0">Pet Framework</h3>
        <nav class="nav nav-masthead justify-content-center float-md-end">
          <a class="nav-link fw-bold py-1 px-0 active" aria-current="page" href="#">Начиная</a>
          <a class="nav-link fw-bold py-1 px-0" href="#">Документация</a>
          <a class="nav-link fw-bold py-1 px-0" href="#">Почему Pet?</a>
        </nav>
      </div>
    </header>

    <main class="px-3 mt-5 d-flex flex-column justify-content-center align-items-center">
      <h1>Начиная</h1>
      <a class="link" target="_blank" href="https://github.com/AlexNextProgramm/pet-framework">Репозиторий проекта</a>
      <p class="lead"> Для старта проекта вам потребуеться composer, php >= 8.0.</p>
      <p class="lead">Создайте файл composer.json. Проект запуститься и построет новый шаблон проекта</p>
      <code> composer install  <span class="copy"></span></code>
      
<pre class="code">
  <span class="copy"></span>
{
  "name": "test/farameworck",
  "autoload": {
      "psr-4": {
        "Pet\\": "vendor/pet/framework/"
      },
      files": [
            "vendor/pet/framework/function.php"
      ]
    },
    "require": {
        "php":">=8.0",
        "pet/framework":"dev-main"
    }
    "scripts":{
        "post-package-install":[
            "php ./vendor/pet/framework/Command/build_sample.php"
        ]
    }
}
</pre>

    </main>
    <main class="px-3 mt-4 d-flex flex-column justify-content-center align-items-center">
       <h2>Основные команды</h2>
       <br/>
       <p class="lead">Собрать шаблон</p>
       <code> php pet build_sample <span class="copy"></span></code>
       </br>
       <p class="lead">Создать миграцию</p>
       <i>Создаст файл мирграции <b>1_20241007_[name migration].php</b></i>
       <code> php pet make:migrate [name migration] <span class="copy"></span></code>
       <br/>
       <p class="lead">Накатить миграцию</p>
       <code> php pet migrate  <span class="copy"></span></code>
       <code> php pet migrate:up  <span class="copy"></span></code>
       <br/>
       <p class="lead">Откатить все миграции</p>
       <code> php pet migrate:back  <span class="copy"></span></code>
       <br/>
       <p class="lead">Откатить последнюю</p>
       <code> php pet migrate:back:end  <span class="copy"></span></code>
    </main>

    <footer class="mt-auto text-white-50">
      
    </footer>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>

</html>