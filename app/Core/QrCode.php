<?php
declare(strict_types=1);

namespace App\Core;

final class QrCode
{
    private const VERSION = 5;
    private const SIZE = 37;
    private const DATA_CODEWORDS = 108;
    private const ECC_CODEWORDS = 26;

    private array $modules = [];
    private array $functionModules = [];

    private function __construct()
    {
        for ($y = 0; $y < self::SIZE; $y++) {
            $this->modules[$y] = array_fill(0, self::SIZE, false);
            $this->functionModules[$y] = array_fill(0, self::SIZE, false);
        }
    }

    public static function svg(string $text, int $scale = 6, int $border = 4): string
    {
        $qr = new self();
        $qr->build($text);

        $dimension = (self::SIZE + ($border * 2)) * $scale;
        $path = '';

        for ($y = 0; $y < self::SIZE; $y++) {
            for ($x = 0; $x < self::SIZE; $x++) {
                if ($qr->modules[$y][$x]) {
                    $px = ($x + $border) * $scale;
                    $py = ($y + $border) * $scale;
                    $path .= "M{$px},{$py}h{$scale}v{$scale}h-{$scale}z";
                }
            }
        }

        return '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" width="' . $dimension . '" height="' . $dimension . '" viewBox="0 0 ' . $dimension . ' ' . $dimension . '" role="img">'
            . '<rect width="100%" height="100%" fill="#fff"/>'
            . '<path d="' . $path . '" fill="#0f172a"/>'
            . '</svg>';
    }

    private function build(string $text): void
    {
        $data = $this->encodeData($text);
        $ecc = $this->reedSolomonRemainder($data, self::ECC_CODEWORDS);
        $codewords = array_merge($data, $ecc);

        $this->drawFunctionPatterns();
        $this->drawFormatBits(0);
        $this->drawCodewords($codewords);
        $this->applyMask(0);
        $this->drawFormatBits(0);
    }

    private function encodeData(string $text): array
    {
        if (strlen($text) > 106) {
            throw new \RuntimeException('El contenido del QR excede la capacidad local.');
        }

        $bits = [];
        $this->appendBits($bits, 0b0100, 4);
        $this->appendBits($bits, strlen($text), 8);

        for ($i = 0, $length = strlen($text); $i < $length; $i++) {
            $this->appendBits($bits, ord($text[$i]), 8);
        }

        $capacity = self::DATA_CODEWORDS * 8;
        $this->appendBits($bits, 0, min(4, $capacity - count($bits)));

        while ((count($bits) % 8) !== 0) {
            $bits[] = 0;
        }

        $data = [];
        for ($i = 0; $i < count($bits); $i += 8) {
            $byte = 0;
            for ($j = 0; $j < 8; $j++) {
                $byte = ($byte << 1) | $bits[$i + $j];
            }
            $data[] = $byte;
        }

        for ($pad = 0xEC; count($data) < self::DATA_CODEWORDS; $pad = $pad === 0xEC ? 0x11 : 0xEC) {
            $data[] = $pad;
        }

        return $data;
    }

    private function appendBits(array &$bits, int $value, int $length): void
    {
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    private function drawFunctionPatterns(): void
    {
        $this->drawFinderPattern(0, 0);
        $this->drawFinderPattern(self::SIZE - 7, 0);
        $this->drawFinderPattern(0, self::SIZE - 7);
        $this->drawAlignmentPattern(30, 30);

        for ($i = 8; $i < self::SIZE - 8; $i++) {
            $dark = ($i % 2) === 0;
            $this->setFunctionModule(6, $i, $dark);
            $this->setFunctionModule($i, 6, $dark);
        }

        $this->setFunctionModule(8, self::SIZE - 8, true);
    }

    private function drawFinderPattern(int $x, int $y): void
    {
        for ($dy = -1; $dy <= 7; $dy++) {
            for ($dx = -1; $dx <= 7; $dx++) {
                $xx = $x + $dx;
                $yy = $y + $dy;
                if ($xx < 0 || $xx >= self::SIZE || $yy < 0 || $yy >= self::SIZE) {
                    continue;
                }

                $dark = $dx >= 0 && $dx <= 6 && $dy >= 0 && $dy <= 6
                    && ($dx === 0 || $dx === 6 || $dy === 0 || $dy === 6 || ($dx >= 2 && $dx <= 4 && $dy >= 2 && $dy <= 4));

                $this->setFunctionModule($xx, $yy, $dark);
            }
        }
    }

    private function drawAlignmentPattern(int $centerX, int $centerY): void
    {
        for ($dy = -2; $dy <= 2; $dy++) {
            for ($dx = -2; $dx <= 2; $dx++) {
                $dark = max(abs($dx), abs($dy)) === 2 || ($dx === 0 && $dy === 0);
                $this->setFunctionModule($centerX + $dx, $centerY + $dy, $dark);
            }
        }
    }

    private function drawCodewords(array $codewords): void
    {
        $bitIndex = 0;
        $totalBits = count($codewords) * 8;

        for ($right = self::SIZE - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right = 5;
            }

            for ($vertical = 0; $vertical < self::SIZE; $vertical++) {
                $upward = (($right + 1) & 2) === 0;
                $y = $upward ? self::SIZE - 1 - $vertical : $vertical;

                for ($j = 0; $j < 2; $j++) {
                    $x = $right - $j;
                    if ($this->functionModules[$y][$x]) {
                        continue;
                    }

                    $dark = false;
                    if ($bitIndex < $totalBits) {
                        $dark = (($codewords[intdiv($bitIndex, 8)] >> (7 - ($bitIndex % 8))) & 1) !== 0;
                    }

                    $this->modules[$y][$x] = $dark;
                    $bitIndex++;
                }
            }
        }
    }

