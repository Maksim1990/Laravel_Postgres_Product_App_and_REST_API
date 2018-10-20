<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $guarded = [];

    protected $primaryKey = 'id';
    public function product(){
        return $this->belongsTo(Product::class);
    }
}
