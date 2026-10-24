<?php

declare(strict_types=1);

/**
 * Panada validation API.
 *
 * Extend it, describe your rules, then validate:
 *
 *   class Register extends \Resources\Validation
 *   {
 *       public function setRules(): array
 *       {
 *           return [
 *               'name'  => ['rules' => ['required', 'max' => 50], 'label' => 'Name'],
 *               'email' => ['rules' => ['required', 'email'], 'filter' => ['lower']],
 *               'age'   => ['rules' => ['integer'], 'filter' => ['int']],
 *           ];
 *       }
 *   }
 *
 *   $form = new Register();
 *   if ($form->validate()) {            // reads $_POST
 *       $db->insert('users', $form->value());
 *   } else {
 *       $errors = $form->errorMessages();
 *   }
 *
 * Every string is trimmed and cleaned of invisible control characters before
 * the rules run (see $autoSanitize). value() returns only the fields that have
 * rules, so extra fields a visitor adds to the form never reach your database.
 *
 * Sanitizing is not escaping: always print user data in views with e().
 *
 * @link	http://panadaframework.com/
 *
 * @license	http://www.opensource.org/licenses/bsd-license.php
 * @author	Iskandar Soesman <k4ndar@yahoo.com>
 *
 * @since	Version 0.1
 */

namespace Resources;

class Validation
{
    /** Trim strings and drop control characters from all input before validating. */
    protected bool $autoSanitize = true;

    protected array $ruleErrorMessages = [];
    protected array $validValues = [];

    private array $errorMessages = [];
    private array $unsetValues = [];

    public function __construct()
    {
        $this->setRuleErrorMessages();
    }

    /**
     * Specify the validation rules. Override this in your class.
     */
    public function setRules(): array
    {
        return [];
    }

    /**
     * Performs the validation.
     *
     * @param array $fields Values to check. Empty: $_POST (and $_FILES).
     */
    public function validate(array $fields = []): bool
    {
        if ($fields === []) {
            $fields = $_POST + $_FILES;
        }

        $rules = $this->setRules();
        $this->errorMessages = $this->validValues = $this->unsetValues = [];
        $return = true;

        // Loop over the RULES, not the input: a required field that was
        // removed from the form is still reported as missing.
        foreach ($rules as $field => $rule) {
            $value = $fields[$field] ?? null;
            $label = $rule['label'] ?? $field;

            if (is_array($value) && !$this->isUploadedFile($value)) {
                foreach ($value as $key => $item) {
                    $item = $this->sanitize($item, $rule['filter'] ?? []);

                    if (!$this->check($field, $item, $label.' #'.$key, $rule, $rules, $fields)) {
                        $return = false;
                        unset($this->validValues[$field]);

                        continue 2;
                    }

                    $this->validValues[$field][$key] = $item;
                }

                continue;
            }

            $value = $this->sanitize($value, $rule['filter'] ?? []);

            if ($this->check($field, $value, $label, $rule, $rules, $fields)) {
                $this->validValues[$field] = $value;
            } else {
                $return = false;
            }
        }

        foreach ($this->unsetValues as $field) {
            unset($this->validValues[$field]);
        }

        return $return;
    }

    /**
     * Run every rule of one field, stop at the first failure.
     */
    private function check(string $field, mixed $value, string $label, array $rule, array $rules, array $fields): bool
    {
        $fieldRules = $rule['rules'] ?? [];
        $isRequired = in_array('required', $fieldRules, true) || in_array('file', $fieldRules, true);

        // An optional field left empty is valid, its other rules are skipped.
        if (!$isRequired && $this->isEmpty($value)) {
            return true;
        }

        foreach ($fieldRules as $name => $parameter) {
            if (is_int($name)) {
                [$name, $parameter] = [$parameter, null];
            }

            $passed = match ($name) {
                'callback' => $this->runCallback($parameter, $field, $value, $label),
                'compare' => $this->ruleCompare($field, $value, $label, $fields[$parameter] ?? null, $rules[$parameter]['label'] ?? $parameter, $parameter),
                default => $this->runRule($name, $field, $value, $label, $parameter),
            };

            if (!$passed) {
                return false;
            }
        }

        return true;
    }

