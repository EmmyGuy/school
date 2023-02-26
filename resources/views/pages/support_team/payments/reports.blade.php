@extends('layouts.master')
@section('page_title', 'School Fee Reports')
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
                                <select required name="session" id="session" class="select-search form-control" placeholder="select session">
                                    <option value=""></option>
                                    @for($y=date('Y', strtotime('- 5 years')); $y<=date('Y', strtotime('+ 1 years')); $y++)
                                        <option {{ ($data['s']['current_session'] == (($y-=1).'-'.($y+=1))) ? 'selected' : '' }}>{{ ($y-=1).'-'.($y+=1) }}</option>
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

                        <div class="col-md-6">
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
                        </div>

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

       
                                <!-- <br /> -->
    <div class="card-body">                    <!-- </div class="panel">  -->
        </div class="panel">
        <div class="panel"> 
            <div class="panel-body pn">
                <div class="col-sm-12" >
                    <div class="table-responsive" style="scrollX: true", height: 'auto' ">
                        <table class="table table-bordered table-striped table-responsive   tc-checkbox-1 fs13 table-hover datatable" id="street_table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" name="select_all" value="1" id="example-select-all"></th>
                                    <th>adm no</th>
                                    <th>student fullname</th>
                                    <th>payment item title</th>
                                    <th>amount</th>
                                    <th>payment reference</th>
                                    <th>payment session</th>
                                    <th>payment date</th>
                                    
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
                url: "{{ route('payments.view_selected_payments')}}"
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
                {},
                {
                    data: 'std_adm_no', 
                    name: 'std_adm_no'
                },
                {
                    data: 'fullname',
                    name: 'fullname',
                    searchable: true
                },
                {
                    data: 'title',
                    name: 'title',
                    searchable: true
                },
                {
                    data: 'amt_paid',
                    name: 'amt_paid',
                    searchable: false
                },
                {
                    data: 'ref_no',
                    name: 'ref_no',
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
                  
                // {
                //     data: 'action',
                //     name: 'action',
                //     orderable: false,
                //     searchable: false
                // },
                
            ],
        })

        $('#create-new-paymentSetup').click(function () {
            $('#btn-save').val("create-questionaire");
            $('#userForm').trigger("reset");
            $('#userCrudModal').html("Add Department/Unit");
            $('#ajax-crud-modal').modal('show');
        });


    $('body').on('click', '.search', function () {
       $("#street_table").dataTable().fnDestroy();
       $('#street_table').DataTable({
        processing: true,
        serverSide: true,
        // bDestroy: true
        ajax: {
            type: 'POST',
            url: "{!! route('payments.all_fetch') !!}",
            data(d) {
                d.session = $('#session').val();
                d.my_class_id = $('#my_class_id').val();
                d.from = $('#from').val();
                d.to = $('#to').val();
              	// d.parameter_name_2 = 'value';
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
                {},
                {
                    data: 'std_adm_no', 
                    name: 'std_adm_no'
                },
                {
                    data: 'fullname',
                    name: 'fullname',
                    searchable: true
                },
                {
                    data: 'title',
                    name: 'title',
                    searchable: true
                },
                {
                    data: 'amt_paid',
                    name: 'amt_paid',
                    searchable: false
                },
                {
                    data: 'ref_no',
                    name: 'ref_no',
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
   });

});

</script>
@endsection

