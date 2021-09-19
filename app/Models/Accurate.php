<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Accurate extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "accurate_config";

    public $timestamps = false;

}
