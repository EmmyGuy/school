<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'session', 'name', 'amount', 'openning_date', 'closing_date', 'status', 'applicant_type'
    ];

    
}
