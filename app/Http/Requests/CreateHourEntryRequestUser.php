<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Auth;

class CreateHourEntryRequestUser extends FormRequest {

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        if (Auth::user()->isUser()) {
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
            "days.*" => ["required", "string", "date_format:d/m/Y", 'before_or_equal:' . now()->format('d/m/Y')],
            "hours" => "required|array",
            "hours.*" => "required|int|min:1|max:24",
            "customers" => "required|array",
            "customers.*" => "required|string",
            "projects" => "required|array",
            "projects.*" => "required|string",
            "inputed_hours" => "required|array",
            "inputed_hours.*" => "required|int|min:1|max:24",
            "desc" => "required|array",
            "desc.*" => "required|string|min:5|max:150",
        ];
    }
    
    public $validator = null;
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $this->validator = $validator;
    }

}
