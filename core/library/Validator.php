<?php

namespace core\library;

class Validator
{

    private array $messages = [];
    private array $params = [];
    private bool $valid;

    public function __construct(
        private array|string|int|float|bool|null $data,
        private array|string $rules,
        private array $customMessages = []
    ) {
        if (is_array($this->rules)) {
            $this->valid = $this->fromArray($this->data, $this->rules);

            array_walk($this->rules, function ($value, $key) {
                if (!array_key_exists($key, $this->data))
                    if (in_array("required", $value))
                        $this->valid = $this->required($key, null);
            });
        } else
            $this->valid = $this->fromValue($this->data, $this->rules);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    private function fromArray(array $data, array $rules): bool
    {
        $this->rules = $this->setRules($rules);
        $allValidations = true;
        foreach ($data as $field => $value) {
            if (!$this->validate($field, $value))
                $allValidations = false;
        }

        return $allValidations;
    }

    private function fromValue(string|int|float|bool|null $value, string $rules): bool
    {
        $this->rules = $this->setRules(["value" => $rules]);
        return $this->validate("value", $value);
    }

    private function validate(string $field, mixed $value): bool
    {
        if (isset($this->rules[$field])) {
            foreach ($this->rules[$field] as $rule) {
                if (!method_exists($this, $rule))
                    throw new \Exception("This validation rule {$rule} not exist!");

                if (!empty($this->params[$field][$rule])) {
                    if (!$this->$rule($field, $value, $this->params[$field][$rule]))
                        return false;
                } else {
                    if (!$this->$rule($field, $value))
                        return false;
                }
            }
        }

        return true;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public function getFirstMessage(): string
    {
        return $this->messages[array_key_first($this->messages)] ?? "No message found!";
    }

    private function addMessage(array $aliases, string $message, string $validation): void
    {
        $field = $aliases["{:field}"];
        $alias = array_keys($aliases);
        $value = array_values($aliases);

        $this->messages[$field] = !empty($this->customMessages[$validation]) ? str_replace($alias, $value, $this->customMessages[$validation]) : $message;
    }

    private function setRules(array $rules): array
    {
        foreach ($rules as $field => $rule) {
            $rules[$field] = explode("|", $rule);
            foreach ($rules[$field] as $key => $ruleField) {
                if (str_contains($ruleField, ":")) {
                    [$rule, $params] = explode(":", $ruleField);
                    $params = explode(",", string: $params);
                    $this->params[$field][$rule] = $params;
                    $rules[$field][$key] = $rule;
                }
            }
        }

        return $rules;
    }


    private function required(string $field, mixed $value): bool
    {
        if (!empty($value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} is mandatory!", __FUNCTION__);
        return false;
    }

    private function alpha(string $field, string $value): bool
    {
        if (is_string($value) and preg_match("/^[A-zÀ-ú]+$/", $value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must contain only letters!", __FUNCTION__);
        return false;
    }

    private function email(string $field, mixed $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be a valid email!", __FUNCTION__);
        return false;
    }

    private function int(string $field, mixed $value): bool
    {
        if (is_numeric($value) and is_int($value + 0))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be of type integer!", __FUNCTION__);
        return false;
    }

    private function float(string $field, mixed $value): bool
    {
        if (is_numeric($value) and is_float($value + 0))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be of type float!", __FUNCTION__);
        return false;
    }

    private function uppercase(string $field, mixed $value): bool
    {
        if (is_string($value) and preg_match_all("/^[^a-zà-û]+$/", $value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be in all capital letters!", __FUNCTION__);
        return false;
    }

    private function lowercase(string $field, mixed $value): bool
    {
        if (is_string($value) and preg_match_all("/^[^A-ZÀ-Û]+$/", $value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must have all lowercase letters!", __FUNCTION__);
        return false;
    }

    private function json(string $field, mixed $value): bool
    {
        if (is_string($value) and json_validate($value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be a valid json!", __FUNCTION__);
        return false;
    }

    private function numeric(string $field, mixed $value): bool
    {
        if (is_numeric($value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be a number!", __FUNCTION__);
        return false;
    }

    private function alphanum(string $field, mixed $value): bool
    {
        if (is_string($value) and preg_match("/^[A-zÀ-ú0-9]+$/", $value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must contain only letters and numbers!", __FUNCTION__);
        return false;
    }

    private function alphaspace(string $field, mixed $value): bool
    {
        if (is_string($value) and preg_match("/^[A-zÀ-û\s]+$/", $value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must contain only letters and spaces!", __FUNCTION__);
        return false;
    }

    private function alphadash(string $field, mixed $value): bool
    {
        if (is_string($value) and preg_match("/^[A-zÀ-û\-\_]+$/", $value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must contain only letters, dashes and underscores!", __FUNCTION__);
        return false;
    }

    private function alphanumspecial(string $field, mixed $value): bool
    {
        if (is_string($value) and preg_match("/^[A-zÀ-û0-9[:punct:]]+$/", $value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must contain only letters, numbers and special characters!", __FUNCTION__);
        return false;
    }

    private function in(string $field, mixed $value, array $params): bool
    {
        if (in_array($value, $params))
            return true;

        $this->addMessage(["{:field}" => $field, "{:in}" => implode(", ", $params)], "The field {$field} must be a value contained in: [" . implode(", ", $params) . "]!", __FUNCTION__);
        return false;
    }

    private function notin(string $field, mixed $value, array $params): bool
    {
        if (!in_array($value, $params))
            return true;

        $this->addMessage(["{:field}" => $field, "{:notin}" => implode(", ", $params)], "The field {$field} must be different from the following values: [" . implode(", ", $params) . "]!", __FUNCTION__);
        return false;
    }

    private function min(string $field, mixed $value, array $params): bool
    {
        $value = is_numeric($value) ? $value + 0 : strlen($value);
        if ($value >= $params[0])
            return true;

        $this->addMessage(["{:field}" => $field, "{:min}" => $params[0]], "The field {$field} must have at least {$params[0]} characters!", __FUNCTION__);
        return false;
    }

    private function max(string $field, mixed $value, array $params): bool
    {
        $value = is_numeric($value) ? $value + 0 : strlen($value);
        if ($value <= $params[0])
            return true;

        $this->addMessage(["{:field}" => $field, "{:max}" => $params[0]], "The field {$field} must have a maximum of {$params[0]} characters!", __FUNCTION__);
        return false;
    }

    private function between(string $field, mixed $value, array $params): bool
    {
        $value = is_numeric($value) ? $value + 0 : strlen($value);
        if ($value >= $params[0] and $value <= $params[1])
            return true;

        $this->addMessage(["{:field}" => $field, "{:min}" => $params[0], "{:max}" => $params[1]], "The field {$field} must be between {$params[0]} and {$params[1]}!", __FUNCTION__);
        return false;
    }

    private function bool(string $field, mixed $value): bool
    {
        if (is_bool($value) or in_array($value, [0, 1, "0", "1", "true", "false", true, false], true))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be of boolean type!", __FUNCTION__);
        return false;
    }

    private function array(string $field, mixed $value): bool
    {
        if (is_array($value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be of type array!", __FUNCTION__);
        return false;
    }

    private function same(string $field, mixed $value, array $params): bool
    {
        if ($value === $this->data[$params[0]])
            return true;

        $this->addMessage(["{:field}" => $field, "{:same}" => $params[0]], "The field {$field} must be equal to the field {$params[0]}!", __FUNCTION__);
        return false;
    }

    private function different(string $field, mixed $value, array $params): bool
    {
        if ($value !== $this->data[$params[0]])
            return true;

        $this->addMessage(["{:field}" => $field, "{:different}" => $params[0]], "The field {$field} must be different from the field {$params[0]}!", __FUNCTION__);
        return false;
    }

    private function string(string $field, mixed $value): bool
    {
        if (is_string($value))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be of type string!", __FUNCTION__);
        return false;
    }

    private function ip(string $field, mixed $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_IP))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be a valid IP!", __FUNCTION__);
        return false;
    }

    private function url(string $field, mixed $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL))
            return true;

        $this->addMessage(["{:field}" => $field], "The field {$field} must be a valid url!", __FUNCTION__);
        return false;
    }

    private function regex(string $field, mixed $value, array $params): bool
    {
        if (preg_match("/^{$params[0]}$/", $value))
            return true;

        $this->addMessage(["{:field}" => $field, "{:regex}" => "/^{$params[0]}$/"], "The field {$field} must match the rule: /^{$params[0]}$/!", __FUNCTION__);
        return false;
    }
}
