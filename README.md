# Couponetta

## What the project is

Couponetta is a PHP 8.3 program for issuing vouchers and redeeming their value in parts. Each voucher carries a code, one holder, a face value in cents, an issued day and a last day.

A holder can redeem part of the voucher's remaining balance. Each redemption records an amount in cents and the day it happened. Couponetta refuses a redemption of zero or an amount above the remaining balance. It also refuses redemptions before the issued day or after the last day. A different holder cannot redeem the voucher.

Each accepted redemption reduces the balance. The balance never falls below zero. When the balance reaches zero, the voucher answers spent.

The demo contains 3 vouchers and 4 redemptions. Together, the vouchers hold 14550 cents in remaining balances.

The program uses plain PHP 8.3. It requires no Composer and no packages.

## How to run it

Run the test suite from the repository root:

```bash
php tests/run.php
```

Start the demo with PHP's built-in server:

```bash
php -S 127.0.0.1:8108 -t showroom showroom/index.php
```

The application uses `/` as its default URL prefix. If a proxy passes a different prefix, set `COUPONETTA_BASE` to that value.

## What the routes answer

The demo exposes 2 routes.

`/` renders the sample vouchers and their redemptions.

`/health` answers with the voucher count, redemption count and total remaining balance in cents.

The home route shows the voucher details alongside their redemption history. Visitors can inspect the issued value and see how accepted redemptions change the remaining balance.

The health route reports the current counts and the total value still held across the vouchers.

## How the layout is arranged

The repository separates voucher logic from request handling, presentation, tests and deployment files.

`src/Home/` holds `Voucher` and `Sample`.

`src/App/` holds the router, front controller and HTML.

`showroom/` holds the entry file and stylesheet.

`tests/` holds the test suite.

`deploy/` holds the systemd unit and nginx location block.

`SPEC.md` describes the voucher rules.

The specification states the project vocabulary, what a voucher refuses and what version 1 leaves open.

The src/Home/ directory contains the voucher logic and sample data. The src/App/ directory handles requests and page output. The remaining directories support the demo, testing and deployment.

## The state of the suite

The repository includes a hand-written test runner with 18 checks.

Run all checks with:

```bash
php tests/run.php
```

The runner needs no package installation and no test framework. It checks the program from the command line.

## Licence

The repository uses the Mozilla Public License 2.0 (MPL 2.0). You may use, modify and distribute the code. You must publish changes to files covered by the licence.

