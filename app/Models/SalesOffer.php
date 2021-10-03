<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOffer extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "sales_offer";

    public $incrementing = false;

    public function sales_offer_item(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalesOfferItem::class);
    }

}
