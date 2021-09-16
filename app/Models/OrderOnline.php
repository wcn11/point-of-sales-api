<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderOnline extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    protected $table = "order_online";

    public function order_online_item(): \Illuminate\Database\Eloquent\Relations\HasMany
    {

        return $this->hasMany(OrderOnlineItem::class);

    }

}
