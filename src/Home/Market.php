<?php

declare(strict_types=1);

namespace Couponetta\Home;

/**
 * The deal site. It holds the businesses, the deals and every coupon sold, and it answers
 * what the wall shows and what the takings come to.
 */
final class Market
{
    /** @var array<string, Business> */
    private array $businesses = [];

    /** @var array<string, Deal> */
    private array $deals = [];

    /** @var array<string, Coupon> */
    private array $coupons = [];

    /** @var array<string, string> */
    private array $codes = [];

    public function __construct(private readonly string $codeSeed = 'K7M2Q9')
    {
    }

    public function addBusiness(Business $business): void
    {
        if (isset($this->businesses[$business->handle])) {
            throw new \RuntimeException('the market holds ' . $business->handle);
        }
        $this->businesses[$business->handle] = $business;
    }

    public function business(string $handle): Business
    {
        return $this->businesses[$handle]
            ?? throw new \RuntimeException('the market holds no business called ' . $handle);
    }

    public function post(Deal $deal): void
    {
        $this->business($deal->business);
        if (isset($this->deals[$deal->handle])) {
            throw new \RuntimeException('the market already holds ' . $deal->handle);
        }
        $this->deals[$deal->handle] = $deal;
    }

    public function deal(string $handle): Deal
    {
        return $this->deals[$handle] ?? throw new \RuntimeException('the market holds no deal called ' . $handle);
    }

    /** A coupon takes six capitals or digits, and no two coupons share one. */
    private function nextCode(int $sold): string
    {
        $code = strtoupper(substr(hash('sha256', $this->codeSeed . ':' . $sold), 0, 6));
        $code = preg_replace('/[^A-Z0-9]/', 'Q', $code);

        return $code;
    }

    /** A buyer pays for one coupon. Selling the last one closes the deal. */
    public function buy(string $dealHandle, string $buyer, string $day): Coupon
    {
        $deal = $this->deal($dealHandle);
        if (!$deal->buyingOpenOn($day)) {
            throw new \RuntimeException('buying closed on ' . $deal->buyByDay);
        }
        if ($this->sold($dealHandle) >= $deal->onSale) {
            throw new \RuntimeException('deal ' . $dealHandle . ' sold out');
        }
        $sold = count($this->coupons);
        $code = $this->nextCode($sold);
        while (isset($this->codes[$code])) {
            $code = $this->nextCode(++$sold);
        }
        $coupon = new Coupon($code, $dealHandle, $buyer, $day);
        $this->coupons[$code] = $coupon;
        $this->codes[$code] = $dealHandle;

        return $coupon;
    }

    public function coupon(string $code): Coupon
    {
        return $this->coupons[$code] ?? throw new \RuntimeException('no coupon carries ' . $code);
    }

    /** The business checks a code off on the day the buyer turns up. */
    public function use(string $code, string $day): Coupon
    {
        $coupon = $this->coupon($code);
        $coupon->use($this->deal($coupon->deal), $day);

        return $coupon;
    }

    public function sold(string $dealHandle): int
    {
        $count = 0;
        foreach ($this->coupons as $coupon) {
            if ($coupon->deal === $dealHandle) {
                $count++;
            }
        }

        return $count;
    }

    public function left(string $dealHandle): int
    {
        return $this->deal($dealHandle)->onSale - $this->sold($dealHandle);
    }

    public function used(string $dealHandle): int
    {
        $count = 0;
        foreach ($this->coupons as $coupon) {
            if ($coupon->deal === $dealHandle && $coupon->spent()) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * The open deals, cheapest saving first, and each one carries what the wall shows.
     *
     * @return list<array{deal: Deal, business: Business, sold: int, left: int, used: int}>
     */
    public function wall(string $today): array
    {
        $rows = [];
        foreach ($this->deals as $deal) {
            $left = $this->left($deal->handle);
            if (!$deal->buyingOpenOn($today) || $left <= 0) {
                continue;
            }
            $rows[] = [
                'deal' => $deal,
                'business' => $this->business($deal->business),
                'sold' => $this->sold($deal->handle),
                'left' => $left,
                'used' => $this->used($deal->handle),
            ];
        }
        usort($rows, static fn (array $a, array $b): int => [$b['deal']->savingPercent(), $a['deal']->handle]
            <=> [$a['deal']->savingPercent(), $b['deal']->handle]);

        return $rows;
    }

    /**
     * What each deal took and how the money splits.
     *
     * @return array{deal: Deal, business: Business, sold: int, used: int, takings: int, to_business: int, to_platform: int}
     */
    public function ledger(string $dealHandle): array
    {
        $deal = $this->deal($dealHandle);
        $sold = $this->sold($dealHandle);

        return [
            'deal' => $deal,
            'business' => $this->business($deal->business),
            'sold' => $sold,
            'used' => $this->used($dealHandle),
            'takings' => $sold * $deal->dealCents,
            'to_business' => $sold * $deal->payoutCents(),
            'to_platform' => $sold * $deal->commissionCents(),
        ];
    }

    public function takings(): int
    {
        $sum = 0;
        foreach (array_keys($this->deals) as $handle) {
            $sum += $this->ledger($handle)['takings'];
        }

        return $sum;
    }

    public function toPlatform(): int
    {
        $sum = 0;
        foreach (array_keys($this->deals) as $handle) {
            $sum += $this->ledger($handle)['to_platform'];
        }

        return $sum;
    }

    public function toBusinesses(): int
    {
        return $this->takings() - $this->toPlatform();
    }

    public function couponsSold(): int
    {
        return count($this->coupons);
    }

    /** @return list<Coupon> */
    public function coupons(): array
    {
        return array_values($this->coupons);
    }
}
