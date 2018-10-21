<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $primaryKey = 'id';
    protected $guarded = [];

    public function attachments(){
        return $this->hasMany('App\Attachment');
    }

    public function categories(){
            return $this->belongsToMany('App\Category', 'product_category',
                'product_id', 'category_id');
    }

}
