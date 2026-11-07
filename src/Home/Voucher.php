<?php

declare(strict_types=1);

namespace Couponetta\Home;

/**
 * One issued credit with a face value and a last day. The voucher keeps every
 * redemption it took, in order, and derives the balance from them.
 */
final class Voucher
{
    /** @var list<array{day: string, amount: int, left: int}> */
    private array $redemptions = [];

    public function __construct(
        public readonly string $code,
        public readonly string $holder,
        public readonly int $faceValue,
        public readonly string $issuedDay,
        public readonly string $lastDay,
    ) {
        if (trim($code) === '') {
            throw new \RuntimeException('a voucher carries a code');
        }
        if (trim($holder) === '') {
            throw new \RuntimeException('a voucher names one holder');
        }
        if ($faceValue < 1) {
            throw new \RuntimeException('a voucher carries a face value above zero cents');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $issuedDay)) {
            throw new \RuntimeException('a voucher carries its issued day as 2026-01-31');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $lastDay)) {
            throw new \RuntimeException('a voucher carries its last day as 2026-01-31');
        }
        if ($lastDay < $issuedDay) {
            throw new \RuntimeException('a voucher cannot end before it was issued');
        }
    }

    public function balance(): int
    {
        $spent = 0;
        foreach ($this->redemptions as $r) {
            $spent += $r['amount'];
        }

        return $this->faceValue - $spent;
    }

    public function redeem(string $holder, int $amount, string $day): int
    {
        if ($holder !== $this->holder) {
            throw new \RuntimeException('this voucher belongs to ' . $this->holder);
        }
        if ($day > $this->lastDay) {
            throw new \RuntimeException('the voucher expired on ' . $this->lastDay);
        }
        if ($day < $this->issuedDay) {
            throw new \RuntimeException('the voucher was issued on ' . $this->issuedDay);
        }
        if ($amount < 1) {
            throw new \RuntimeException('a redemption carries a whole number of cents above zero');
        }
        if ($amount > $this->balance()) {
            throw new \RuntimeException('a redemption of ' . $amount . ' cents is above the balance of ' . $this->balance() . ' cents');
        }
        $left = $this->balance() - $amount;
        $this->redemptions[] = ['day' => $day, 'amount' => $amount, 'left' => $left];

        return $left;
    }

    public function open(): bool
    {
        return $this->balance() > 0;
    }

    public function spent(): int
    {
        return $this->faceValue - $this->balance();
    }

    /** @return list<array{day: string, amount: int, left: int}> */
    public function redemptions(): array
    {
        return $this->redemptions;
    }

    /** @return array{code: string, holder: string, face: int, balance: int, open: bool, redemptions: list<array{day: string, amount: int, left: int}>} */
    public function answer(): array
    {
        return [
            'code' => $this->code,
            'holder' => $this->holder,
            'face' => $this->faceValue,
            'balance' => $this->balance(),
            'open' => $this->open(),
            'redemptions' => $this->redemptions,
        ];
    }
}
