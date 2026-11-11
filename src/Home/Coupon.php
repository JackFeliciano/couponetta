<?php

declare(strict_types=1);

namespace Couponetta\Home;

/**
 * One sale. The buyer pays up front, the code is what the business checks off, and the
 * used day starts empty.
 */
final class Coupon
{
    private ?string $usedDay = null;

    public function __construct(
        public readonly string $code,
        public readonly string $deal,
        public readonly string $buyer,
        public readonly string $paidDay,
    ) {
        if (!preg_match('/^[A-Z0-9]{6}$/', $code)) {
            throw new \RuntimeException('a coupon carries a code of six capitals or digits');
        }
        if (trim($buyer) === '') {
            throw new \RuntimeException('a coupon carries a buyer');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $paidDay)) {
            throw new \RuntimeException('a coupon carries its paid day as 2026-01-31');
        }
    }

    public function usedOn(): ?string
    {
        return $this->usedDay;
    }

    public function spent(): bool
    {
        return $this->usedDay !== null;
    }

    /** The business checks the code off. A second use lands nowhere. */
    public function use(Deal $deal, string $day): void
    {
        if ($this->spent()) {
            throw new \RuntimeException('coupon ' . $this->code . ' is already used');
        }
        if ($day < $this->paidDay) {
            throw new \RuntimeException('a coupon is used on or after the day it was bought');
        }
        if ($day > $deal->useByDay) {
            throw new \RuntimeException('coupon ' . $this->code . ' ran out on ' . $deal->useByDay);
        }
        $this->usedDay = $day;
    }
}
