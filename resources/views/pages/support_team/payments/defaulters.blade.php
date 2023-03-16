@extends('layouts.master')
@section('page_title', 'School Fee Defaulters')
@section('content')

<div class="card">    
    <div class="card-header bg-white header-elements-inline">
        <div class="row">
            <form class action="post">
                <meta name="csrf-token" content="{{ csrf_token() }}" />
                <!-- <span class="panel-title hidden-xs"> Appriasal Lists </span> -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Seesion: <span class="text-danger">*</span></label>
                                <select required name="session" id="session" class="select-search form-control" data-placeholder="select session">
                                    <option value=""></option>
                                    @for($y=date('Y', strtotime('- 5 years')); $y<=date('Y', strtotime('+ 1 years')); $y++)
                                        <option {{ ($data['s']['current_session'] == (($y-=1).'-'.($y+=1))) ? '' : '' }}>{{ ($y-=1).'-'.($y+=1) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="my_class_id">Class: <span class="text-danger">*</span></label>
                                <select onchange="getClassSections(this.value)" data-placeholder="Choose..." required name="my_class_id" id="my_class_id" class="select-search form-control">
                                    <option value=""></option>
                                    @foreach($data['my_classes'] as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- <div class="col-md-6">
                            <div class="form-group">
                                <label>from:</label>
                                <input name="from" id="from" value="{{ $data['applicant']->applicant_dob ?? '' }}" type="date" class="form-control" placeholder="Select Date...">

                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>To:</label>
                                <input name="to" id="to" value="{{ $data['applicant']->applicant_dob ?? '' }}" type="date" class="form-control" placeholder="Select Date...">

                            </div>
                        </div> -->

                        <div class="col-md-2offset-md-6">
                            <div class="text-right mt-1">
                                <!-- <button class="btn btn-primary search">Submit <i class="icon-paperplane ml-2"></i></button> -->
                                <a href="javascript:void(0)" class="btn btn-primary search" id="search">search</a>
                            </div>
                        </div>                  
                        
                    </div>
            </form>
        </div>
    </div>

       
    <div class="card-body">           
        </div class="panel">
        <div class="panel"> 
            <div class="panel-body pn">
                <div class="col-sm-12" >
                    <div class="table-responsive" style="scrollX: true", height: 'auto' ">
                        <table class="table table-bordered table-striped table-responsive   tc-checkbox-1 fs13 table-hover datatable" id="street_table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" name="select_all" value="1" id="checkAll"></th>
                                    <th>adm no</th>
                                    <th>student fullname</th>
                                    <th>payment item title</th>
                                    <th>class of payment</th>
                                    <th>balance</th>
                                    <th>Debt session</th>
                                    <th>payment date</th>
                                    <!-- <th>Action</th> -->
                                    
                                </tr>
                            </thead>
                            <tbody id="street_table">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <button class="btn btn-primary send_sms">send notification <i class="icon-paperplane ml-2"></i></button>
    </div>
                    
</div>

<!-- Modal Start -->
<div class="modal fade" id="ajax-crud-modal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="userCrudModal"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <textarea id="message" name="message" class="mytextarea" value=""></textarea>
                </div>
                
                <button class="btn btn-success send_notification">send notification <i class="icon-paperplane ml-2"></i></button>

            </div>
        </div>

</div>
    <!-- Modal End -->

<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    $(document).ready(function() {
        // alert('here!');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var table = $('#street_table');

        //check all function
        $("#checkAll").click(function(){
            // alert('here');
            $('input:checkbox').not(this).prop('checked', this.checked); //Checks or uncheck All
            
        });

        tinymce.init({
            selector: '.mytextarea',
            height: "360",
        });

        table.dataTable({
            processing: true,
            serverSide: true,
            ajax:{
                url: "{{ route('payments.defaulters')}}"
            },
            buttons: [
                'copy', 'excel','csv','pdf','print'
            ],
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
                {
                    data: 'id', 
                    name: 'id[]'
                },
                {
                    data: 'std_adm_no', 
                    name: 'std_adm_no'
                },
                {
                    data: 'full_name',
                    name: 'full_name',
                    searchable: true
                },
                {
                    data: 'title',
                    name: 'title',
                    searchable: true
                },
                {
                    data: 'ref_no',
                    name: 'ref_no',
                    searchable: false
                },
                {
                    data: 'balance',
                    name: 'balance',
                    searchable: false
                },
                {
                    data: 'year',
                    name: 'year',
                    searchable: false
                },
                {
                    data: 'date_of_pay',
                    name: 'date_of_pay',
                    searchable: false
                },                
            ],
        });

    table.on('draw', function () {
        alert('here');
    });

    $('.send_sms').click(function () {
        $('#btn-save').val("create-questionaire");
        $('#userForm').trigger("reset");
        $('#userCrudModal').html("Compose sms");
        $('#ajax-crud-modal').modal('show');
    });

    $('.send_notification').click(function () {
        var defalut_id = new Array();

        $("input:checked", table.fnGetNodes()).each(function(){
            defalut_id.push($(this).val());
        });

        tinyMCE.triggerSave();
        var message = $('#message').val().replace(/(<([^>]+)>)/gi, "");

        console.log(message);
        $.ajax({
            data: {defaulters: defalut_id, message: message },  
            url: '{!! route("payments.notify_defaulter_via_sms") !!}',
            type: "POST",
        //   dataType: 'json',
          success: function (data) {
            swal(data +' notification sent!');
            // $('#download_invoce').html(data);
          },
          error: function (data) {
            alert('Oops!');

              console.log('Error:', data);
          }
      });
    });


    $('body').on('click', '.search', function () {
        $('#search').html('fetching..');
       $("#street_table").dataTable().fnDestroy();
       
       table.dataTable({
        processing: true,
        serverSide: true,
        // bDestroy: true
        ajax: {
            type: 'POST',
            url: "{!! route('payments.all_defaulters') !!}",
            data(d) {
                d.session = $('#session').val();
                d.my_class_id = $('#my_class_id').val();
                d.from = $('#from').val();
                d.to = $('#to').val();
              	// d.parameter_name_2 = 'value';
                },
            "dataSrc": function ( json ) {
                //Make your callback here.
                $('#search').html('search');

                return json.data;
                }  
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
                {
                    data: 'id', 
                    name: 'id[]'
                },
                {
                    data: 'std_adm_no', 
                    name: 'std_adm_no'
                },
                {
                    data: 'full_name',
                    name: 'full_name',
                    searchable: true
                },
                {
                    data: 'title',
                    name: 'title',
                    searchable: true
                },
                {
                    data: 'ref_no',
                    name: 'ref_no',
                    searchable: false
                },
                {
                    data: 'balance',
                    name: 'balance',
                    searchable: false
                },
                {
                    data: 'year',
                    name: 'year',
                    searchable: false
                },
                {
                    data: 'date_of_pay',
                    name: 'date_of_pay',
                    searchable: false
                },    
            ],
        });
        // $('#search').html('search');
        
    });

    // table.DataTable({
    //     "initComplete": function(){
    //         alert('Data loaded successfully');
    //     }
    // });

});

</script>
@endsection

