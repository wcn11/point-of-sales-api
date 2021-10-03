<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "promo";

    public $timestamps = true;

}
