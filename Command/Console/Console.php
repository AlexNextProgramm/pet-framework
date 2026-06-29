<?php
namespace Pet\Command\Console;

/**
 * Console — утилита для работы с командной строкой
 * 
 * Предоставляет методы для цветного вывода, ввода данных,
 * таблиц, прогресс-баров и подтверждений.
 */
class Console {

    const OS = PHP_OS;

    /** @var string Путь к Linux-скриптам */
    public static string $DIR_LINUX = '';
    
    /** @var string Путь к Windows-скриптам */
    public static string $DIR_WIN = '';

    // Константы цветов для удобного использования
    const RED    = 'red';
    const GREEN  = 'green';
    const YELLOW = 'yellow';
    const BLUE   = 'blue';
    const BLACK  = 'black';
    const WHITE  = 'white';
    const VIOLET = 'violet';

    /** @var string[] Варианты утвердительного ответа */
    private static array $isInputYes = ['y', 'Y', 'Д', 'д', 'yes', 'Yes', 'YES', 'да', 'Да', 'ДА'];

    /** @var array<string, string> Коды цветов текста */
    public static array $color = [
        'red'    => '31',
        'green'  => '32',
        'yellow' => '33',
        'blue'   => '34',
        'black'  => '30',
        'white'  => '37',
        'violet' => '35',
        'cyan'   => '36',
    ];

    /** @var array<string, string> Коды цветов фона */
    public static array $background = [
        'red'     => '41',
        'green'   => '42',
        'yellow'  => '43',
        'blue'    => '44',
        'black'   => '40',
        'white'   => '47',
        'violet'  => '45',
        'cyan'    => '46',
        'default' => '0',
    ];

    /** @var array<string, string> Коды escape-последовательностей */
    public static array $code = [
        "hex"     => "\x1b[",
        "unicode" => "\u001b[",
        "oct"     => "\033[",
    ];

    /** @var array<string, string> Коды стилей текста */
    public static array $style = [
        'bold'      => '1',
        'dim'       => '2',
        'italic'    => '3',
        'underline' => '4',
        'blink'     => '5',
        'reverse'   => '7',
        'hidden'    => '8',
    ];

    /**
     * Инициализация путей к директориям со скриптами
     */
    public function __construct() {
        self::$DIR_LINUX = ROOT . "/vendor/pet/framework/Command/Console/linux/";
        self::$DIR_WIN   = ROOT . "/vendor/pet/framework/Command/Console/win/";
    }

    /**
     * Запрос ввода от пользователя
     *
     * @param string|null $output Переменная для результата
     * @param string|null $prompt Текст подсказки (необязательно)
     * @return string Введённая строка
     */
    static function input(string &$output = '', ?string $prompt = null): string
    {
        // Инициализация путей, если ещё не сделано
        if (empty(self::$DIR_LINUX)) {
            new self();
        }

        $output = '';

        if ($prompt !== null) {
            echo $prompt;
        }

        if (self::OS === 'Linux') {
            $script = self::$DIR_LINUX . 'input.sh';
            if (file_exists($script)) {
                exec($script, $out);
                $output = $out[0] ?? '';
            } else {
                // Fallback: читаем из STDIN
                $output = trim(fgets(STDIN));
            }
        } elseif (self::OS === 'WINNT') {
            $script = self::$DIR_WIN . 'input.bat';
            if (file_exists($script)) {
                exec($script, $out);
                $output = $out[2] ?? '';
            } else {
                $output = trim(fgets(STDIN));
            }
        } else {
            $output = trim(fgets(STDIN));
        }

        return $output;
    }

    /**
     * Проверка, является ли строка утвердительным ответом
     *
     * @param string $str
     * @return bool
     */
    static function isYes(string $str): bool
    {
        return in_array($str, self::$isInputYes, true);
    }

    /**
     * Вывод цветного текста в консоль
     *
     * @param string      $text       Текст для вывода
     * @param string      $color      Цвет текста (red, green, yellow, blue, white, violet, cyan)
     * @param string|null $background Цвет фона (red, green, yellow, blue, white, violet, cyan, default)
     * @param string      $code       Тип escape-кода (oct, hex, unicode)
     * @param string|null $style      Стиль текста (bold, dim, italic, underline, blink, reverse, hidden)
     * @return void
     */
    static function text(
        string $text,
        string $color = 'white',
        ?string $background = null,
        string $code = 'oct',
        ?string $style = null
    ): void {
        $esc = self::$code[$code] ?? self::$code['oct'];
        $colorCode = self::$color[$color] ?? '37';
        
        $parts = [];
        
        // Стиль
        if ($style !== null && isset(self::$style[$style])) {
            $parts[] = self::$style[$style];
        } else {
            $parts[] = '0';
        }
        
        // Цвет текста
        $parts[] = $colorCode;
        
        // Цвет фона
        if ($background !== null && isset(self::$background[$background])) {
            $parts[] = self::$background[$background];
        }
        
        echo $esc . implode(';', $parts) . 'm' . $text . $esc . '0m' . PHP_EOL;
    }

