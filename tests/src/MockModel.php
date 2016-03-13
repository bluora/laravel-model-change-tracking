<?php

class MockModel extends Illuminate\Database\Eloquent\Model
{
    use \ModelChangeTracking\ChangeByUserTrait;

    protected $json_columns;

    public function __construct(array $attributes = [])
    {
        static::$booted[get_class($this)] = true;
        parent::__construct($attributes);
    }

    public function setCustomSetAttribute($value)
    {
        $this->setJsonAttribute($this->jsonAttributes['custom_set'], 'custom_set', "custom {$value}");
    }
}
