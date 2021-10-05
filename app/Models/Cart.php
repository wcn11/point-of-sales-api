<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "cart";

    public $timestamps = true;

    public function cart_item(): \Illuminate\Database\Eloquent\Relations\HasMany
    {

        return $this->hasMany(CartItem::class);

    }

}
