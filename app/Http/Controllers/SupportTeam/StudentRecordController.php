<?php

namespace App\Http\Controllers\SupportTeam;

use App\Helpers\Qs;
use App\Helpers\Mk;
use App\Helpers\Http;

use App\Http\Requests\Student\StudentRecordCreate;
use App\Http\Requests\Student\StudentRecordUpdate;
use App\Repositories\SettingRepo;
use App\Repositories\LocationRepo;
use App\Repositories\MyClassRepo;
use App\Repositories\StudentRepo;
use App\Repositories\UserRepo;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Maatwebsite\Excel\Facades\Excel;

use App\User;

use App\Models\ApplicantRecord;
use App\Models\StudentRecord;

use Datatables;
use DB;

class StudentRecordController extends Controller
{
    protected $loc, $my_class, $user, $student;

   public function __construct(LocationRepo $loc, SettingRepo $setting, MyClassRepo $my_class, UserRepo $user, StudentRepo $student)
   {
       $this->middleware('teamSA', ['only' => ['edit','update', 'reset_pass', 'create', 'store', 'graduated'] ]);
       $this->middleware('super_admin', ['only' => ['destroy',] ]);

        $this->loc = $loc;
        $this->my_class = $my_class;
        $this->user = $user;
        $this->student = $student;
        $this->setting = $setting;

   }

    public function reset_pass($st_id)
    {
        $st_id = Qs::decodeHash($st_id);
        $data['password'] = Hash::make('student');
        $this->user->update($st_id, $data);
        return back()->with('flash_success', __('msg.p_reset'));
    }

    public function create()
    {
        $data['my_classes'] = $this->my_class->all();
        $data['parents'] = $this->user->getUserByType('parent');
        $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();
        return view('pages.support_team.students.add', $data);
    }

    public function store(StudentRecordCreate $req)
    {
       $data =  $req->only(Qs::getUserRecord());
       $sr =  $req->only(Qs::getStudentData());

        $ct = $this->my_class->findTypeByClass($req->my_class_id)->code;
       /* $ct = ($ct == 'J') ? 'JSS' : $ct;
        $ct = ($ct == 'S') ? 'SS' : $ct;*/

        $data['user_type'] = 'student';
        $data['name'] = ucwords($req->name);
        $data['code'] = strtoupper(Str::random(10));
        $data['password'] = Hash::make('student');
        $data['photo'] = Qs::getDefaultUserImage();
        $adm_no = $req->adm_no;
        $data['username'] = strtoupper(Qs::getAppCode().'/'.$ct.'/'.$sr['year_admitted'].'/'.($adm_no ?: mt_rand(1000, 99999)));

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = $sr->user->code . '.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath('student').$data['code'], $f['name']);
            $data['photo'] = asset('storage/' . $f['path']);
        }

        $user = $this->user->create($data); // Create User

        $sr['adm_no'] = $data['username'];
        $sr['user_id'] = $user->id;
        $sr['session'] = Qs::getSetting('current_session');