    /**
     * Алиас для text()
     *
     * @param string      $text
     * @param string      $color
     * @param string|null $background
     * @param string      $code
     * @return void
     */
    static function log(
        string $text,
        string $color = 'white',
        ?string $background = null,
        string $code = 'oct'
    ): void {
        self::text($text, $color, $background, $code);
    }

    /**
     * Выполнение команды и обработка вывода через callback
     *
     * @param string        $cmd      Команда для выполнения
     * @param callable|null $callback Функция обратного вызова для каждой строки вывода
     * @param mixed|null    $argm     Дополнительный аргумент для callback
     * @return void
     */
    static function cmd(string $cmd, ?callable $callback = null, mixed &$argm = null): void {
        exec($cmd, $outputs);
        foreach ($outputs as $out) {
            if ($callback) {
                $callback($out, $argm);
            }
        }
    }

    /**
     * Выполнение системной команды с выводом результата
     *
     * @param string $cmd
     * @return void
     */
    static function system(string $cmd): void {
        $text = system($cmd, $out);
        self::text((string)$text);
        self::text((string)$out);
    }

    /**
     * Вывод текста и завершение скрипта
     *
     * @param string      $text
     * @param string|null $color
     * @return never
     */
    static function die(string $text, ?string $color = null): never {
        self::text($text, $color ?? 'white');
        exit;
    }

    /**
     * Вывод отладочной информации (print_r)
     *
     * @param mixed       $data
     * @param string|null $color
     * @return void
     */
    static function print(mixed $data, ?string $color = null): void {
        self::text(print_r($data, true), $color ?? 'white');
    }

    /**
     * Вывод успешного сообщения (зелёный текст)
     *
     * @param string $text
     * @return void
     */
    static function success(string $text): void {
        self::text("✓ {$text}", 'green');
    }

    /**
     * Вывод предупреждения (жёлтый текст)
     *
     * @param string $text
     * @return void
     */
    static function warning(string $text): void {
        self::text("⚠ {$text}", 'yellow');
    }

    /**
     * Вывод сообщения об ошибке (красный текст)
     *
     * @param string $text
     * @return void
     */
    static function error(string $text): void {
        self::text("✗ {$text}", 'red');
    }

    /**
     * Вывод информационного сообщения (синий текст)
     *
     * @param string $text
     * @return void
     */
    static function info(string $text): void {
        self::text("ℹ {$text}", 'blue');
    }

    /**
     * Запрос подтверждения (y/n)
     *
     * @param string $question Текст вопроса
     * @param bool   $default  Значение по умолчанию (true = Yes)
     * @return bool
     */
    static function confirm(string $question, bool $default = true): bool {
        $hint = $default ? '[Y/n]' : '[y/N]';
        $answer = self::input($output, "{$question} {$hint}: ");
        
        if ($answer === '') {
            return $default;
        }
        
        return self::isYes($answer);
    }

    /**
     * Запрос ввода с подсказкой
     *
     * @param string      $question Текст вопроса
     * @param string|null $default  Значение по умолчанию
     * @return string
     */
    static function ask(string $question, ?string $default = null): string {
        $hint = $default !== null ? " [{$default}]" : '';
        $answer = self::input($output, "{$question}{$hint}: ");
        
        if ($answer === '' && $default !== null) {
            return $default;
        }
        
        return $answer;
    }

    /**
     * Вывод табличных данных
     *
     * @param array<array<string, string>> $rows   Массив строк, каждая строка — ассоциативный массив
     * @param string[]|null                $headers Заголовки столбцов (если null — берутся из ключей первой строки)
     * @param string                       $color  Цвет рамки и заголовков
     * @return void
     */
    static function table(array $rows, ?array $headers = null, string $color = 'white'): void {
        if (empty($rows)) {
            self::text("(empty table)", 'yellow');
            return;
        }

        // Определяем заголовки
        if ($headers === null) {
            $headers = array_keys($rows[0]);
        }

        // Приводим rows к индексному массиву для единообразия
        $normalizedRows = [];
        foreach ($rows as $row) {
            $normalizedRow = [];
            foreach ($headers as $header) {
                $normalizedRow[] = (string)($row[$header] ?? '');
            }
            $normalizedRows[] = $normalizedRow;
        }

        // Вычисляем ширину каждого столбца
        $widths = [];
        foreach ($headers as $i => $header) {
            $widths[$i] = strlen($header);
        }
        foreach ($normalizedRows as $row) {
            foreach ($row as $i => $value) {
                $widths[$i] = max($widths[$i], strlen($value));
            }
        }

        // Разделитель
        $separator = '+' . implode('+', array_map(fn($w) => str_repeat('-', $w + 2), $widths)) . '+';
        self::text($separator, $color);

        // Заголовки
        $headerLine = '|';
        foreach ($headers as $i => $header) {
            $headerLine .= ' ' . str_pad($header, $widths[$i], ' ', STR_PAD_BOTH) . ' |';
        }
        self::text($headerLine, $color, null, 'oct', 'bold');

        // Разделитель после заголовков
        self::text($separator, $color);

        // Данные
        foreach ($normalizedRows as $row) {
            $line = '|';
            foreach ($row as $i => $value) {
                $line .= ' ' . str_pad($value, $widths[$i]) . ' |';
            }
            self::text($line, 'white');
        }

        // Нижний разделитель
        self::text($separator, $color);
    }

