<?php

declare(strict_types=1);

use Couponetta\Home\Deal;
use Couponetta\Tests\Assert;

return [
    ['a deal price sits below the normal price', static function (): void {
        Assert::same(3100, cp_deal()->savingCents());
        Assert::same(51, cp_deal()->savingPercent());
        Assert::throws(static fn () => cp_deal(['deal' => 6000]), 'below the normal price');
        Assert::throws(static fn () => cp_deal(['deal' => 7000]), 'below the normal price');
    }],
    ['both prices are positive', static function (): void {
        Assert::throws(static fn () => cp_deal(['normal' => 0]), 'positive number of cents');
        Assert::throws(static fn () => cp_deal(['deal' => 0]), 'positive number of cents');
    }],
    ['the platform takes at most half the deal price', static function (): void {
        Assert::same(725, cp_deal()->commissionCents());
        Assert::same(2175, cp_deal()->payoutCents());
        Assert::throws(static fn () => cp_deal(['share' => 5001]), 'at most half');
        Assert::throws(static fn () => cp_deal(['share' => -1]), 'at most half');
    }],
    ['the platform takes nothing at a share of zero', static function (): void {
        Assert::same(0, cp_deal(['share' => 0])->commissionCents());
        Assert::same(2900, cp_deal(['share' => 0])->payoutCents());
    }],
    ['a deal puts at least one coupon on sale', static function (): void {
        Assert::throws(static fn () => cp_deal(['onSale' => 0]), 'at least one coupon');
    }],
    ['a coupon is usable after buying closes', static function (): void {
        Assert::throws(static fn () => cp_deal(['useBy' => '2026-11-30']), 'usable after buying closes');
        Assert::throws(static fn () => cp_deal(['useBy' => '2026-11-01']), 'usable after buying closes');
    }],
    ['buying stays open up to the buy-by day and closes after it', static function (): void {
        $deal = cp_deal();
        Assert::same(true, $deal->buyingOpenOn('2026-11-30'));
        Assert::same(false, $deal->buyingOpenOn('2026-12-01'));
    }],
];
