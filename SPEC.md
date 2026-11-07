# Couponetta

Couponetta issues a voucher with a limited face value and a last day, and it
redeems it in parts. One voucher, one holder, one balance that falls as the holder
spends it.

## Words

- **voucher**: one issued credit. It carries a code, a holder, a face value in
  cents, an issued day and a last day.
- **holder**: the party the voucher was issued to.
- **face value**: the whole amount the voucher carried when it was issued.
- **balance**: what is left on the voucher.
- **redemption**: one spend against a voucher. It carries the voucher code, an
  amount in cents and the day it happened.
- **last day**: the day after which the voucher takes no redemption.

## What a voucher enforces

- A redemption above the balance lands nowhere.
- A redemption of zero or less lands nowhere.
- A voucher takes no redemption after its last day.
- A voucher belongs to one holder, and a redemption from another holder lands
  nowhere.
- A voucher with a balance of zero answers spent.
- The balance never falls below zero.

## What a voucher answers

The answer names the code, the holder, the face value, the balance, whether the
voucher still takes a redemption, and the redemptions in the order they happened.

## Open in version 1

- Whether a voucher can be topped up. Version 1 says no.
- Whether a voucher can move from one holder to another. Version 1 says no.
- Whether an expired voucher keeps its balance or forfeits it. Version 1 keeps the
  balance and refuses the redemption.
