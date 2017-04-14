<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogModelStateChange extends Model
{
    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'log_model_state_change';

    /**v
     * The attributes that require casting
     *
     * @var array
     */
    protected $casts = [
        'model'       => 'string',
        'model_id'    => 'integer',
        'state'       => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'model',
        'model_id',
        'state',
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
        return $this->hasOne('App\Models\User');
    }
}
