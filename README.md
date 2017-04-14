# Change tracking for Laravel Eloquent model's

This package provides a number of traits to track changes made to a model.

[![Latest Stable Version](https://poser.pugx.org/bluora/laravel-model-change-tracking/v/stable.svg)](https://packagist.org/packages/bluora/laravel-model-change-tracking) [![Total Downloads](https://poser.pugx.org/bluora/laravel-model-change-tracking/downloads.svg)](https://packagist.org/packages/bluora/laravel-model-change-tracking) [![Latest Unstable Version](https://poser.pugx.org/bluora/laravel-model-change-tracking/v/unstable.svg)](https://packagist.org/packages/bluora/laravel-model-change-tracking) [![License](https://poser.pugx.org/bluora/laravel-model-change-tracking/license.svg)](https://packagist.org/packages/bluora/laravel-model-change-tracking)

[![Build Status](https://travis-ci.org/bluora/laravel-model-change-tracking.svg?branch=master)](https://travis-ci.org/bluora/laravel-model-change-tracking) [![StyleCI](https://styleci.io/repos/53252133/shield?branch=master)](https://styleci.io/repos/53252133) [![Test Coverage](https://codeclimate.com/github/bluora/laravel-model-change-tracking/badges/coverage.svg)](https://codeclimate.com/github/bluora/laravel-model-change-tracking/coverage) [![Issue Count](https://codeclimate.com/github/bluora/laravel-model-change-tracking/badges/issue_count.svg)](https://codeclimate.com/github/bluora/laravel-model-change-tracking) [![Code Climate](https://codeclimate.com/github/bluora/laravel-model-change-tracking/badges/gpa.svg)](https://codeclimate.com/github/bluora/laravel-model-change-tracking) 

## State Change
Track state changes on your model and by which user for the following states - `created`, `updated`, `deleted`, and `restored`.

## Attribute Change Trait
Adds a `saving` event to the model to track changes to all attribute values.

## Change by User Trait
Adds events to set a attribute to the current user for when a model is `created`, `updated`, `archived`, or `deleted`.

## Install

Via composer:

`$ composer require-dev bluora/laravel-model-change-tracking dev-master`

Enable the service provider by editing config/app.php:

```php
    'providers' => [
        ...
        Bluora\LaravelModelChangeTracking\ServiceProvider::class,
        ...
    ];
```

## Usage

### User tracking of changes.

Add a `created_by`, `updated_by`, `archived_by`, and `deleted_by` attributes to your model's database table.

```php

namespace App\Models;

use Bluora\LaravelModelChangeTracking\ChangeByUserTrait;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use ChangeByUserTrait;
}
```

#### Turn off tracking attribute

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

public function getArchivedByColumn()
{
    return false;
}

public function getDeletedByColumn()
{
    return false;
}
```

#### Different attribute name

You can specify the attribute name in the return value.

```php
public function getCreatedByColumn()
{
    return 'created_by';
}

public function getUpdatedByColumn()
{
    return 'updated_by';
}

public function getArchivedByColumn()
{
    return 'updated_by';
}

public function getDeletedByColumn()
{
    return 'deleted_by';
}
```

### Track state changes of models

Tracks model state changes externally in database table.

```php

namespace App\Models;

use Bluora\LaravelModelChangeTracking\LogStateChangeTrait;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use LogStateChangeTrait;
}
```

### Log each attribute value change

Tracks attribute value changes.


```php

namespace App\Models;

use Bluora\LaravelModelChangeTracking\LogChangeTrait;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use LogChangeTrait;

    protected $do_not_log = [
        'password',
        'remember_token',
    ];
}
```
