<style>
    .error {
        color: #F00;
        background-color: #FFF;
        }
</style>
<!-- <div class="card">  -->
    <div class="card-header bg-white header-elements-inline">
        <h6 class="card-title">Please fill The form Below To Admit A New Student</h6>

    </div>

    <form id="ajax_reg"  enctype="multipart/form-data" class="steps-validation"  data-fouc>
        @csrf
        <h6>Personal data</h6>
        <fieldset>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Full Name: <span class="text-danger">*</span></label>
                        <input value="{{ $data['applicant']->fullname ?? '' }}" required type="text" name="name" placeholder="Full Name" class="form-control">
                        </div>
                    </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label>Address: <span class="text-danger">*</span></label>
                        <input value="{{ $data['applicant']->home_address ?? '' }}" class="form-control" placeholder="Address" name="address" type="text" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Email address: </label>
                        <input type="email" value="{{ $data['applicant']->email ?? '' }}" name="email" class="form-control" placeholder="Email Address">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="gender">Gender: <span class="text-danger">*</span></label>
                        <select class="select form-control" id="gender" name="gender" required  data-placeholder="Choose..">
                            <option value=""></option>
                            <option {{ $data['applicant'] == null ? '' : ($data['applicant']->gender == 'Male' ? 'selected' : '') }} value="Male">Male</option>
                            <option {{ $data['applicant'] == null ? '' : ($data['applicant']->gender == 'Female' ? 'selected' : '') }} value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Phone:</label>
                        <input value="{{  $data['applicant']->parent_phone ?? '' }}" type="text" name="phone" class="form-control" placeholder="" >
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Telephone:</label>
                        <input value="{{ $data['applicant']->phone2 ?? '' }}" type="text" name="phone2" class="form-control" placeholder="" >
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Date of Birth:</label>
                        <input name="dob" value="{{ $data['applicant']->dob ?? '' }}" type="text" class="form-control" readonly>

                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="nal_id">Nationality: <span class="text-danger">*</span></label>
                        <select data-placeholder="Choose..." required name="nal_id" id="nal_id" class="select-search form-control">
                            <option value=""></option>
                            @foreach($data['nationals'] as $nal)
                                <option {{  $data['applicant'] == null ? '' : ($data['applicant']->nal_id == $nal->id ? 'selected' : '') }} value="{{ $nal->id }}">{{ $nal->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <label for="state_id">State: <span class="text-danger">*</span></label>
                    <select onchange="getLGA(this.value)" required data-placeholder="Choose.." class="select-search form-control" name="state_id" id="state_id">
                        <option value=""></option>
                        @foreach($data['states'] as $st)
                            <option {{ $data['applicant'] == null ? '' : ($data['applicant']->state_id == $st->id ? 'selected' : '') }} value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="lga_id">LGA: <span class="text-danger">*</span></label>
                    <select required data-placeholder="Select State First" class="select-search form-control" name="lga_id" id="lga_id">
                        <option value="{{ $data['applicant']->lga_id ?? ''}}"> {{$data['applicant']->lga_name}}</option>
                    </select>
                </div>
            </div>
            <div class="row">

            <div class="col-md-3">
                    <div class="form-group">
                        <label for="my_class_id">Class Applied for: <span class="text-danger">*</span></label>
                        <select onchange="getClassSections(this.value)" data-placeholder="Choose..." required name="my_class_id" id="my_class_id" class="select-search form-control">
                            <option value=""></option>
                            @foreach($data['my_classes'] as $c)
                                <option {{ ($data['applicant'] == null ? '' : ($data['applicant']->my_class_id == $c->id ? 'selected' : '')) }} value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="bg_id">Blood Group: </label>
                        <select class="select form-control" id="bg_id" name="bg_id" data-placeholder="Choose..">
                            <option value=""></option>
                            @foreach(App\Models\BloodGroup::all() as $bg)
                                <option {{ $data['applicant'] == null ? '' : ($data['applicant']->bg_id == $bg->id ? 'selected' : '') }} value="{{ $bg->id }}">{{ $bg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="d-block">Upload Passport Photo:</label>
                            <input value="{{ $data['applicant']->passport }}"  type="hidden" name="photo_url" class="form-control" >
                        </div>
                        <div class="col-md-6">
                            <img class="rounded-circle" style="height: 60px; width: 50px;" src="{{ $data['applicant']->passport }}" alt="photo">
                        </div>
                    </div>
                    
                </div>
            </div>

        </fieldset>

        <h6>Student Data</h6>
        <fieldset>
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="my_class_id">Class: <span class="text-danger">*</span></label>
                        <select onchange="getClassSections(this.value)" data-placeholder="Choose..." required name="my_class_id" id="my_class_id" class="select-search form-control">
                            <option value=""></option>
                            @foreach($data['my_classes'] as $c)
                                <option {{ (old('my_class_id') == $c->id ? 'selected' : '') }} value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                        </select>
                </div>
                    </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="section_id">Section: <span class="text-danger">*</span></label>
                        <select data-placeholder="Select Class First" required name="section_id" id="section_id" class="select-search form-control">
                            <option {{ (old('section_id')) ? 'selected' : '' }} value="{{ old('section_id') }}">{{ (old('section_id')) ? 'Selected' : '' }}</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="my_parent_id">Parent: </label>
                        <select data-placeholder="Choose..."  name="my_parent_id" id="my_parent_id" class="select-search form-control">
                            <option  value=""></option>
                            @foreach($data['parents'] as $p)
                                <option {{ ( $data['applicant']->my_parent_id == $p->id) ? 'selected' : '' }} value="{{ Qs::hash($p->id) }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="year_admitted">Year Admitted: <span class="text-danger">*</span></label>
                        <select data-placeholder="Choose..." required name="year_admitted" id="year_admitted" class="select-search form-control">
                            <option value=""></option>
                            @for($y=date('Y', strtotime('- 3 years')); $y<=date('Y', strtotime('+ 1 years')); $y++)
                                <option {{ ($data['s']['current_session'] == (($y-=1).'-'.($y+=1))) ? 'selected' : '' }}>{{ ($y-=1).'-'.($y+=1) }}</option>
                            @endfor
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <label for="dorm_id">Dormitory: </label>
                    <select data-placeholder="Choose..."  name="dorm_id" id="dorm_id" class="select-search form-control">
                        <option value=""></option>
                        @foreach($data['dorms'] as $d)
                            <option {{ (old('dorm_id') == $d->id) ? 'selected' : '' }} value="{{ $d->id }}">{{ $d->name }}</option>
                            @endforeach
                    </select>

                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Dormitory Room No:</label>
                        <input type="text" name="dorm_room_no" placeholder="Dormitory Room No" class="form-control" value="{{ old('dorm_room_no') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Sport House:</label>
                        <input type="text" name="house" placeholder="Sport House" class="form-control" value="{{ old('house') }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label>Admission Number:</label>
                        <input type="text" name="adm_no" placeholder="Admission Number" class="form-control" value="{{ old('adm_no') }}">
                    </div>
                </div>
            </div>
        </fieldset>
        <button  class="btn-save btn-success" id="btn-save" >Save</button>

        <input type="hidden" id="applicant_id"  name="applicant_id" value="{{ $data['applicant']->applicant_id ?? '' }}" />

    </form>
</div>