# Файловая библиотека (File)

Фреймворк предоставляет полноценную объектную файловую библиотеку в пространстве имён [`Pet\File`](../File/). Она включает классы для работы с отдельными файлами, коллекциями, хранилищами, изображениями и MIME-типами.

## Класс File

[`Pet\File\File`](../File/File.php) — объектное представление отдельного файла в файловой системе.

```php
use Pet\File\File;

$file = new File('/path/to/file.txt');

// Метаданные
$file->path();              // /path/to/file.txt
$file->name();              // file.txt
$file->filename();          // file
$file->extension();         // txt
$file->dirname();           // /path/to
$file->size();              // размер в байтах
$file->sizeFormatted();     // "1.23 MB"
$file->mimeType();          // "text/plain"
$file->hash();              // md5-хеш
$file->hash('sha256');      // sha256-хеш
$file->lastModified();      // Unix timestamp
$file->permissions();       // "0644"
$file->owner();             // UID владельца
$file->group();             // GID группы

// Проверки
$file->exists();
$file->isFile();
$file->isReadable();
$file->isWritable();
$file->isImage();
$file->isText();
$file->isArchive();
$file->isPdf();

// Чтение и запись
$content = $file->content();            // чтение всего файла
$file->put('new content');              // перезапись
$file->append('more content');          // добавление в конец
$file->prepend('prefix content');       // добавление в начало
$lines = $file->lines();                // чтение построчно

// Операции с файлами
$copied = $file->copy('/new/path.txt');     // копирование
$file->move('/new/path.txt');               // перемещение
$file->rename('newname.txt');               // переименование
$file->delete();                            // удаление

// Конвертация изображения в WebP
$webpFile = $file->convertImage(80);

// Из загруженного файла
$uploaded = File::fromUpload($_FILES['avatar']);

// Сериализация
$info = $file->toArray();
```

## FileCollection

[`Pet\File\FileCollection`](../File/FileCollection.php) — коллекция файлов с поддержкой фильтрации, сортировки и массовых операций. Реализует `Countable` и `IteratorAggregate`.

```php
use Pet\File\FileCollection;

// Создание коллекции
$collection = new FileCollection([$file1, $file2]);

// Из glob-паттерна
$collection = FileCollection::fromGlob('/path/to/*.txt');

// Из директории
$collection = FileCollection::fromDirectory('/path/to/dir');
$collection = FileCollection::fromDirectory('/path/to/dir', '*.jpg');

// Из загруженных файлов (поддержка вложенных массивов)
$collection = FileCollection::fromUploadedFiles($_FILES['photos']);

// Фильтрация
$images    = $collection->images();
$texts     = $collection->texts();
$archives  = $collection->archives();
$byExt     = $collection->byExtension('jpg', 'png');
$byMime    = $collection->byMimeType('image/jpeg');
$large     = $collection->largerThan(1024 * 1024);  // больше 1MB
$small     = $collection->smallerThan(50000);        // меньше 50KB

// Сортировка
$sorted = $collection->sortByName();
$sorted = $collection->sortBySize(false);   // по убыванию
$sorted = $collection->sortByDate();
$sorted = $collection->sortByExtension();

// Массовые операции
$collection->each(fn(File $f) => /* ... */);
$collection->map(fn(File $f) => $f->name());
$collection->filter(fn(File $f) => $f->size() > 1000);
$collection->copyTo('/backup/');
$collection->moveTo('/archive/');
$collection->deleteAll();

// Агрегация
$collection->totalSize();           // суммарный размер
$collection->totalSizeFormatted();  // "15.42 MB"
$collection->names();               // массив имён
$collection->paths();               // массив путей
$collection->extensions();          // массив расширений
$collection->toArray();             // массив метаданных

// Доступ к элементам
$collection->first();
$collection->last();
$collection->get(3);
$collection->isEmpty();
count($collection);

// Итерация
foreach ($collection as $file) {
    echo $file->name();
}
```

## FileManager

