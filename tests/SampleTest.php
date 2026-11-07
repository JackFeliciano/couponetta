<?php

declare(strict_types=1);

use Couponetta\Home\Sample;
use Couponetta\Tests\Assert;

return [
    ['the sample holds three vouchers and four redemptions', static function (): void {
        $sample = new Sample();
        Assert::same(3, count($sample->vouchers()));
        Assert::same(4, $sample->redemptions());
    }],
    ['the part spent voucher holds 7750 cents', static function (): void {
        Assert::same(7750, (new Sample())->voucher('CPN-4A7')->balance());
    }],
    ['the spent voucher holds a balance of zero', static function (): void {
        $voucher = (new Sample())->voucher('CPN-9K2');
        Assert::same(0, $voucher->balance());
        Assert::same(false, $voucher->open());
    }],
    ['the closed voucher keeps its balance and takes nothing', static function (): void {
        $voucher = (new Sample())->voucher('CPN-1T6');
        Assert::same(6800, $voucher->balance());
        Assert::throws(static fn () => $voucher->redeem('kestrel', 100, '2026-10-01'), 'expired on 2026-09-30');
    }],
    ['the sample holds 14550 cents in balances', static function (): void {
        Assert::same(14550, (new Sample())->balance());
    }],
    ['an unknown voucher answers nothing', static function (): void {
        Assert::throws(static fn () => (new Sample())->voucher('CPN-000'), 'no voucher called');
    }],
];
