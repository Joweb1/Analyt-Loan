<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;
use Livewire\Wireable;

/**
 * Money Value Object.
 * All internal math is performed on integers (minor units).
 */
class Money implements JsonSerializable, Wireable
{
    protected int $amount; // minor units (e.g. cents, kobo)

    protected string $currency;

    protected bool $isMissing = false;

    public function __construct(int|string $amount, string $currency = 'NGN', bool $isMissing = false)
    {
        $this->amount = (int) $amount;
        $this->currency = $currency;
        $this->isMissing = $isMissing;
    }

    /**
     * Create a 'Missing' Money object that signifies data fetch error.
     */
    public static function missing(): self
    {
        return new self(0, 'NGN', true);
    }

    public function isMissing(): bool
    {
        return $this->isMissing;
    }

    /**
     * Create a Money object from a major unit (e.g., 100.50).
     * ONLY USE THIS DURING INPUT/IMPORT.
     */
    public static function fromMajor(float|string $amount, string $currency = 'NGN'): self
    {
        // Use string conversion to avoid float precision issues
        $minor = (int) bcmul((string) $amount, '100', 0);

        return new self($minor, $currency);
    }

    public function getMinorAmount(): int
    {
        return $this->amount;
    }

    public function getMajorAmount(): float
    {
        return (float) bcdiv((string) $this->amount, '100', 2);
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function add(self $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency, $this->isMissing || $other->isMissing);
    }

    public function subtract(self $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency, $this->isMissing || $other->isMissing);
    }

    public function multiply(float|string $multiplier): self
    {
        // Ensure the multiplier is a well-formed decimal string
        $multiplierStr = is_numeric($multiplier) ? number_format((float) $multiplier, 10, '.', '') : (string) $multiplier;

        // Keep 0 scale for minor units as they are integers
        $result = bcmul((string) $this->amount, $multiplierStr, 0);

        return new self((int) $result, $this->currency, $this->isMissing);
    }

    /**
     * Divide the money and round to the nearest minor unit.
     */
    public function divide(float|string $divisor, int $roundingMode = PHP_ROUND_HALF_UP): self
    {
        $result = bcdiv((string) $this->amount, (string) $divisor, 0);

        return new self((int) $result, $this->currency, $this->isMissing);
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function absolute(): self
    {
        return new self(abs($this->amount), $this->currency, $this->isMissing);
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    protected function ensureSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Currencies do not match: {$this->currency} vs {$other->currency}");
        }
    }

    public function format(): string
    {
        if ($this->isMissing) {
            return 'Error fetching data';
        }

        // Round UP to nearest whole number for display (e.g. 999.99 -> 1000)
        return number_format(ceil($this->getMajorAmount()), 0);
    }

    public function formatWithDecimals(int $decimals = 2): string
    {
        if ($this->isMissing) {
            return 'Error fetching data';
        }

        return number_format($this->getMajorAmount(), $decimals);
    }

    public function jsonSerialize(): mixed
    {
        return [
            'amount' => $this->amount,
            'major_amount' => $this->getMajorAmount(),
            'currency' => $this->currency,
            'formatted' => $this->format(),
        ];
    }

    public function __toString(): string
    {
        return $this->format();
    }

    public function toLivewire(): array
    {
        return [
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
    }

    public static function fromLivewire($value): self
    {
        return new self($value['amount'], $value['currency']);
    }
}
