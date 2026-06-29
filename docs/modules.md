# Модули (Module)

В пространстве имён [`Pet\Module`](../Module/) находятся готовые интеграции с внешними сервисами.

## PlusOfon — отправка SMS

[`Pet\Module\PlusOfon`](../Module/PlusOfon.php) — клиент для отправки SMS через API сервиса PlusOfon.

```php
use Pet\Module\PlusOfon;

$sms = new PlusOfon('your-api-token');

$result = $sms->sms('+79161234567', 'Текст сообщения');

// Результат:
// [
//     'success' => true,
//     'id' => '12345',
// ]
//
// При ошибке:
// [
//     'success' => false,
//     'error' => 'PlusOfon: неверный номер получателя',
//     'status' => 400,
//     'details' => [...],
// ]
```

## Imap — работа с почтой

[`Pet\Module\Imap`](../Module/Imap.php) — абстрактный класс для подключения к IMAP-серверам. Требует реализации метода [`loadVariable()`](../Module/Imap.php:615) для получения настроек.

```php
use Pet\Module\Imap;

class MailHandler extends Imap
{
    public function loadVariable(string $name): string
    {
        // Вернуть значение по ключу (host, port, username, password, encryption, verify_ssl, folder)
        return $_ENV['imap.' . $name] ?? '';
    }
}

$mail = new MailHandler();

// Проверка конфигурации
$mail->isConfigured();
$mail->getMissingSettings();  // ['Хост (imap.host)', ...]
$mail->testConnection();

// Управление папками
$mail->getFolders();          // ['INBOX', 'Sent', ...]
$mail->ensureFolder('Archive');

// Получение писем
$mail->getMessages(50, 'ALL');              // последние 50 писем
$mail->getMessagesPaginated(0, 20, 'UNSEEN'); // непрочитанные с пагинацией
$mail->getMessage(123);                     // полное письмо с телом и вложениями

// Операции с письмами
$mail->markAsRead(123);
$mail->markAsUnread(123);
$mail->deleteMessage(123);
$mail->moveMessage(123, 'Archive');

// Вложения
$attachment = $mail->getAttachment(123, '1.2');
// ['success' => true, 'content' => '...']
```

Параметры конструктора [`Imap`](../Module/Imap.php:22):

| Параметр | Тип | По умолчанию | Описание |
|----------|-----|--------------|----------|
| `$host` | `?string` | из `loadVariable()` | Хост IMAP-сервера |
| `$port` | `?int` | `993` | Порт |
| `$username` | `?string` | из `loadVariable()` | Логин |
| `$password` | `?string` | из `loadVariable()` | Пароль |
| `$encryption` | `?string` | `'ssl'` | Шифрование (`ssl`, `tls`, `none`) |
| `$verifySsl` | `?bool` | `true` | Проверка SSL-сертификата |
| `$folder` | `?string` | `'INBOX'` | Папка по умолчанию |