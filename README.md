# Attla ULID

<p align="center">
<a href="LICENSE"><img src="https://img.shields.io/badge/license-MIT-lightgrey.svg" alt="License"></a>
<a href="https://packagist.org/packages/attla/ulid"><img src="https://img.shields.io/packagist/v/attla/ulid" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/attla/ulid"><img src="https://img.shields.io/packagist/dt/attla/ulid" alt="Total Downloads"></a>
</p>

A PHP port of [ulid/javascript](https://github.com/ulid/javascript) with some minor improvements.

## Universally Unique Lexicographically Sortable Identifier

UUID can be suboptimal for many uses-cases because:

- It isn't the most character efficient way of encoding 128 bits of randomness
- UUID v1/v2 is impractical in many environments, as it requires access to a unique, stable MAC address
- UUID v3/v5 requires a unique seed and produces randomly distributed IDs, which can cause fragmentation in many data structures
- UUID v4 provides no other information than randomness which can cause fragmentation in many data structures

Instead, herein is proposed ULID:

- 128-bit compatibility with UUID
- 1.21e+24 unique ULIDs per millisecond
- Lexicographically sortable!
- Canonically encoded as a 26 character string, as opposed to the 36 character UUID
- Uses Crockford's base32 for better efficiency and readability (5 bits per character)
- Case insensitive
- No special characters (URL safe)
- Monotonic sort order (correctly detects and handles the same millisecond)

You can read more [here](https://github.com/ulid/javascript)

### What are the benefits?

1. With distributed systems you can be pretty confident that the primary key’s will never collide.
2. When building a large scale application when an auto increment primary key is not ideal.
3. It makes replication trivial (as opposed to int’s, which makes it REALLY hard)
4. Safe enough doesn’t show the user that you are getting information by id, for example `https://example.com/item/10`

## Installation

```bash
composer require attla/ulid
```

## Usage

```php
use Attla\Ulid;

$ulid = Ulid::generate();
echo (string) $ulid; // 01B8KYR6G8BC61CE8R6K2T16HY

// Or if you prefer a lowercased output
$ulid = Ulid::generate(true);
echo (string) $ulid; // 01b8kyr6g8bc61ce8r6k2t16hy

// If you need the timestamp from an ULID instance
$ulid = Ulid::generate();
echo $ulid->toTimestamp(); // 1561622862

// You can also generate a ULID for a specific UNIX-time in milliseconds
$ulid = Ulid::fromTimestamp(1593048767015);
// or with a lower cased output: $ulid = Ulid::fromTimestamp(1593048767015, true);
echo (string) $ulid; // 01EBMHP6H7TT1Q4B7CA018K5MQ
```

### Migrations

When using the migration you should change $table->increments('id') to:

```php
$table->char('id', 26)->primary();
```

> Simply, the schema seems something like this.

```php
Schema::create('items', function (Blueprint $table) {
  $table->char('id', 26)->primary();
  ....
  ....
  $table->timestamps();
});
```

If the related model is using an ULID, the column type should reflect that also.

``` php
Schema::create('items', function (Blueprint $table) {
  $table->char('id', 26)->primary();
  ....
  // related model that uses ULID
  $table->char('category_id', 26);
  $table->foreign('category_id')->references('id')->on('categories');
  ....
  $table->timestamps();
});
```

## Models

To set up a model to use ULID, simply use the HasUlid trait.

```php
use Illuminate\Database\Eloquent\Model;
use Attla\Ulid\HasUlid;

class Item extends Model
{
  use HasUlid;
}
```

### Controller

When you create a new instance of a model which uses ULIDs, this package will automatically add ULID as id of the model.

```php
// 'HasUlid' trait will automatically generate and assign id field.
$item = Item::create(['name' => 'Awesome item']);
echo $item->id;
// 01brh9q9amqp7mt7xqqb6b5k58
```

### Testing

``` bash
composer test
```

## License

This package is licensed under the [MIT license](LICENSE) © [Octha](https://octha.com).
