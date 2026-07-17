<?php
declare(strict_types=1);

namespace App\Core\Security;

use App\Core\Contracts\CanonicalPayloadInterface;

final class CanonicalPayloadSerializer implements CanonicalPayloadInterface
{
    public function serialize(array $payload): string
    {
        $normalized = $this->normalize($payload);
        $encoded = json_encode(
            $normalized,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS
        );

        return $encoded === false ? '{}' : $encoded;
    }

    private function normalize(mixed $value): mixed
    {
        if (is_array($value)) {
            $isList = array_is_list($value);
            $normalizedItems = [];
            foreach ($value as $item) {
                $normalizedItems[] = $this->normalize($item);
            }

            if (!$isList) {
                ksort($value, SORT_STRING);
                $normalizedAssoc = [];
                foreach ($value as $key => $item) {
                    $normalizedAssoc[$key] = $this->normalize($item);
                }

                return $normalizedAssoc;
            }

            return $normalizedItems;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }

        if ($value === null) {
            return null;
        }

        return (string) $value;
    }
}
