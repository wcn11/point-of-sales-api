<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "sales";

    public $incrementing = false;

    public function sales_item(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SalesItem::class);
    }

    public function customers(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

}
