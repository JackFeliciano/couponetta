<?php

declare(strict_types=1);

namespace Couponetta\App;

use Couponetta\Home\Sample;
use Couponetta\Home\Voucher;

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
        $TITLE = 'Couponetta, one voucher spent in parts';
        $LEDE = 'Couponetta issues vouchers with limited values that holders can redeem in parts before the last day.';
        $WHAT = 'Each voucher has a code, one holder, a face value, an issued day and a last day. The holder can redeem part of the value by submitting an amount and the day of redemption. Couponetta checks that the amount is above zero, within the remaining balance and within the voucher\'s valid dates. Another holder cannot redeem it. Each accepted redemption reduces the balance. A zero balance means the voucher is spent, and the balance never falls below zero.';

        $out = $this->header($TITLE);
        $out .= '<h1>' . $this->h($TITLE) . '</h1>' . "\n";
        $out .= '<p class="lede">' . $this->h($LEDE) . '</p>' . "\n";
        $out .= '<p>' . $this->h($WHAT) . '</p>' . "\n";
        $out .= '<p class="stage">Vouchers: ' . count($sample->vouchers()) . '. Redemptions: ' . $sample->redemptions()
            . '. Balance held: ' . $this->h($this->money($sample->balance())) . '.</p>' . "\n";

        $out .= '<div class="vouchers">' . "\n";
        foreach ($sample->vouchers() as $voucher) {
            $out .= $this->card($voucher);
        }
        $out .= '</div>' . "\n";

        $out .= '<h2>What a voucher does</h2>' . "\n";
        $out .= '<ol class="steps">' . "\n";
        foreach ([
            ['Voucher issued', 'Couponetta creates a voucher for one holder with a value and valid dates.'],
            ['Holder redeems', 'The holder submits an amount and the day of redemption.'],
            ['Balance falls', 'Couponetta deducts each accepted redemption from the remaining value.'],
            ['Last day closes', 'The voucher takes no redemption after its last day.'],
            ['Record answers', 'The voucher reports whether its balance is spent or still holds value.'],
        ] as [$step, $line]) {
            $out .= '<li><strong>' . $this->h($step) . '</strong> ' . $this->h($line) . '</li>' . "\n";
        }
        $out .= '</ol>' . "\n";
        $out .= $this->footer();

        return $out;
    }

    private function card(Voucher $voucher): string
    {
        $answer = $voucher->answer();
        $out = '<article class="voucher">' . "\n";
        $out .= '<h2>' . $this->h($voucher->code) . '</h2>' . "\n";
        $out .= '<p class="where">' . $this->h($voucher->holder) . '. Face ' . $this->h($this->money($voucher->faceValue))
            . ', issued ' . $this->h($voucher->issuedDay) . ', last day ' . $this->h($voucher->lastDay) . '.</p>' . "\n";
        $out .= '<p class="money">Balance ' . $this->h($this->money((int) $answer['balance'])) . ' of '
            . $this->h($this->money((int) $answer['face'])) . '.</p>' . "\n";
        if ($answer['redemptions'] === []) {
            $out .= '<p class="untouched">No redemption yet.</p>' . "\n";
        } else {
            $out .= '<table class="runs"><thead><tr><th>Day</th><th>Spent</th><th>Left</th></tr></thead><tbody>' . "\n";
            foreach ($answer['redemptions'] as $r) {
                $out .= '<tr><td>' . $this->h((string) $r['day']) . '</td><td>' . $this->h($this->money((int) $r['amount']))
                    . '</td><td>' . $this->h($this->money((int) $r['left'])) . '</td></tr>' . "\n";
            }
            $out .= '</tbody></table>' . "\n";
        }
        $out .= $answer['open'] ? '<p class="open">Takes a redemption.</p>' . "\n" : '<p class="closed">Spent to nothing.</p>' . "\n";
        $out .= '</article>' . "\n";

        return $out;
    }

    public function missing(): string
    {
        return $this->header('Not found') . "<h1>Not found</h1>\n<p>That path does not exist here.</p>\n" . $this->footer();
    }
}
