<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogModelChange extends Model
{
    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'log_model_change';

    /**v
     * The attributes that require casting
     *
     * @var array
     */
    protected $casts = [
        'model'       => 'string',
        'model_id'    => 'integer',
        'column_name' => 'string',
        'old_value'   => 'string',
        'new_value'   => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model',
        'model_id',
        'column_name',
        'old_value',
        'new_value',
        'log_by',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Get the user who created log.
     */
    public function logBy()
    {
        return $this->hasOne(User::class, 'id', 'log_by');
    }
}
