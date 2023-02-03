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
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PDF;

class MyController extends Controller
{
    protected $student, $pay, $year;
    public function __construct(SettingRepo $setting, MyClassRepo $my_class, PaymentRepo $pay, StudentRepo $student)
    {
        $this->my_class = $my_class;
        $this->pay = $pay;
        $this->year = Qs::getCurrentSession();
        $this->student = $student;
        $this->setting = $setting;
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

        if($class_id){
            $d['students'] = $st = $this->student->getRecord(['my_class_id' => $class_id])->get()->sortBy('user.name');
            if($st->count() < 1){
                return Qs::goWithDanger('payments.manage');
            }
            $d['selected'] = true;
            $d['my_class_id'] = $class_id;
        }

        // return view('hrms.hodRecommendation.appraisal', compact('modaldata'))->render();
        return view('pages.parent.application.chooseForm', $d);
    }

    public function showApplicationForm($id)
    {
         $s = $this->setting->all();

         $d['class_types'] = $this->my_class->getTypes();
         $d['s'] = $s->flatMap(function($s){
            return [$s->type => $s->description];
        });
        // dd( $d['class_types']);

        return view('pages.parent.application.highSchoolForm', compact('d'))->render();
    }

}