<?php

namespace App\Domain\Ledger\Service;

class CanonicalSerializer
{
    public function serialize(array $payload): string
    {
        // Sort keys alphabetically for deterministic order
        ksort($payload);

        // Convert numeric values to fixed decimal strings (no floating point)
        $payload = $this->convertValuesToFixedDecimal($payload);

        // Convert dates to ISO-8601 format
        $payload = $this->convertDatesToISO8601($payload);

        // Return deterministic JSON representation (no spaces, keys sorted)
        return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function convertValuesToFixedDecimal(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_numeric($value)) {
                // Convert to string with fixed decimal representation
                if ((int)$value == $value) {
                    $result[$key] = (string)(int)$value;
                } else {
                    $result[$key] = (string)$value;
                }
            } elseif (is_array($value)) {
                $result[$key] = $this->convertValuesToFixedDecimal($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function convertDatesToISO8601(array $array): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_string($value) && $this->isDateString($value)) {
                $result[$key] = $this->toISO8601($value);
            } elseif (is_array($value)) {
                $result[$key] = $this->convertDatesToISO8601($value);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function isDateString(string $value): bool
    {
        // Simple detection of date-like strings
        $patterns = [
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/',
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            '/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\d{2}$/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    private function toISO8601(string $date): string
    {
        // Handle dd/MM/yyyy HH:mm:ss
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2}):(\d{2})$/', $date, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            $hour = $matches[4];
            $minute = $matches[5];
            $second = $matches[6];
            return "{$year}-{$month}-{$day}T{$hour}:{$minute}:{$second}Z";
        }

        // For now, return as-is if already in ISO-8601 format
        return $date;
    }
}