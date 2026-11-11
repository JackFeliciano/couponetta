# Couponetta roadmap

Couponetta sells a batch of discounted coupons and honours one code once at redemption.
Version 1 keeps one deal in one process, and the list below is what a business and a
buyer need next.

## Open in the model

- A voucher cannot be topped up, so a shop that wants to add value issues a second
  voucher and the holder carries two.
- A voucher cannot move from one holder to another. A gift needs a new voucher.
- An expired voucher keeps its balance and takes no redemption. Whether the balance
  forfeits is a question for SPEC.md, and a shop will ask it.

## Owed to a holder

- Nothing tells the holder that a redemption landed. The caller reads the answer.
- A holder cannot see how many days are left before the last day.
- A redemption cannot be reversed.

## Owed to a shop

- A shop cannot cancel a voucher.
- There is no way to ask for the vouchers one holder carries.
- No voucher carries a seal, so a shop cannot tell a voucher it issued from one
  written in afterwards.

## Owed to a reader

- The page shows every voucher on the server and holds no filters.
- The balance is derived on every call by walking the redemptions. At a hundred
  thousand redemptions a voucher needs a stored balance and a check behind it.
- The redemptions carry a day and no time, so two redemptions on one day cannot be
  ordered by anything but arrival.

## Checks owed

- A voucher redeemed in the same cent twice at the end of its balance.
- A voucher with a last day equal to its issued day.
- A redemption list of a hundred entries.
