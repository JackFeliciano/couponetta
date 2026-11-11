# Couponetta

Couponetta is a deal site. A business offers a service at a cut price for a fixed run, a buyer
pays the cut price up front, and the buyer turns up before the use-by date with a code the
business checks off. The platform keeps a share of every sale.

## Words

- **business**: the party offering the deal. It carries a handle, a name, a town and a category.
- **deal**: one offer. It carries a handle, the business, a title, the normal price in cents,
  the deal price in cents, the number of coupons on sale, the day buying closes and the day
  the coupon must be used by.
- **coupon**: one sale. It carries a code, the deal, the buyer's handle, the day the buyer
  paid and the day the buyer used it, which starts empty.
- **share**: the part of the deal price the platform keeps. A deal carries its own, in basis
  points.
- **payout**: what the business receives from one coupon. The deal price less the platform's
  share, in whole cents.

## What the model enforces

- A deal price sits below the normal price. A deal that saves a buyer nothing lands nowhere.
- Both prices are positive, and the deal price is at least one cent.
- The platform's share sits between 0 and 5000 basis points, so half the deal price at most.
- A deal sells at most the coupons it puts on sale. Selling the last one closes it.
- Buying closes at the buy-by date. A purchase after it lands nowhere.
- The use-by date is later than the buy-by date.
- A coupon is used once. A second use lands nowhere.
- A coupon that is used after the use-by date lands nowhere.
- A coupon carries a code of six characters, unique across the site.

## What the page shows

- the wall of open deals, with the saving, the price, how many are left and how many days the
  buyer has left to buy
- the takings for the sample, split between the businesses and the platform

## Open in version 1

- A coupon has no refund path. A buyer who never uses it leaves the money with the business
  and the platform.
- Every coupon sold counts, so a deal with a hundred on sale takes a hundred payments in the
  model with no queue between them.
