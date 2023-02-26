        <style>
            .error {
                color: #F00;
                background-color: #FFF;
                }
        </style>
        <!-- <div class="card"> -->
            <div class="card-header bg-white header-elements-inline">
                <h6 class="card-title">Please fill The form below to admit new pupil - Primary</h6>

                {!! Qs::getPanelOptions() !!}
            </div>

            <form id="ajax_reg"  enctype="multipart/form-data" class="steps-validation"  data-fouc>
               @csrf
               
                <h6>Personal data</h6>
                <fieldset>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name: <span class="text-danger">*</span></label>
                                <input value="{{ $data['applicant']->fullname ?? ''}}" required type="text" name="name" placeholder="Full Name -- Surname First" class="form-control">
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
                                <label>Date of Birth:</label>
                                <input name="dob" value="{{ $data['applicant']->applicant_dob ?? ''}}" required type="date" class="form-control date-pick" placeholder="Select Date...">

                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="nal_id">Nationality: <span class="text-danger">*</span></label>
                                <select data-placeholder="Choose..." required name="nal_id" id="nal_id" class="select-search form-control">
                                    <option value=""></option>
                                    @foreach($data['nationals'] as $nal)
                                        <option {{ $data['applicant'] == null ? '' : ($data['applicant']->nal_id == $nal->id ? 'selected' : '') }} value="{{ $nal->id }}">{{ $nal->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label for="state_id">State: <span class="text-danger">*</span></label>
                            <select onchange="getLGA(this.value)" required placeholder="Choose.." class="select-search form-control" name="state_id" id="state_id">
                                <option value=""></option>
                                @foreach($data['states'] as $st)
                                    <option {{ (old('state_id') == $st->id ? 'selected' : '') }} value="{{ $st->id }}">{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="lga_id">LGA: <span class="text-danger">*</span></label>
                            <select required data-placeholder="Select State First" class="select-search form-control" name="lga_id" id="lga_id">
                                <option value=""></option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                                <div class="form-group">
                                    <label for="my_class_id">If child has been to school before, enter previous class else leave empty <span class="text-danger"></span></label>
                                    <input value="{{ $data['applicant']->last_class ?? '' }}"  type="text" name="previous_class" placeholder="e.g Creche" class="form-control">
                                </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="gender">Gender: <span class="text-danger">*</span></label>
                                <select class="select form-control" id="gender" name="gender" required  data-placeholder="Choose..">
                                    <option value=""></option>
                                    <option {{ ($data['applicant'] == null ? '' : ($data['applicant']->gender == 'Male' ? 'selected' : '')) }} value="Male">Male</option>
                                    <option {{ ($data['applicant'] == null ? '' : ($data['applicant']->gender == 'Female' ? 'selected' : '')) }} value="Female">Female</option>
                                </select>
                            </div>
                        </div>
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

                    </div>

                    <div class="row">
                      

                    </div>

                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Seesion applying for: <span class="text-danger">*</span></label>
                                <select data-placeholder="Choose..." required name="current_session" id="current_session" class="select-search form-control">
                                    <option value=""></option>
                                    @for($y=date('Y', strtotime('- 3 years')); $y<=date('Y', strtotime('+ 1 years')); $y++)
                                        <option {{ ($data['s']['current_session'] == (($y-=1).'-'.($y+=1))) ? 'selected' : '' }}>{{ ($y-=1).'-'.($y+=1) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="bg_id">Blood Group: </label>
                                <select class="select form-control" id="bg_id" name="bg_id"  data-placeholder="Choose..">
                                    <option value=""></option>
                                    @foreach(App\Models\BloodGroup::all() as $bg)
                                        <option {{ $data['applicant'] == null ? '' : ($data['applicant']->applicant_blood_group == $bg->id ? 'selected' : '') }}} value="{{ $bg->id }}">{{ $bg->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Pupil Schedule type: <span class="text-danger">*</span></label>
                                <select required id="schedule_type" name="schedule_type" class="form-control select">
                                      <option  value="">Schedule type</option>
                                      <option {{ ($data['applicant'] == null ? '' : ($data['applicant']->applicant_schedule == 'Day' ? 'selected' : '')) }} value="Day">Day</option>
                                      <option {{ ($data['applicant'] == null ? '' : ($data['applicant']->applicant_schedule == 'Boarding' ? 'selected' : '')) }} value="Boarding">Boarding</option>
                                     
                                  </select>                            
                                </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="d-block">Upload Passport Photo:</label>
                                <input value="{{ old('photo') }}" accept="image/*" type="file" name="photo" required class="form-input-styled" >
                                <span class="form-text text-muted">Accepted Images: jpeg, png. Max file size 25kb</span>
                            </div>
                        </div>
                    </div>

                </fieldset>

                <h6><b>Parent's/Guardian's Data</b></h6>
                <fieldset>
                    <div class="row">
                    <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name: <span class="text-danger">*</span></label>
                                <input value="{{ $data['applicant']->name ?? '' }}" required type="text" name="name" placeholder="Full Name -- Surname First" class="form-control">
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="nal_id">Nationality: <span class="text-danger">*</span></label>
                                    <select data-placeholder="Choose..." required name="parent_nal_id" id="parent_nal_id" class="select-search form-control">
                                        <option value=""></option>
                                        @foreach($data['nationals'] as $nal)
                                            <option {{ $data['applicant'] == null ? '' : ($data['applicant']->parent_nal_id ==$nal->id ? 'selected' : '') }} value="{{ $nal->id }}">{{ $nal->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Occupation: <span class="text-danger">*</span></label>
                                <input value="{{ $data['applicant']->parent_occupation ?? '' }}" required type="text" name="occupation" placeholder="occupation" class="form-control">
                                </div>
                            </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Office Address: <span class="text-danger">*</span></label>
                                <input value="{{ $data['applicant']->parent_address ?? '' }}" class="form-control" placeholder="Office Address" name="office_address" type="text" >
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Office Phone Number:</label>
                                <input value="{{ $data['applicant']->phone ?? '' }}" type="text" name="office_phone" class="form-control" placeholder="Office Address" >
                            </div>
                        </div>

                        

                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Home Address: <span class="text-danger">*</span></label>
                                <input value="{{ $data['applicant']->home_address ?? '' }}" class="form-control" placeholder="home_address" name="home_address" type="text" required>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Home Phone Number:</label>
                                <input value="{{ $data['applicant']->phone2 ?? '' }}" type="text" name="home_phone" class="form-control" placeholder="" >
                            </div>
                        </div>
                        
                    </div>

                    <div class="row">
                    <div class="col-md-4">
                            <div class="form-group">
                                <label>List any medical conditions/Allergies if any</label>
                                <input value="{{ $data['applicant'] == null ? '' : (json_decode($data['applicant']->allergies) ?? '') }}" type="text" name="mediacal_condition" class="form-control" placeholder="e.g: peanuts;rain water" >
                            </div>
                        </div><div class="col-md-4">
                            <div class="form-group">
                                <label>lists of Immunisations and Date <span class="text-danger">*</span></label>
                                <input value="{{ $data['applicant'] == null ? '' : (implode(';', json_decode($data['applicant']->immunization)) ?? '') }}" type="text" name="immunization" class="form-control" placeholder="e.g.: polio - 10/4/2020;smallpox - 30/4/2020" required>
                            </div>
                        </div><div class="col-md-4">
                            <div class="form-group">
                                <label>Who picks the child from school: <span class="text-danger">*</span></label>
                                <input value="{{  $data['applicant']->name_of_peson_who_picks_ward ?? ''  }}" type="text" require name="person_who_pick_child" class="form-control" placeholder="John Doe" >
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="checkbox" id='check' class="form-control" name="check" value="" > I certify that the information above are true and cor? 
                            </div>
                        </div>
                    </div>
                </fieldset>
                <button  class="btn-save btn-success" id="btn-save" >Save</button>

                <input type="hidden" id="certification"  name="certification" value="false" />
                <input type="hidden" id="section_id"  name="section_id" value="1" />
                <input type="hidden" id="applicant_id"  name="applicant_id" value="{{ $data['applicant']->applicant_id ?? '' }}" />
                <input type="hidden" id="applicant_type"  name="applicant_type" value="{{ $data['applicant_type'] ?? '' }}" />
            </form>
        </div>
