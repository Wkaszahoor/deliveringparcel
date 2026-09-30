@component('mail::message')
# Hello ,{{ $details['name'] }} {{ $details['title'] }}

@component('mail::table')
| Title | Content |
| ------------- |:-------------:|
| Name | {{ $details['name'] }} |
| Number | {{ $details['number'] }} |
| Email | {{ $details['email'] }} |
| Address | {{ $details['address'] }} |
| Message | {{ $details['message'] }} |
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent