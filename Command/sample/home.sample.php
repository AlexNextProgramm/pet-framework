<!doctype html>
<html lang="en" data-bs-theme="auto">
<? view("head");?>
<body class="d-flex  text-center text-bg-dark">
  <div class="cover-container d-flex w-100  p-3 mx-auto flex-column">
  <? view("header");?>
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

    <? view('footer')?>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

</body>

</html>