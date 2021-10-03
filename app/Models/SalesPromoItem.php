<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPromoItem extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "sales_promo_item";

    public $timestamps = false;

    public function sales_promo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {

        return $this->belongsTo(SalesPromo::class);

    }

}
