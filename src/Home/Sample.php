<?php

declare(strict_types=1);

namespace Couponetta\Home;

/**
 * Three vouchers on one server. One is part spent, one is spent to the last cent,
 * and one has been refused for the last day.
 */
final class Sample
{
    /** @var list<Voucher> */
    private array $vouchers = [];

    public function __construct()
    {
        $part = new Voucher('CPN-4A7', 'ana', 12000, '2026-09-01', '2026-12-31');
        $part->redeem('ana', 2500, '2026-09-14');
        $part->redeem('ana', 1750, '2026-09-28');
        $this->vouchers[] = $part;

        $spent = new Voucher('CPN-9K2', 'bright', 5000, '2026-08-15', '2026-11-15');
        $spent->redeem('bright', 5000, '2026-09-02');
        $this->vouchers[] = $spent;

        $closed = new Voucher('CPN-1T6', 'kestrel', 8000, '2026-07-01', '2026-09-30');
        $closed->redeem('kestrel', 1200, '2026-09-30');
        $this->vouchers[] = $closed;
    }

    /** @return list<Voucher> */
    public function vouchers(): array
    {
        return $this->vouchers;
    }

    public function voucher(string $code): Voucher
    {
        foreach ($this->vouchers as $voucher) {
            if ($voucher->code === $code) {
                return $voucher;
            }
        }

        throw new \RuntimeException('this server holds no voucher called ' . $code);
    }

    public function redemptions(): int
    {
        $sum = 0;
        foreach ($this->vouchers as $voucher) {
            $sum += count($voucher->redemptions());
        }

        return $sum;
    }

    public function balance(): int
    {
        $sum = 0;
        foreach ($this->vouchers as $voucher) {
            $sum += $voucher->balance();
        }

        return $sum;
    }
}
