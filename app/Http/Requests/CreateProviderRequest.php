<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Auth;

class CreateProviderRequest extends FormRequest
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
            
            'name' => 'unique:providers||required||max:50',
            'email' => 'unique:providers||required||email||max:50',
            'phone' => 'unique:providers||required||regex:/[0-9]{2}[- ]{0,1}[0-9]{3}[- ]{0,1}[0-9]{2}[- ]{0,1}[0-9]{2}/|',
            'tax_number' => 'unique:providers||required',
            'contact_person' => 'max:150',
            'description' => 'max:400',
            'address' => 'required||max:100',
            'postal_code' => 'required||max:10',
            'iban' => 'unique:providers||required||max:24',
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
