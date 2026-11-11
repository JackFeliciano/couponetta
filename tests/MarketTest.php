<?php

declare(strict_types=1);

use Couponetta\Home\Business;
use Couponetta\Home\Deal;
use Couponetta\Home\Market;
use Couponetta\Home\Sample;
use Couponetta\Tests\Assert;

function cp_market(): Market
{
    $m = new Market();
    $m->addBusiness(new Business('taberna', 'Taberna do Largo', 'Porto', 'restaurants'));
    $m->post(cp_deal());

    return $m;
}

return [
    ['a market holds each business once', static function (): void {
        $m = cp_market();
        Assert::same('Taberna do Largo', $m->business('taberna')->name);
        Assert::throws(static fn () => $m->addBusiness(new Business('taberna', 'Other', 'Braga', 'cafes')), 'holds taberna');
        Assert::throws(static fn () => $m->business('nobody'), 'no business called nobody');
    }],
    ['a deal belongs to a business the market holds', static function (): void {
        $m = new Market();
        Assert::throws(static fn () => $m->post(cp_deal()), 'no business called taberna');
    }],
    ['a market holds each deal once', static function (): void {
        $m = cp_market();
        Assert::throws(static fn () => $m->post(cp_deal()), 'already holds tab-dinner');
        Assert::same(6000, $m->deal('tab-dinner')->normalCents);
    }],
    ['a buyer pays for one coupon and takes a code', static function (): void {
        $m = cp_market();
        $coupon = $m->buy('tab-dinner', 'joana-m', '2026-11-02');
        Assert::same('tab-dinner', $coupon->deal);
        Assert::same('joana-m', $coupon->buyer);
        Assert::same('2026-11-02', $coupon->paidDay);
        Assert::same(1, $m->sold('tab-dinner'));
        Assert::same(39, $m->left('tab-dinner'));
    }],
    ['no two coupons share a code', static function (): void {
        $m = cp_market();
        $codes = [];
        for ($i = 0; $i < 12; $i++) {
            $codes[] = $m->buy('tab-dinner', 'buyer-' . $i, '2026-11-02')->code;
        }
        Assert::same(12, count(array_unique($codes)));
        Assert::same(1, preg_match('/^[A-Z0-9]{6}$/', $codes[0]));
    }],
    ['buying closes on the buy-by day', static function (): void {
        $m = cp_market();
        Assert::throws(static fn () => $m->buy('tab-dinner', 'late-buyer', '2026-12-01'), 'buying closed on 2026-11-30');
    }],
    ['selling the last coupon closes the deal', static function (): void {
        $m = new Market();
        $m->addBusiness(new Business('taberna', 'Taberna do Largo', 'Porto', 'restaurants'));
        $m->post(cp_deal(['onSale' => 2]));
        $m->buy('tab-dinner', 'first', '2026-11-02');
        $m->buy('tab-dinner', 'second', '2026-11-03');
        Assert::same(0, $m->left('tab-dinner'));
        Assert::throws(static fn () => $m->buy('tab-dinner', 'third', '2026-11-04'), 'sold out');
        Assert::same([], $m->wall('2026-11-05'));
    }],
    ['a business checks a code off', static function (): void {
        $m = cp_market();
        $code = $m->buy('tab-dinner', 'joana-m', '2026-11-02')->code;
        Assert::same(0, $m->used('tab-dinner'));
        Assert::same('2026-11-20', $m->use($code, '2026-11-20')->usedOn());
        Assert::same(1, $m->used('tab-dinner'));
        Assert::throws(static fn () => $m->use($code, '2026-11-21'), 'already used');
        Assert::throws(static fn () => $m->use('ZZZZZZ', '2026-11-21'), 'no coupon carries ZZZZZZ');
    }],
    ['the wall carries the open deals, best saving first', static function (): void {
        $m = new Market();
        $m->addBusiness(new Business('taberna', 'Taberna do Largo', 'Porto', 'restaurants'));
        $m->addBusiness(new Business('alvorada', 'Alvorada Spa', 'Coimbra', 'wellness'));
        $m->post(cp_deal());
        $m->post(new Deal('alv-massage', 'alvorada', 'Ninety minute massage', 8000, 3900, 30, 2000, '2026-12-20', '2027-04-30'));
        $rows = $m->wall('2026-11-09');
        Assert::same(2, count($rows));
        Assert::same('alv-massage', $rows[0]['deal']->handle);
        Assert::same(30, $rows[0]['left']);
        Assert::same('Taberna do Largo', $rows[1]['business']->name);
    }],
    ['the wall leaves out a deal whose buying closed', static function (): void {
        $m = new Market();
        $m->addBusiness(new Business('taberna', 'Taberna do Largo', 'Porto', 'restaurants'));
        $m->post(new Deal('tab-lunch', 'taberna', 'Lunch for two', 3200, 1900, 20, 2500, '2026-10-31', '2026-12-31'));
        Assert::same([], $m->wall('2026-11-09'));
        Assert::same(1, count($m->wall('2026-10-31')));
    }],
    ['the ledger splits every coupon between the business and the platform', static function (): void {
        $m = cp_market();
        $m->buy('tab-dinner', 'a', '2026-11-02');
        $m->buy('tab-dinner', 'b', '2026-11-03');
        $row = $m->ledger('tab-dinner');
        Assert::same(2, $row['sold']);
        Assert::same(5800, $row['takings']);
        Assert::same(4350, $row['to_business']);
        Assert::same(1450, $row['to_platform']);
        Assert::same($row['takings'], $row['to_business'] + $row['to_platform']);
    }],
    ['the takings cover every deal the market holds', static function (): void {
        $sample = new Sample();
        $m = $sample->market();
        $sum = 0;
        foreach ($m->coupons() as $coupon) {
            $sum += $m->deal($coupon->deal)->dealCents;
        }
        Assert::same($sum, $m->takings());
        Assert::same($m->takings(), $m->toBusinesses() + $m->toPlatform());
    }],
];
