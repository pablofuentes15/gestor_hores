<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\App;
use Auth;

class EditProviderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if(Auth::user()->isAdmin()){
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        App::setLocale($this->lang);
        
        return [
            'name' => ['required', Rule::unique('providers')->ignore($this->provider->id), 'max:50'],
            'email' => ['required','email',Rule::unique('providers')->ignore($this->provider->id), 'max:50'],
            'phone' => ['required', 'regex:/[0-9]{2}[- ]{0,1}[0-9]{3}[- ]{0,1}[0-9]{2}[- ]{0,1}[0-9]{2}/',
            Rule::unique('providers')->ignore($this->provider->id)],
            'tax_number' => ['required', Rule::unique('providers')->ignore($this->provider->id), 'regex:/^\d{8}[a-zA-Z]{1}$/'],
            'contact_person' => 'max:150',
            'description' => 'max:400',
            'address' => 'required||max:100',
            'postal_code' => 'required||max:10',
            'iban' => ['required', Rule::unique('providers')->ignore($this->provider->id), 'max:24'],
            'country' => 'required',
            'province' => '',
            'municipality' => ''
        ];
    }
    public function messages() 
    {
        return [
            'phone.numeric' => __('message.phone_numeric'),
            'phone.min' => __('message.phone_min'),
            'phone.max' => __('message.phone_max')
        ];
    }
}
