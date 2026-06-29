# Контроллеры

Контроллеры наследуются от [`Pet\Controller`](../Controller.php).

```php
namespace App\Controller;

use Pet\Controller;

class UserController extends Controller
{
    public function index() {
        return ['users' => User::all()];
    }

    public function store() {
        $data = attrs(); // все входные данные
        // ...
    }
}
```

Базовый контроллер предоставляет метод [`saveFile()`](../Controller.php:15) для загрузки файлов.