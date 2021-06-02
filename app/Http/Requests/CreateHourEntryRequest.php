<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Auth;

class CreateHourEntryRequest extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        if (Auth::user()->isAdmin()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        App::setLocale($this->lang);

        return [
            "days" => "required",
            "days.*" => "required|string",
            "users" => "required|array",
            "users.*" => "required|string",
        ];
    }

}
