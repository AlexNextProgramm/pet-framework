# Модели и ORM

Модели наследуются от [`Pet\Model\Model`](../Model/Model.php) и используют traits [`Select`](../DataBase/Select.php), [`Insert`](../DataBase/Insert.php), [`Update`](../DataBase/Update.php), [`Delete`](../DataBase/Delete.php) для операций CRUD. Базовый класс [`DB`](../DataBase/DB.php) предоставляет подключение к БД через PDO.

## Определение модели

```php
use Pet\Model\Model;

class User extends Model
{
    protected string $table = 'users';
    public array $hidden = ['password']; // скрытые поля при data()
    protected bool $allowMultiple = false; // true — исключение при >1 строки
    protected static ?string $connection = null; // имя подключения для модели
}
```

## Конструктор

```php
// По ID — загружает запись
$user = new User(1);

// По массиву условий
$user = new User(['email' => 'john@example.com']);

// Создание, если не существует
$user = new User(['name' => 'John'], true); // $isNotExistCreate = true

// С указанием подключения к БД
$user = new User(1, false, 'analytics');
```

## Основные операции

```php
// Поиск
$users = (new User())->find(['role' => 'admin']);
$user  = (new User(1)); // по ID

// Создание
$user = new User(['name' => 'John', 'email' => 'john@example.com'], true);
// или
$id = $user->create(['name' => 'John', 'email' => 'john@example.com']);

// Обновление
$user->set('name', 'Jane');
// или
$user->set(['name' => 'Jane', 'email' => 'jane@example.com']);

// Удаление
$user->delete();

// Массовое удаление
$user->findDelete(['role' => 'guest']);
```

## Построитель запросов (Fluent API)

Все методы построителя возвращают `$this`, что позволяет выстраивать цепочки вызовов.

```php
$users = (new User())
    ->select('id', 'name')
    ->where('role = ?', ['admin'])
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->offset(0)
    ->fetch();
```

## Полный список методов

### Выборка (Select)

| Метод | Описание |
|-------|----------|
| [`select()`](../DataBase/Select.php:23) | Указать поля для выборки (`select('id', 'name')`) |
| [`selectDistinct()`](../DataBase/Select.php:48) | Выборка с DISTINCT |
| [`find()`](../Model/Model.php:106) | Поиск по массиву полей, возвращает массив результатов |
| [`findM()`](../Model/Model.php:128) | Поиск, возвращает массив моделей |
| [`findAll()`](../Model/Model.php:140) | Все записи из таблицы |
| [`findBy()`](../Model/Model.php:153) | Поиск по полю и значению (`findBy('email', 'a@b.com')`) |
| [`findByM()`](../Model/Model.php:166) | Поиск по полю, возвращает массив моделей |
| [`findById()`](../DataBase/Select.php:453) | Поиск по ID, возвращает одну запись |
| [`first()`](../DataBase/Select.php:441) | Первая запись |
| [`pluck()`](../Model/Model.php:179) | Массив значений одного поля (`pluck('email')`) |
| [`count()`](../DataBase/Select.php:74) | Количество записей (`count('*', 'total')`) |
| [`sum()`](../DataBase/Select.php:87) | Сумма значений поля |
| [`avg()`](../DataBase/Select.php:100) | Среднее значение поля |
| [`min()`](../DataBase/Select.php:113) | Минимальное значение поля |
| [`max()`](../DataBase/Select.php:126) | Максимальное значение поля |
| [`fetch()`](../DataBase/DB.php:138) | Выполнить запрос и вернуть результат |
| [`chunk()`](../Model/Model.php:192) | Обработка записей частями по N штук |

### Условия (WHERE)

| Метод | Описание |
|-------|----------|
| [`where()`](../DataBase/Select.php:197) | Условие (`where('age', 18, '>')` или `where('role = ?', ['admin'])`) |
| [`whereAdd()`](../DataBase/Select.php:217) | Добавить условие с произвольным разделителем |
| [`whereRaw()`](../DataBase/Select.php:232) | Сырой SQL в WHERE |
| [`whereId()`](../DataBase/Select.php:387) | По ID (`whereId(42)`) |
| [`whereNull()`](../DataBase/Select.php:243) | `WHERE column IS NULL` |
| [`whereNotNull()`](../DataBase/Select.php:254) | `WHERE column IS NOT NULL` |
| [`whereBetween()`](../DataBase/Select.php:267) | `WHERE column BETWEEN x AND y` |
| [`whereIn()`](../DataBase/Select.php:279) | `WHERE column IN (a, b, c)` |
| [`and()`](../DataBase/Select.php:292) | Дополнительное условие с AND |
| [`or()`](../DataBase/Select.php:304) | Дополнительное условие с OR |

