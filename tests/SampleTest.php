<?php

declare(strict_types=1);

use Couponetta\Home\Sample;
use Couponetta\Tests\Assert;

return [
    ['the sample carries three businesses and four deals', static function (): void {
        $s = new Sample();
        $m = $s->market();
        Assert::same('Taberna do Largo', $m->business('taberna')->name);
        Assert::same('Veleiro Nails', $m->business('veleiro')->name);
        Assert::same('Alvorada Spa', $m->business('alvorada')->name);
        Assert::same(6000, $m->deal('tab-dinner')->normalCents);
        Assert::same(3900, $m->deal('alv-massage')->dealCents);
    }],
    ['eight coupons sell across the sample', static function (): void {
        $s = new Sample();
        Assert::same(8, $s->couponsSold());
        Assert::same(3, $s->market()->sold('tab-dinner'));
        Assert::same(2, $s->market()->sold('vel-mani'));
        Assert::same(2, $s->market()->sold('alv-massage'));
        Assert::same(1, $s->market()->sold('tab-lunch'));
    }],
    ['the sample wall shows three deals', static function (): void {
        $s = new Sample();
        $rows = $s->market()->wall(Sample::TODAY);
        Assert::same(3, $s->deals());
        Assert::same('alv-massage', $rows[0]['deal']->handle);
        Assert::same(28, $rows[0]['left']);
        Assert::same('vel-mani', $rows[2]['deal']->handle);
    }],
    ['three coupons are used', static function (): void {
        $s = new Sample();
        Assert::same(3, $s->couponsUsed());
        Assert::same(1, $s->market()->used('tab-dinner'));
        Assert::same(1, $s->market()->used('vel-mani'));
        Assert::same(1, $s->market()->used('alv-massage'));
    }],
    ['the sample takes 21800 cents and the platform keeps 5230', static function (): void {
        $s = new Sample();
        Assert::same(21800, $s->takings());
        Assert::same(5230, $s->toPlatform());
        Assert::same(16570, $s->market()->toBusinesses());
    }],
    ['the saving reads as a whole percentage of the normal price', static function (): void {
        $m = (new Sample())->market();
        Assert::same(51, $m->deal('tab-dinner')->savingPercent());
        Assert::same(51, $m->deal('vel-mani')->savingPercent());
        Assert::same(51, $m->deal('alv-massage')->savingPercent());
        Assert::same(40, $m->deal('tab-lunch')->savingPercent());
    }],
];
