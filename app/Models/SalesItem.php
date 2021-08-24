<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesItem extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "sales_item";

    public $timestamps = false;

    public function sales(){

        return $this->belongsTo(Sales::class);

    }

}
