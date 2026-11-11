<?php

declare(strict_types=1);

namespace Couponetta\Home;

/**
 * Three businesses, four deals and eight coupons. The sample fixes its own today, so the
 * wall and the ledger read the same on any day a reader opens it.
 */
final class Sample
{
    public const TODAY = '2026-11-09';

    private Market $market;

    public function __construct()
    {
        $this->market = new Market();
        $this->market->addBusiness(new Business('taberna', 'Taberna do Largo', 'Porto', 'restaurants'));
        $this->market->addBusiness(new Business('veleiro', 'Veleiro Nails', 'Lisbon', 'beauty'));
        $this->market->addBusiness(new Business('alvorada', 'Alvorada Spa', 'Coimbra', 'wellness'));

        $this->market->post(new Deal('tab-dinner', 'taberna', 'Dinner for two with wine', 6000, 2900, 40, 2500, '2026-11-30', '2027-03-31'));
        $this->market->post(new Deal('vel-mani', 'veleiro', 'Manicure and pedicure', 3500, 1700, 25, 3000, '2026-11-15', '2027-01-31'));
        $this->market->post(new Deal('alv-massage', 'alvorada', 'Ninety minute massage', 8000, 3900, 30, 2000, '2026-12-20', '2027-04-30'));
        $this->market->post(new Deal('tab-lunch', 'taberna', 'Lunch for two', 3200, 1900, 20, 2500, '2026-10-31', '2026-12-31'));

        $this->market->buy('tab-dinner', 'joana-m', '2026-11-02');
        $this->market->buy('tab-dinner', 'rui-s', '2026-11-03');
        $this->market->buy('tab-dinner', 'ines-p', '2026-11-04');
        $this->market->buy('vel-mani', 'clara-t', '2026-11-05');
        $this->market->buy('vel-mani', 'nuno-a', '2026-11-05');
        $this->market->buy('alv-massage', 'sofia-l', '2026-11-06');
        $this->market->buy('alv-massage', 'marta-r', '2026-11-07');
        $this->market->buy('tab-lunch', 'hugo-b', '2026-10-20');

        $codes = $this->codes();
        $this->market->use($codes[0], '2026-11-05');
        $this->market->use($codes[3], '2026-11-08');
        $this->market->use($codes[5], '2026-11-09');
    }

    /** @return list<string> */
    private function codes(): array
    {
        return array_map(static fn (Coupon $coupon): string => $coupon->code, $this->market->coupons());
    }

    public function market(): Market
    {
        return $this->market;
    }

    public function deals(): int
    {
        return count($this->market->wall(self::TODAY));
    }

    public function couponsSold(): int
    {
        return $this->market->couponsSold();
    }

    public function couponsUsed(): int
    {
        $used = 0;
        foreach ($this->market->wall(self::TODAY) as $row) {
            $used += $row['used'];
        }

        return $used;
    }

    public function takings(): int
    {
        return $this->market->takings();
    }

    public function toPlatform(): int
    {
        return $this->market->toPlatform();
    }
}
