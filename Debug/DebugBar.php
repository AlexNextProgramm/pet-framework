<?php

namespace Pet\Debug;

/**
 * DebugBar — коллектор отладочных данных.
 *
 * Собирает SQL-запросы, время выполнения, список подключённых файлов.
 * Данные выводятся в виде фиксированной панели внизу страницы.
 *
 * @package Pet\Debug
 */
class DebugBar
{
    /**
     * @var array Собранные SQL-запросы
     */
    private static array $queries = [];

    /**
     * @var float|null Время старта приложения (microtime)
     */
    private static ?float $startTime = null;

    /**
     * @var float|null Время финиша (рендеринга View)
     */
    private static ?float $endTime = null;

    /**
     * @var array Список подключённых файлов (get_included_files)
     */
    private static array $includedFiles = [];

    /**
     * Засекает время старта.
     *
     * @return void
     */
    public static function start(): void
    {
        self::$startTime = microtime(true);
    }

    /**
     * Засекает время финиша.
     *
     * @return void
     */
    public static function stop(): void
    {
        self::$endTime = microtime(true);
    }

    /**
     * Добавляет SQL-запрос в лог.
     *
     * @param string $query SQL-запрос
     * @param float  $time  Время выполнения в секундах
     * @return void
     */
    public static function addQuery(string $query, float $time): void
    {
        self::$queries[] = [
            'query' => $query,
            'time'  => round($time, 6),
        ];
    }

    /**
     * Возвращает все собранные SQL-запросы.
     *
     * @return array
     */
    public static function getQueries(): array
    {
        return self::$queries;
    }

    /**
     * Возвращает общее время выполнения.
     *
     * @return float
     */
    public static function getExecutionTime(): float
    {
        if (self::$startTime === null) {
            return 0.0;
        }
        $end = self::$endTime ?? microtime(true);
        return round($end - self::$startTime, 6);
    }

    /**
     * Возвращает пиковое использование памяти.
     *
     * @return string
     */
    public static function getMemoryUsage(): string
    {
        $bytes = memory_get_peak_usage(true);
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }

    /**
     * Возвращает список подключённых файлов.
     *
     * @return array
     */
    public static function getIncludedFiles(): array
    {
        return self::$includedFiles;
    }

