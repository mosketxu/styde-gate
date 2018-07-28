<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function isAdmin(){
        return $this->role==='admin';
    }

    /*  public function owns(Model $model){
            return $this->id=== $model->user_id; //verifico que la llave primaria del usuario sea la misma que la llave foranea del modelo
        }
        o puedo hacer que la llave sea personalizable y que venga como variable del modelo own y que por defecto sea= 'user_id'
    */
    public function owns(Model $model, $foreignKey='user_id'){
        return $this->id=== $model->$foreignKey;
    }


}