    private function runRule(string $name, string $field, mixed $value, string $label, mixed $parameter): bool
    {
        $method = 'rule'.ucfirst($name);

        if (!method_exists($this, $method)) {
            throw new RunException('Unknown validation rule "'.$name.'" for field '.$field);
        }

        if (!$this->$method($value, $parameter)) {
            $this->setErrorMessage($field, str_replace(
                ['%label%', '%size%'],
                [$label, is_scalar($parameter) ? (string) $parameter : ''],
                $this->ruleErrorMessages[strtolower($name)] ?? '%label% is not valid'
            ));

            return false;
        }

        return true;
    }

    /**
     * 'callback' => 'myMethod' calls $this->myMethod($field, $value, $label),
     * which returns bool and may call setErrorMessage() itself.
     */
    private function runCallback(mixed $callback, string $field, mixed $value, string $label): bool
    {
        $callable = is_string($callback) && method_exists($this, $callback) ? [$this, $callback] : $callback;

        if (!is_callable($callable)) {
            throw new RunException('Validation callback for '.$field.' is not callable');
        }

        $passed = (bool) $callable($field, $value, $label);

        if (!$passed && !isset($this->errorMessages[$field])) {
            $this->setErrorMessage($field, str_replace('%label%', $label, '%label% is not valid'));
        }

        return $passed;
    }

    /**
     * Automatic + per-field sanitizing.
     *
     * 'filter' accepts these names, or any PHP callable ('ucwords', fn ...):
     *   trim, lower, upper, int, float, digits, strip_tags, email, escape
     */
    protected function sanitize(mixed $value, array $filters = []): mixed
    {
        if (is_array($value)) {
            return $this->isUploadedFile($value) ? $value : array_map(fn ($item) => $this->sanitize($item, $filters), $value);
        }

        if ($value === null) {
            return null;
        }

        if (is_scalar($value) && !is_string($value)) {
            $value = (string) $value;
        }

        if ($this->autoSanitize && is_string($value)) {
            // Remove NUL and other invisible control characters, keep tab/newline.
            $value = trim((string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $value));
        }

        foreach ($filters as $filter) {
            $value = match ($filter) {
                'trim' => trim((string) $value),
                'lower' => mb_strtolower((string) $value),
                'upper' => mb_strtoupper((string) $value),
                'int' => filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE) ?? $value,
                'float' => filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ?? $value,
                'digits' => (string) preg_replace('/\D/', '', (string) $value),
                'strip_tags' => strip_tags((string) $value),
                'email' => (string) filter_var($value, FILTER_SANITIZE_EMAIL),
                'escape' => e($value),
                default => is_callable($filter) ? $filter($value) : throw new RunException('Unknown filter: '.(is_string($filter) ? $filter : get_debug_type($filter))),
            };
        }

