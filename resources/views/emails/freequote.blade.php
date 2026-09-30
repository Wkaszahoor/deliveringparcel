@component('mail::message')
# Hello , {{ $details['title'] }} {{ $details['name'] }}

@component('mail::table')
| Title | Content |
| ------------- |:-------------:|
| Name | {{ $details['name'] }} |
| Number | {{ $details['number'] }} |
| Email | {{ $details['email'] }} |
| Cargotype | {{ $details['cargotype'] }} |
| Country | {{ $details['country'] }} |
| Destination | {{ $details['destination'] }} |
| Weight | {{ $details['weight'] }} |
| Width | {{ $details['width'] }} |
| Height | {{ $details['height'] }} |
| Detail | {{ $details['detail'] }} |
@endcomponent


Thanks,<br>
{{ config('app.name') }}
@endcomponent