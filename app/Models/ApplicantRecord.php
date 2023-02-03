<?php

namespace App\Models;

use App\User;
use Eloquent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApplicantRecord extends Eloquent
{
    use HasFactory;

    protected $fillable = [
        'session',
         'fullname',
          'my_class_id', 
        //   'section_id',
          'my_parent_id', 
          'application_no', 
          'year_admitted', 
          'dob', 
          "been_to_sch", 
          'last_class', 
          'class_qualified', 
          'nal_id', 
          'parent_address', 
          'parent_occupation', 
          'home_address', 
          'name_of_peson_who_picks_ward', 
          'passport', 
          'date_of_entrance_exam', 
          'lga_id', 
          'email', 
          'religion', 
          'certificate_obtained', 
          'certification', 
          'application_status', 
          'payment_id'
    ];


}
