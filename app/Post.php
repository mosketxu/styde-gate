<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    // protected $guarded=[]; //solo si no estoy seguro de que voy a usar request->all()
    protected $fillable=['title']; //podria poner tb ,'user_id' pero a DUilio no le mola
    public function isPublished(){
        return $this->status==='published';
    }
}