    /**
     * Очищает все собранные данные.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$queries = [];
        self::$startTime = null;
        self::$endTime = null;
        self::$includedFiles = [];
    }

    /**
     * Возвращает HTML-код отладочной панели.
     *
     * @return string
     */
    public static function render(): string
    {
        $queries = self::getQueries();
        $execTime = self::getExecutionTime();
        $memory = self::getMemoryUsage();
        $files = get_included_files();
        $queryCount = count($queries);

        // Фильтруем файлы: исключаем vendor/
        $appFiles = array_values(array_filter($files, function ($f) {
            return strpos($f, '/vendor/') === false && strpos($f, '\\vendor\\') === false;
        }));
        $vendorFiles = array_values(array_filter($files, function ($f) {
            return strpos($f, '/vendor/') !== false || strpos($f, '\\vendor\\') !== false;
        }));
        $fileCount = count($appFiles);
        $vendorCount = count($vendorFiles);

        // Подключаем CSS из отдельного файла (файл возвращает строку через return)
        $html = (string) include __DIR__ . '/style.php';

        // Строим HTML панели
        $html .= <<<HTML
<div class="pet-debug-bar" id="pet-debug-bar">
    <div class="pet-debug-toolbar">
        <span class="pet-debug-brand">🐞 PET Debug</span>
        <button class="pet-debug-tab active" data-tab="speed" onclick="switchDebugTab('speed')">
            ⚡ Скорость
        </button>
        <button class="pet-debug-tab" data-tab="sql" onclick="switchDebugTab('sql')">
            🗄️ SQL <span class="badge sql-badge">{$queryCount}</span>
        </button>
        <button class="pet-debug-tab" data-tab="files" onclick="switchDebugTab('files')">
            📁 Файлы <span class="badge file-badge">{$fileCount}</span>
        </button>
        <button class="pet-debug-toggle" onclick="toggleDebugBar()" title="Свернуть/развернуть">─</button>
    </div>
HTML;

        // --- TAB: Speed ---
        $html .= '<div class="pet-debug-content active" id="pet-debug-tab-speed">';
        $html .= '<div class="stat-grid">';
        $html .= '<div class="stat-card"><div class="stat-label">Время выполнения</div><div class="stat-value time">' . round($execTime, 4) . ' s</div></div>';
        $html .= '<div class="stat-card"><div class="stat-label">Пик памяти</div><div class="stat-value memory">' . $memory . '</div></div>';
        $html .= '<div class="stat-card"><div class="stat-label">SQL запросов</div><div class="stat-value queries">' . $queryCount . '</div></div>';
        $html .= '<div class="stat-card"><div class="stat-label">Подключено файлов</div><div class="stat-value files">' . $fileCount . ' <span style="font-size:12px;color:#6c7086;">(+' . $vendorCount . ' vendor)</span></div></div>';
        $html .= '</div></div>';

        // --- TAB: SQL ---
        $html .= '<div class="pet-debug-content" id="pet-debug-tab-sql">';
        if (empty($queries)) {
            $html .= '<p style="color: #6c7086; padding: 12px 0;">SQL-запросы не выполнялись.</p>';
        } else {
            $html .= '<table><thead><tr><th style="width:60px;">#</th><th>Запрос</th><th style="width:100px;">Время</th></tr></thead><tbody>';
            foreach ($queries as $i => $q) {
                $num = $i + 1;
                $escaped = htmlspecialchars($q['query'], ENT_QUOTES, 'UTF-8');
                $html .= "<tr><td>{$num}</td><td class=\"query-sql\">{$escaped}</td><td class=\"query-time\">{$q['time']} s</td></tr>";
            }
            $html .= '</tbody></table>';
        }
        $html .= '</div>';

        // --- TAB: Files ---
        $html .= '<div class="pet-debug-content" id="pet-debug-tab-files">';
        if (empty($appFiles) && empty($vendorFiles)) {
            $html .= '<p style="color: #6c7086; padding: 12px 0;">Нет информации о файлах.</p>';
        } else {
            // Кнопка-переключатель показа vendor-файлов
            $html .= '<div style="padding: 4px 0 8px 0;">';
            $html .= '<label style="display:inline-flex;align-items:center;gap:6px;cursor:pointer;color:#a6adc8;font-size:12px;">';
            $html .= '<input type="checkbox" id="pet-debug-show-vendor" onchange="toggleVendorFiles()">';
            $html .= 'Показать vendor-файлы (' . $vendorCount . ' шт.)';
            $html .= '</label>';
            $html .= '</div>';

            // Таблица app-файлов
            $html .= '<table id="pet-debug-app-files"><thead><tr><th style="width:60px;">#</th><th>Путь к файлу</th></tr></thead><tbody>';
            foreach ($appFiles as $i => $file) {
                $num = $i + 1;
                $escaped = htmlspecialchars($file, ENT_QUOTES, 'UTF-8');
                $html .= "<tr><td>{$num}</td><td class=\"file-path\">{$escaped}</td></tr>";
            }
            $html .= '</tbody></table>';

            // Таблица vendor-файлов (скрыта по умолчанию)
            $html .= '<table id="pet-debug-vendor-files" style="display:none;"><thead><tr><th style="width:60px;">#</th><th>Путь к файлу (vendor)</th></tr></thead><tbody>';
            foreach ($vendorFiles as $i => $file) {
                $num = $i + 1;
                $escaped = htmlspecialchars($file, ENT_QUOTES, 'UTF-8');
                $html .= "<tr><td>{$num}</td><td class=\"file-path\" style=\"color:#6c7086;\">{$escaped}</td></tr>";
            }
            $html .= '</tbody></table>';
        }
        $html .= '</div>';

        // --- JS ---
        $html .= '</div>';
        $html .= (string) include __DIR__ . '/script.php';

        return $html;
    }
}