<?php

namespace App\Http\Controllers\SupportTeam;


use App\Http\Controllers\Controller;

use App\Helpers\Qs;
use App\Helpers\Pay;
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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Storage;
use PDF;
use DB;


use App\Models\Payment;
use App\Models\PaymentRecord;
use App\Fee;
use App\ApiKeys;
use App\Customer;

use Paystack;
use Alert;
use Redirect;
use Invoice;


class CashierController extends Controller
{

    protected $my_class, $pay, $student, $year;

    public function __construct(MyClassRepo $my_class, PaymentRepo $pay, StudentRepo $student)
    {
        $this->my_class = $my_class;
        $this->pay = $pay;
        $this->year = Qs::getCurrentSession();
        $this->student = $student;

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
        $pr = $inv->get();
        $d['uncleared'] = $pr->where('paid', 0);
        $d['cleared'] = $pr->where('paid', 1);
        return view('pages.parent.payment', $d);
    }


    public function store(Request $request){
        //  dd($request);
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
        if($paymentDetails['status']){

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
        dd($request);

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

}
