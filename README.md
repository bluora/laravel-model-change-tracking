# Change tracking in Laravel model

## State Change
Track state changes on your model and by which user for the following states - `created`, `updated`, `deleted`, and `restored`.

## Column Change Trait
Adds a `saving` event to the model to track changes to all column values.

## Change by User Trait
Adds events to set a column to the current user for when a model is `created`, `updated`, or `deleted`.


## Installation

Require this package in your `composer.json` file:

`"bluora/laravel-model-change-tracking": "dev-master"`

Then run `composer update` to download the package to your vendor directory.

## Usage

### Basic

Add a `created_by`, `updated_by` and `deleted_by` columns to your model's database table.


```php
use ModelChangeTracking\ChangeByUserTrait;

class User extends Model
{
    use ChangeByUserTrait;
}
```
### Turn off column

You can turn off by returning false.
```php
public function getCreatedByColumn()
{
    return false;
}

public function getUpdatedByColumn()
{
    return false;
}

public function getDeletedByColumn()
{
    return false;
}
```

### Different column name

You can specify the column name in the return value.

```php
public function getCreatedByColumn()
{
    return 'created_by';
}

public function getUpdatedByColumn()
{
    return 'updated_by';
}

public function getDeletedByColumn()
{
    return 'deleted_by';
}
```

