<!DOCTYPE html>
<html>

<head>
    <title>Invoice Email</title>
<style>
.btn {
    margin-top: 1.5rem;
    color: #fff !important;
    background-color: #007bff;
    display: inline-block;
    text-decoration: none;
    text-align: center;
    vertical-align: middle;
    border: 1px solid #007bff;
    font-family: 'Open Sans';
    padding: 10px;
    padding-left: 40px;
    padding-right: 40px;
    box-shadow: 1px 2px 15px #ABABAB;
    border-radius: 20px;
}
.btn:hover {
    background-color: #2E93FF;
    transition: 0.5s;
}
.btn:active {
    background-color: #0065D1;
    transition: 0.5s;
}
.clickable {
    cursor: pointer;
}
.container {
    padding: 1.5rem;
}
.main-wrapper {
    max-width: 500px;
    margin: auto;
    background-color: #fff;
    height: auto;
    padding: 2.5rem;
    box-shadow: 1px 1px 15px #A6A9AA;
}
.header-content {
    font-family: 'Open Sans';
    text-align: center;
    color: #474747;
    margin-top: 0;
    font-size: 2rem;
}
.text-center {
    text-align: center;
}
.greeting {
    text-align: left;
    margin: 15px 0px;
}
.first-name {
    text-transform: capitalize;
}
table {
    border: 1px solid black;
    width: 100%;
}
td {
    border: 1px solid black;
    padding: 0px 5px;
}
.w-50 {
    width: 50%;
}
.footer {
    margin: 0 auto;
    padding: 0;
    padding-bottom: .25rem;
    text-align: center;
    width: 570px;
    -premailer-cellpadding: 0;
    -premailer-cellspacing: 0;
    -premailer-width: 570px;
}
.footer p {
    color: #AEAEAE;
    font-size: 12px;
    text-align: center;
    font-family: 'Open Sans';
}
.payment-details {
    font-family: 'Open Sans';
    color: #575757;
    line-height: 1.75;
}
.mb-0 {
    margin-bottom: 0px;
}
</style>

</head>

<body style="background-color: #e4e7e9;">
    <div class="container">
        <div class="main-wrapper">
            <div class="greeting">
                Dear <span class="first-name">{{join(' ', array_filter(array($user['first_name'], $user['last_name'])))}}</span>,
                <p>Thank you for registering for the <b>4<sup>th</sup> Asian Society of Dermatopathology</b>. Your transaction has been successfully processed.</p>
                <p>Please check the information in your registration details. Your user email is <b>{{$user['email']}}</b> and contains the following information:</p>
            </div>

            @php
                $raw_date = new DateTime($payment->created_at);
                $date_paid = $raw_date->format('F j, Y h:i:s A')." ".$raw_date->getTimezone()->getName();
            @endphp

            <div class="payment-details text-left mb-3">
                <table>
                    <tr>
                        <td class="w-50">Registration Type:</td>
                        <td>{{ $user->member->ExplicitType }}</td>
                    </tr>                    
                    <tr>
                        <td class="w-50">Last Name:</td>
                        <td>{{ $user['last_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-50">First Name:</td>
                        <td>{{ $user['first_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-50">Middle Initial:</td>
                        <td>{{ $user['middle_name'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-50">Name to Appear in Certificate:</td>
                        <td>{{ $user['certificate_name'] }}</td>
                    </tr>   
                    <tr>
                        <td class="w-50">Email Address:</td>
                        <td>{{ $user['email'] }}</td>
                    </tr>
                    <tr>
                        <td class="w-50">Country:</td>
                        <td>{{ $user['country'] }}</td>
                    </tr>
                    @if(!$user->member->scope)
                        <tr>
                            <td class="w-50">PRC number:</td>
                            <td>{{ $user->member->prc_number }}</td>
                        </tr>
                        <tr> 
                            <td class="w-50">PDS number:</td>
                            <td>{{ $user->member->pds_member }}</td>
                        </tr>
                    @endif
                </table>
                <p class="mb-0">You may log in to your delegate dashboard through this link:</p>
                <a class="btn clickable" target="_blank" href="{{ config('settings.CONVENTION_URL').'login' }}">
                    Login
                </a><br><br>
                See you virtually!<br><br>
                <b>ASD Registration Committee, 4th Annual Meeting</b><br>
                
                <div class="main-header mb-3">
                    <div style="width: 100px; height: 100px;">
                        <img style="width:100%" src="{{url('/storage/images/logos/logo-asd2022.png')}}">
                    </div>
                </div>                
            </div>
        </div>
    </div>
    <div class="footer">
       <p> © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')</p>
    </div>
</body>

</html>