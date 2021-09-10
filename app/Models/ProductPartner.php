<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductPartner extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "product_partner";

    public $timestamps = false;

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {

        return $this->belongsTo(Product::class);

    }

    public function users(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {

        return $this->belongsTo(User::class);

    }

}
