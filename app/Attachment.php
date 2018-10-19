<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $guarded = [];

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
