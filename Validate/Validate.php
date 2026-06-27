<?php

namespace Pet;

use Pet\Request\Request;
use Pet\Session\Session;

class Validate
{
    /**
     * @var array Ошибки валидации
     */
    protected array $errors = [];

    /**
     * @var array Валидированные данные
     */
    protected array $validated = [];

    /**
     * @var array Правила валидации
     */
    protected array $rules = [];

    /**
     * @var array Сообщения об ошибках (пользовательские)
     */
    protected array $messages = [];

    /**
     * @var array Данные для валидации
     */
    protected array $data = [];

    /**
     * @var array Встроенные сообщения об ошибках по умолчанию
     */
    protected array $defaultMessages = [
        'required' => 'Поле «{field}» обязательно для заполнения.',
        'email'    => 'Поле «{field}» должно быть корректным email-адресом.',
        'min'      => 'Поле «{field}» должно содержать минимум {param} символов.',
        'max'      => 'Поле «{field}» должно содержать максимум {param} символов.',
        'numeric'  => 'Поле «{field}» должно быть числом.',
        'integer'  => 'Поле «{field}» должно быть целым числом.',
        'url'      => 'Поле «{field}» должно быть корректным URL.',
        'phone'    => 'Поле «{field}» должно быть корректным номером телефона.',
        'date'     => 'Поле «{field}» должно быть корректной датой.',
        'boolean'  => 'Поле «{field}» должно быть логическим значением (true/false).',
        'in'       => 'Поле «{field}» должно быть одним из: {param}.',
        'not_in'   => 'Поле «{field}» не должно быть одним из: {param}.',
        'confirmed'=> 'Подтверждение поля «{field}» не совпадает.',
        'regex'    => 'Поле «{field}» имеет неверный формат.',
        'alpha'    => 'Поле «{field}» должно содержать только буквы.',
        'alpha_num'=> 'Поле «{field}» должно содержать только буквы и цифры.',
        'alpha_dash'=> 'Поле «{field}» должно содержать только буквы, цифры, дефисы и подчёркивания.',
        'array'    => 'Поле «{field}» должно быть массивом.',
        'file'     => 'Поле «{field}» должно быть загруженным файлом.',
        'image'    => 'Поле «{field}» должно быть изображением.',
        'mimes'    => 'Поле «{field}» должно быть файлом одного из типов: {param}.',
        'max_size' => 'Поле «{field}» не должно превышать {param} КБ.',
    ];

    /**
     * @param array $data     Данные для валидации
     * @param array $rules    Правила валидации
     * @param array $messages Пользовательские сообщения об ошибках
     */
    public function __construct(array $data, array $rules, array $messages = [])
    {
        $this->data     = $data;
        $this->rules    = $rules;
        $this->messages = $messages;
    }

    /**
     * make
     *
     * Статический фабричный метод для быстрого создания валидации.
     *
     * @param  array $data     Данные для валидации
     * @param  array $rules    Правила валидации
     * @param  array $messages Пользовательские сообщения
     * @return static
     */
    public static function make(array $data, array $rules, array $messages = []): static
    {
        return new static($data, $rules, $messages);
    }

    /**
     * validate
     *
     * Запускает процесс валидации.
     *
     * @return bool true, если ошибок нет
     */
    public function validate(): bool
    {
        $this->errors    = [];
        $this->validated = [];

        foreach ($this->rules as $field => $ruleString) {
            $value   = $this->data[$field] ?? null;
            $ruleset = explode('|', $ruleString);

            foreach ($ruleset as $rule) {
                $this->applyRule($field, $value, $rule);
            }

            // Если по полю нет ошибок — сохраняем значение
            if (!isset($this->errors[$field])) {
                $this->validated[$field] = $value;
            }
        }

        return empty($this->errors);
    }

