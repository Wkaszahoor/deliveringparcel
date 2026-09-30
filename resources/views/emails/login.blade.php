@component('mail::message')
# Hello , {{ $details['title'] }}
This mail is from delivering parcel.<br>
This is your email address => {{ $details['email'] }}<br><br>
This is your your password => {{ $details['password'] }}<br><br>

Now you can login by pressing the login button 
@component('mail::button', ['url' => $details['url']])
Login Here
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent