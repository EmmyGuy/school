<?php

namespace App\Http\Controllers\SupportTeam;


use App\Http\Controllers\Controller;

use App\Helpers\Qs;
use App\Helpers\Pay;
use App\Helpers\Http;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
//use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use App\Models\Setting;
use App\Repositories\MyClassRepo;
use App\Repositories\PaymentRepo;
use App\Repositories\StudentRepo;
use App\Repositories\SettingRepo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use PDF;
use DB;


use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Models\ApplicantPayment;
use App\User;
use App\Fee;
use App\ApiKeys;
use App\Customer;

use Paystack;
use Alert;
use Redirect;
use Invoice;
use Datatables;


class CashierController extends Controller
{

    protected $my_class, $pay, $student, $year;

    public function __construct(MyClassRepo $my_class, PaymentRepo $pay, StudentRepo $student, SettingRepo $setting)
    {
        $this->my_class = $my_class;
        $this->pay = $pay;
        $this->year = Qs::getCurrentSession();
        $this->student = $student;
        $this->setting = $setting;

        // $this->middleware('teamAccount');
    }


    public function index(){
        $fees_fields = Fee::bySchool(auth()->user()->school_id)->get();
        // var_dump($fees_fields);
        return view('stripe.payment', compact('fees_fields'));
    }

    public function invoice($st_id, $year = NULL)
    {

        if(!$st_id) {return Qs::goWithDanger();}

        $inv = $year ? $this->pay->getAllMyPR($st_id, $year) : $this->pay->getAllMyPR($st_id);

        $d['sr'] = $this->student->findByUserId($st_id)->first();
        $d['parent_email'] = auth()->user()->email;
        $pr = $inv->get();
        $d['uncleared'] =  count($pr->where('balance', '>', 0)) > 0  ? $pr->where('balance', '>', 0) : $pr->where('balance', null);
        $d['cleared'] = $pr->where('paid', 1);
        // dd($d['uncleared']);
        return view('pages.parent.payment', $d);
    }


    public function store(Request $request){

        $paymentRref = Paystack::genTranxRef();
        $amount = $request->amount > 0 ? intval($request->amount * 100) : intval($request->charge_field * 100);
        $chargeField = $request->charge_field;
        
        $payment = PaymentRecord::where('id', $request->paymet_id)->first();
        // dd($payment);
        try {
            $payment->ref_no = $paymentRref;
            $payment->save();
        } catch (\Exception $e) {
            return back()->with(['error'=>true,'status'=>__('Payment Unsuccessful')]);
        }

        $id = $payment->id;

        $data = [
            'email' => $request->email,
            'id'   => $payment->id,
            'amount' => $amount,
            'payment_ref' => $payment->ref_no,
            // 'split_code' => Crypt::decryptString($apiDetail->split_code),
            //'subAcctCode' => $apiDetail->sub_act_code,
        ];

        // dd($data);
        return view('pages.parent.redirect')->with('data', $data);
    }


    public function redirect($id){

        $user = auth()->user();
        $payment = Payment::where('id', $id)->get();

        $payment = $payment[0];
        $data = [
            'email' => $user->email,
            'id'   => $payment->id,
            'amount' => $payment->amount,
            'payment_id' => $payment->payment_id,
            // 'split_code' => Crypt::decryptString($apiDetail->split_code),
            'subAcctCode' => $apiDetail->sub_act_code,
        ];
        return view('stripe.redirect')->with('data', $data);
    }