    /**
     * applyRule
     *
     * Применяет одно правило к полю.
     *
     * @param  string $field Название поля
     * @param  mixed  $value Значение поля
     * @param  string $rule  Правило (например, "required", "min:3")
     * @return void
     */
    protected function applyRule(string $field, mixed $value, string $rule): void
    {
        // Если есть уже ошибка по полю — не проверяем дальше (кроме "required")
        if (isset($this->errors[$field]) && $rule !== 'required') {
            return;
        }

        // Правило с параметром: "min:3"
        $param = null;
        if (str_contains($rule, ':')) {
            [$rule, $param] = explode(':', $rule, 2);
        }

        switch ($rule) {
            case 'required':
                $this->validateRequired($field, $value);
                break;

            case 'email':
                if ($value !== null && $value !== '') {
                    $this->validateEmail($field, $value);
                }
                break;

            case 'min':
                if ($value !== null && $value !== '') {
                    $this->validateMin($field, $value, (int) $param);
                }
                break;

            case 'max':
                if ($value !== null && $value !== '') {
                    $this->validateMax($field, $value, (int) $param);
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '') {
                    $this->validateNumeric($field, $value);
                }
                break;

            case 'integer':
                if ($value !== null && $value !== '') {
                    $this->validateInteger($field, $value);
                }
                break;

            case 'url':
                if ($value !== null && $value !== '') {
                    $this->validateUrl($field, $value);
                }
                break;

            case 'phone':
                if ($value !== null && $value !== '') {
                    $this->validatePhone($field, $value);
                }
                break;

            case 'date':
                if ($value !== null && $value !== '') {
                    $this->validateDate($field, $value);
                }
                break;

            case 'boolean':
                if ($value !== null && $value !== '') {
                    $this->validateBoolean($field, $value);
                }
                break;

            case 'in':
                if ($value !== null && $value !== '') {
                    $allowed = explode(',', $param ?? '');
                    $this->validateIn($field, $value, $allowed);
                }
                break;

            case 'not_in':
                if ($value !== null && $value !== '') {
                    $disallowed = explode(',', $param ?? '');
                    $this->validateNotIn($field, $value, $disallowed);
                }
                break;

            case 'confirmed':
                $confirmationField = $field . '_confirmation';
                $confirmationValue = $this->data[$confirmationField] ?? null;
                $this->validateConfirmed($field, $value, $confirmationValue);
                break;

            case 'regex':
                if ($value !== null && $value !== '' && $param !== null) {
                    $this->validateRegex($field, $value, $param);
                }
                break;

            case 'alpha':
                if ($value !== null && $value !== '') {
                    $this->validateAlpha($field, $value);
                }
                break;

            case 'alpha_num':
                if ($value !== null && $value !== '') {
                    $this->validateAlphaNum($field, $value);
                }
                break;

            case 'alpha_dash':
                if ($value !== null && $value !== '') {
                    $this->validateAlphaDash($field, $value);
                }
                break;

            case 'array':
                $this->validateArray($field, $value);
                break;

            case 'file':
                $this->validateFile($field, $value);
                break;

            case 'image':
                $this->validateImage($field, $value);
                break;

            case 'mimes':
                if ($value !== null && is_array($value) && isset($value['tmp_name']) && $value['error'] === UPLOAD_ERR_OK) {
                    $allowed = explode(',', $param ?? '');
                    $this->validateMimes($field, $value, $allowed);
                }
                break;

            case 'max_size':
                if ($value !== null && is_array($value) && isset($value['size'])) {
                    $this->validateMaxSize($field, $value['size'], (int) ($param ?? 0));
                }
                break;
        }
    }

    /**
     * ---------------------------------------------------------------------------
     *  Правила валидации
     * ---------------------------------------------------------------------------
     */

