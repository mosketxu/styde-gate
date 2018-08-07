<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // protected $guarded=[]; //solo si no estoy seguro de que voy a usar request->all()
    protected $fillable=['title'];
    public function isPublished(){
        return $this->status==='published';
    }
}
