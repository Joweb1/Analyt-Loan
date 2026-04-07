<?php

namespace App\ValueObjects;

use InvalidArgumentException;
use JsonSerializable;

class Money implements JsonSerializable
{
    protected int $amount; // minor units (e.g. cents, kobo)

    protected string $currency;

    public function __construct(int $amount, string $currency = 'NGN')
    {
        $this->amount = $amount;
        $this->currency = $currency;
    }

    public static function fromMajor(float|string $amount, string $currency = 'NGN'): self
    {
        return new self((int) round((float) $amount * 100), $currency);
    }

    public function getMinorAmount(): int
    {
        return $this->amount;
    }

    public function getMajorAmount(): float
    {
        return $this->amount / 100;
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

    public function multiply(float $multiplier): self
    {
        return new self((int) round($this->amount * $multiplier), $this->currency);
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
}
