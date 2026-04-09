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

    public function __construct(int|string $amount, string $currency = 'NGN')
    {
        $this->amount = (int) $amount;
        $this->currency = $currency;
    }

    /**
     * Create a Money object from a major unit (e.g., 100.50).
     * ONLY USE THIS DURING INPUT/IMPORT.
     */
    public static function fromMajor(float|string $amount, string $currency = 'NGN'): self
    {
        // We use string conversion to avoid float precision issues during the multiplier phase
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

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->ensureSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float|string $multiplier): self
    {
        // When multiplying (e.g., for interest), we use bcmath for precision
        $result = bcmul((string) $this->amount, (string) $multiplier, 0);

        return new self((int) $result, $this->currency);
    }

    /**
     * Divide the money and round to the nearest minor unit.
     */
    public function divide(float|string $divisor, int $roundingMode = PHP_ROUND_HALF_UP): self
    {
        $result = bcdiv((string) $this->amount, (string) $divisor, 0);

        return new self((int) $result, $this->currency);
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

    protected function ensureSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Currencies do not match: {$this->currency} vs {$other->currency}");
        }
    }

    public function format(): string
    {
        return number_format($this->getMajorAmount(), 2);
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
