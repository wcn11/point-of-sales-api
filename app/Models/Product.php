<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "product";

    public function product_partner(): \Illuminate\Database\Eloquent\Relations\HasMany
    {

        return $this->hasMany(ProductPartner::class);

    }

}
