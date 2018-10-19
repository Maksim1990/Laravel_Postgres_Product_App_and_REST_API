<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function attachments(){
        return $this->hasMany(Attachment::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

}
