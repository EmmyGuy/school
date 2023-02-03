<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicantPayment extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'session', 'applicant_id', 'application_amount', 'ref_num', 'status'
    ];

    
}
