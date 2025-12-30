<?php

namespace Core\Utilities;

class Validator
{
    private $data;
    private $rules;
    private $errors = [];

    public function __construct($data, $rules)
    {
        $this->data = $data;
        $this->rules = $rules;
    }

    public function validate()
    {
        foreach ($this->rules as $field => $ruleSet) {
            $rules = explode('|', $ruleSet);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                $this->applyRule($field, $value, $rule);
            }
        }

        return empty($this->errors);
    }

    private function applyRule($field, $value, $rule)
    {
        if (strpos($rule, ':') !== false) {
            list($ruleName, $parameter) = explode(':', $rule, 2);
        } else {
            $ruleName = $rule;
            $parameter = null;
        }

        switch ($ruleName) {
            case 'required':
                if (empty($value) && $value !== '0') {
                    $this->addError($field, "{$field} is required");
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addError($field, "{$field} must be a valid email");
                }
                break;

            case 'min':
                if (strlen($value) < $parameter) {
                    $this->addError($field, "{$field} must be at least {$parameter} characters");
                }
                break;

            case 'max':
                if (strlen($value) > $parameter) {
                    $this->addError($field, "{$field} must not exceed {$parameter} characters");
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    $this->addError($field, "{$field} must be numeric");
                }
                break;

            case 'alpha':
                if (!empty($value) && !ctype_alpha($value)) {
                    $this->addError($field, "{$field} must contain only letters");
                }
                break;

            case 'alphanumeric':
                if (!empty($value) && !ctype_alnum($value)) {
                    $this->addError($field, "{$field} must contain only letters and numbers");
                }
                break;

            case 'url':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_URL)) {
                    $this->addError($field, "{$field} must be a valid URL");
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($this->data[$confirmField] ?? null)) {
                    $this->addError($field, "{$field} confirmation does not match");
                }
                break;

            case 'unique':
                // Format: unique:table,column
                list($table, $column) = explode(',', $parameter);
                if ($this->checkUnique($table, $column, $value)) {
                    $this->addError($field, "{$field} already exists");
                }
                break;
        }
    }

    private function checkUnique($table, $column, $value)
    {
        // This would need database connection
        return false;
    }

    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }

    public function errors()
    {
        return $this->errors;
    }

    public static function make($data, $rules)
    {
        return new self($data, $rules);
    }
}
