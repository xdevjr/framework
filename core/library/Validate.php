<?php

namespace core\library;

class Validate
{

    private array $messages = [];

    public function __construct(
        private array $rules
    ) {
    }

    public function validate(array $data)
    {
        $allValidations = true;
        foreach ($data as $field => $value) {
            foreach ($this->getRules()[$field] as $rule) {
                if (!$this->$rule($field, $value)) {
                    $allValidations = false;
                    break;
                }
            }
        }

        return $allValidations;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    private function addMessage(string $field, string $value)
    {
        $this->messages[$field][] = $value;
    }

    private function getRules(): array
    {
        $rules = [];
        foreach ($this->rules as $field => $rule) {
            $rules[$field] = explode("|", $rule);
        }

        return $rules;
    }


    private function required(string $field, mixed $value): bool
    {
        if (!empty($value))
            return true;

        $this->addMessage($field, "O campo {$field} é obrigatorio!");
        return false;
    }

    private function alpha(string $field, string $value): bool
    {
        if (is_string($value) and preg_match('/^[A-z]+$/', $value))
            return true;

        $this->addMessage($field, "O campo {$field} tem que conter apenas letras sem espaços!");
        return false;
    }

    private function email(string $field, mixed $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_EMAIL))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser um email válido!");
        return false;
    }

    private function int(string $field, mixed $value): bool
    {
        if (is_int($value))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser do tipo inteiro!");
        return false;
    }

    private function float(string $field, mixed $value): bool
    {
        if (is_float($value))
            return true;

        $this->addMessage($field, "O campo {$field} deve ser do tipo float!");
        return false;
    }

}