        $this->student->createRecord($sr); // Create Student
        return Qs::jsonStoreOk();
    }

    public function saveAdmission(StudentRecordCreate $req)
    {
        // dd($req);
       $data =  $req->only(Qs::getUserRecord());
       $sr =  $req->only(Qs::getStudentData());

        $ct = $this->my_class->findTypeByClass($req->my_class_id)->code;
       /* $ct = ($ct == 'J') ? 'JSS' : $ct;
        $ct = ($ct == 'S') ? 'SS' : $ct;*/

        $data['user_type'] = 'student';
        $data['name'] = ucwords($req->name);
        $data['code'] = strtoupper(Str::random(10));
        $data['password'] = Hash::make('student');
        $data['photo'] = $req->photo_url;
        $adm_no = $req->adm_no;
        $data['username'] = strtoupper(Qs::getAppCode().'/'.$ct.'/'.$sr['year_admitted'].'/'.($adm_no ?: mt_rand(1000, 99999)));

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = $sr->user->code . '.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath('student').$data['code'], $f['name']);
            $data['photo'] = asset('storage/' . $f['path']);
        }

        $user = $this->user->create($data); // Create User

        $sr['adm_no'] = $data['username'];
        $sr['user_id'] = $user->id;
        $sr['session'] = Qs::getSetting('current_session');

        try {
            
            $std = $this->student->createRecord($sr); // Create Student;

            $applicant = ApplicantRecord::where('id', $req->applicant_id)->first();
            $applicant->application_status = "admitted";
            $applicant->admission_id  = $std->id;
            $applicant->save();

            //send sms
            $data = Http::get('https://api.ebulksms.com:8080/sendsms?username=eiemmieguy93@gmail.com&apikey=7c0f29d2e47c1b7fd49b8ca0ad0c1c6f4143a97e&sender='.'Starlet'.'&messagetext=Dear '.$applicant->fullname.' you have been offered admission to &flash=0&recipients='.$applicant->phone);
            $posts = json_decode($data->getBody()->getContents());

            return Qs::jsonStoreOk();
        } catch (Throwable $e) {
            report($e);
    
            return response()->json(false);
        }

        return response()->json(true);
    }


    public function importFile()
    {
        return view('pages.student.upload');
    }

        /**
     * Uploads an employee list.
     *
     * @param Request $request The uploaded file
     */
    public function uploadFile(Request $request)
    {
        $file = $request->upload_file;

        // foreach ($files as $file)
        // {
            // Excel::toArray($file, function ($reader) {
            //     $rows = $reader->get(['my_class_id', 'section_id', 'adm_no', 'my_parent_id', 'dorm_id', 'dorm_room_no', 'session', 'house', 'age', 'year_admitted', 'grad', 'grad_date', 'email', 'code', 
            //         'name', 'user_type', 'dob', 'gender', 'phone', 'phone2', 'bg_id', 'state_id', 'lga_id', 'nal_id', 'address', 'remember_token']);
                    // $rows=$reader->get(['faculty_id','department_name', 'department_code']);
                $rows = Excel::toArray([], $request->file('upload_file'));
               
                    foreach ($rows as $results) {

                        for ($i=1; $i <= count($results); $i++) {
                            // $department = new Department;
                            // $department->faculty_id     =  $row->faculty_id;
                            // $department->department_name  =  $row->department_name;
                            // $department->department_code  =  $row->department_code;
                            // $department->save();
                            //  dd($results[0]);
                        // if has work or personal email, create user
                        if ((strlen(trim($results[$i][12])) > 5 )) {

                            // Create user access
                            $user           = new User;
                            $user->name     = $results[$i][13];
                            $user->email    = /*$results[$i][11];*/ strtolower($results[$i][2].'@starletschools.com'); 
                            $user->code     = $results[$i][12];
                            $user->username = strtoupper(Qs::getAppCode().'/'.$results[$i][8].'/'.($results[$i][2] ?: mt_rand(1000, 99999)));
                            $user->user_type = $results[$i][14];
                            $user->dob      = $results[$i][15];
                            $user->gender   = $results[$i][16];
                            $user->photo    = Qs::getDefaultUserImage();
                            $user->phone    = $results[$i][17];
                            $user->state_id = $results[$i][20];
                            $user->nal_id   = $results[$i][22];
                            $user->address  = $results[$i][23];
                            $user->password  = bcrypt('student');
                            $user->save();

                            // $data['user_type'] = 'student';
                            // $data['name'] = ucwords($req->name);
                            // $data['code'] = strtoupper(Str::random(10));
                            // $data['password'] = Hash::make('student');
                            // $data['photo'] = Qs::getDefaultUserImage();
                            // $adm_no = $req->adm_no;
                            // $data['username'] = strtoupper(Qs::getAppCode().'/'.$ct.'/'.$sr['year_admitted'].'/'.($adm_no ?: mt_rand(1000, 99999)));

                            /// Create employee record
                            $attachment                   = new StudentRecord();
                            $attachment->session          = $results[$i][5];
                            $attachment->user_id          = $user->id;
                            $attachment->my_class_id      = $results[$i][0];
                            $attachment->section_id       = $results[$i][1];
                            $attachment->adm_no           = $results[$i][2];
                            $attachment->year_admitted    = $results[$i][8];
                            // $attachment->gender             = ($row->gender == 'male') ? 0: 1;
                            // $attachment->date_of_birth      = ($row->date_of_birth) ? $row->date_of_birth->toDateTimeString() : null;
                            // $attachment->date_of_joining    = ($row->date_of_joining) ? $row->date_of_joining->toDateTimeString() : null;
                            // $attachment->primary_phone      = $row->primary_phone;
                            // $attachment->secondary_phone    = $row->secondary_phone;
                            // $attachment->work_email         = $row->work_email;
                            // $attachment->personal_email     = $row->personal_email;
                            // $attachment->contact_person     = $row->contact_person;
                            // $attachment->contact_person_phone  = $row->contact_person_phone;
                            // $attachment->sss_number         = $row->sss_number;
                            // $attachment->pagibig_number     = $row->pagibig_number;
                            // $attachment->tin_number         = $row->tin_number;
                            // $attachment->philhealth_number  = $row->philhealth_number;
                            // $attachment->civil_status       = $row->civil_status;
                            // $attachment->current_address    = $row->current_address;
                            // $attachment->department_id    = $row->department_id;
                            // $attachment->title_id         = $row->title_id;
                            // $attachment->rank_id    = $row->rank_id;
                            // $attachment->staff_grade_id    = $row->staff_grade_id;
                            // $attachment->salary_sacle_id    = $row->salary_sacle_id;
                            // $attachment->staff_level_id    = $row->staff_level_id;
                            // $attachment->date_of_last_promotion    = $row->date_of_last_promotion;
                            // $attachment->date_confirmed    = $row->date_confirmed;
                            // $attachment->blood_group    = $row->blood_group;
                            // $attachment->employee_type_id    = $row->employee_type_id;
                            // $attachment->ipppis_id    = $row->ipppis_id;
                            // $attachment->user_id            = $user->id;
                            $attachment->save();

                            // Create default role (Employee Role)
                            // $user_roles = new UserRole();
                            // $user_roles->user_id = $user->id;
                            // $user_roles->role_id = 4;
                            // $user_roles->save();
                        }
                    }
                }
            // });
        // }

        \Session::flash('success', ' students details uploaded successfully.');

        return redirect()->back();
    }

    public function listByClass($class_id)
    {
        $data['my_class'] = $mc = $this->my_class->getMC(['id' => $class_id])->first();
        $data['students'] = $this->student->findStudentsByClass($class_id);
        $data['sections'] = $this->my_class->getClassSections($class_id);

        return is_null($mc) ? Qs::goWithDanger() : view('pages.support_team.students.list', $data);
    }

    public function graduated()
    {
        $data['my_classes'] = $this->my_class->all();
        $data['students'] = $this->student->allGradStudents();

        return view('pages.support_team.students.graduated', $data);
    }

    public function not_graduated($sr_id)
    {
        $d['grad'] = 0;
        $d['grad_date'] = NULL;
        $d['session'] = Qs::getSetting('current_session');
        $this->student->updateRecord($sr_id, $d);

        return back()->with('flash_success', __('msg.update_ok'));
    }

    public function show($sr_id)
    {
        $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger();}

        $data['sr'] = $this->student->getRecord(['id' => $sr_id])->first();

        /* Prevent Other Students/Parents from viewing Profile of others */
        if(Auth::user()->id != $data['sr']->user_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild($data['sr']->user_id, Auth::user()->id)){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        return view('pages.support_team.students.show', $data);
    }

    public function edit($sr_id)
    {
        $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger();}

        $data['sr'] = $this->student->getRecord(['id' => $sr_id])->first();
        $data['my_classes'] = $this->my_class->all();
        $data['parents'] = $this->user->getUserByType('parent');
        $data['dorms'] = $this->student->getAllDorms();
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();
        return view('pages.support_team.students.edit', $data);
    }

    public function update(StudentRecordUpdate $req, $sr_id)
    {
        $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger();}

        $sr = $this->student->getRecord(['id' => $sr_id])->first();
        $d =  $req->only(Qs::getUserRecord());
        $d['name'] = ucwords($req->name);

        if($req->hasFile('photo')) {
            $photo = $req->file('photo');
            $f = Qs::getFileMetaData($photo);
            $f['name'] = $sr->user->code . '.' . $f['ext'];
            $f['path'] = $photo->storeAs(Qs::getUploadPath('student'), $f['name']);
            $d['photo'] = asset('storage/' . $f['path']);
        }

        $this->user->update($sr->user->id, $d); // Update User Details

        $srec = $req->only(Qs::getStudentData());

        $this->student->updateRecord($sr_id, $srec); // Update St Rec

        /*** If Class/Section is Changed in Same Year, Delete Marks/ExamRecord of Previous Class/Section ****/
        Mk::deleteOldRecord($sr->user->id, $srec['my_class_id']);

        return Qs::jsonUpdateOk();
    }

    public function destroy($st_id)
    {
        $st_id = Qs::decodeHash($st_id);
        if(!$st_id){return Qs::goWithDanger();}

        $sr = $this->student->getRecord(['user_id' => $st_id])->first();
        $path = Qs::getUploadPath('student').$sr->user->code;
        Storage::exists($path) ? Storage::deleteDirectory($path) : false;
        $this->user->delete($sr->user->id);

        return back()->with('flash_success', __('msg.del_ok'));
    }

    public function processApplication($class_id = NULL)
    {
        $d['my_classes'] = $this->my_class->all();
        $d['current_session'] = 
        $d['selected'] = false;

        if($class_id){

            if($st->count() < 1){
                return Qs::goWithDanger('payments.manage');
            }
            $d['selected'] = true;
            $d['my_class_id'] = $class_id;
        }

        return view('pages.support_team.application.manage', $d);
    }

    public function select_year(Request $req)
    {
        return Qs::goToRoute(['applicants.show', $req->current_session]);
    }

    public function showApplicants($year)
    {
        $d['applicants'] = DB::table( 'applicant_records' )
            ->join('my_classes', 'my_classes.id', '=', 'applicant_records.my_class_id')
            ->where('session', '=', $year)
            ->select('applicant_records.id','applicant_records.application_no', 'applicant_records.fullname',
                    'applicant_records.passport', 'applicant_records.application_status', 'applicant_records.session',
                    'my_classes.name as class_name', 'applicant_records.*',
                    DB::raw('(CASE 
                            WHEN (SELECT count(*) from applicant_payments where applicant_id = applicant_records.id AND status = "paid" ) > 0 THEN "Paid" 
                            ELSE "Pending" 
                            END) AS payment_status'),
                    DB::raw('(CASE 
                                WHEN (SELECT count(*) from student_records where admission_id = student_records.id ) > 0 THEN "Admitted" 
                                ELSE "Pending" 
                                END) AS admission_status'))->get();

        if((count($d) < 1)){
            return Qs::goWithDanger('payments.index');
        }

            // dd($d['applicants']);

        $d['selected'] = true;
        $d['my_classes'] = $this->my_class->all();
        // $d['years'] = $this->pay->getPaymentYears();
        $d['year'] = $year;

        return view('pages.support_team.application.manage', $d);

    }

    public function showApplicant($id)
    {
        $sr_id = $id;
        // $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger();}

        $data['sr'] = ApplicantRecord::where(['applicant_records.id' => $sr_id])
                                      ->join('users', 'users.id', '=', 'applicant_records.my_parent_id')
                                      ->join('lgas', 'lgas.id', '=', 'applicant_records.lga_id')
                                      ->join('blood_groups', 'blood_groups.id', '=', 'applicant_records.bg_id')
                                      ->join('states', 'states.id', '=', 'lgas.state_id')
                                      ->join('nationalities', 'nationalities.id', '=', 'applicant_records.nal_id')
                                      ->join('my_classes', 'my_classes.id', '=', 'applicant_records.my_class_id')
                                      ->select('my_classes.name As class_name','lgas.name As lga', 'applicant_records.*',
                                       'states.name As state', 'users.name As parent_name', 'users.phone As parent_phone',
                                        'blood_groups.name AS blood_gp', 'nationalities.name AS nation')
                                      ->first();
        // dd( $data['sr']);
        /* Prevent Other Students/Parents from viewing Profile of others */
        if(Auth::user()->id != $data['sr']->user_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild($data['sr']->user_id, Auth::user()->id)){
            return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        }

        return view('pages.support_team.application.show', $data);
    }

    public function AdmitApplication($id)
    {
        $sr_id = $id;
        // $sr_id = Qs::decodeHash($sr_id);
        if(!$sr_id){return Qs::goWithDanger();}

        $s = $this->setting->all();
        $d['class_types'] = $this->my_class->getTypes();
        $data['s'] = $s->flatMap(function($s){
            return [$s->type => $s->description];
        });

        $data['applicant'] = ApplicantRecord::where(['applicant_records.id' => $sr_id])
                                      ->join('users', 'users.id', '=', 'applicant_records.my_parent_id')
                                      ->join('lgas', 'lgas.id', '=', 'applicant_records.lga_id')
                                      ->join('blood_groups', 'blood_groups.id', '=', 'applicant_records.bg_id')
                                      ->join('states', 'states.id', '=', 'lgas.state_id')
                                      ->join('nationalities', 'nationalities.id', '=', 'applicant_records.nal_id')
                                      ->join('my_classes', 'my_classes.id', '=', 'applicant_records.my_class_id')
                                      ->select('my_classes.name As class_name','lgas.id As lga_id', 'lgas.name As lga_name', 'applicant_records.*',
                                       'states.id As state_id', 'users.name As parent_name', 'users.phone As parent_phone', 'applicant_records.id As applicant_id',
                                        'blood_groups.name AS blood_gp', 'blood_groups.id AS bg_id', 'nationalities.name AS nation')
                                      ->first();

        $data['my_classes'] = $this->my_class->all();
        $data['dorms'] = $this->student->getAllDorms();
        $data['parents'] = $this->user->getUserByType('parent');
        $data['states'] = $this->loc->getStates();
        $data['nationals'] = $this->loc->getAllNationals();
        // dd( $data['applicant']);

        /* Prevent Other Students/Parents from viewing Profile of others */
        // if(Auth::user()->id != $data['applicant']->user_id && !Qs::userIsTeamSAT() && !Qs::userIsMyChild($data['applicant']->user_id, Auth::user()->id)){
        //     return redirect(route('dashboard'))->with('pop_error', __('msg.denied'));
        // }

        return view('pages.support_team.application.admit', compact('data'))->render();
    }

      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function notification()
    {
        // dd('here');
        $today_date = \Carbon\Carbon::now();
        if(request()->ajax())
        {

            $data = DB::table('student_records')
            ->join('my_classes', 'my_classes.id', '=', 'student_records.my_class_id')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->select('student_records.id As id', 'users.name As full_name', 'my_classes.name As class', 'student_records.adm_no As std_adm_no', 
            );
            // dd(count($data));

            return Datatables::of($data)
                    ->addColumn('id', function($data){
                        $button = $data->id;
                        return $button;
                    })
                    ->filterColumn('full_name', function ($query, $keyword) {
                        $keywords = trim($keyword);
                        $query->whereRaw("CONCAT(fullname) like ?", ["%{$keywords}%"]);
                    })
                    ->make(true);
        }
        
        $s = $this->setting->all();
        $data['my_classes'] = $this->my_class->all();
        $data['s'] = $s->flatMap(function($s){
            return [$s->type => $s->description];
        });
        // dd($data['my_classes']);       
        return view('pages.support_team.students.notification', compact('data'));
    }

    public function getStudents(Request $request)
    {
        // if($request->to){dd($request->to);}
        

        if(request()->ajax())
        {
            
            $data = DB::table('student_records')
            ->join('my_classes', 'my_classes.id', '=', 'student_records.my_class_id')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->select('student_records.id As id', 'users.name As full_name', 'my_classes.name As class', 'student_records.adm_no As std_adm_no', 
            );

            if($request->session){
                $data = $data->where('payment_records.year', "=", $request->session);
                // dd($data);
            }
            if($request->my_class_id){
                $data = $data->where('student_records.my_class_id', "=", $request->my_class_id);
            }
        

            return Datatables::of($data)
            ->addColumn('id', function($data){
                $button = $data->id;
                return $button;
            })
            ->filterColumn('full_name', function ($query, $keyword) {
                $keywords = trim($keyword);
                $query->whereRaw("CONCAT(fullname) like ?", ["%{$keywords}%"]);
            })->make(true);

        }
            
    }

    public function nottifyStudents(Request $request)
    {
        // dd($request->message);
        $count = 0;
        foreach($request->defaulters as $id){
            //get payment_record
            $pr =  DB::table('student_records')
                    ->join('users', 'users.id', '=', 'student_records.my_parent_id')
                    ->select('users.phone', 'users.phone2')
                    ->first();
            if($pr->phone != null || $pr->phone2)
            {
                $data = Http::get('https://api.ebulksms.com:8080/sendsms?username=eiemmieguy93@gmail.com&apikey=7c0f29d2e47c1b7fd49b8ca0ad0c1c6f4143a97e&sender='.'Starlet'.'&messagetext='.$request->message.'&flash=0&recipients='.$pr->phone.','.$pr->phone2);
                $posts = json_decode($data->getBody()->getContents());
            }
            $count++;
        }
        // dd($request->message);

        return json_encode($count);
    }
}
