<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', 'email', 'phone', 'tax_number', 'contact_person', 'description', 'address', 'postal_code', 'country', 'province', 'municipality', 'iban'
    ];

}
