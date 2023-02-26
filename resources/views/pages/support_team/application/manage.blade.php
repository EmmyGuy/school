@extends('layouts.master')
@section('page_title', 'Manage Applications')
@section('content')

    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-cash2 mr-2"></i> Select year</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <form method="post" action="{{ route('application.select_year') }}">
                @csrf
                <div class="row">
                    <div class="col-md-6 offset-md-3">
                        <div class="row">
                            <div class="col-md-10">
                                <div class="form-group">
                                    <label for="year" class="col-form-label font-weight-bold">Select Year <span class="text-danger">*</span></label>
                                    <select data-placeholder="Choose..." required name="current_session" id="current_session" class="select-search form-control">
                                        <option value=""></option>
                                        @for($y=date('Y', strtotime('- 3 years')); $y<=date('Y', strtotime('+ 10 years')); $y++)
                                            <option >{{ ($y-=1).'-'.($y+=1) }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 mt-4">
                                <div class="text-right mt-1">
                                    <button type="submit" class="btn btn-primary">Submit <i class="icon-paperplane ml-2"></i></button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

@if($selected)
    <div class="card">
        <div class="card-header header-elements-inline">
            <h6 class="card-title">Manage Applicants for {{ $year }} Session</h6>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-highlight">
                <li class="nav-item"><a href="#all-payments" class="nav-link active" data-toggle="tab">All Classes</a></li>
                <li class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">Class Payments</a>
                    <div class="dropdown-menu dropdown-menu-right">
                        @foreach($my_classes as $mc)
                            <a href="#pc-{{ $mc->id }}" class="dropdown-item" data-toggle="tab">{{ $mc->name }}</a>
                        @endforeach
                    </div>
                </li>
            </ul>

            <div class="tab-content">
                    <div class="tab-pane fade show active" id="all-payments">
                        <table class="table datatable-button-html5-columns">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Application no</th>
                                <th>image</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Class</th>
                                <!-- <th>DoB</th> -->
                                <th>payment status</th>
                                <th>Schedule</th>
                                <th>immunization</th>
                                <th>allergies</th>
                                <th>Admission status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($applicants as $p)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $p->application_no }}</td>
                                    <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $p->passport }}" alt="photo"></td>
                                    <td>{{ $p->fullname }}</td>
                                    <td>{{ $p->gender }}</td>
                                    <td>{{ $p->class_name }}</td>
                                    <!-- <td>{{ $p->dob }}</td> -->
                                    <td>{{ $p->payment_status }}</td>
                                    <td>{{ $p->applicant_schedule }}</td>
                                    <td>{{ implode(';', json_decode($p->immunization)) }}</td>
                                    <td>{{ json_decode($p->allergies) }}</td>
                                    <td style="color:red">{{ $p->admission_status }}</td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                            <div class="dropdown">
                                                <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                    <i class="icon-menu9"></i>
                                                </a>

                                                <div class="dropdown-menu dropdown-menu-left">
                                                <a href="{{ route('applicant_show', Qs::hash($p->id)) }}" class="dropdown-item"><i class="icon-eye"></i> View Profile</a>
                                                    {{--Edit--}}
                                                <a data-id="{{ (Qs::hash($p->id)) }}" class="dropdown-item admit-application"><i class="icon-pencil"></i> admit</a>
                                                    {{--Delete--}}
                                                    <!-- <a id="{{ $p->id }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a> -->
                                                    <!-- <form method="post" id="item-delete-{{ $p->id }}" action="{{ route('payments.destroy', $p->id) }}" class="hidden">@csrf @method('delete')</form> -->

                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                @foreach($my_classes as $mc)
                    <div class="tab-pane fade" id="pc-{{ $mc->id }}">
                        <table class="table datatable-button-html5-columns">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Application no</th>
                                <th>image</th>
                                <th>Name</th>
                                <th>Gender</th>
                                <th>Class</th>
                                <!-- <th>DoB</th> -->
                                <th>payment status</th>
                                <th>Schedule</th>
                                <th>immunization</th>
                                <th>allergies</th>
                                <th>Admission status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($applicants->where('my_class_id', $mc->id) as $p)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $p->application_no }}</td>
                                    <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $p->passport }}" alt="photo"></td>
                                    <td>{{ $p->fullname }}</td>
                                    <td>{{ $p->gender }}</td>
                                    <td>{{ $p->class_name }}</td>
                                    <!-- <td>{{ $p->dob }}</td> -->
                                    <td>{{ $p->payment_status }}</td>
                                    <td>{{ $p->applicant_schedule }}</td>
                                    <td>{{ implode(';', json_decode($p->immunization)) }}</td>
                                    <td>{{ json_decode($p->allergies) }}</td>
                                    <td>{{ $p->admission_status }}</td>
                                    <td class="text-center">
                                        <div class="list-icons">
                                                <div class="dropdown">
                                                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                                                        <i class="icon-menu9"></i>
                                                    </a>

                                                    <div class="dropdown-menu dropdown-menu-left">
                                                    <a href="{{ route('applicant_show', Qs::hash($p->id)) }}" class="dropdown-item"><i class="icon-eye"></i> View Profile</a>
                                                        {{--Edit--}}
                                                    <a data-id="{{ (Qs::hash($p->id)) }}" class="dropdown-item admit-application"><i class="icon-pencil"></i> admit</a>
                                                        {{--Delete--}}
                                                        <!-- <a id="{{ $p->id }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item"><i class="icon-trash"></i> Delete</a> -->
                                                        <!-- <form method="post" id="item-delete-{{ $p->id }}" action="{{ route('payments.destroy', $p->id) }}" class="hidden">@csrf @method('delete')</form> -->

                                                    </div>
                                                </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                    </div>
                    @endforeach
            </div>
        </div>
    </div>
    @endif

    {{--Payments List Ends--}}

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
                
                </div>

            </div>
        </div>
        </div>
    <!-- Modal End -->

<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>   -->

<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        /* When click edit button */
        $('body').on('click', '.admit-application', function () {

        var Id = $(this).data('id');
        var url =  "{!! route('applicant_admit',  ['id' => ':Id']) !!}";
        url = url.replace(':Id', Id);

            $.get(url, function (data) {
                console.log(data);
                $(".modal-body").html(data);
                $('#ajax-crud-modal').modal();
            })
        });


        $('body').on('click', '.btn-save', function (e) {
            e.preventDefault();

            var data =  $('#ajax_reg').serialize();

            // var oTable = $('#street_table').DataTable();
            console.log(data);
            if($('#ajax_reg').valid()){
                var actionType = $('#btn-save').val();
                $('#btn-save').html('Sending..');

                $.ajax({
                    data: new FormData($('#ajax_reg')[0]),
                    url: '{!! route("applicant_admition_save") !!}',
                    type: "POST",
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        if(data)
                        {
                            swal({
                                title: "Good job!",
                                text: "Record Added/Edited Successfully!",
                                icon: "success",
                            });
                        $('#userForm').trigger("reset");
                        $('#ajax-crud-modal').modal('hide');
                        $('#btn-save').html('Save Changes');
                        }
                        window.reload();
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

@endsection