    protected function validateRequired(string $field, mixed $value): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, 'required');
        }
    }

    protected function validateEmail(string $field, mixed $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, 'email');
        }
    }

    protected function validateMin(string $field, mixed $value, int $min): void
    {
        if (is_string($value) && mb_strlen($value) < $min) {
            $this->addError($field, 'min', ['param' => $min]);
        }
        if (is_numeric($value) && (float) $value < $min) {
            $this->addError($field, 'min', ['param' => $min]);
        }
        if (is_array($value) && count($value) < $min) {
            $this->addError($field, 'min', ['param' => $min]);
        }
    }

    protected function validateMax(string $field, mixed $value, int $max): void
    {
        if (is_string($value) && mb_strlen($value) > $max) {
            $this->addError($field, 'max', ['param' => $max]);
        }
        if (is_numeric($value) && (float) $value > $max) {
            $this->addError($field, 'max', ['param' => $max]);
        }
        if (is_array($value) && count($value) > $max) {
            $this->addError($field, 'max', ['param' => $max]);
        }
    }

    protected function validateNumeric(string $field, mixed $value): void
    {
        if (!is_numeric($value)) {
            $this->addError($field, 'numeric');
        }
    }

    protected function validateInteger(string $field, mixed $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            $this->addError($field, 'integer');
        }
    }

    protected function validateUrl(string $field, mixed $value): void
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            $this->addError($field, 'url');
        }
    }

    protected function validatePhone(string $field, mixed $value): void
    {
        // Поддерживаем форматы: +7XXXXXXXXXX, 8XXXXXXXXXX, XXXXXXXXXX
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $value);
        if (!preg_match('/^(\+?7|8)?\d{10}$/', $cleaned)) {
            $this->addError($field, 'phone');
        }
    }

    protected function validateDate(string $field, mixed $value): void
    {
        if (strtotime((string) $value) === false) {
            $this->addError($field, 'date');
        }
    }

    protected function validateBoolean(string $field, mixed $value): void
    {
        $allowed = [true, false, 1, 0, '1', '0', 'true', 'false', 'on', 'off', 'yes', 'no'];
        if (!in_array($value, $allowed, true)) {
            $this->addError($field, 'boolean');
        }
    }

    protected function validateIn(string $field, mixed $value, array $allowed): void
    {
        if (!in_array((string) $value, $allowed, true)) {
            $this->addError($field, 'in', ['param' => implode(', ', $allowed)]);
        }
    }

    protected function validateNotIn(string $field, mixed $value, array $disallowed): void
    {
        if (in_array((string) $value, $disallowed, true)) {
            $this->addError($field, 'not_in', ['param' => implode(', ', $disallowed)]);
        }
    }

    protected function validateConfirmed(string $field, mixed $value, mixed $confirmationValue): void
    {
        if ((string) $value !== (string) $confirmationValue) {
            $this->addError($field, 'confirmed');
        }
    }

    protected function validateRegex(string $field, mixed $value, string $pattern): void
    {
        if (!preg_match($pattern, (string) $value)) {
            $this->addError($field, 'regex');
        }
    }

    protected function validateAlpha(string $field, mixed $value): void
    {
        if (!preg_match('/^[\p{L}]+$/u', (string) $value)) {
            $this->addError($field, 'alpha');
        }
    }

    protected function validateAlphaNum(string $field, mixed $value): void
    {
        if (!preg_match('/^[\p{L}0-9]+$/u', (string) $value)) {
            $this->addError($field, 'alpha_num');
        }
    }

    protected function validateAlphaDash(string $field, mixed $value): void
    {
        if (!preg_match('/^[\p{L}0-9_\-]+$/u', (string) $value)) {
            $this->addError($field, 'alpha_dash');
        }
    }

    protected function validateArray(string $field, mixed $value): void
    {
        if (!is_array($value)) {
            $this->addError($field, 'array');
        }
    }

    protected function validateFile(string $field, mixed $value): void
    {
        if (!is_array($value) || !isset($value['tmp_name']) || $value['error'] !== UPLOAD_ERR_OK) {
            $this->addError($field, 'file');
        }
    }

    protected function validateImage(string $field, mixed $value): void
    {
        if (!is_array($value) || !isset($value['tmp_name']) || $value['error'] !== UPLOAD_ERR_OK) {
            $this->addError($field, 'image');
            return;
        }

        $imageInfo = @getimagesize($value['tmp_name']);
        if ($imageInfo === false) {
            $this->addError($field, 'image');
        }
    }

    protected function validateMimes(string $field, mixed $value, array $allowed): void
    {
        $ext = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed, true)) {
            $this->addError($field, 'mimes', ['param' => implode(', ', $allowed)]);
        }
    }

    protected function validateMaxSize(string $field, int $size, int $maxKb): void
    {
        if ($size > $maxKb * 1024) {
            $this->addError($field, 'max_size', ['param' => $maxKb]);
        }
    }

    /**
     * ---------------------------------------------------------------------------
     *  Работа с ошибками
     * ---------------------------------------------------------------------------
     */

    /**
     * addError
     *
     * Добавляет ошибку для указанного поля.
     *
     * @param  string      $field  Название поля
     * @param  string      $rule   Название правила
     * @param  array       $params Дополнительные параметры для сообщения
     * @return void
     */
    protected function addError(string $field, string $rule, array $params = []): void
    {
        $params['field'] = $field;

        // Пользовательское сообщение для конкретного поля и правила
        $messageKey = "{$field}.{$rule}";
        if (isset($this->messages[$messageKey])) {
            $message = $this->messages[$messageKey];
        }
        // Пользовательское сообщение для правила
        elseif (isset($this->messages[$rule])) {
            $message = $this->messages[$rule];
        }
        // Сообщение по умолчанию
        else {
            $message = $this->defaultMessages[$rule] ?? "Поле «{$field}» не прошло проверку «{$rule}».";
        }

        // Замена плейсхолдеров
        $message = str_replace(['{field}', '{param}'], [$field, $params['param'] ?? ''], $message);

        $this->errors[$field][] = $message;
    }

    /**
     * getErrors
     *
     * Возвращает массив всех ошибок валидации.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * getFirstError
     *
     * Возвращает первую ошибку для указанного поля.
     *
     * @param  string      $field Название поля
     * @return string|null
     */
    public function getFirstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * hasErrors
     *
     * Проверяет, есть ли ошибки валидации.
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * getValidated
     *
     * Возвращает только валидированные данные.
     *
     * @return array
     */
    public function getValidated(): array
    {
        return $this->validated;
    }

    /**
     * fails
     *
     * Псевдоним для hasErrors (для читаемости кода).
     *
     * @return bool
     */
    public function fails(): bool
    {
        return $this->hasErrors();
    }

    /**
     * passes
     *
     * Возвращает true, если валидация пройдена успешно.
     *
     * @return bool
     */
    public function passes(): bool
    {
        return !$this->hasErrors();
    }

    /**
     * ---------------------------------------------------------------------------
     *  Flash-сохранение в сессию
     * ---------------------------------------------------------------------------
     */

    /**
     * flash
     *
     * Сохраняет ошибки и старые данные в сессию для отображения после редиректа.
     *
     * @return $this
     */
    public function flash(): static
    {
        Session::set(['__errors' => $this->errors]);
        Session::set(['__old' => $this->data]);
        return $this;
    }
}