<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPromo extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "sales_promo";

    public $incrementing = false;

    public function sales_promo_item(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalesPromoItem::class);
    }

}