[`Pet\File\FileManager`](../File/FileManager.php) — синглтон-менеджер для управления дисками (Storage) и вспомогательных операций.

```php
use Pet\File\FileManager;

// Доступ к дискам
$local  = FileManager::disk('local');   // var/uploads/
$public = FileManager::disk('public');  // public_html/uploads/
$tmp    = FileManager::disk('tmp');     // sys_get_temp_dir()/pet-uploads/

// Регистрация кастомного диска
FileManager::registerDisk('s3', new Storage('/s3/path', '/s3-url'));

// Временные файлы
$tempFile = FileManager::temp('content', 'txt');

// Управление директориями
FileManager::ensureDirectory('/path/to/dir');
FileManager::cleanDirectory('/path/to/dir');    // рекурсивная очистка
FileManager::copyDirectory('/from', '/to');     // рекурсивное копирование

// Поиск файлов
$files = FileManager::glob('/path/to/*.php');
$files = FileManager::find('/path/to', '*.txt');  // рекурсивный поиск

// Утилиты
FileManager::humanSize(1048576);            // "1.0 MB"
FileManager::sanitizeFilename('bad/name');  // "bad_name"
FileManager::uniqueFilename('/dir', 'file.txt');
FileManager::extension('image/jpeg');       // "jpg"
```

## Storage

[`Pet\File\Storage`](../File/Storage.php) — абстракция файлового хранилища с привязкой к корневой директории и URL-префиксу.

```php
use Pet\File\Storage;

$storage = new Storage('/var/www/uploads', '/uploads');

// Сохранение загруженных файлов
$path = $storage->save($_FILES['file'], 'avatars');
// Возвращает относительный путь: "avatars/ab12cd34.jpg"

$path = $storage->saveWithOriginalName($_FILES['file'], 'docs');
// Сохраняет с оригинальным именем (санитизированным)

$path = $storage->saveContent('content', 'data/file.txt');
$path = $storage->saveFile($file, 'backup');

// URL и пути
$url  = $storage->url('avatars/ab12cd34.jpg');   // /uploads/avatars/ab12cd34.jpg
$path = $storage->path('avatars/ab12cd34.jpg');   // /var/www/uploads/avatars/ab12cd34.jpg

// Проверки
$storage->exists('avatars/ab12cd34.jpg');
$storage->size('avatars/ab12cd34.jpg');
$storage->mimeType('avatars/ab12cd34.jpg');
$storage->lastModified('avatars/ab12cd34.jpg');

// Удаление
$storage->delete('avatars/ab12cd34.jpg');
$storage->deleteDirectory('old_folder');

// Список файлов
$storage->files();              // файлы в корне хранилища
$storage->files('avatars');     // файлы в поддиректории
$storage->allFiles();           // рекурсивно все файлы
$storage->directories();        // поддиректории

// Управление
$storage->makeDirectory('new_dir');
$storage->copy('from/file.txt', 'to/file.txt');
$storage->move('from/file.txt', 'to/file.txt');

// Отдача файла браузеру
$storage->serve('avatars/ab12cd34.jpg');     // вывод с Content-Type
$storage->download('avatars/ab12cd34.jpg');  // принудительное скачивание

// Получение объекта File
$file = $storage->file('avatars/ab12cd34.jpg');

// Storage как middleware для отдачи статики
// Router::get('/uploads/*', Storage::disk('local'));
```

## Image

[`Pet\File\Image`](../File/Image.php) — класс для работы с изображениями через GD. Поддерживает изменение размера, обрезку, поворот, водяные знаки и конвертацию.

