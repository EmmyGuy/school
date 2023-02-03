<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Application extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'session', 'name', 'application_amount', 'opening_date', 'closing_date', 'status'
    ];

    
}