    /**
     * Redirect the User to Paystack Payment Page
     * @return Url
     */
    public function redirectToGateway()
    {
        // dd('HERE');
        try{
            return Paystack::getAuthorizationUrl()->redirectNow();
        }catch(\Exception $e) {
            return Redirect::back()->withMessage(['msg'=>'The paystack token has expired. Please refresh the page and try again.', 'type'=>'error']);
        }
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function handleGatewayCallback()
    {
        $paymentDetails = Paystack::getPaymentData();
        // dd($paymentDetails);
        $transaction_id     = $paymentDetails['data']['id'];
        $amount_paid            = $paymentDetails['data']['amount'] / 100;
        $transaction_date   = $paymentDetails['data']['transaction_date'];
        $transaction_status = $paymentDetails['data']['status'];
        $transaction_ref    = $paymentDetails['data']['reference'];
        $client_auth_code   = $paymentDetails['data']['authorization']['authorization_code'];

        // $pr = $this->pay->findRecord($pr_id);
        if($paymentDetails['status'])
        {
            $pr = PaymentRecord::where('ref_no', '=', $transaction_ref)->first();
            $req = Payment::where('id', '=', $pr->payment_id)->first();

            $payment = $this->pay->find($pr->payment_id);
            $d['amt_paid'] = $amt_p = $pr->amt_paid + $amount_paid;
            $d['balance'] = $bal = $payment->amount - $amt_p;
            $d['paid'] = $bal < 1 ? 1 : 0;

            $this->pay->updateRecord($pr->id, $d);

            $d2['amt_paid'] = $amount_paid;
            $d2['balance']  = $bal;
            $d2['pr_id']    = $pr->id;
            $d2['year']     = $this->year;

            $this->pay->createReceipt($d2);
            // dd($pr->student_id);
            // return \Redirect::route('child.payments', $pr->student_id);
            return Qs::goToRoute(['payments.child_payments', QS::hash($pr->student_id)]);

        }
        dd('Error!!!');
        
        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
    }

    /**
     * Obtain Paystack payment information
     * @return void
     */
    public function applicantHandleGatewayCallback()
    {
        $paymentDetails = Paystack::getPaymentData();
        //dd($paymentDetails);
        $transaction_id     = $paymentDetails['data']['id'];
        $amount_paid            = $paymentDetails['data']['amount'] / 100;
        $transaction_date   = $paymentDetails['data']['transaction_date'];
        $transaction_status = $paymentDetails['data']['status'];
        $transaction_ref    = $paymentDetails['data']['reference'];
        $client_auth_code   = $paymentDetails['data']['authorization']['authorization_code'];

        // $pr = $this->pay->findRecord($pr_id);
        if($paymentDetails['status'])
        {
            $pr = ApplicantPayment::where('ref_num', '=', $transaction_ref)->first();
            // dd($pr);
            $applicantion =  DB::table('applications')->where('session', $pr->session)->first();

            $pr->status = "paid";
            $pr->save();

            $d['amt_paid'] = $amt_p = $amount_paid;
            $d['balance'] = $bal = 0;
            $d['paid'] = $bal < 1 ? 1 : 0;

            $d2['amt_paid'] = $amount_paid;
            $d2['balance']  = $bal;
            $d2['pr_id']    = $pr->id;
            $d2['year']     = $pr->created_at;

            //$this->pay->createReceipt($d2);

            return Qs::goToRoute(['select_class']);

        }
        dd('Error!!!');
        
        // Now you have the payment details,
        // you can store the authorization_code in your db to allow for recurrent subscriptions
        // you can then redirect or do whatever you want
    }

    public function myChildPayments($st_id, $year = NULL)
    {
        
        if(!$st_id) {return Qs::goWithDanger();}


        $inv = $year ? $this->pay->getAllMyPR($st_id, $year) : $this->pay->getAllMyPR($st_id);

        $d['sr'] = $this->student->findByUserId($st_id)->first();
        // dd($d['sr']);
        $pr = $inv->get();
        $d['uncleared'] = $pr->where('paid', 0);
        $d['cleared'] = $pr->where('paid', 1);

        return view('pages.support_team.payments.childInvoice', $d);
    }
    
    public function receipts($pr_id)
    {
        if(!$pr_id) {return Qs::goWithDanger();}

        try {
             $d['pr'] = $pr = $this->pay->getRecord(['id' => $pr_id])->with('receipt')->first();
        } catch (ModelNotFoundException $ex) {
            return back()->with('flash_danger', __('msg.rnf'));
        }
        $d['receipts'] = $pr->receipt;
        $d['payment'] = $pr->payment;
        $d['sr'] = $this->student->findByUserId($pr->student_id)->first();
        $d['s'] = Setting::all()->flatMap(function($s){
            return [$s->type => $s->description];
        });

        return view('pages.support_team.payments.receipt', $d);
    }

    public function applicantPayments($st_id, $year = NULL)
    {
        $user = auth()->user();
        $applicant =  DB::table('applicant_records')->where('id', $st_id)->first();
        $applicantion =  DB::table('applications')->where('session', $applicant->session)->first(); //sesstion = section_id = 1 for primary; 2 for secondary

        if($applicantion == null){
            // return back()->with(['error'=>true,'status'=>__('Payment has not been set for this application')]);
            return redirect()->route('select_class')->with('flash_danger', __('msg.srnf'));
        }
        //check for pending payment

        $payment = DB::table('applicant_payments')
                       ->where('session', $applicant->session)
                       ->where('applicant_id', $applicant->id)
                       ->first();
        // dd($payment);
        if($payment == null){

            $payment = new ApplicantPayment();

            $payment->session            = $applicant->session;
            $payment->name               = "application_pay";
            $payment->applicant_id       = $applicant->id;
            $payment->application_amount = $applicantion->amount;
            $payment->status             = "pending";
            $payment->ref_num = Paystack::genTranxRef();

            try {
                $payment->ref_num = Paystack::genTranxRef();
                $payment->save();

            } catch (\Exception $e) {
                return back()->with(['error'=>true,'status'=>__('Payment Unsuccessful')]);
            }
        }

        if($payment->status != "pending"){
            // dd('here');
            return redirect()->back()->with('success', 'Updated successfully!');
            return back()->with(['error'=>true,'status'=>__('Payment Already made')]);
            return redirect()->route('pupil_application')->with('flash_danger', __('msg.srnf'));

        }

        $data = [
            'email' => $user->email,
            'id'   => $payment->id,
            'amount' => $payment->application_amount * 100,
            'payment_ref' => $payment->ref_num,
            'callback_url'    => "",   //used to deifferentiate payments so as to act appropristely on callback
            // 'split_code' => Crypt::decryptString($apiDetail->split_code),
            //'subAcctCode' => $apiDetail->sub_act_code,
        ];

        // dd($data);
        return view('pages.parent.application.redirect')->with('data', $data);
    }

    public function setEnvironmentValue($envKey, $envValue)
    {

        $path = base_path('.env');
        var_dump(env($envKey));
        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                $envKey.'='.env($envKey), $envKey.'='.$envValue, file_get_contents($path)
            ));
        }
    }

    public function getInvoice(Request $request)
    {
        // return response()->json($request);
        // dd($request);

        $user = auth()->user();
        $splitName = explode(' ', $user->name, 2);
        $apiDetail = ApiKeys::where('school_id', $user->school_id)->first();

        //Decrypt and update environment variable with keys
        $publicKey = Crypt::decryptString($apiDetail->public);
        $privateKey = Crypt::decryptString($apiDetail->private);

        $this->setEnvironmentValue('PAYSTACK_PUBLIC_KEY', $publicKey);
        $this->setEnvironmentValue('PAYSTACK_SECRET_KEY', $privateKey);
        $secret = config('paystack.secretKey');

        //query paystack api keys from db for auth user
        if($apiDetail == null){
            return back()->with(['error'=>true,'status'=>__('Please contact CacTus Analytics for Payment Setup!')]);
        }

        //check if customer exist: No, create else jus get create invoice
        $customerCheck = Customer::where('user_id', $user->id)->first();

        if($customerCheck == null){
            $url = "https://api.paystack.co/customer";


            $fields = [
                "email"      => $user->email,
                "first_name" => $splitName[0],
                "last_name"  => $splitName[1],
                "phone"      => $user->phone_number,
            ];

            $fields_string = http_build_query($fields);
            //open connection
            $ch = curl_init();

            //set the url, number of POST vars, POST data
            curl_setopt($ch,CURLOPT_URL, $url);
            curl_setopt($ch,CURLOPT_POST, true);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $secret"]);

            //So that curl_exec returns the contents of the cURL; rather than echoing it
            curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);

            //execute post
            $result = curl_exec($ch);

            // var_dump($result);
            //return response()->json($result);
            if($result->status == true){
                $newCustomer = new Customer;
                $newCustomer->user_id = auth()->user()->id;
                $newCustomer->customer_code = $result->data->customer_code;

                try {
                    $newCustomer->save();
                } catch (\Exception $e) {
                    return response()->json($e);
                }
            }
        }

        $invoiceUrl = "https://api.paystack.co/paymentrequest";

        $fields = [
            "description" => "Shool Fee Invoice",
            "line_items"=> [
            ["name" => "Shool Fee Invoice", "amount" => $request->amount * 100],
            ],
            "tax" => [
            ["name" => "VAT", "amount" => 100 * 100]
            ],
            "customer" => $customerCheck->customer_code,
            "due_date" => "2020-07-08"
            ];
            // return response()->json($fields);

        $fields_string = http_build_query($fields);
        //open connection
        $ch = curl_init();

        //set the url, number of POST vars, POST data
        curl_setopt($ch,CURLOPT_URL, $invoiceUrl);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $secret"]);

        //So that curl_exec returns the contents of the cURL; rather than echoing it
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        //execute post
        $resultCustomerCreate = curl_exec($ch);
        $resultCustomerCreate = json_decode($resultCustomerCreate);
        //var_dump($resultCustomerCreate);
        if($resultCustomerCreate->status == true){

            $client = new Buyer([
                'name'          => $user->name,
                'custom_fields' => [
                    'email' => $user->email,
                ],
            ]);

            $item = (new InvoiceItem())->title('Service 1')->pricePerUnit(2);

            $invoice = Invoice::make()
                ->buyer($client)
                // ->discountByPercent(10)
                ->taxRate(100)
                //->shipping(1.99)
                ->addItem($item);

            $url =  $invoice->url();
            $return_array = compact('url');
            return json_encode($return_array);

        }else {
            $message = "Oops! something went wrong!!";
            return json_encode($message);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function paymentReports()
    {
        $today_date = \Carbon\Carbon::now();
        if(request()->ajax())
        {

            $data = DB::table('receipts')
            ->join('payment_records', 'payment_records.id', '=', 'receipts.pr_id')
            ->join('payments', 'payments.id', '=', 'payment_records.payment_id')
            ->join('student_records', 'student_records.user_id', '=', 'payment_records.student_id')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->select('users.name As fullname','payments.title As title', 'payments.ref_no As ref_no','payments.description','payments.year As year',
                        'receipts.amt_paid As amt_paid', 'receipts.created_at As date_of_pay', 'student_records.adm_no As std_adm_no', 
            
            );
            // dd(count($data));

            return Datatables::of($data)
                    // ->addColumn('action', function($data){
                    //     $button = '<button type="button" name="detail" id="'.$data->id.'" data-id="'.$data->id. '" class="detail btn btn-success btn-sm">Detail</button>';
                    //     return $button;
                    // })
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
        return view('pages.support_team.payments.reports', compact('data'));
    }

    public function AdminOfficeIindex()
    {
        // dd(Auth::user()->isAdmin);
        if(request()->ajax())
        {
           
            if(request()->ajax())
            {

            }
        }
        return view('pages.support_team.payments.reports');
    }

    public function getPaymentReports(Request $request)
    {
        // if($request->to){dd($request->to);}
        

        if(request()->ajax())
        {
            
            $data = DB::table('receipts')
                ->join('payment_records', 'payment_records.id', '=', 'receipts.pr_id')
                ->join('payments', 'payments.id', '=', 'payment_records.payment_id')
                ->join('student_records', 'student_records.user_id', '=', 'payment_records.student_id')
                ->join('users', 'users.id', '=', 'student_records.user_id')
                // ->where('payment_records.year', "=", $request->session)
                ->select('users.name As fullname','payments.title As title', 'payments.ref_no As ref_no','payments.description','payments.year As year',
                        'receipts.amt_paid As amt_paid', 'receipts.created_at As date_of_pay', 'student_records.adm_no As std_adm_no', 
                );

                if($request->session){
                    $data = $data->where('payment_records.year', "=", $request->session);
                }
                if($request->my_class_id){
                    $data = $data->where('student_records.my_class_id', "=", $request->my_class_id);
                }

                if($request->from){
                    $data = $data->where('receipts.created_at', ">=", $request->from)->where('receipts.created_at', "<=", date('Y-m-d'));
                }

                if($request->from && $request->to){
                    $data = $data->where('receipts.created_at', ">=", $request->from)->where('receipts.created_at', "<=", $request->to);
                }
            

            return Datatables::of($data)
                ->filterColumn('full_name', function ($query, $keyword) {
                    $keywords = trim($keyword);
                    $query->whereRaw("CONCAT(fullname) like ?", ["%{$keywords}%"]);
                })->make(true);

                
            
        }
            
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function defaulters()
    {
        // dd('here');
        $today_date = \Carbon\Carbon::now();
        if(request()->ajax())
        {

            $data = DB::table('payment_records')
            // ->join('payment_records', 'payment_records.id', '=', 'receipts.pr_id')
            ->join('payments', 'payments.id', '=', 'payment_records.payment_id')
            ->join('student_records', 'student_records.user_id', '=', 'payment_records.student_id')
            ->join('my_classes', 'my_classes.id', '=', 'student_records.my_class_id')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->where('payment_records.paid', 0)
            ->select('payment_records.id As id', 'users.name As full_name','payments.title As title', 'my_classes.name As ref_no','payments.description','payments.year As year',
                    'payment_records.balance As balance', 'payment_records.created_at As date_of_pay','student_records.adm_no As std_adm_no', 
            
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
        return view('pages.support_team.payments.defaulters', compact('data'));
    }

    public function getDefaulterReports(Request $request)
    {
        // if($request->to){dd($request->to);}
        

        if(request()->ajax())
        {
            
            $data = DB::table('payment_records')
            // ->join('payment_records', 'payment_records.id', '=', 'receipts.pr_id')
            ->join('payments', 'payments.id', '=', 'payment_records.payment_id')
            ->join('student_records', 'student_records.user_id', '=', 'payment_records.student_id')
            ->join('my_classes', 'my_classes.id', '=', 'student_records.my_class_id')
            ->join('users', 'users.id', '=', 'student_records.user_id')
            ->where('payment_records.paid', 0)
            ->select('payment_records.id As id', 'users.name As full_name','payments.title As title', 'my_classes.name As ref_no','payments.description','payments.year As year',
                    'payment_records.balance As balance', 'payment_records.created_at As date_of_pay','student_records.adm_no As std_adm_no', 
            
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

    public function nottifyDefaulters(Request $request)
    {
        // dd($request->message);
        $count = 0;
        foreach($request->defaulters as $id){
            //get payment_record
            $pr =  DB::table('payment_records')
                    ->join('payments', 'payments.id', '=', 'payment_records.payment_id')
                    ->join('student_records', 'student_records.user_id', '=', 'payment_records.student_id')
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
