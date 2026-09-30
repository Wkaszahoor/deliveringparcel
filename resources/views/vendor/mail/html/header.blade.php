<tr>
    <td class="header">
        <a href="{{ $url }}" style="display: inline-block;">
            @if (trim($slot) === 'Laravel')
            <img src="{{asset('images/deliveringlogo.png')}}" class="logo" alt="Delivering parcel logo">
            @else
            {{ $slot }}
            @endif
        </a>
    </td>
</tr>