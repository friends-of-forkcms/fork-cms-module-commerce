# Working with monetary values

> "If I had a dime for every time I've seen someone use FLOAT to store currency, I'd have $999.997634" -- Bill Karwin

Money is a perfect candidate for a value object. Numbers are meaningless when not combined with a currency. In [Patterns of Enterprise Application Architecture](https://martinfowler.com/books/eaa.html), Martin Fowler describes the Money Pattern. There are endless reasons why not to represent money as a simple value (e.g. floating point calculations and rounding errors), so the Money Pattern describes a class encapsulating the amount and currency.

![Money Pattern UML](img/money-UML.png)

It also defines all the mathematical operations on the value with respect to the currency. **It stores the amount as integer in cents**, the lowest possible factor of the currency. We can not divide it more.

In the Fork CMS commerce module we use the [moneyphp/money](https://github.com/moneyphp/money/) library that implements this pattern. Some advantages are:

* Money objects are **immutable**
* Easy to use as Doctrine embeddable: `@ORM\Embedded(class="\Money\Money")`
* Easy Money formatting (including intl formatter)

As a consequence, monetary values in the MySQL database are stored in cents instead of a decimal number. A database column is added for the `price_amount` and `price_currency_code`. This is similar to how other libraries work, e.g. [Stripe](https://stripe.com/docs/api/charges/create?lang=php) describes an amount as "A positive integer representing how much to charge in the smallest currency unit (e.g., 100 cents to charge $1".


## Examples

```php
$fiveEuro = Money::EUR(500);
$tenEuro = $fiveEur->add($fiveEur);
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

⚠️ Currently the Commerce module only supports a single currency: EURO


## Read more

* http://martinfowler.com/eaaCatalog/money.html
* https://verraes.net/2011/04/fowler-money-pattern-in-php/
* https://github.com/moneyphp/money/
* https://www.slideshare.net/PiotrHorzycki/how-to-count-money-using-php-and-not-lose-money
