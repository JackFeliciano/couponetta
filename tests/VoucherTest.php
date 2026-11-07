<?php

declare(strict_types=1);

use Couponetta\Home\Voucher;
use Couponetta\Tests\Assert;

function cpn_voucher(): Voucher
{
    return new Voucher('CPN-4A7', 'ana', 12000, '2026-09-01', '2026-12-31');
}

return [
    ['a voucher carries a code, one holder, a face value and two days', static function (): void {
        Assert::throws(static fn () => new Voucher(' ', 'ana', 12000, '2026-09-01', '2026-12-31'), 'carries a code');
        Assert::throws(static fn () => new Voucher('C', ' ', 12000, '2026-09-01', '2026-12-31'), 'one holder');
        Assert::throws(static fn () => new Voucher('C', 'ana', 0, '2026-09-01', '2026-12-31'), 'face value above zero');
        Assert::throws(static fn () => new Voucher('C', 'ana', 12000, '1 September', '2026-12-31'), 'issued day');
        Assert::throws(static fn () => new Voucher('C', 'ana', 12000, '2026-09-01', '31 December'), 'last day');
    }],
    ['a voucher cannot end before it was issued', static function (): void {
        Assert::throws(static fn () => new Voucher('C', 'ana', 12000, '2026-09-01', '2026-08-01'), 'cannot end before');
    }],
    ['a new voucher holds its whole face value', static function (): void {
        $voucher = cpn_voucher();
        Assert::same(12000, $voucher->balance());
        Assert::same(0, $voucher->spent());
        Assert::same(true, $voucher->open());
        Assert::same([], $voucher->redemptions());
    }],
    ['a redemption takes the amount off the balance', static function (): void {
        $voucher = cpn_voucher();
        Assert::same(9500, $voucher->redeem('ana', 2500, '2026-09-14'));
        Assert::same(9500, $voucher->balance());
        Assert::same(2500, $voucher->spent());
    }],
    ['a redemption above the balance lands nowhere', static function (): void {
        $voucher = cpn_voucher();
        $voucher->redeem('ana', 10000, '2026-09-14');
        Assert::throws(static fn () => $voucher->redeem('ana', 2001, '2026-09-15'), 'above the balance of 2000 cents');
        Assert::same(2000, $voucher->balance());
    }],
    ['a redemption of zero lands nowhere', static function (): void {
        Assert::throws(static fn () => cpn_voucher()->redeem('ana', 0, '2026-09-14'), 'above zero');
    }],
    ['a redemption from another holder lands nowhere', static function (): void {
        Assert::throws(static fn () => cpn_voucher()->redeem('bright', 500, '2026-09-14'), 'belongs to ana');
    }],
    ['a voucher takes nothing after its last day', static function (): void {
        $voucher = cpn_voucher();
        Assert::throws(static fn () => $voucher->redeem('ana', 500, '2027-01-01'), 'expired on 2026-12-31');
        Assert::same(12000, $voucher->balance());
    }],
    ['a voucher takes nothing before it was issued', static function (): void {
        Assert::throws(static fn () => cpn_voucher()->redeem('ana', 500, '2026-08-31'), 'issued on 2026-09-01');
    }],
    ['a voucher takes a redemption on its last day', static function (): void {
        $voucher = cpn_voucher();
        Assert::same(11500, $voucher->redeem('ana', 500, '2026-12-31'));
    }],
    ['a voucher spent to nothing answers closed', static function (): void {
        $voucher = cpn_voucher();
        $voucher->redeem('ana', 12000, '2026-09-20');
        Assert::same(0, $voucher->balance());
        Assert::same(false, $voucher->open());
        Assert::same(12000, $voucher->spent());
    }],
    ['the answer holds every redemption in order', static function (): void {
        $voucher = cpn_voucher();
        $voucher->redeem('ana', 2500, '2026-09-14');
        $voucher->redeem('ana', 1750, '2026-09-28');
        $answer = $voucher->answer();
        Assert::same(2, count($answer['redemptions']));
        Assert::same('2026-09-14', $answer['redemptions'][0]['day']);
        Assert::same(9500, $answer['redemptions'][0]['left']);
        Assert::same(7750, $answer['redemptions'][1]['left']);
        Assert::same(7750, $answer['balance']);
        Assert::same(true, $answer['open']);
    }],
];
