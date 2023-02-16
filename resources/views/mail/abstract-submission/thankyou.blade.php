<!DOCTYPE html>
<html>

<head>
    <title>Abstract Submission</title>
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
        .text-uppercase {
            text-transform: uppercase;
        }
        .fw-bold {
            font-weight: bold;
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
        .footer p,
        .footer i {
            color: #AEAEAE;
            font-size: 12px;
            text-align: center;
            font-family: 'Open Sans';
        }
        .submission-details {
            font-family: 'Open Sans';
            color: #575757;
            line-height: 1.75;
        }
        .mb-0 {
            margin-bottom: 0px;
        }
        .mb-5 {
            margin-bottom: 5px;
        }
        .open-sans {
            font-family: 'Open Sans', Arial;
        }
    </style>

</head>

<body style="background-color: #e4e7e9;">
<div class="container">
    <div class="main-wrapper">
        <div class="greeting">
            <p><b class="mb-3">Subject: ASD 2022 Abstract Submission Receipt - {{ $submission_date }} </b></p>
            Dear <span class="first-name">{{ join(' ', array_filter(array($user['first_name'], $user['last_name']))) }}</span>,
            <p>Thank you for submitting your Abstract to the <b>4th<sup>th</sup> Asian Society of Dermatology Annual Meeting.</b>.
        </div>

        <h4 class="text-uppercase open-sans mb-5">Abstract Details</h4>
        <div class="submission-details text-left mb-3">
            <table>
                <tr>
                    <td class="w-50 fw-bold">Submitting Author's Name</td>
                    <td>{{ join(' ', array_filter(array($user['first_name'], $user['last_name']))) }}</td>
                </tr>
                <tr>
                    <td class="w-50 fw-bold">Title of the Abstract</td>
                    <td>{{ $abstract_submission['title'] }}</td>
                </tr>
                <tr>
                    <td class="w-50 fw-bold">Date Submitted</td>
                    <td>{{ $submission_date }}</td>
                </tr>
            </table>
            <p class="mb-0 open-sans">You may log in again to your Delegates dashboard through this link to view your Abstract Content, or submit another Abstract:</p>
            <a class="btn clickable" target="_blank" href="{{ config('settings.CONVENTION_URL').'login' }}">
                Login
            </a><br><br>
            <p class="open-sans">
                Submitting authors will be notified starting July 30, 2022 as to the status of their submissions.
                Detailed instructions regarding the requirements and instructions for the presentation will also be provided at that
                time.
            </p>
            <p class="open-sans">
                For any questions or clarifications, please email the ASD Research Committee at:
                <a href="mailto:research@asdmeeting2022.com">ASD Research Committee</a> to learn more.
            <p>
            <p class="open-sans">Thank you for your participation.</p>
            <p class="open-sans mb-0">Regards,</p><br><br>
            <b class="open-sans">ASD Research Committee</b><br>

            <div class="main-header mb-3">
                <div style="width: 100px; height: 100px;">
                    <img style="width:100%" src="{{url('/storage/images/logos/asd-logo2022.png')}}">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="footer">
    <i>THIS IS A SYSTEM GENERATED MESSAGE. PLEASE DO NOT REPLY TO THIS EMAIL.</i>
    <p> © {{ date('Y') }} {{ config('app.name') }}. @lang('All rights reserved.')</p>
</div>
</body>

</html>
