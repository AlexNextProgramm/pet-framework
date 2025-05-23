<?php
namespace Pet\Command\Console;

class Console {
    const OS = PHP_OS;
    public static $DIR_LINUX;
    public static $DIR_WIN;

    const RED = 'red';
    const GREEN = 'green';
    const YELLOW = 'yellow';
    private static $isInputYes = ['y', 'Y', 'Д', 'д'];
    public function __construct() {
        self::$DIR_LINUX = str_replace(" ", "\ ",ROOT . "/vendor/pet/framework/Command/Console/linux/");
        self::$DIR_WIN = str_replace(" ", "\ ", ROOT . "/vendor/pet/framework/Command/Console/win/");
    }
    public static $color = [
        'red'    => '31',
        'green'  => '32',
        'yellow' => '33',
        'blue'   => '34',
        'black'  => '30',
        'white'  => '37',
        'violet' => '35'
    ];

    public static $background = [
        'red'     => '41',
        'green'   => '42',
        'yellow'  => '43',
        'blue'    => '44',
        'black'   => '40',
        'white'   => '47',
        'violet'  => '45',
        'default' => '0'
    ];

    public static $code = [
        "hex"     => "\x1b[",
        "unicode" => "\u001b[",
        "oct"     => "\033["
    ];
    
    /**
     * input
     *
     * @param string|null $output
     * @return void
     */
    static function input(string|null &$output = ''): string
    {
        new self();
        $output = '';

        if (self::OS == 'Linux') {
            exec(self::$DIR_LINUX . 'input.sh', $out);
            $output = $out[0];
        }
        if (self::OS == 'WINNT') {
            exec(self::$DIR_WIN . 'input.bat', $out);
            $output = $out[2];
        }
        return $output;
    }

    static function isYes(string $str):bool
    {
        return in_array($str, self::$isInputYes);
    }
    
    /**
     * text
     * Вывод текста в консоль
     * @return void
     */
    static function text(
        string $text,
        string $color = 'white',
        string $background = null,
        string $code = 'oct'
    ): void
    {
        $background = $background ? ";" . self::$background[$background] : "";
        echo self::$code[$code] . '0;' . self::$color[$color] . $background . 'm' . $text . "\n" . self::$code[$code] . '0m';
    }

    static function log(
        string $text,
        string $color = 'white',
        string $background = null,
        string $code = 'oct'
    ): void {
        self::text($text, $color, $background, $code);
    }
    
    /**
     * cmd
     *
     * @param  mixed $cmd
     * @param  callable $callback
     * @return void
     */
    static function cmd(string $cmd, callable $callback = null, &$argm = null) {
        exec($cmd, $outputs);
        foreach ($outputs as $out) {
            if ($callback) $callback($out, $argm);
        }
    }

    static function system(string $cmd) {
       $text = system($cmd, $out);
        self::text($text);
        self::text($out);
    }
}
