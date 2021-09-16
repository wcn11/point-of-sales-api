<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderOnlineItem extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "order_online_item";

    public $timestamps = false;

    public function order_online(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {

        return $this->belongsTo(OrderOnline::class);

    }

}
