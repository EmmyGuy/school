<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applicant_medical_condition extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    
}
