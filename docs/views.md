# Шаблоны (View)

PET Framework поддерживает два типа шаблонов:

1. **Обычные PHP-шаблоны** (`.php`) — через [`Pet\View\View`](../View/View.php)
2. **Blade-шаблоны** (`.blade.php`) — через [`Pet\View\Blade`](../View/Blade.php) и [`Pet\View\BladeCompiler`](../View/BladeCompiler.php)

---

## Обычные шаблоны (PHP)

### Отображение шаблона

```php
use Pet\View\View;

View::open('user.profile', ['name' => 'John', 'age' => 30]);
// загрузит view/user/profile.php
```

### Передача данных

```php
View::append(['title' => 'Главная']);
View::appendHtmlspecialchars(['user_input' => $unsafeData]); // XSS-защита
```

### Получение HTML без вывода

```php
$html = View::getTemplate('email.welcome', ['name' => 'John']);
```

### Вспомогательная функция

```php
view('user.profile', ['name' => 'John']);
```

### Проверка существования шаблона

```php
if (View::exists('user.profile')) {
    view('user.profile');
}
```

---

## Blade-шаблоны

Blade — Laravel-подобный шаблонизатор, который компилирует `.blade.php` файлы в чистый PHP и кеширует их.

### Рендеринг Blade-шаблона

```php
use Pet\View\Blade;

$html = Blade::render('user.profile', ['name' => 'John']);
// загрузит view/user/profile.blade.php
```

### Вспомогательная функция

```php
$html = blade('user.profile', ['name' => 'John']);
```

### Вывод данных

```php
{{ $name }}                // Экранированный вывод (htmlspecialchars)
{!! $html !!}              // Неэкранированный вывод (raw)
{{{ $name }}}              // Строгое экранирование (ENT_QUOTES)
{{-- комментарий --}}      // Blade-комментарий (не попадает в HTML)
```

### Условные директивы

```blade
@if ($condition)
    ...
@elseif ($other)
    ...
@else
    ...
@endif

@unless ($condition)
    ...
@endunless

@isset($variable)
    ...
@endisset

@empty($items)
    ...
@endempty

@auth
    Пользователь авторизован
@endauth

@guest
    Гость
@endguest

@production
    Только в production
@endproduction

@env('local')
    Только в локальном окружении
@endenv
```

### Циклы

```blade
@for ($i = 0; $i < 10; $i++)
    {{ $i }}
@endfor

@foreach ($users as $user)
    {{ $user->name }}
@endforeach

@forelse ($users as $user)
    {{ $user->name }}
@empty
    Нет пользователей
@endforelse

@while ($condition)
    ...
@endwhile

@continue($condition)
@break($condition)
```

### Switch

```blade
@switch($role)
    @case('admin')
        Администратор
        @break
    @case('user')
        Пользователь
        @break
    @default
        Гость
@endswitch
```

### Макеты и секции

**layout.blade.php:**
```blade
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title', 'Default Title')</title>
    @stack('styles')
</head>
<body>
    @yield('content')

    @stack('scripts')
</body>
</html>
```

**page.blade.php:**
```blade
@extends('layout')

@section('title', 'Моя страница')

@section('content')
    <h1>{{ $heading }}</h1>
    <p>{{ $message }}</p>
@endsection

@push('scripts')
    <script src="/js/app.js"></script>
@endpush
```

**Рендеринг:**
```php
echo Blade::render('page', ['heading' => 'Привет', 'message' => 'Текст']);
```

### Директивы секций

| Директива | Описание |
|---|---|
| `@extends('layout')` | Указывает, что шаблон наследует лейаут |
| `@section('name')` | Начинает секцию |
| `@section('name', 'content')` | Инлайн-секция |
| `@endsection` / `@stop` | Завершает секцию |
| `@yield('name')` | Выводит содержимое секции |
| `@yield('name', 'default')` | Выводит секцию со значением по умолчанию |
| `@show` | Завершает секцию и сразу выводит её |
| `@append` | Добавляет к существующей секции |
| `@overwrite` | Перезаписывает секцию |

### Стеки (@push / @prepend / @stack)

```blade
@push('scripts')
    <script src="/js/vendor.js"></script>
@endpush

@prepend('scripts')
    <script src="/js/essential.js"></script>
@endprepend

@stack('scripts')
```

### Компоненты

```php
// Регистрация компонента
Blade::component('alert', 'components.alert');

// Рендеринг
echo Blade::renderComponent('alert', ['type' => 'success']);
```

### Атрибуты форм

```blade
@csrf                                   // CSRF-токен
@method('PUT')                          // SPOOF-метод
@checked($isActive)                     // checked="checked"
@selected($isSelected)                  // selected="selected"
@disabled($isDisabled)                  // disabled="disabled"
@readonly($isReadonly)                  // readonly="readonly"
@required($isRequired)                  // required="required"
```

### Включение подшаблонов

```blade
@include('header')
@include('sidebar', ['active' => 'home'])

@includeIf('ads.banner')               // только если существует
@includeWhen($show, 'sidebar')          // по условию
@includeUnless($hide, 'footer')         // если НЕ условие
@includeFirst(['custom', 'default'])    // первый существующий
```

### Прочие директивы

```blade
@json($data)                            // JSON-вывод
@js($data)                              // JS-инлайн данные
@css('/css/app.css')                    // CSS-ссылка
@vite('resources/js/app.js')            // Vite-скрипт
@verbatim @{{ $name }} @endverbatim     // Сырой вывод (без компиляции)
@php $count = 1; @endphp                // PHP-блок
@dd($variable)                          // Дамп и завершение
@dump($variable)                        // var_dump
@debug ... @enddebug                    // Только при APP_DEBUG=true
@class(['active' => $isActive])         // Условные CSS-классы
@style(['color' => 'red'])              // Условные inline-стили
@use(Iterator::class)                   // PHP use
@props(['type' => 'default'])           // Свойства компонента
@aware(['type' => 'default'])           // Свойства из родителя
@once ... @endonce                      // Выполняется один раз
```

### Пользовательские директивы

```php
use Pet\View\BladeCompiler;

BladeCompiler::directive('datetime', function ($expression) {
    return "<?php echo date('Y-m-d H:i:s', strtotime($expression)); ?>";
});
```

### Пользовательские условия

```php
BladeCompiler::if('admin', function () {
    return auth()->role() === 'admin';
});
```

```blade
@admin
    Привет, админ!
@endadmin
```

### Очистка кеша Blade

```php
Blade::clearCache();
```

### Конфигурация

```php
Blade::setViewDir('/custom/path/to/views');
Blade::setCacheDir('/custom/path/to/cache');
```

По умолчанию:
- Директория шаблонов: `VIEW_DIR` из конфигурации
- Директория кеша: `STORAGE_DIR/blade_cache/`