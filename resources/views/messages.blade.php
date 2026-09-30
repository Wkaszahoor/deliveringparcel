{{-- Conversation content only (no scroll wrapper). The wrapper lives in
     chat.blade.php so the chat_messages poll response can be injected into
     #view_messages without nesting duplicate containers. --}}
<div id="view_messages">
         <span class="chat_msg_item chat_msg_item_admin">
             <div class="chat_avatar">
                 <img src="{{asset('images/deliveringlogo.png')}}" alt="Logo">
             </div>
             Hey there! Any question?
         </span>
         @php
             // Admin bubbles are keyed by role, not user id 1 (admin ids vary by install).
             $dpAdminIds = \App\Models\User::where('type', 'admin')->pluck('id')->map(function ($i) { return (int) $i; })->all();
             if (!$dpAdminIds) { $dpAdminIds = [1]; }
         @endphp
         @foreach($chat as $msg)
            @if(in_array((int) $msg->from, $dpAdminIds, true))
                <span class="chat_msg_item chat_msg_item_admin">
                    @if($msg->body != null)
                        <div class="chat_avatar">
                            <img src="{{asset('images/deliveringlogo.png')}}" width="10" height="10" alt="Logo">
                        </div>
                            {{$msg->body}}
                        @else
                    @endif
                            <br>
                    @if($msg->image != null)

                        <div class="chat_avatar">
                            <img src="{{asset('images/deliveringlogo.png')}}" width="10" height="10" alt="Logo">
                        </div>
                        <a download="{{$msg->image}}" href="{{url('uploads/chatimages/'.$msg->image)}}" title="ImageName">
                            <img src="{{url('uploads/chatimages/'.$msg->image)}}" alt="user" width="140" height="140" class="rounded" />
                        </a>
                    @endif
                </span>
                @else
                    <span class="chat_msg_item chat_msg_item_user">
                        @if($msg->body != null)
                            {{$msg->body}}
                        @endif
                        <br>
                        @if($msg->image != null)
                            <a download="{{$msg->image}}" href="{{url('uploads/chatimages/'.$msg->image)}}" title="ImageName">
                                <img src="{{url('uploads/chatimages/'.$msg->image)}}" alt="user" width="140" height="140" class="rounded" />
                            </a>
                        @endif
                    </span>
            @endif
         @endforeach
</div>