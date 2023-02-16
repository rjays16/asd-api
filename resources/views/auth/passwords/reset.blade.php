@php
    $token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
@endphp

<html>
<head>
    <title>Reset Admin Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <style type="text/css">
        body {
            margin: 0;
            font-size: .9rem;
            font-weight: 400;
            line-height: 1.6;
            color: #212529;
            text-align: left;
            background-color: #f5f8fa;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header font-weight-bold">{{ __('Reset Password') }}</div>

                    <div class="card-body">
                        @if(isset($status))
                            <div class="alert alert-success" role="alert">
                                {{ $status }}
                                <script>window.location = "{{env("REDIRECT_LOGIN_PAGE")}}";</script>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.reset') }}" aria-label="{{ __('Reset Password') }}">

                            <input type="hidden" name="token" value="{{$token}}">

                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('E-Mail Address') }}</label>

                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" value="{{ $email ?? old('email') }}" required autofocus>
                                </div>
                                @if(isset($errors) && $errors->has('email'))
                                    <div class="col-12 text-center">
                                        <span class="text-danger">{{ $errors->first('email') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password" required>
                                </div>
                                @if(isset($errors) && $errors->has('password'))
                                    <div class="col-12 text-center">
                                        <span class="text-danger">{{ $errors->first('password') }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="form-group row">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('Confirm Password') }}</label>

                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                </div>
                            </div>

                            <div class="form-group row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ __('Reset Password') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
