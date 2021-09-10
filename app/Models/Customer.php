<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "customers";

    public function sales(): \Illuminate\Database\Eloquent\Relations\HasMany
    {

        return $this->hasMany(Sales::class);

    }

}
