<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesOfferItem extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "sales_offer_item";

    public $timestamps = false;

    public function sales_offer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {

        return $this->belongsTo(SalesOffer::class);

    }

}