```php
use Pet\File\Image;

// Загрузка
$image = new Image('photo.jpg');
$image = Image::fromFile($file);
$image = Image::fromString($binaryData);
$image = Image::create(800, 600, '#ff0000');  // создание нового

// Метаданные
$image->width();
$image->height();
$image->mimeType();
$image->path();

// Изменение размера
$image->resize(400, 300);               // точный размер
$image->resize(400, null, true);        // по ширине с сохранением пропорций
$image->resizeToWidth(400);             // по ширине
$image->resizeToHeight(300);            // по высоте

// Обрезка
$image->crop(10, 10, 200, 150);         // произвольная область
$image->cropCenter(200, 200);           // центр
$image->cropThumbnail(150);             // квадратный превью

// Трансформации
$image->rotate(90, '#ffffff');          // поворот
$image->flipHorizontal();               // отражение по горизонтали
$image->flipVertical();                 // отражение по вертикали

// Водяной знак и текст
$image->watermark('logo.png', Image::BOTTOM_RIGHT, 10);
$image->text('(c) 2026', 5, '#000000', 10, 10);

// Сохранение
$image->save('output.jpg', 'jpeg', 90);
$image->saveAsJpeg('output.jpg', 90);
$image->saveAsPng('output.png');
$image->saveAsWebp('output.webp', 80);
$image->saveAsGif('output.gif');
$image->convertToWebp(80);  // конвертация рядом с оригиналом

// Base64
$dataUri = $image->toBase64();  // "data:image/png;base64,..."

// Доступ к GD-ресурсу
$gd = $image->getResource();
```

Константы позиций для водяного знака:

| Константа | Позиция |
|-----------|---------|
| `Image::TOP_LEFT` | Верхний левый угол |
| `Image::TOP_CENTER` | Верхний центр |
| `Image::TOP_RIGHT` | Верхний правый угол |
| `Image::MIDDLE_LEFT` | Левый центр |
| `Image::MIDDLE_CENTER` | Центр |
| `Image::MIDDLE_RIGHT` | Правый центр |
| `Image::BOTTOM_LEFT` | Нижний левый угол |
| `Image::BOTTOM_CENTER` | Нижний центр |
| `Image::BOTTOM_RIGHT` | Нижний правый угол |

## MimeTypeDetector

[`Pet\File\MimeTypeDetector`](../File/MimeTypeDetector.php) — определение MIME-типов по расширению, файлу, а также категоризация.

```php
use Pet\File\MimeTypeDetector;

// Определение
MimeTypeDetector::fromExtension('jpg');     // "image/jpeg"
MimeTypeDetector::fromFile('/path/to/file'); // по содержимому
MimeTypeDetector::extensionFor('image/jpeg'); // "jpg"

// Проверки
MimeTypeDetector::isImage('image/png');         // true
MimeTypeDetector::isVideo('video/mp4');         // true
MimeTypeDetector::isAudio('audio/mpeg');        // true
MimeTypeDetector::isText('text/plain');         // true
MimeTypeDetector::isArchive('application/zip'); // true
MimeTypeDetector::isDocument('application/pdf');// true
MimeTypeDetector::isExecutable('application/x-sh'); // true
MimeTypeDetector::isWebSafeImage('image/webp'); // true

// Валидация по белому списку (поддержка wildcard)
MimeTypeDetector::isAllowed(['image/*', 'application/pdf'], 'image/png'); // true

// Категории
MimeTypeDetector::category('image/jpeg');   // "image"
MimeTypeDetector::iconFor('video/mp4');     // "🎬"

// Регистрация кастомного MIME-типа
MimeTypeDetector::register('avif', 'image/avif');

// Список всех известных расширений/MIME-типов
MimeTypeDetector::allExtensions();
MimeTypeDetector::allMimeTypes();
```

## FileException

[`Pet\File\Exception\FileException`](../File/Exception/FileException.php) — базовое исключение для всех файловых операций с фабричными методами.

```php
use Pet\File\Exception\FileException;

// Фабричные методы (HTTP-статус в коде исключения)
throw FileException::notFound($path);           // 404
throw FileException::notReadable($path);        // 403
throw FileException::notWritable($path);        // 403
throw FileException::uploadError();             // 400
throw FileException::invalidPath($path);        // 400
throw FileException::invalidImage();            // 400
throw FileException::directoryNotCreated($dir); // 500
throw FileException::saveError();               // 500