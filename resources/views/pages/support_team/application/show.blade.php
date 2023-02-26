@extends('layouts.master')
@section('page_title', 'Applicant Profile - '.$sr->fullname)
@section('content')
<div class="row">
    <div class="col-md-3 text-center">
        <div class="card">
            <div class="card-body">
                <img style="width: 90%; height:90%" src="{{ $sr->passport }}" alt="photo" class="rounded-circle">
                <br>
                <h3 class="mt-3">{{ $sr->fullname }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs nav-tabs-highlight">
                    <li class="nav-item">
                        <a href="#" class="nav-link active">{{ $sr->fullname }}</a>
                    </li>
                </ul>

                <div class="tab-content">
                    {{--Basic Info--}}
                    <div class="tab-pane fade show active" id="basic-info">
                        <table class="table table-bordered">
                            <tbody>
                            <tr>
                                <td class="font-weight-bold">Name</td>
                                <td>{{ $sr->fullname }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Application NO</td>
                                <td>{{ $sr->application_no }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Class Applied</td>
                                <td>{{ $sr->class_name }}</td>
                            </tr>
                            @if($sr->my_parent_id)
                                <tr>
                                    <td class="font-weight-bold">Parent Name</td>
                                    <td>
                                        <span><a target="_blank" href="{{ route('users.show', Qs::hash($sr->my_parent_id)) }}">{{ $sr->parent_name }}</a></span>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="font-weight-bold">Year Applied</td>
                                <td>{{ $sr->session }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Gender</td>
                                <td>{{ $sr->gender }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Parent Address</td>
                                <td>{{ $sr->home_address }}</td>
                            </tr>
                            @if($sr->email)
                            <tr>
                                <td class="font-weight-bold">Email</td>
                                <td>{{$sr->user->email }}</td>
                            </tr>
                            @endif
                            @if($sr->phone)
                                <tr>
                                    <td class="font-weight-bold">Parent Phone</td>
                                    <td>{{$sr->parent_phone }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td class="font-weight-bold">Birthday</td>
                                <td>{{$sr->dob }}</td>
                            </tr>
                            @if($sr->blood_gp)
                            <tr>
                                <td class="font-weight-bold">Blood Group</td>
                                <td>{{$sr->blood_gp }}</td>
                            </tr>
                            @endif
                            @if($sr->nal_id)
                            <tr>
                                <td class="font-weight-bold">Nationality</td>
                                <td>{{$sr->nation }}</td>
                            </tr>
                            @endif
                            @if($sr->state)
                            <tr>
                                <td class="font-weight-bold">State</td>
                                <td>{{$sr->state }}</td>
                            </tr>
                            @endif
                            @if($sr->lga_id)
                            <tr>
                                <td class="font-weight-bold">LGA</td>
                                <td>{{$sr->lga }}</td>
                            </tr>
                            @endif
                            @if($sr->certificate_obtained)
                                <tr>
                                    <td class="font-weight-bold">Dormitory</td>
                                    <td>{{$sr->certificate_obtained }}</td>
                                </tr>
                            @endif

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>


    {{--Student Profile Ends--}}

@endsection
