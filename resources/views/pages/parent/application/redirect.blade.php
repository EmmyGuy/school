@extends('layouts.master')
@section('page_title', 'My Children')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2" id="side-navbar">
        </div>
        <div class="col-md-6" id="main-container">
            <div class="panel panel-default">
                <div class="page-panel-title">@lang('Payment')
              </div>
                <div class="panel-body">
                    @if (session('status'))
                        <div class="alert alert-{{(\Session::has('error'))?'danger':'success'}}">
                            {{ session('status') }}
                        </div>
                    @endif
                     <?php 

                        // $subActShare = 50000 /  $data['amount'];
                        // // $subActShare = 500;
                        // $charges = $data['amount'] * 0.015 + 100;

                        // $split = [
                        //     "type" => "flat",
                        //     "currency" => "KES",
                        //     "subaccounts" => [
                        //         [ "subaccount" => $data['subAcctCode'], "share" => $subActShare ],
                        //     ],
                        //     "bearer_type" => "account",
                        //     "main_account_share" => $data['amount'] - $subActShare,
                        //     ];
                            
                    ?>
                    
                    <form action="{{route('payments.pay')}}" method="post" id="check-out-form">
                        {{ csrf_field() }}
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h3 class="panel-title">Redirecting to payment gateway...</h3>
                            </div>
                            <input type="hidden" name="email" id="buyer-email"value="{{ $data['email'] }}"> {{-- required --}}
                            <input type="hidden" name="orderID" id="orderID" value="{{ $data['id'] }}" >
                            <input type="hidden" name="amount" value="{{ ($data['amount'])}}" id="amount"> {{-- required in kobo --}}
                            <input type="hidden" name="quantity" value="1">
                            <input type="hidden" name="currency" value="NGN">
                            <input type="hidden" name="reference" id="reference" value="{{ $data['payment_ref'] }}"> {{-- required --}}
                            <input type="hidden" name="key" value="{{ config('paystack.secretKey') }}">
                            <input type="hidden" name="callback_url" id="callback_url" value="{{ route('payments.applicant_pay_callback') }}" >
                           
                            {{ csrf_field() }} {{-- works only when using laravel 5.1, 5.2 --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

</script>
<script>
    jQuery(document).ready(function($){
        var paymentForm = new $("#check-out-form").serialize();
        console.log(paymentForm);
        $("#check-out-form").submit();
    });
</script>

@endsection