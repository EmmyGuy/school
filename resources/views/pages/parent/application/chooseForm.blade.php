@extends('layouts.master')
@section('page_title', 'Pupil Application')
@section('content')
    <div class="card">
        <div class="card-header header-elements-inline">
            <h5 class="card-title"><i class="icon-cash2 mr-2"></i> Pupil Applications</h5>
            {!! Qs::getPanelOptions() !!}
        </div>

        <div class="card-body">
            <!-- <form  > -->
                @csrf
              <div class="row">
                  <div class="col-md-6 offset-md-3">
                      <div class="row">
                          <div class="col-md-10">
                              <div class="form-group">
                                  <label for="my_class_id" class="col-form-label font-weight-bold">Class Applying for:</label>
                                  <select required id="my_class_id" name="my_class_id" class="form-control select">
                                      <option value="">Select Class</option>
                                      <option value=pre_school>Nursery and Primary</option>
                                      <option value=high_school>Starlet High</option>
                                      <!-- @foreach($my_classes as $c)
                                          <option {{ ($selected && $my_class_id == $c->id) ? 'selected' : '' }} value="{{ $c->id }}">{{ $c->name }}</option>
                                      @endforeach -->
                                  </select>
                              </div>
                          </div>

                          <div class="col-md-2 mt-4">
                              <div class="text-right mt-1">
                                  <button  class="btn btn-primary">Submit <i class="icon-paperplane ml-2"></i></button>
                              </div>
                          </div>

                      </div>
                  </div>
              </div>

            <!-- </form> -->
        </div>
    </div>
    @if($selected)
        <div class="card">
            <div class="card-body">
                <table class="table datatable-button-html5-columns">
                    <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Application_No</th>
                        <th>Session </th>
                        <th>Class Applied </th>
                        <th>Payments status</th>
                        <th>Application status</th>

                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($students as $s)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td><img class="rounded-circle" style="height: 40px; width: 40px;" src="{{ $s->passport }}" alt="photo"></td>
                            <td>{{ $s->fullname }}</td>
                            <td>{{ $s->application_no }}</td>
                            <td>{{ $s->session }}</td>
                            <td>{{ $s->class_name }}</td>
                            <td>
                                {{ $s->payment_status }}
                            </td>
                            <td>
                                {{ $s->application_status }}
                            </td>

                            <td class="text-center">
                            <div class="list-icons">
                                <div class="dropdown">
                                    <a href="#" class="list-icons-item" data-toggle="dropdown">
                                        <i class="icon-menu9"></i>
                                    </a>

                                    <div class="dropdown-menu dropdown-menu-left">
                                        <a href="{{ route('students.show', Qs::hash($s->id)) }}" class="dropdown-item"><i class="icon-eye"></i> View Profile</a>
                                        <a data-id="{{ (Qs::hash($s->id)) }}" class="dropdown-item edit_application"><i class="icon-check"></i> Edit</a>
                                        <a  href="{{ route('payments.applicant_payments', Qs::hash($s->id)) }}" class="dropdown-item"><i class="icon-cash"></i> pay now</a>

                                    </div>
                                </div>
                            </div>
                        </td>

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

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

    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>  -->

    <script>
    $(document).ready(function() {
        // alert('here!');
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });


        /* When click detail button */
        $('body').on('click', '.btn-primary', function () {
            
            $('#btn-save').val("create-questionaire");
            $('#userForm').trigger("reset");
            $(".modal-body").html("");
            $('#userCrudModal').html("Process Applicatioin");

            var Id = $('#my_class_id').val();
            var url =  "{!! route('form_show',  ['id' => ':Id']) !!}";
            url = url.replace(':Id', Id);
            console.log(url);

            $.get(url, function (data) {
                $(".modal-body").html(data);
                $('#ajax-crud-modal').modal();

                //used for inline data editing
                // $('.xedit').editable({
                //     url: '{{url("employees/update")}}',
                //     title: 'Update',
                //     success: function (response, newValue) {
                //         // console.log('Updated', response)
                //         swal('Success: record updated successfully!');
                //     }
                // });
            })
        });

        $('body').on('click', '.edit_application', function (e) {
            e.preventDefault();

            $('#btn-save').val("Amit Applicant");
            $('#userForm').trigger("reset");
            $(".modal-body").html("");
            $('#userCrudModal').html("Admit Applicant");

            var Id = $(this).attr("data-id"); ;
            var url =  "{!! route('form_show',  ['id' => ':Id']) !!}";
            url = url.replace(':Id', Id);

            $.get(url, function (data) {
                $(".modal-body").html(data);
                $('#ajax-crud-modal').modal();
            })

        });

        $('body').on('click', '.btn-save', function (e) {
            e.preventDefault();

            var x = document.getElementById("check").required; 

            var data =  $('#ajax_reg').serialize();

            var x = document.getElementById("check");


            if (x.checked == true) {
                document.getElementById("certification").value = true;
            }else{
                document.getElementById("certification").value = false;
            }
            // var oTable = $('#street_table').DataTable();
            console.log(data);
            if($('#ajax_reg').valid()){
                var actionType = $('#btn-save').val();
                $('#btn-save').html('Sending..');

                $.ajax({
                    data: new FormData($('#ajax_reg')[0]),
                    url: '{!! route("pupil_application_save") !!}',
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