        return $value;
    }

    /**
     * Populate the error message(s).
     *
     * @return array|string|null all messages, one field's message, or null
     */
    public function errorMessages(string|false $field = false, ?string $tagOpen = null, ?string $tagClose = null): array|string|null
    {
        if ($this->errorMessages === []) {
            return null;
        }

        if ($field !== false) {
            if (!isset($this->errorMessages[$field])) {
                return null;
            }

            return ($tagOpen !== null || $tagClose !== null)
                ? $tagOpen.$this->errorMessages[$field].$tagClose
                : $this->errorMessages[$field];
        }

        if ($tagOpen !== null || $tagClose !== null) {
            return implode('', array_map(fn ($message) => $tagOpen.$message.$tagClose, $this->errorMessages));
        }

        return $this->errorMessages;
    }

    /**
     * Setter for error message.
     */
    public function setErrorMessage(string $field, string $message): void
    {
        $this->errorMessages[$field] = $message;
    }

    /**
     * Validated (and sanitized) values: all of them, or one field.
     */
    public function value(string|false $field = false): mixed
    {
        if ($field === false) {
            return $this->validValues;
        }

        return $this->validValues[$field] ?? null;
    }

    /**
     * Define the default error messages.
     *
     * @param array $messages User defined message by rule
     */
    protected function setRuleErrorMessages(array $messages = []): void
    {
        $this->ruleErrorMessages = array_merge([
            'required' => '%label% can not be empty',
            'email' => '%label% not valid email format',
            'min' => '%label% need more then %size% character',
            'max' => '%label% need less then %size% character',
            'compare' => '%label% value did not match compare to %comparatorLabel%',
            'file' => '%label% can not be empty',
            'regex' => '%label% input format not valid',
            'in' => '%label% did not match to any specified values',
            'url' => '%label% not valid URL format',
            'alpha' => '%label% can only alphabet character',
            'numeric' => '%label% can only numerical character',
            'integer' => '%label% must be a whole number',
            'alphanumeric' => '%label% can only alphabet or numerical character',
            'match' => '%label% length must exactly %size% character',
        ], $messages);
    }

    // ---------------------------------------------------------------------
    // Rules. Each one receives the value and its parameter, returns bool.
    // ---------------------------------------------------------------------

    protected function ruleRequired(mixed $value): bool
    {
        return !$this->isEmpty($value);
    }

    protected function ruleEmail(mixed $value): bool
    {
        return is_string($value) && $this->isEmail($value) !== false;
    }

    protected function ruleUrl(mixed $value): bool
    {
        return is_string($value) && $this->isUrl($value) !== false;
    }

    protected function ruleMin(mixed $value, mixed $size): bool
    {
        return mb_strlen((string) $value) >= (int) $size;
    }

    protected function ruleMax(mixed $value, mixed $size): bool
    {
        return mb_strlen((string) $value) <= (int) $size;
    }

    protected function ruleMatch(mixed $value, mixed $size): bool
    {
        return mb_strlen((string) $value) === (int) $size;
    }

    protected function ruleIn(mixed $value, mixed $list): bool
    {
        return in_array((string) $value, array_map('strval', (array) $list), true);
    }

    protected function ruleRegex(mixed $value, mixed $pattern): bool
    {
        return preg_match((string) $pattern, (string) $value) === 1;
    }

    protected function ruleAlpha(mixed $value): bool
    {
        return preg_match('/^\p{L}+$/u', (string) $value) === 1;
    }

    protected function ruleNumeric(mixed $value): bool
    {
        return preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', (string) $value) === 1;
    }

    protected function ruleInteger(mixed $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    protected function ruleAlphanumeric(mixed $value): bool
    {
        return preg_match('/^[\p{L}0-9]+$/u', (string) $value) === 1;
    }

    /**
     * Ensuring the field contains an uploaded file (or several).
     */
    protected function ruleFile(mixed $value): bool
    {
        if (!is_array($value) || !isset($value['name'])) {
            return false;
        }

        foreach ((array) $value['name'] as $name) {
            if ($name === '' || $name === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Ensuring the attribute is equal to another attribute (e.g. password confirmation).
     */
    private function ruleCompare(string $field, mixed $value, string $label, mixed $comparatorValue, string $comparatorLabel, string $comparatorField): bool
    {
        if (!$this->isEmpty($comparatorValue) && $this->sanitize($comparatorValue) === $value) {
            return true;
        }

        $this->setErrorMessage($field, str_replace(['%label%', '%comparatorLabel%'], [$label, $comparatorLabel], $this->ruleErrorMessages['compare']));
        $this->unsetValues[] = $comparatorField;

        return false;
    }

    // ---------------------------------------------------------------------
    // Helpers, also usable on their own.
    // ---------------------------------------------------------------------

    /**
     * Trim then make it lower.
     */
    public function trimLower(string $string): string
    {
        return trim(mb_strtolower($string));
    }

    /**
     * Email format validation. Returns the cleaned email or false.
     */
    public function isEmail(string $string): string|false
    {
        $string = $this->trimLower($string);

        return filter_var($string, FILTER_VALIDATE_EMAIL) ? $string : false;
    }

    /**
     * URL validation, http and https only (rejects javascript: and data: links).
     */
    public function isUrl(string $string): string|false
    {
        $string = trim($string);
        $scheme = strtolower((string) parse_url($string, PHP_URL_SCHEME));

        return in_array($scheme, ['http', 'https'], true) ? filter_var($string, FILTER_VALIDATE_URL) : false;
    }

    /**
     * Return only numeric character.
     */
    public function stripNumeric(string $string): string
    {
        return (string) filter_var($string, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Positive numeric validation.
     */
    public function isPositiveNumeric(string|int|float $string): bool
    {
        return (bool) preg_match('/^[0-9]*\.?[0-9]+$/', (string) $string);
    }

    /**
     * Use this for filterize user first name and last name.
     */
    public function displayName(string $string): string
    {
        $string = strip_tags(trim($string));

        return (string) preg_replace('/[^\p{L}0-9 .,\'"-]/u', '', $string);
    }

    private function isEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    private function isUploadedFile(array $value): bool
    {
        return isset($value['tmp_name'], $value['name']);
    }
}