    private function applyMask(int $mask): void
    {
        for ($y = 0; $y < self::SIZE; $y++) {
            for ($x = 0; $x < self::SIZE; $x++) {
                if (!$this->functionModules[$y][$x] && $this->maskBit($mask, $x, $y)) {
                    $this->modules[$y][$x] = !$this->modules[$y][$x];
                }
            }
        }
    }

    private function maskBit(int $mask, int $x, int $y): bool
    {
        return match ($mask) {
            0 => (($x + $y) % 2) === 0,
            default => false,
        };
    }

    private function drawFormatBits(int $mask): void
    {
        $data = (0b01 << 3) | $mask;
        $remainder = $data;

        for ($i = 0; $i < 10; $i++) {
            $remainder = ($remainder << 1) ^ ((($remainder >> 9) & 1) !== 0 ? 0x537 : 0);
        }

        $bits = (($data << 10) | $remainder) ^ 0x5412;

        for ($i = 0; $i <= 5; $i++) {
            $this->setFunctionModule(8, $i, $this->getBit($bits, $i));
        }

        $this->setFunctionModule(8, 7, $this->getBit($bits, 6));
        $this->setFunctionModule(8, 8, $this->getBit($bits, 7));
        $this->setFunctionModule(7, 8, $this->getBit($bits, 8));

        for ($i = 9; $i < 15; $i++) {
            $this->setFunctionModule(14 - $i, 8, $this->getBit($bits, $i));
        }

        for ($i = 0; $i < 8; $i++) {
            $this->setFunctionModule(self::SIZE - 1 - $i, 8, $this->getBit($bits, $i));
        }

        for ($i = 8; $i < 15; $i++) {
            $this->setFunctionModule(8, self::SIZE - 15 + $i, $this->getBit($bits, $i));
        }

        $this->setFunctionModule(8, self::SIZE - 8, true);
    }

    private function getBit(int $value, int $index): bool
    {
        return (($value >> $index) & 1) !== 0;
    }

    private function setFunctionModule(int $x, int $y, bool $dark): void
    {
        $this->modules[$y][$x] = $dark;
        $this->functionModules[$y][$x] = true;
    }

    private function reedSolomonRemainder(array $data, int $degree): array
    {
        $generator = $this->reedSolomonGenerator($degree);
        $remainder = array_fill(0, $degree, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ $remainder[0];
            array_shift($remainder);
            $remainder[] = 0;

            for ($i = 0; $i < $degree; $i++) {
                $remainder[$i] ^= $this->gfMultiply($generator[$i + 1], $factor);
            }
        }

        return $remainder;
    }

    private function reedSolomonGenerator(int $degree): array
    {
        $poly = [1];
        for ($i = 0; $i < $degree; $i++) {
            $root = $this->gfPow2($i);
            $next = array_fill(0, count($poly) + 1, 0);
            foreach ($poly as $j => $coefficient) {
                $next[$j] ^= $coefficient;
                $next[$j + 1] ^= $this->gfMultiply($coefficient, $root);
            }
            $poly = $next;
        }

        return $poly;
    }

    private function gfPow2(int $power): int
    {
        $value = 1;
        for ($i = 0; $i < $power; $i++) {
            $value <<= 1;
            if (($value & 0x100) !== 0) {
                $value ^= 0x11D;
            }
        }

        return $value;
    }

    private function gfMultiply(int $x, int $y): int
    {
        $result = 0;
        while ($y > 0) {
            if (($y & 1) !== 0) {
                $result ^= $x;
            }

            $x <<= 1;
            if (($x & 0x100) !== 0) {
                $x ^= 0x11D;
            }
            $y >>= 1;
        }

        return $result & 0xFF;
    }
}
