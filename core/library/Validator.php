<?php

namespace core\library;

class Validator
{

    private array $messages = [];
    private array $customMessages = [];
    private array $rules = [];
    private array $params = [];

    public function fromArray(array $data, array $rules)
    {
        $this->rules = $this->setRules($rules);
        $allValidations = true;
        foreach ($data as $field => $value) {
            if (isset($this->params[$field]["same"])) {
                $this->params[$field]["same"][] = $data[$this->params[$field]["same"][0]];
            } elseif (isset($this->params[$field]["different"])) {
                $this->params[$field]["different"][] = $data[$this->params[$field]["different"][0]];
            }

            if (!$this->validate($field, $value))
                $allValidations = false;
        }

        return $allValidations;
    }

    public function fromValue(mixed $value, string $rules): bool
    {
        $this->rules["value"] = $this->setRules([$rules]);
        return $this->validate("value", $value);
    }

    private function validate(string $field, mixed $value): bool
    {
        if (isset($this->rules[$field])) {
            foreach ($this->rules[$field] as $rule) {
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

    private function addMessage(string $field, string $value, string $validation): void
    {
        $this->messages[$field] = !empty($this->customMessages[$validation]) ? str_replace("{:field}", $field, $this->customMessages[$validation]) : $value;
    }

    public function setCustomMessages(array $messages): void
    {
        $this->customMessages = $messages;
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

        $this->addMessage($field, "O campo {$field} é obrigatorio!", __FUNCTION__);
        return false;
    }

    private function alpha(string $field, string $value): bool
    {
        if (is_string($value) and ctype_alpha($value))
            return true;

        $this->addMessage($field, "O campo {$field} tem que conter apenas letras sem espaços!", __FUNCTION__);
        return false;
    }

    private function email(string $field, mixed $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser um email válido!", __FUNCTION__);
        return false;
    }

    private function int(string $field, mixed $value): bool
    {
        if (is_int($value))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser do tipo inteiro!", __FUNCTION__);
        return false;
    }

    private function float(string $field, mixed $value): bool
    {
        if (is_float($value))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser do tipo float!", __FUNCTION__);
        return false;
    }

    public function uppercase(string $field, mixed $value): bool
    {
        if (is_string($value) and ctype_upper($value))
            return true;

        $this->addMessage($field, "O campo {$field} precisa ter todas as letras maiúsculas!", __FUNCTION__);
        return false;
    }

    public function lowercase(string $field, mixed $value): bool
    {
        if (is_string($value) and ctype_lower($value))
            return true;

        $this->addMessage($field, "O campo {$field} precisa ter todas as letras minusculas!", __FUNCTION__);
        return false;
    }

    public function json(string $field, mixed $value): bool
    {
        if (is_string($value) and json_validate($value))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser um json valido!", __FUNCTION__);
        return false;
    }

    public function numeric(string $field, mixed $value): bool
    {
        if (is_numeric($value))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser um número!", __FUNCTION__);
        return false;
    }

    public function alphanum(string $field, mixed $value): bool
    {
        if (is_string($value) and ctype_alnum($value))
            return true;

        $this->addMessage($field, "O campo {$field} deve conter apenas letras, números e espaços!", __FUNCTION__);
        return false;
    }

    public function alphaspace(string $field, mixed $value): bool
    {
        if (is_string($value) and preg_match("/^[A-z\s]+$/", $value))
            return true;

        $this->addMessage($field, "O campo {$field} deve conter apenas letras e espaços!", __FUNCTION__);
        return false;
    }

    public function alphadash(string $field, mixed $value): bool
    {
        if (is_string($value) and preg_match("/^[A-z\-\_]+$/", $value))
            return true;

        $this->addMessage($field, "O campo {$field} deve conter apenas letras e espaços!", __FUNCTION__);
        return false;
    }

    public function in(string $field, mixed $value, array $params): bool
    {
        if (in_array($value, $params))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser um valor contido em: [" . implode(", ", $params) . "]!", __FUNCTION__);
        return false;
    }

    public function notin(string $field, mixed $value, array $params): bool
    {
        if (!in_array($value, $params))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser diferente dos valores a seguir: [" . implode(", ", $params) . "]!", __FUNCTION__);
        return false;
    }

    public function min(string $field, mixed $value, array $params): bool
    {
        if ($value >= $params[0])
            return true;

        $this->addMessage($field, "O campo {$field} não pode ser menor que {$params[0]}!", __FUNCTION__);
        return false;
    }

    public function max(string $field, mixed $value, array $params): bool
    {
        if ($value <= $params[0])
            return true;

        $this->addMessage($field, "O campo {$field} não pode ser maior que {$params[0]}!", __FUNCTION__);
        return false;
    }

    public function between(string $field, mixed $value, array $params): bool
    {
        if ($value >= $params[0] and $value <= $params[1])
            return true;

        $this->addMessage($field, "O campo {$field} tem que estar entre {$params[0]} e {$params[1]}!", __FUNCTION__);
        return false;
    }

    public function bool(string $field, mixed $value): bool
    {
        if (is_bool($value))
            return true;

        $this->addMessage($field, "O campo {$field} tem que do tipo booleano!", __FUNCTION__);
        return false;
    }

    public function array(string $field, mixed $value): bool
    {
        if (is_array($value))
            return true;

        $this->addMessage($field, "O campo {$field} tem que do tipo array!", __FUNCTION__);
        return false;
    }

    public function same(string $field, mixed $value, array $params): bool
    {
        if ($value === $params[1])
            return true;

        $this->addMessage($field, "O campo {$field} tem que ser igual ao campo {$params[0]}!", __FUNCTION__);
        return false;
    }

    public function different(string $field, mixed $value, array $params): bool
    {
        if ($value !== $params[1])
            return true;

        $this->addMessage($field, "O campo {$field} tem que ser diferente do campo {$params[0]}!", __FUNCTION__);
        return false;
    }

    public function string(string $field, mixed $value): bool
    {
        if (is_string($value) and ctype_print($value))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser do tipo string!", __FUNCTION__);
        return false;
    }

    public function ip(string $field, mixed $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_IP))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser um ip valido!", __FUNCTION__);
        return false;
    }

    public function url(string $field, mixed $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser uma url valida!", __FUNCTION__);
        return false;
    }

    public function regex(string $field, mixed $value, array $params): bool
    {
        if (preg_match("/^{$params[0]}$/", $value))
            return true;

        $this->addMessage($field, "O campo {$field} precisa combinar com a regra: /^{$params[0]}$/!", __FUNCTION__);
        return false;
    }
}
