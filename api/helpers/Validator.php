<?php
/**
 * Input Validator
 */

class Validator {
    private array $errors = [];
    private array $data;
    
    public function __construct(array $data) {
        $this->data = $data;
    }
    
    public function required(string $field, string $message = null): self {
        if (empty($this->data[$field])) {
            $this->errors[$field] = $message ?? "{$field} จำเป็นต้องระบุ";
        }
        return $this;
    }
    
    public function email(string $field): self {
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "รูปแบบอีเมลไม่ถูกต้อง";
        }
        return $this;
    }
    
    public function numeric(string $field): self {
        if (!empty($this->data[$field]) && !is_numeric($this->data[$field])) {
            $this->errors[$field] = "{$field} ต้องเป็นตัวเลข";
        }
        return $this;
    }
    
    public function min(string $field, float $min): self {
        if (!empty($this->data[$field]) && floatval($this->data[$field]) < $min) {
            $this->errors[$field] = "{$field} ต้องมากกว่า {$min}";
        }
        return $this;
    }
    
    public function passes(): bool {
        return empty($this->errors);
    }
    
    public function errors(): array {
        return $this->errors;
    }
    
    public function get(string $field, $default = null) {
        return $this->data[$field] ?? $default;
    }
}
