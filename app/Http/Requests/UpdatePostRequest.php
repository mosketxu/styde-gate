<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use \Illuminate\Support\Facades\Gate;

class UpdatePostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        /* Este metodo era un poco molesto porque no lo usaba y la linea la solia poner a true y me olvidaba de esto
                return false;
            Sin embargo puedo usarlo con los gates
            En este caso lo uso para ver si el Gate permite hacer update al usuario del post que viene de la ruta
            En PostController ya no uso el metodo Request sino el FormRequest

            Cuidado con las importaciones del use el automatico no siempre coge el que toca.
            */  
        return  Gate::allows('update',$this->post);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
        ];
    }
}
