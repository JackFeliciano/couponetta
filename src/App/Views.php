<?php

declare(strict_types=1);

namespace Couponetta\App;

use Couponetta\Home\Sample;

/**
 * The HTML. Every string reaching the page goes through h(), and every amount in
 * cents goes through money(), so no bare integer reaches a reader as a price.
 */
final class Views
{
    public function __construct(private readonly string $base = '/')
    {
    }

    private function h(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, '.', ',') . ' EUR';
    }

    public function header(string $title): string
    {
        $base = $this->h($this->base);
        $t = $this->h($title);

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$t}</title>
<base href="{$base}">
<link rel="stylesheet" href="assets/site.css">
</head>
<body>
<main>
HTML;
    }

    public function footer(): string
    {
        return "</main>\n</body>\n</html>\n";
    }

    public function front(Sample $sample): string
    {
        $TITLE = 'Couponetta, deals a business posts and a buyer claims';
        $LEDE = 'Couponetta connects businesses offering discounted products or services with buyers who pay upfront to claim a coupon.';
        $WHAT = 'Each deal defines its normal price, discounted price, available coupon quantity, purchase deadline and final redemption date. Buyers receive a unique six character code, which the business validates once at redemption. The platform shares each sale with the business under an agreed arrangement.';

        $m = $sample->market();
        $out = $this->header($TITLE);
        $out .= '<h1>' . $this->h($TITLE) . '</h1>' . "\n";
        $out .= '<p class="lede">' . $this->h($LEDE) . '</p>' . "\n";
        $out .= '<p>' . $this->h($WHAT) . '</p>' . "\n";
        $out .= '<p class="stage">Deals on the wall: ' . $sample->deals() . '. Coupons sold: ' . $sample->couponsSold()
            . '. Coupons used: ' . $sample->couponsUsed() . '. Takings: ' . $this->h($this->money($sample->takings()))
            . '. Platform share: ' . $this->h($this->money($sample->toPlatform())) . ".</p>\n";

        $out .= '<div class="deals">' . "\n";
        foreach ($m->wall(Sample::TODAY) as $row) {
            $out .= $this->card($row);
        }
        $out .= '</div>' . "\n";

        $out .= '<h2>What a deal does</h2>' . "\n";
        $out .= '<ol class="steps">' . "\n";
        foreach ([
            ['Post the deal', 'A business posts a deal with both prices, the coupon quantity, the purchase deadline and the final redemption date.'],
            ['Buy the coupon', 'A buyer pays upfront and receives a unique six character code.'],
            ['Redeem once', 'The business validates the code once at redemption, and the platform shares the sale with the business.'],
        ] as [$step, $line]) {
            $out .= '<li><strong>' . $this->h($step) . '</strong> ' . $this->h($line) . '</li>' . "\n";
        }
        $out .= '</ol>' . "\n";
        $out .= '<h2>Where the money went</h2>' . "\n";
        $out .= '<table class="ledger"><thead><tr><th>Deal</th><th>Sold</th><th>Takings</th><th>To business</th><th>To platform</th></tr></thead><tbody>' . "\n";
        foreach (array_keys($this->dealHandles($sample)) as $handle) {
            $row = $m->ledger((string) $handle);
            $out .= '<tr><td>' . $this->h($row['deal']->title) . '</td><td>' . $row['sold'] . '</td><td>'
                . $this->h($this->money($row['takings'])) . '</td><td>' . $this->h($this->money($row['to_business']))
                . '</td><td>' . $this->h($this->money($row['to_platform'])) . '</td></tr>' . "\n";
        }
        $out .= '</tbody></table>' . "\n";
        $out .= $this->footer();

        return $out;
    }

    /** @return array<string, true> */
    private function dealHandles(Sample $sample): array
    {
        $handles = [];
        foreach ($sample->market()->coupons() as $coupon) {
            $handles[$coupon->deal] = true;
        }

        return $handles;
    }

    /** @param array{deal: \Couponetta\Home\Deal, business: \Couponetta\Home\Business, sold: int, left: int, used: int} $row */
    private function card(array $row): string
    {
        $deal = $row['deal'];
        $out = '<article class="deal">' . "\n";
        $out .= '<h2>' . $this->h($deal->title) . '</h2>' . "\n";
        $out .= '<p class="where">' . $this->h($row['business']->name) . ', ' . $this->h($row['business']->town)
            . '. ' . $this->h($row['business']->category) . ".</p>\n";
        $out .= '<p class="price">' . $this->h($this->money($deal->dealCents)) . ' instead of '
            . $this->h($this->money($deal->normalCents)) . ', ' . $deal->savingPercent() . '% off.</p>' . "\n";
        $out .= '<p class="terms">' . $row['left'] . ' of ' . $deal->onSale . ' left. Buy by '
            . $this->h($deal->buyByDay) . ', use by ' . $this->h($deal->useByDay) . ".</p>\n";
        $out .= '<p class="split">Business takes ' . $this->h($this->money($deal->payoutCents())) . ' a coupon, the platform '
            . $this->h($this->money($deal->commissionCents())) . ".</p>\n";
        $out .= '</article>' . "\n";

        return $out;
    }

    public function missing(): string
    {
        return $this->header('Not found') . "<h1>Not found</h1>\n<p>That path does not exist here.</p>\n" . $this->footer();
    }
}
