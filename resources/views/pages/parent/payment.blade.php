@extends('layouts.master')
@section('page_title', 'My Children')
@section('content')
<style>
/**
 * The CSS shown here will not be introduced in the Quickstart guide, but shows
 * how you can use CSS to style your Element's container.
 */
.StripeElement {
  box-sizing: border-box;

  height: 40px;

  padding: 10px 12px;

  border: 1px solid transparent;
  border-radius: 4px;
  background-color: white;

  box-shadow: 0 1px 3px 0 #e6ebf1;
  -webkit-transition: box-shadow 150ms ease;
  transition: box-shadow 150ms ease;
}

.StripeElement--focus {
  box-shadow: 0 1px 3px 0 #cfd7df;
}

.StripeElement--invalid {
  border-color: #fa755a;
}

.StripeElement--webkit-autofill {
  background-color: #fefde5 !important;
}
</style>
<div class="container-fluid">
    <div class="row">
       
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
                    <form action="{{route('payment.charge')}}" method="post" id="payment-form">
                        {{ csrf_field() }}
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <!-- <h3 class="panel-title">@lang('Enter your credit card information')</h3> -->
                                <h3 class="panel-title">Payment Page</h3>
                            </div>
                            <div class="panel-body">
                              <div class="form-group">
                                <label for="amount">@lang('Pay Fee For')</label>
                                <select class="form-control" name="charge_field" id='charge_field'  onchange="SetAmount()" required>
                                  @foreach ($uncleared as $uc)
                                    <option  value="{{ $uc->balance ?: $uc->payment->amount }}" data-id="{{ $uc->id }}">{{ $uc->balance ?: $uc->payment->amount }} -- {{ $uc->payment->title }}</option>
                                    
                                  @endforeach
                                </select>
                              </div>

                              <input type="hidden" id="paymet_id" name="paymet_id" value="">
                              <input type="hidden" id="email"  name="email" value="{{ $parent_email }}" />

                              <div class="form-group">
                                <div class="input-group">
                                    <input type="checkbox" id='fulpay' name="fulpay" value="" onClick="myFunction()"> want to pay Part? 
                                    <input type="hidden" id="fullpay"  name="fullpay" value="false" />
                                </div>
                              </div>

                              <div class="form-group">
                                  <label for="amount">@lang('Amount')</label>
                                  <div class="input-group">
                                    <input type="number" value="false" class="form-control" id="amount" name="amount" placeholder="Enter amount you want to pay" required >
                                  </div>
                                </div>
                                <br>
                                <label for="card-element"></label>
                                <div id="card-element">
                                <!-- A Stripe Element will be inserted here. -->
                                </div>
                            </div>
                            <div class="panel-footer">
                                <button class="btn btn-sm btn-success proceed-pay" type="submit">@lang('Pay')</button>
                                <button class="btn btn-sm btn-default pull-right" id="get-invoice" type="button">@lang('invoice')</button>
                            </div>
                            <div id="download-invoce">

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    jQuery(document).ready(function($){
        // console.log("here!");
        document.getElementById("fullpay").value = false;
        var amount = document.getElementById("amount");
        amount.readOnly = true;

        document.getElementById('paymet_id').value = $(this).find(':selected').data("id");

      $('#get-invoice').click(function(){
        $.ajax({
          data: $('#payment-form').serialize(),
          url: '{!! route("payments.online_pay") !!}',
          type: "POST",
          dataType: 'json',
          success: function (data) {

            $('#download_invoce').html(data);
          },
          error: function (data) {
            alert('Oops!');

              console.log('Error:', data);
          }
      });
    });
  });

  $('#amount').on('change', function() {
    // alert( this.value );
    if(document.getElementById("charge_field").value < this.value){
      swal('Danger: payable amount cannot be more than charges!');
      this.value = null;
    }
  });


  function myFunction() {
        var x = document.getElementById("fulpay");
        var amount = document.getElementById("amount");
        
        if (x.checked == true) {
          amount.value = '';
          document.getElementById("fullpay").value = true;
          amount.readOnly = false;
        } else {
          amount.value = x.value;
        }

        if (x.checked == false && amount.value == '') {
          document.getElementById("amount").value = x.value;
          amount.readOnly = true;
          document.getElementById("fullpay").value = false;
        }
    } 

    function SetAmount(){

    }

    $("#charge_field").change(function () {
      
    document.getElementById('paymet_id').value = $(this).find(':selected').data("id");
    
});

</script>


@endsection