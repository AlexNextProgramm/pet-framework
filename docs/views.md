# Шаблоны (View)

Шаблонизатор находится в [`Pet\View\View`](../View/View.php).

## Отображение шаблона

```php
use Pet\View\View;

View::open('user.profile', ['name' => 'John', 'age' => 30]);
// загрузит view/user/profile.php
```

## Передача данных

```php
View::append(['title' => 'Главная']);
View::appendHtmlspecialchars(['user_input' => $unsafeData]); // XSS-защита
```

## Получение HTML без вывода

```php
$html = View::getTemplate('email.welcome', ['name' => 'John']);
```

## Вспомогательная функция

```php
view('user.profile', ['name' => 'John']);