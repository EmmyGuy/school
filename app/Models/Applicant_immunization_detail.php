<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Applicant_immunization_detail extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'name', 'immunization_date',
    ];

    
}
