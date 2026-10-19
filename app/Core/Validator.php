<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\ValidationException;
use DateTimeImmutable;

/**
 * Small rule-based validator.
 *
 *   Validator::validate($data, [
 *       'email' => 'required|email|max:120',
 *       'capacity' => 'required|integer|between:1,5000',
 *   ], ['email' => 'email address']);
 */
final class Validator
{
    public const DATETIME_FORMAT = 'Y-m-d\TH:i'; // <input type="datetime-local">

    /**
     * @param array<string, string> $rules
     * @param array<string, string> $labels friendlier field names for messages
     * @return array validated data (only the fields that had rules)
     * @throws ValidationException
     */
    public static function validate(array $data, array $rules, array $labels = []): array
    {
        $errors = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            $label = $labels[$field] ?? str_replace('_', ' ', $field);
            $ruleList = explode('|', $ruleString);
            $isEmpty = $value === null || $value === '';

            if ($isEmpty && in_array('nullable', $ruleList, true)) {
                continue;
            }

            foreach ($ruleList as $rule) {
                [$name, $arg] = array_pad(explode(':', $rule, 2), 2, null);
                $error = self::check($name, $arg, $field, $value, $isEmpty, $label, $data);
                if ($error !== null) {
                    $errors[$field] = $error;
                    break; // one message per field is enough
                }
            }
        }

        if ($errors) {
            // Never flash passwords back into the form.
            $old = array_diff_key($data, array_flip(['password', 'password_confirmation', 'current_password', '_token']));
            throw new ValidationException($errors, $old);
        }

        return array_intersect_key($data, $rules);
    }

    private static function check(string $rule, ?string $arg, string $field, mixed $value, bool $isEmpty, string $label, array $data): ?string
    {
        $Label = ucfirst($label);

        if ($rule === 'required') {
            return $isEmpty ? "$Label is required." : null;
        }
        if ($isEmpty || $rule === 'nullable') {
            return null;
        }

        $str = is_scalar($value) ? (string) $value : '';

        return match ($rule) {
            'email' => filter_var($str, FILTER_VALIDATE_EMAIL) ? null : 'Enter a valid email address.',
            'min' => mb_strlen($str) >= (int) $arg ? null : "$Label must be at least $arg characters.",
            'max' => mb_strlen($str) <= (int) $arg ? null : "$Label may not be longer than $arg characters.",
            'integer' => filter_var($str, FILTER_VALIDATE_INT) !== false ? null : "$Label must be a whole number.",
            'between' => (function () use ($str, $arg, $Label) {
                [$min, $max] = array_map('intval', explode(',', (string) $arg));
                $n = (int) $str;

                return $n >= $min && $n <= $max ? null : "$Label must be between $min and $max.";
            })(),
            'in' => in_array($str, explode(',', (string) $arg), true) ? null : "Choose a valid $label.",
            'confirmed' => $str === (string) ($data[$field . '_confirmation'] ?? '') ? null : "The {$label}s do not match.",
            'password' => preg_match('/[A-Za-z]/', $str) && preg_match('/\d/', $str)
                ? null : "$Label must contain at least one letter and one number.",
            'name' => preg_match("/^[\p{L}\p{M}' .-]+$/u", $str) ? null : "$Label may only contain letters, spaces, apostrophes and hyphens.",
            'datetime' => self::parseDate($str) ? null : "$Label must be a valid date and time.",
            'future' => ($d = self::parseDate($str)) && $d > new DateTimeImmutable() ? null : "$Label must be in the future.",
            'after' => ($d = self::parseDate($str)) && ($other = self::parseDate((string) ($data[$arg] ?? ''))) && $d > $other
                ? null : "$Label must be after the start time.",
            default => throw new \InvalidArgumentException("Unknown validation rule [$rule]."),
        };
    }

    public static function parseDate(string $value): ?DateTimeImmutable
    {
        $date = DateTimeImmutable::createFromFormat(self::DATETIME_FORMAT, $value);

        return $date && $date->format(self::DATETIME_FORMAT) === $value ? $date : null;
    }
}
