<?php


namespace App\Http\Controllers\MyParent;

use App\Helpers\Qs;
use App\Helpers\Pay;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\PaymentCreate;
use App\Http\Requests\Payment\PaymentUpdate;
use App\Models\Setting;

use App\Repositories\SettingRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\PaymentRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\Repositories\LocationRepo;

use Illuminate\Support\Facades\Input;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use PDF;
use DB;
use App\User;
use App\Http\Requests;

use App\Models\ApplicantRecord;
use App\Models\StudentRecord;

class MyController extends Controller
{
    protected $student, $pay, $year, $loc;
    public function __construct(LocationRepo $loc, SettingRepo $setting, MyClassRepo $my_class, PaymentRepo $pay, StudentRepo $student, UserRepo $user)
    {
        $this->my_class = $my_class;
        $this->pay = $pay;
        $this->year = Qs::getCurrentSession();
        $this->student = $student;
        $this->setting = $setting;
        $this->user = $user;
        $this->loc = $loc;
    }

    public function children()
    {
        $data['students'] = $this->student->getRecord(['my_parent_id' => Auth::user()->id])->with(['my_class', 'section'])->get();

        return view('pages.parent.children', $data);
    }

    public function manage($class_id = NULL)
    {

        $d['my_classes'] = $this->my_class->all();
        $d['selected'] = false;

            $d['students'] = DB::table( 'applicant_records' )->where( 'my_parent_id', Auth::user()->id)
                                ->join('my_classes', 'my_classes.id', '=', 'applicant_records.my_class_id')
                                ->select('applicant_records.id','applicant_records.application_no', 'applicant_records.fullname',
                                'applicant_records.passport', 'applicant_records.application_status', 'applicant_records.session',
                                'my_classes.name as class_name',
                                DB::raw('(CASE 
                                        WHEN (SELECT count(*) from applicant_payments where applicant_id = applicant_records.id AND status = "paid" ) > 0 THEN "Paid" 
                                        ELSE "Pending" 
                                        END) AS payment_status'),
                                // DB::raw('(CASE 
                                //         WHEN (SELECT count(*) from student_records where admission_id = student_records.id ) > 0 THEN "Admitted" 
                                //         ELSE "Pending" 
                                //         END) AS admission_status')
                                        )->get();

            // dd($d['students']);
            
            $d['selected'] = true;
            $d['my_class_id'] = $class_id;


        // return view('hrms.hodRecommendation.appraisal', compact('modaldata'))->render();
        return view('pages.parent.application.chooseForm', $d);
    }

    public function showApplicationForm($applicantId = NULL)
    {
         $s = $this->setting->all();

         $d['class_types'] = $this->my_class->getTypes();
         $data['s'] = $s->flatMap(function($s){
            return [$s->type => $s->description];
        });

        if($applicantId){
            $data['applicant'] =  DB::table( 'applicant_records' )
                                      ->where( 'applicant_records.id', $applicantId)
                                      ->join('users', 'users.id', '=', 'applicant_records.my_parent_id')
                                      ->select('applicant_records.*', 'applicant_records.id AS applicant_id', 'applicant_records.dob AS applicant_dob',
                                       'users.*', 'users.nal_id AS parent_nal_id','applicant_records.date_of_entrance_exam AS applicant_date_of_entrance_exam',
                                       'applicant_records.bg_id AS applicant_blood_group',
                                       )->first();  
        }else{
            $data['applicant'] = null;
        }

        // dd($data['applicant']); 

        $data['my_classes'] = $this->my_class->all();
        // $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();

        if(request()->route()->originalParameters('id')['id'] === "pre_school" ||  $data['applicant'] != null && $data['applicant']->applicant_type == "pre_school")
        {
            $data['applicant_type'] = "pre_school";
            return view('pages.parent.application.preSchoolForm', compact('data'))->render();

        }else
        {
            $data['applicant_type'] = "high_school";
            return view('pages.parent.application.highSchoolForm', compact('data'))->render();

        }

    }

    public function  saveApplication(Request $request)
    {
        // dd($request->schedule_type);

        $user = User::where('email', \Auth::user()->email)->first();
        $user->phone     = $request->office_phone; //office phone
        $user->phone2     = $request->home_phone; //office phone
        $user->state_id  = $request->state_id;
        $user->lga_id    = $request->lga_id;
        $user->nal_id    = $request->parent_nal_id;
        $user->address    = $request->office_address;

        $user->save();

        if($request->applicant_id == null){
            $applicant = new ApplicantRecord;
            do {
                $applicantion_no = Qs::getAppCode().mt_rand( 1000000000, 9999999999 );
             } while ( DB::table( 'applicant_records' )->where( 'application_no', $applicantion_no )->exists() );
             $applicant->application_no  = $applicantion_no;
            //  dd($applicant);
        }
        else {
            $applicant = ApplicantRecord::where( 'id', $request->applicant_id)->first();
            //  dd($applicant);
        }

        $applicant->fullname             = $request->name;
        $applicant->section_id           = $request->section_id;
        $applicant->dob                  = $request->dob;
        // $applicant->date_of_entrance_exam = $request->date_of_entrance_exam; //to entered by admin
        $applicant->gender               = $request->gender;
        $applicant->bg_id                = $request->bg_id;
        $applicant->been_to_sch          = $request->previous_class != null ? true : false;
        $applicant->last_class           = $request->previous_class;
        $applicant->session              = $request->current_session;
        $applicant->nal_id               = $request->nal_id;
        $applicant->parent_address       = $request->home_address;
        $applicant->parent_occupation    = $request->occupation;
        $applicant->home_address         = $request->address;
        $applicant->name_of_peson_who_picks_ward = $request->person_who_pick_child;
        $applicant->lga_id                       = $request->lga_id;
        $applicant->my_parent_id                 = $user->id;
        $applicant->my_class_id                  = $request->my_class_id;
        $applicant->applicant_schedule           = $request->schedule_type;
        $applicant->applicant_type               = $request->applicant_type;
        // $applicant->allergies                    = json_encode($request->mediacal_condition);
        $applicant->examination_hall             = $request->exam_center;
        $applicant->certification                = $request->certification;
        $applicant->immunization                 = json_encode(explode(";",$request->immunization));
        $applicant->allergies                    = json_encode($request->mediacal_condition);
        $applicant->application_status            = "pending";

        // dd( $applicant);

        $applicant->save();

        if($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $fileName = $applicantion_no.'.'.$request->file('photo')->extension();
            $request->file('photo')->move(public_path('storage/uploads/applicant'), $fileName);
            
            $applicant->passport =  asset('storage/' . Qs::getUploadPath('applicant') . $fileName);
            $applicant->save();
        }

        return response()->json(true);

    }

    
}