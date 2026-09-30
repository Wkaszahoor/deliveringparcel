<div class="chat" id="chat_message">
    <div class="chat_header">
        <div class="chat_option">
            <div class="header_img">
                <img src="{{asset('images/deliveringlogo.png')}}" alt="Logo">
            </div>
            <span id="chat_head">Shopper</span> <br> <span class="agent">Agent</span>
            <!-- <span class="online">(Online)</span> -->
        </div>
    </div>
    {{-- Scroll container lives here; messages.blade.php renders content-only
         so the chat_messages poll response injects cleanly without nesting. --}}
    <div id="chat_converse chat_body chat_login scroll" class="chat_conversion chat_converse ">
        @include('messages')
    </div>
    <div class="fab_field">
        <form id="ChatForm" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="order_id" id="order_id" value="{{$order->id}}">
            <!-- <input type="text" name="message" value="" class="" placeholder="Enter a message..." autocomplete="off"> -->
            <input type="hidden" name="user_id" id="user_id" value="{{Auth()->user()->id}}">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <!-- <input type="file" > -->
            <a id="fab_camera" class="fab file">
                <label for="file-input">
                    <i class="fas fa-cloud-upload"></i>
                </label>
                <input type="file" name="image" id="file-input" value="" class="file_image" />
            </a>
            <button type="submit" class="btn" id="fab_send" class="fab"><i class="fas fa-paper-plane"></i></button>
            <!-- <textarea id="Message" name="message" value="" placeholder="Send a message" class="chat_field chat_message"></textarea> -->
            <input type="text" id="Message" name="message" value="" placeholder="Send a message" class="chat_field chat_message" />
        </form>
    </div>
</div>
<a id="prime" class="fab"><i class="fas fa-comment-dots prime"><h1><span class="badge badge-warning navbar-badge even-larger-badge count">{{ $chat_count }} </span></h1> </i></a>
