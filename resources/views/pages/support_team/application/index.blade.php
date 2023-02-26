@extends('layouts.master')
@section('page_title', 'Application')
@section('content')

<style>
    .error {
        color: #F00;
        background-color: #FFF;
        }
</style>
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- <div class="content-wrapper"> -->
    <!-- Content Header (Page header) -->
    <div class="content">
    <header id="topbar" class="alt">
        <div class="topbar-left">
            <ol class="breadcrumb">
                <li class="breadcrumb-icon">
                    <a href="/dashboard">
                        <span class="fa fa-home"></span>
                    </a>
                </li>
                <li class="breadcrumb-active">
                    <a href="/dashboard"> Dashboard /</a>
                </li>
                <li class="breadcrumb-link">
                    <a href=""> Applications /</a>
                </li>
                <li class="breadcrumb-current-item">Application Listings </li>
            </ol>
        </div>
    </header>

    <section  id="content" class="table-layout animated fadeIn">
        <div class="chute chute-center">
            <!-- Default box -->
            <div class="row">
                <div class="col-xs-12">
                    <div class="box box-success">
                        <div class="panel">
                            <div class="row">
                                <div class="box-tools pull-right offset-11">
                                    <a href="javascript:void(0)" class="btn btn-primary mb-2" id="create-new-paymentSetup">Add Appraisal</a>
                                </div>
                            </div>
                        </div class="panel">
                        <div class="panel"> 
                            <div class="panel-body pn">
                                <div class="col-sm-12" >
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-responsive allcp-form theme-warning tc-checkbox-1 fs13 table-hover datatable" id="street_table">
                                            <thead>
                                        <tr>
                                            <th><input type="checkbox" name="select_all" value="1" id="example-select-all"></th>
                                            <th>name</th>
                                            <th>session </th>
                                            <th>amount</th>
                                            <th>status</th>
                                            <th>opening date</th>
                                            <th>closing date</th>
                                            <th>applicate category</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="street_table">
                                
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- Modal Start -->
<div class="modal fade" id="ajax-crud-modal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title" id="userCrudModal"></h4>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="userForm" name="userForm" class="form-horizontal" >
                <input name="_token" value="{{ csrf_token() }}" type="hidden">
               <input type="hidden" name="id" id="id" class="street_hidden_id">

               <div class="form-group" >
                    <label for="questionaire" class="col-6 col-md-4">name</label>
                    <div class="col-sm-12">
                        <input type="text" class="form-control" id="name" name="name" value="" required="">
                    </div>
                </div>


               <div class="form-group col-sm-12" >
                    <label for="questionaire" class="col-6 col-md-4">Session</label>
                    <div class="id_100">
                        <select data-placeholder="Choose..." required name="current_session" id="current_session" class="form-control">
                            <option value=""></option>
                            @for($y=date('Y', strtotime('- 3 years')); $y<=date('Y', strtotime('+ 10 years')); $y++)
                                <option >{{ ($y-=1).'-'.($y+=1) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-6 col-md-4">amount</label>
                    <div class="col-sm-12">
                        <input type="number" class="form-control" id="amount" name="amount" placeholder="1500" value="" required="">
                    </div>
                </div>

                <div class="form-group col-sm-12">
                    <label class="col-6 col-md-4">status</label>
                    <div class="id_300">
                        <select required id="status" name="status" class="form-control">
                            <option value="">Select status</option>
                            <option value=open>open</option>
                            <option value=closed>closed</option>
                        </select>                    
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-6 col-md-4" >openning Date</label>
                    <div class="col-sm-12">
                        <input type="date" class="form-control" id="openning_date" name="openning_date" data-date-format="YYYY MMMM DD" value="" required="">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-6 col-md-4">closing Date</label>
                    <div class="col-sm-12">
                        <input type="date" class="form-control" id="closing_date" name="closing_date" placeholder="01/01/2022" value="" required="">
                    </div>
                </div>

                <div class="form-group col-sm-12">
                    <label class="col-6 col-md-4">application categoty</label>
                    <div class="id_200">
                        <select required id="application_category" name="application_category" class="form-control">
                            <option value="">Select categoty</option>
                            <option value=pre_school>Nursery and Primary</option>
                            <option value=high_school>Starlet High</option>
                        </select> 
                    </div>
                </div>


                <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="btn-save" value="send">Save changes
            </button>
        </div>
            </form>
        </div>

    </div>
  </div>
</div>
<!-- Modal End -->

@endsection

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>  

<script>
    $(document).ready(function() {
    // alert('here!');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#street_table').DataTable({
        processing: true,
        serverSide: true,
        ajax:{
            url: "{{ route('application_mgt.index')}}"
        },
        columnDefs: [{
         'targets': 0,
         'searchable': false,
         'orderable': false,
         'className': 'dt-body-center',
         'render': function (data, type, full, meta){
             return '<input type="checkbox" name="id[]" value="' + $('<div/>').text(data).html() + '">';
         }
      }],
        columns:[
            {},
            {
                data: 'name', 
                name: 'name'
            },
            {
                data: 'session',
                name: 'session'
            },
            {
                data: 'amount',
                name: 'amount',
                searchable: false
            },
            {
                data: 'status',
                name: 'status',
                searchable: false
            },
            {
                data: 'openning_date',
                name: 'openning_date',
                searchable: false
            },
            {
                data: 'closing_date',
                name: 'closing_date',
                searchable: false
            },
            {
                data: 'applicant_type',
                name: 'applicant_type',
                searchable: false
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            },
            
        ]
    });

    $('#ajax-crud-modal').on('hidden.bs.modal', function(e) {
        $(this).find('form').trigger('reset');
        $("#userForm").trigger("reset");
    });

    $('#create-new-paymentSetup').click(function () {
        $('#btn-save').val("create-questionaire");
        $('#userForm').trigger("reset");
        $('#userCrudModal').html("Add Application");
        $('#ajax-crud-modal').modal('show');
    });

   /* When click edit button */
    $('body').on('click', '.edit-application', function () {
       
        var Id = $(this).data('id');
        var url =  "{!! route('application_mgt.edit',  ['application_mgt' => ':Id']) !!}";
        url = url.replace(':Id', Id);

        $.get(url, function (data) {
            console.log(data.openning_date  );
            $('#userCrudModal').html("Edit Application");
            $('#btn-save').val("edit-application");
            $('#ajax-crud-modal').modal('show');
            $('#id').val(data.id);
            $('#name').val(data.name); //val(data.staff_deadline.toIsoString().substring(0,10));
            $('#amount').val(data.amount);
            $('#openning_date').attr('value', data.openning_date);
            $('#closing_date').attr('value', data.closing_date);

            $("div.id_100 select").val(data.session);
            $("div.id_200 select").val(data.applicant_type);
            $("div.id_300 select").val(data.status);
      })
   });

   //delete department
    $('body').on('click', '.delete', function () {
        var Id = $(this).data('id');
        var url =  "{!! route('application_mgt.destroy',  ['application_mgt' => ':Id']) !!}";
        url = url.replace(':Id', Id);
        var oTable = $('#street_table').DataTable();
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this Record!",
            icon: "warning",
            buttons: [
                'No, cancel it!',
                'Yes, I am sure!'
            ],
            dangerMode: true,
            }).then(function(isConfirm) {
            if (isConfirm) {
                swal({
                title: 'WARNING!',
                text: 'Record will be deleted!!!',
                icon: 'info'
                }).then(function() {
                // form.submit(); // <--- submit form programmatically
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                    $.ajax({
                        type: "DELETE",
                        url: url,
                        success: function (data) {
                           if(data.message){
                            oTable.ajax.reload();
                            swal("Good", "Record Deleted Successfully!", "success");
                           }
                           else{
                            swal("Ooops", "Something went wrong, try again", "error");
                           }
                        },
                        error: function (data) {
                            console.log('Error:', data);
                        }
                    });
                });
            } else {
                swal("Cancelled", "Your Record is Safe :)", "error");
            }
        })

    });

    $('#btn-save').click(function(){
        var data =  $('#userForm').serialize();
        var oTable = $('#street_table').DataTable();
        console.log(data);
        if($('#userForm').valid()){
            var actionType = $('#btn-save').val();
            $('#btn-save').html('Sending..');

            $.ajax({
                data: $('#userForm').serialize(),
                url: '{!! route("application_mgt.store") !!}',
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    //console.log(data);
                    oTable.ajax.reload();
                    swal({
                        title: "Good job!",
                        text: "Record Added/Edited Successfully!",
                        icon: "success",
                    });
                    $(this).find('form').trigger('reset');
                    $('#ajax-crud-modal').modal('hide');
                    $('#btn-save').html('Save Changes');

                },
                error: function (data) {
                    console.log('Error:', data);
                    $('#btn-save').html('Save Changes');
                }
            });
        }else{
            // alert('invalid!');
        }
    });

});

</script>