### Сортировка и группировка

| Метод | Описание |
|-------|----------|
| [`orderBy()`](../DataBase/Select.php:317) | Сортировка (`orderBy('created_at', 'DESC')`) |
| [`orderByDesc()`](../DataBase/Select.php:340) | Сортировка по убыванию |
| [`groupBy()`](../DataBase/Select.php:351) | Группировка |
| [`having()`](../DataBase/Select.php:374) | Фильтрация после GROUP BY |

### Лимит и пагинация

| Метод | Описание |
|-------|----------|
| [`limit()`](../DataBase/Select.php:401) | Ограничение количества записей |
| [`offset()`](../DataBase/Select.php:414) | Смещение |
| [`page()`](../DataBase/Select.php:428) | Пагинация (`page(2, 20)` — страница 2, по 20 записей) |

### Join

| Метод | Описание |
|-------|----------|
| [`join()`](../DataBase/Select.php:152) | Присоединение таблицы (`join('posts', 'INNER')`) |
| [`on()`](../DataBase/Select.php:169) | Условие JOIN |

```php
$posts = (new Post())
    ->select('posts.*', 'users.name as author')
    ->join('users', 'LEFT')
    ->on(['posts.user_id', 'users.id', '='])
    ->fetch();
```

Поддерживаются множественные JOIN:

```php
$data = (new Order())
    ->select('orders.*', 'users.name', 'products.title')
    ->join('users')
    ->on(['orders.user_id', 'users.id', '='])
    ->join('products', 'LEFT')
    ->on(['orders.product_id', 'products.id', '='])
    ->where('orders.status', 'paid')
    ->fetch();
```

### Вставка (Insert)

| Метод | Описание |
|-------|----------|
| [`insert()`](../DataBase/Insert.php:16) | Вставка записи, возвращает bool |
| [`create()`](../DataBase/Insert.php:35) | Вставка с возвратом ID и установкой info |
| [`insertBatch()`](../DataBase/Insert.php:51) | Массовая вставка нескольких записей |
| [`insertOnDuplicate()`](../DataBase/Insert.php:79) | INSERT с ON DUPLICATE KEY UPDATE |
| [`replace()`](../DataBase/Insert.php:112) | REPLACE INTO (MySQL) |

```php
// Простая вставка
$id = $user->create(['name' => 'John', 'email' => 'john@example.com']);

// Массовая вставка
$user->insertBatch([
    ['name' => 'John', 'email' => 'john@example.com'],
    ['name' => 'Jane', 'email' => 'jane@example.com'],
]);

// Вставка или обновление при дубликате
$user->insertOnDuplicate(
    ['email' => 'john@example.com', 'name' => 'John', 'visits' => 1],
    ['name', 'visits'] // поля для обновления
);

// REPLACE
$user->replace(['id' => 1, 'name' => 'John', 'email' => 'john@example.com']);
```

### Обновление (Update)

| Метод | Описание |
|-------|----------|
| [`update()`](../DataBase/Update.php:17) | Построитель UPDATE, возвращает `$this` для цепочки |
| [`updateBatch()`](../DataBase/Update.php:34) | Массовое обновление через CASE |
| [`increment()`](../DataBase/Update.php:73) | Увеличение поля на значение |
| [`decrement()`](../DataBase/Update.php:88) | Уменьшение поля на значение |
| [`set()`](../Model/Model.php:233) | Установка полей текущей модели (требует info и id) |

```php
// Обновление с условием
$user->update(['name' => 'Jane'])->whereId(1)->execute();

// Инкремент/декремент
$post->increment('views', 1)->whereId(42)->execute();
$product->decrement('stock', 1)->whereId(10)->execute();

// Массовое обновление
$user->updateBatch([
    ['id' => 1, 'name' => 'John', 'role' => 'admin'],
    ['id' => 2, 'name' => 'Jane', 'role' => 'user'],
]);

// Установка полей загруженной модели
$user = new User(1);
$user->set('name', 'Jane');
$user->set(['name' => 'Jane', 'email' => 'jane@example.com']);
```

### Удаление (Delete)

| Метод | Описание |
|-------|----------|
| [`delete()`](../DataBase/Delete.php:16) | Удаление текущей записи или построитель DELETE |
| [`deleteById()`](../DataBase/Delete.php:33) | Удаление по ID |
| [`deleteWhere()`](../DataBase/Delete.php:48) | Удаление с условием |
| [`truncate()`](../DataBase/Delete.php:60) | Очистка таблицы (TRUNCATE) |
| [`softDelete()`](../DataBase/Delete.php:73) | Мягкое удаление (устанавливает `deleted_at = NOW()`) |
| [`restore()`](../DataBase/Delete.php:90) | Восстановление soft-deleted записи |
| [`findDelete()`](../Model/Model.php:347) | Найти и удалить |

