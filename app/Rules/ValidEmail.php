<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Basic email validation
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $fail('The :attribute must be a valid email address.');
            return;
        }

        // Ensure only one @ exists
        if (substr_count($value, '@') !== 1) {
            $fail('The :attribute must contain only one @ symbol.');
            return;
        }

        [$local, $domain] = explode('@', $value);

        // Check local part exists
        if (empty($local)) {
            $fail('The :attribute must have characters before @.');
            return;
        }

        // Domain must exist
        if (empty($domain)) {
            $fail('The :attribute must have a domain after @.');
            return;
        }

        // Domain must contain a dot
        if (!str_contains($domain, '.')) {
            $fail('The :attribute domain must contain a dot.');
            return;
        }

        // Domain must not end with dot
        if (str_ends_with($domain, '.')) {
            $fail('The :attribute domain is invalid.');
            return;
        }
    }
}
