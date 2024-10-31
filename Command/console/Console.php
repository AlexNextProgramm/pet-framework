<?php

class Console {
    const OS = PHP_OS;
    public static $DIR_LINUX;
    public static $DIR_WIN;

    public function __construct() {
        self::$DIR_LINUX = str_replace(' ', '\ ', ROOT_DIR . '/vendor/pet/framework/Command/console/linux/');
        self::$DIR_WIN = str_replace(' ', '\ ', ROOT_DIR . '/vendor/pet/framework/Command/console/win/');
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
     * @param string $output
     * @return void
     */
    static function input(string &$output = ''): string 
    {
        new self();
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
    
    /**
     * cmd
     *
     * @param  mixed $cmd
     * @param  callable $callback
     * @return void
     */
    static function cmd(string $cmd, callable $callback = null) {
        exec($cmd, $outputs);
        foreach ($outputs as $out) {
            if ($callback) $callback($out);
        }
    }
}
