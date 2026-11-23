<?php
require_once __DIR__ . '/ServiceException.php';

// Shared helpers for the entity services: error shortcuts and field validation.
// Services hold the business logic and are the only layer that calls the DAOs.
// Validation helpers add a message to $errors when a value is invalid and return null.
abstract class BaseService
{
    protected function notFound(string $what): ServiceException
    {
        return new ServiceException($what . ' not found.', 404);
    }

    protected function invalid(array $errors): ServiceException
    {
        return new ServiceException('Validation failed.', 400, $errors);
    }

    // Text with a length between $min and $max characters (trimmed).
    protected function text(array $data, string $field, int $min, int $max, array &$errors): ?string
    {
        if (!array_key_exists($field, $data) || $data[$field] === null) {
            $errors[$field] = "$field is required.";
            return null;
        }
        if (!is_string($data[$field])) {
            $errors[$field] = "$field must be text.";
            return null;
        }

        $value = trim($data[$field]);
        $length = mb_strlen($value);
        if ($length < $min || $length > $max) {
            $errors[$field] = "$field must be between $min and $max characters.";
            return null;
        }
        return $value;
    }

    // Whole number between $min and $max. Accepts 5 and "5", rejects 5.5, true and "abc".
    protected function integer(array $data, string $field, int $min, int $max, array &$errors): ?int
    {
        $value = $data[$field] ?? null;
        $number = (is_int($value) || (is_string($value) && $value !== ''))
            ? filter_var($value, FILTER_VALIDATE_INT)
            : false;

        if ($number === false || $number < $min || $number > $max) {
            $errors[$field] = "$field must be a whole number between $min and $max.";
            return null;
        }
        return $number;
    }

    // Id of another record: a whole number of at least 1.
    protected function id(array $data, string $field, array &$errors): ?int
    {
        $value = $data[$field] ?? null;
        $number = (is_int($value) || (is_string($value) && $value !== ''))
            ? filter_var($value, FILTER_VALIDATE_INT)
            : false;

        if ($number === false || $number < 1) {
            $errors[$field] = "$field must be a positive whole number.";
            return null;
        }
        return $number;
    }

    // Number with at most 2 decimals, between $min and $max.
    protected function decimal(array $data, string $field, float $min, float $max, array &$errors): ?float
    {
        $value = $data[$field] ?? null;
        $isNumber = is_int($value) || is_float($value) || (is_string($value) && is_numeric($value));

        if (!$isNumber || $value < $min || $value > $max) {
            $errors[$field] = "$field must be a number between $min and $max.";
            return null;
        }
        return round((float) $value, 2);
    }

    // One of the allowed values.
    protected function choice(array $data, string $field, array $allowed, array &$errors): ?string
    {
        $value = $data[$field] ?? null;
        if (!is_string($value) || !in_array($value, $allowed, true)) {
            $errors[$field] = "$field must be one of: " . implode(', ', $allowed) . '.';
            return null;
        }
        return $value;
    }
}
