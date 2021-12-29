# Working with monetary values

> "If I had a dime for every time I've seen someone use FLOAT to store currency, I'd have $999.997634" -- Bill Karwin

Floating-point calculations, often used in financial transactions, are tricky and error-prone because of how computers 
process them. Small mistakes can accumulate and cause severe damage to a business. For example:

```php
var_dump(0.0.1 + 0.05 === 0.06)
//  bool(false)
```

Money can be represented using simple integers ($1.23 stored as 123), but that causes more trouble than it solves:
* Integers have limited range (they can overflow silently)
* Subunits can change over history
* It's tricky to handle rounding, e.g. taxes: €123.45 * 1.21 equals €149.3745 but that's not a valid amount. You have to
  round it to €149.37.
* Converting floats to int is tricky. Consider: `(int) (4.10 * 100)` which results in 409, not 410. The `(int)` cast
  does not handle rounding. It strips the fractional part! 


## Using a dedicated Money library
Money is a perfect candidate for an immutable value object. Numbers are meaningless when not combined with a currency. In 
[Patterns of Enterprise Application Architecture](https://martinfowler.com/books/eaa.html), Martin Fowler describes 
the Money Pattern. There are endless reasons why not to represent money as a simple value (e.g. floating point 
calculations and rounding errors), so the Money Pattern describes a class encapsulating the amount and currency.

> “A large proportion of the computers in this world manipulate money, so it’s always puzzled me that money isn’t a 
> first-class data type in any mainstream program- ming language.”
> – Martin Fowler

![Money Pattern UML](img/money-UML.png)

It also defines all the mathematical operations on the value with respect to the currency. 
**It stores the amount as integer in cents**, the lowest possible factor of the currency. We can not divide it more.

In this module we use the [moneyphp/money](https://github.com/moneyphp/money/) library that 
implements this pattern. Some advantages are:

- Money objects are **immutable**
- Easy to use as Doctrine embeddable: `@ORM\Embedded(class="\Money\Money")`
- Easy money formatting to different locales (with `IntlMoneyFormatter`)
- Easy conversion between currencies using converts (e.g. using [Swap](https://github.com/florianv/swap))
- Easy to sum up money, find a minimum/maximum/average, do allocations, ...
- Implements `JsonSerializable` to convert money to JSON to exchange monetary data with other systems.
- 

As a consequence, monetary values in the MySQL database are stored in cents instead of a decimal number. A database 
column is added for the `price_amount` and `price_currency_code`. This is similar to how other libraries work, 
e.g. [Stripe](https://stripe.com/docs/api/charges/create?lang=php) also expects monetary values in cents and describes it 
an amount as "A positive integer representing how much to charge in the smallest currency unit (e.g., 100 cents to charge $1".

Integrating moneyPHP in Symfony, Doctrine and Twig can be done using 
[TheBigBrainsCompany/TbbcMoneyBundle](https://github.com/TheBigBrainsCompany/TbbcMoneyBundle).

### Examples

```php
$fiveEuro = Money::EUR(500); // €5
$tenEuro = $fiveEuro->add($fiveEuro); // €10

$net = new Money(123, new Currency('USD')); // $1.23USD
$gross = $net->multiply('1.10', Money::ROUND_UP); // Add 10% to 1.23 = 1.353, and round it up
```

In a Doctrine entity:

```php
// Product.php
// This will create a MySQL column `price_amount` and `price_currency_code`

/**
 * @ORM\Embedded(class="\Money\Money")
 */
private Money $price;
```

Or using our Twig helper function to render values:

```twig
{{ cart.subTotal|format_money_decimal() }}    // 5.00
{{ cart.subTotal|format_money() }}            // €5.00
```

!!! warning

    Currently the Commerce module only supports a single currency: EURO

## Read more

-   http://martinfowler.com/eaaCatalog/money.html
-   https://verraes.net/2011/04/fowler-money-pattern-in-php/
-   https://github.com/moneyphp/money/
-   https://www.slideshare.net/PiotrHorzycki/how-to-count-money-using-php-and-not-lose-money