```php
// Удаление текущей записи
$user = new User(1);
$user->delete();

// Удаление по ID
$user->deleteById(1);

// Удаление с условием
$user->deleteWhere('role', 'guest');

// Мягкое удаление
$user->softDelete()->whereId(1)->execute();

// Восстановление
$user->restore()->whereId(1)->execute();

// Очистка таблицы
$user->truncate();

// Найти и удалить
$user->findDelete(['role' => 'guest']);
```

### Работа с загруженной моделью

| Метод | Описание |
|-------|----------|
| [`data()`](../Model/Model.php:328) | Данные модели с учётом `$hidden` |
| [`get()`](../DataBase/DB.php:276) | Значение поля (`$user->get('name')`) |
| [`__get()`](../Model/Model.php:82) | Магический доступ (`$user->name`) |
| [`__set()`](../Model/Model.php:94) | Магическая установка (`$user->name = 'John'`) |
| [`exist()`](../Model/Model.php:263) | Проверка наличия данных в модели |
| [`isInfo()`](../DataBase/DB.php:265) | Проверка, загружены ли данные |
| [`fresh()`](../Model/Model.php:365) | Обновить данные из БД |
| [`reboot()`](../Model/Model.php:249) | Перезагрузить модель по ID |
| [`toArray()`](../Model/Model.php:378) | Преобразовать в массив (алиас `data()`) |
| [`getInfo()`](../DataBase/DB.php:297) | Получить весь массив info |

```php
$user = new User(1);

// Доступ к полям
echo $user->get('name');    // "John"
echo $user->name;           // "John" (магический __get)
$user->name = 'Jane';       // магический __set (вызов set())

// Данные с исключением скрытых полей
$data = $user->data();      // ['id' => 1, 'name' => 'John'] — без 'password'
$array = $user->toArray();  // то же самое

// Проверки
if ($user->exist()) { /* запись существует */ }
if ($user->isInfo()) { /* данные загружены */ }

// Обновить из БД
$user->fresh();

// Перезагрузить
$user->reboot();
```

### Условные операции

| Метод | Описание |
|-------|----------|
| [`ifExistSetOrCreate()`](../Model/Model.php:280) | Обновить если существует, иначе создать |
| [`ifExistDelete()`](../Model/Model.php:307) | Удалить если существует |

```php
// Обновить или создать
$user->ifExistSetOrCreate(
    ['name' => 'John', 'email' => 'john@example.com'],
    ['email' => 'john@example.com'] // условие поиска
);

// Удалить если существует
$user->ifExistDelete(['role' => 'guest']);
```

### Статические методы

| Метод | Описание |
|-------|----------|
| [`createNew()`](../Model/Model.php:389) | Создать запись и вернуть модель |

```php
$user = User::createNew(['name' => 'John', 'email' => 'john@example.com']);
// Вернёт null при неудаче
```

### Настройка таблицы и алиаса

```php
$user = (new User())
    ->setTable('users')           // установить таблицу
    ->setTableAlias('u');         // установить псевдоним
```

### Множественные подключения к БД

Модель поддерживает работу с разными базами данных:

```php
// Через статическое свойство
class Analytics extends Model
{
    protected static ?string $connection = 'analytics';
}

// Через конструктор
$user = new User(1, false, 'analytics');

// Через метод
$user->setConnection('analytics');
```

### Транзакции

Доступны через базовый класс [`DB`](../DataBase/DB.php):

```php
$user = new User();

try {
    $user->beginTransaction();

    $user->create(['name' => 'John', 'balance' => 100]);
    $order->create(['user_id' => 1, 'amount' => 50]);

    $user->commit();
} catch (\Throwable $e) {
    $user->rollback();
}

// Проверка активности транзакции
$user->inTransaction();
```

### Прямые SQL-запросы

```php
$result = $user->q("SELECT * FROM users WHERE role = 'admin'")->fetchAll();

// Построение запроса вручную
$user->strQuery = "SELECT * FROM users WHERE id = 1";
$data = $user->fetch(false);
```

### Получение мета-информации

```php
$user->getTableName();      // имя таблицы
$user->getDbName();         // имя базы данных
$user->lastInsertId();      // последний вставленный ID
$user->endError();          // последняя ошибка
$user->isTable();           // проверка существования таблицы migrate
$user->getConnectionName(); // имя текущего подключения