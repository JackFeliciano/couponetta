<?php

declare(strict_types=1);

use Couponetta\Home\Coupon;
use Couponetta\Tests\Assert;

return [
    ['a coupon carries a code of six capitals or digits', static function (): void {
        Assert::same('AB12CD', (new Coupon('AB12CD', 'tab-dinner', 'joana-m', '2026-11-02'))->code);
        Assert::throws(static fn () => new Coupon('ab12cd', 'tab-dinner', 'joana-m', '2026-11-02'), 'six capitals');
        Assert::throws(static fn () => new Coupon('ABC', 'tab-dinner', 'joana-m', '2026-11-02'), 'six capitals');
    }],
    ['a new coupon starts unused', static function (): void {
        $coupon = new Coupon('AB12CD', 'tab-dinner', 'joana-m', '2026-11-02');
        Assert::same(false, $coupon->spent());
        Assert::same(null, $coupon->usedOn());
    }],
    ['a coupon is used once', static function (): void {
        $coupon = new Coupon('AB12CD', 'tab-dinner', 'joana-m', '2026-11-02');
        $coupon->use(cp_deal(), '2026-11-20');
        Assert::same(true, $coupon->spent());
        Assert::same('2026-11-20', $coupon->usedOn());
        Assert::throws(static fn () => $coupon->use(cp_deal(), '2026-11-21'), 'already used');
    }],
    ['a coupon used after the use-by day lands nowhere', static function (): void {
        $coupon = new Coupon('AB12CD', 'tab-dinner', 'joana-m', '2026-11-02');
        Assert::throws(static fn () => $coupon->use(cp_deal(), '2027-04-01'), 'ran out on 2027-03-31');
    }],
    ['a coupon is used on or after the day it was bought', static function (): void {
        $coupon = new Coupon('AB12CD', 'tab-dinner', 'joana-m', '2026-11-02');
        Assert::throws(static fn () => $coupon->use(cp_deal(), '2026-11-01'), 'on or after the day it was bought');
    }],
];