    /**
     * Простой прогресс-бар
     *
     * @param int    $current Текущее значение
     * @param int    $total   Общее значение
     * @param int    $width   Ширина прогресс-бара в символах
     * @param string $label   Метка перед прогресс-баром
     * @return void
     */
    static function progressBar(int $current, int $total, int $width = 50, string $label = ''): void {
        if ($total <= 0) {
            return;
        }

        $percent = round(($current / $total) * 100);
        $fill = round(($current / $total) * $width);
        $bar = str_repeat('=', max(0, $fill - 1)) . ($fill > 0 ? '>' : '');
        $bar = str_pad($bar, $width, ' ');

        $text = "\r{$label} [{$bar}] {$percent}% ({$current}/{$total})";
        
        if ($current >= $total) {
            echo "\r{$label} [" . str_repeat('=', $width) . "] 100% ({$total}/{$total})" . PHP_EOL;
        } else {
            echo $text;
        }
    }

    /**
     * Очистка консоли
     *
     * @return void
     */
    static function clear(): void {
        if (self::OS === 'Linux') {
            system('clear');
        } elseif (self::OS === 'WINNT') {
            system('cls');
        } else {
            // Универсальный способ
            echo "\033[2J\033[0;0H";
        }
    }

    /**
     * Вывод пустой строки
     *
     * @param int $count Количество пустых строк
     * @return void
     */
    static function newLine(int $count = 1): void {
        echo str_repeat(PHP_EOL, $count);
    }

    /**
     * Вывод списка (bullet points)
     *
     * @param array  $items Элементы списка
     * @param string $color Цвет маркеров
     * @return void
     */
    static function bulletList(array $items, string $color = 'green'): void {
        foreach ($items as $item) {
            self::text(" • {$item}", $color);
        }
    }

    /**
     * Вывод нумерованного списка
     *
     * @param array  $items Элементы списка
     * @param string $color Цвет цифр
     * @return void
     */
    static function numberedList(array $items, string $color = 'white'): void {
        $i = 1;
        foreach ($items as $item) {
            self::text(" {$i}. {$item}", $color);
            $i++;
        }
    }

    /**
     * Ожидание нажатия клавиши
     *
     * @param string|null $message Сообщение перед ожиданием
     * @return void
     */
    static function wait(?string $message = null): void {
        if ($message !== null) {
            echo $message;
        } else {
            echo "Нажмите Enter для продолжения...";
        }
        fgets(STDIN);
    }

    /**
     * Вывод заголовка с рамкой
     *
     * @param string $title Заголовок
     * @param string $color Цвет рамки
     * @param int    $width Ширина рамки
     * @return void
     */
    static function header(string $title, string $color = 'yellow', int $width = 60): void {
        $title = " {$title} ";
        $padding = $width - mb_strlen($title);
        $leftPad = (int)floor($padding / 2);
        $rightPad = $padding - $leftPad;
        
        $line = str_repeat('=', $width);
        
        self::text($line, $color);
        self::text(str_repeat(' ', $leftPad) . $title . str_repeat(' ', $rightPad), $color, null, 'oct', 'bold');
        self::text($line, $color);
    }

    /**
     * Вывод кликабельной гиперссылки в терминале (OSC 8)
     *
     * Использует escape-последовательность OSC 8 для создания кликабельных ссылок.
     * Поддерживается в современных терминалах: VS Code, GNOME Terminal, iTerm2,
     * kitty, Windows Terminal, Konsole и др.
     *
     * Формат: \033]8;;URL\033\\TEXT\033]8;;\033\\
     *
     * @param string      $text Текст ссылки
     * @param string|null $url  URL (если null, используется $text)
     * @param string|null $color Цвет текста (red, green, yellow, blue, white, violet, cyan)
     * @return void
     */
    static function link(string $text, ?string $url = null, ?string $color = 'blue'): void
    {
       $color = $color ?? 'blue';
       $ccolor = self::$color[$color] ?? '34';
       echo  "\033[" . $ccolor . "m\033]8;;https://example.com\033\\Это ссылка\033]8;;\033\\\033[0m\n";
       
    }
}
