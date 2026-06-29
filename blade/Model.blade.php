{{--
    Model.blade.php

    Blade-шаблон для генерации модели PET Framework.
    Используется командой: php pet make:model ModelName

    Переменные:
    - $namespace — пространство имён (App\Model)
    - $className — имя класса (User)
    - $table     — имя таблицы (users)
    - $fillable  — массив полей для fillable
    - $hidden    — массив скрытых полей
    - $casts     — массив кастов полей
    - $timestamps — использовать ли timestamps (bool)
    - $connection — подключение к БД (null|string)
--}}
<?php

namespace {{ $namespace }};

use Pet\Model\Model;

class {{ $className }} extends Model
{
    protected string $table = '{{ $table }}';

    @if($connection)
    protected static ?string $connection = '{{ $connection }}';
    @endif

    @if(!empty($fillable))
    protected array $fillable = [
        @foreach($fillable as $field)
        '{{ $field }}',
        @endforeach
    ];
    @endif

    @if(!empty($hidden))
    public array $hidden = [
        @foreach($hidden as $field)
        '{{ $field }}',
        @endforeach
    ];
    @endif

    @if(!empty($casts))
    protected array $casts = [
        @foreach($casts as $field => $type)
        '{{ $field }}' => '{{ $type }}',
        @endforeach
    ];
    @endif

    @if($timestamps)
    public bool $timestamps = true;
    @else
    public bool $timestamps = false;
    @endif

    /**
     * Получить все записи.
     *
     * @return array
     */
    public static function all(): array
    {
        $instance = new static();
        return $instance->find();
    }

    /**
     * Найти запись по ID.
     *
     * @param  int $id
     * @return static|null
     */
    public static function find(int $id): ?static
    {
        $instance = new static();
        $result = $instance->find(['id' => $id]);
        return !empty($result) ? $result[0] : null;
    }

    /**
     * Создать новую запись.
     *
     * @param  array $data
     * @return static|null
     */
    public static function create(array $data): ?static
    {
        $instance = new static();
        $id = $instance->create($data);
        return $id ? static::find($id) : null;
    }

    /**
     * Обновить запись.
     *
     * @param  int   $id
     * @param  array $data
     * @return bool
     */
    public static function update(int $id, array $data): bool
    {
        $instance = new static();
        return $instance->edit($id, $data);
    }

    /**
     * Удалить запись.
     *
     * @param  int $id
     * @return bool
     */
    public static function delete(int $id): bool
    {
        $instance = new static();
        return $instance->remove($id);
    }
}