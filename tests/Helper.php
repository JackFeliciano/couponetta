<?php

declare(strict_types=1);

use Couponetta\Home\Deal;

function cp_deal(array $over = []): Deal
{
    $f = array_merge([
        'handle' => 'tab-dinner',
        'business' => 'taberna',
        'title' => 'Dinner for two with wine',
        'normal' => 6000,
        'deal' => 2900,
        'onSale' => 40,
        'share' => 2500,
        'buyBy' => '2026-11-30',
        'useBy' => '2027-03-31',
    ], $over);

    return new Deal($f['handle'], $f['business'], $f['title'], $f['normal'], $f['deal'],
        $f['onSale'], $f['share'], $f['buyBy'], $f['useBy']);
}
