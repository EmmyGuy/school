@extends('layouts.login_master')

@section('content')
<div class="page-content login-cover">

        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Content area -->
            <div class="content d-flex justify-content-center align-items-center">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="card mb-0">
                            <div class="card-body">
                                <div class="text-center mb-3">
                                    <i class="icon-people icon-2x text-warning-400 border-warning-400 border-3 rounded-round p-3 mb-3 mt-1"></i>
                                    <h5 class="mb-0">Register an account</h5>
                                    <span class="d-block text-muted">Your credentials</span>
                                </div>

                                
                                    <!-- <div class="form-group row">
                                        <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('Fullname') }}</label> -->
                                        <div class="form-group">
                                            <input id="username" type="text" placeholder="username" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" required autofocus>

                                            @if ($errors->has('username'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('username') }}</strong>
                                                </span>
                                            @endif
                                        </div>

                                        <div class="form-group">
                                            <input id="name" type="text" placeholder="Fullname" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" required autofocus>

                                            @if ($errors->has('name'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('name') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    <!-- </div> -->

                                    <!-- <div class="form-group row">
                                        <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label> -->

                                        <div class="form-group">
                                            <input id="email" type="email" placeholder="E-Mail Address"  class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required>

                                            @if ($errors->has('email'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('email') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    <!-- </div> -->
<!-- 
                                    <div class="form-group">
                                        <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label> -->

                                        <div class="form-group">
                                            <input id="password" type="password" placeholder="Password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>

                                            @if ($errors->has('password'))
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $errors->first('password') }}</strong>
                                                </span>
                                            @endif
                                        </div>
                                    <!-- </div> -->

                                    <div class="form-group row">
                                        <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                                        <div class="col-md-8">
                                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                        </div>
                                    </div>
                          

                                <div class="form-group row mb-0">
                                    <div class="col-md-6 offset-md-4">
                                        <button type="submit" class="btn btn-primary">
                                            {{ __('Register') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
            </div>

        </div>
</div>
@endsection
