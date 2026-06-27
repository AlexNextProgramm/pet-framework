<?php

namespace Pet;

use Pet\Request\Request;
use Pet\View\View;
use Pet\Session\Session;
use Pet\Errors\AppException;
use Pet\Router\Header;

abstract class Controller
{
    /**
     * @var array Данные, передаваемые в представление
     */
    protected array $data = [];

    /**
     * @var array Middleware, которые должны выполниться перед экшеном
     */
    protected array $middleware = [];

    /**
     * ---------------------------------------------------------------------------
     *  Ответы (Response)
     * ---------------------------------------------------------------------------
     */

    /**
     * json
     *
     * Возвращает JSON-ответ с указанным HTTP-кодом.
     *
     * @param  mixed $data Данные для сериализации
     * @param  int   $code HTTP-статус (по умолчанию 200)
     * @return void
     */
    protected function json(mixed $data, int $code = 200): void
    {
        Header::status($code);
        Header::json();
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * redirect
     *
     * Выполняет перенаправление на указанный URL.
     *
     * @param  string $url Абсолютный или относительный URL
     * @return never
     */
    protected function redirect(string $url): never
    {
        Header::location($url);
        exit;
    }

    /**
     * back
     *
     * Возвращает пользователя на предыдущую страницу (HTTP_REFERER).
     * Если реферера нет — редирект на '/'.
     *
     * @return never
     */
    protected function back(): never
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    /**
     * withInput
     *
     * Сохраняет данные запроса в сессию для последующего отображения
     * в форме (flash-данные). Удобно при ошибках валидации.
     *
     * @return $this
     */
    protected function withInput(): static
    {
        Session::set(['__old' => Request::$attribute]);
        return $this;
    }

    /**
     * withErrors
     *
     * Сохраняет массив ошибок в сессию (flash-данные).
     *
     * @param  array $errors
     * @return $this
     */
    protected function withErrors(array $errors): static
    {
        Session::set(['__errors' => $errors]);
        return $this;
    }

    /**
     * ---------------------------------------------------------------------------
     *  Работа с представлениями (View)
     * ---------------------------------------------------------------------------
     */

    /**
     * render
     *
     * Отображает шаблон, передавая в него массив данных.
     *
     * @param  string $view Имя шаблона (с точкой вместо разделителя, напр. "user.profile")
     * @param  array  $data Дополнительные данные для шаблона
     * @return void
     */
    protected function render(string $view, array $data = []): void
    {
        View::open($view, array_merge($this->data, $data));
    }

    /**
     * renderPartial
     *
     * Возвращает HTML-код шаблона в виде строки (без вывода в поток).
     *
     * @param  string $view Имя шаблона
     * @param  array  $data Параметры для шаблона
     * @return string|false
     */
    protected function renderPartial(string $view, array $data = []): string|false
    {
        return View::getTemplate($view, array_merge($this->data, $data));
    }

    /**
     * ---------------------------------------------------------------------------
     *  Middleware
     * ---------------------------------------------------------------------------
     */

    /**
     * callMiddleware
     *
     * Последовательно выполняет все middleware, добавленные через `middleware()`.
     * Каждый middleware — это callable, который получает экземпляр Controller.
     * Если middleware вернёт false, цепочка прерывается.
     *
     * @return void
     */
    protected function callMiddleware(): void
    {
        foreach ($this->middleware as $mw) {
            if ($mw($this) === false) {
                break;
            }
        }
    }

    /**
     * middleware
     *
     * Добавляет middleware в цепочку.
     *
     * @param  callable $mw Функция: fn(Controller $controller): ?bool
     * @return $this
     */
    protected function middleware(callable $mw): static
    {
        $this->middleware[] = $mw;
        return $this;
    }

    /**
     * ---------------------------------------------------------------------------
     *  Загрузка файлов
     * ---------------------------------------------------------------------------
     */

    /**
     * saveFile
     *
     * Сохраняет загруженный файл из временной директории в указанную папку.
     *
     * @param  string $tmp    Временный путь к файлу (обычно $_FILES['file']['tmp_name'])
     * @param  string $name   Имя файла для сохранения
     * @param  string $path   Директория назначения (по умолчанию ROOT)
     * @param  int    $access Права доступа для создаваемых папок (по умолчанию 0777)
     * @return bool
     */
    public function saveFile(string $tmp, string $name, string $path = ROOT, int $access = 0777): bool
    {
        if (!is_dir($path)) {
            mkdir($path, $access, true);
        }
        return move_uploaded_file($tmp, $path . DS . $name);
    }

    /**
     * saveUploadedFile
     *
     * Сохраняет файл из массива $_FILES по имени поля формы.
     * Автоматически генерирует уникальное имя, если не указано.
     *
     * @param  string      $fieldName Имя поля в $_FILES
     * @param  string      $path      Директория назначения
     * @param  string|null $name      Желаемое имя файла (без расширения)
     * @param  int         $access    Права доступа
     * @return string|false           Имя сохранённого файла или false при ошибке
     */
    protected function saveUploadedFile(string $fieldName, string $path, ?string $name = null, int $access = 0777): string|false
    {
        $file = $_FILES[$fieldName] ?? null;

        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = $name ? $name . '.' . $ext : basename($file['name']);

        if (!is_dir($path)) {
            mkdir($path, $access, true);
        }

        return move_uploaded_file($file['tmp_name'], $path . DS . $name) ? $name : false;
    }

    /**
     * deleteFile
     *
     * Удаляет файл по указанному пути.
     *
     * @param  string $path Полный путь к файлу
     * @return bool
     */
    protected function deleteFile(string $path): bool
    {
        if (file_exists($path) && is_file($path)) {
            return unlink($path);
        }
        return false;
    }

    /**
     * ---------------------------------------------------------------------------
     *  Вспомогательные методы
     * ---------------------------------------------------------------------------
     */

    /**
     * setData
     *
     * Устанавливает данные для передачи в представление.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    protected function setData(string $key, mixed $value): static
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * getData
     *
     * Возвращает все данные, установленные для представления.
     *
     * @return array
     */
    protected function getData(): array
    {
        return $this->data;
    }

    /**
     * isPost
     *
     * Проверяет, был ли отправлен POST-запрос.
     *
     * @return bool
     */
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * isAjax
     *
     * Проверяет, является ли запрос AJAX-запросом.
     *
     * @return bool
     */
    protected function isAjax(): bool
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * csrf_token
     *
     * Возвращает или генерирует CSRF-токен для текущей сессии.
     *
     * @return string
     */
    protected function csrf_token(): string
    {
        $token = Session::get('__csrf_token');

        if (!$token) {
            $token = bin2hex(random_bytes(32));
            Session::set(['__csrf_token' => $token]);
        }

        return $token;
    }

    /**
     * validateCsrf
     *
     * Проверяет CSRF-токен из запроса против токена в сессии.
     * При несовпадении выбрасывает исключение.
     *
     * @param  string|null $token Токен из запроса (если null — берётся из Request::$attribute)
     * @return void
     * @throws AppException
     */
    protected function validateCsrf(?string $token = null): void
    {
        $token ??= Request::$attribute['__csrf_token'] ?? '';
        $sessionToken = Session::get('__csrf_token');

        if ($token === '' || !hash_equals((string) $sessionToken, $token)) {
            throw new AppException('CSRF-токен недействителен.', 419);
        }
    }

    /**
     * cacheResponse
     *
     * Устанавливает заголовки кэширования для ответа.
     *
     * @param  int $seconds Время кэширования в секундах
     * @return void
     */
    protected function cacheResponse(int $seconds = 3600): void
    {
        Header::cache($seconds);
    }

    /**
     * noCache
     *
     * Запрещает кэширование ответа.
     *
     * @return void
     */
    protected function noCache(): void
    {
        Header::noCache();
    }
}
