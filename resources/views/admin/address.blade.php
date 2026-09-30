<div class="showid">
    <!-- <input type="text" name="shipingaddress" class="form-control" id="shipingaddress" value="" required> -->
    <!-- <input type="text" name="shipingaddress" class="form-control" id="shipingaddress" value=""  list="addresses" required/> -->
    <datalist id="addresses">
        @foreach($shipingaddress as $add)
        <input type="hidden" name="address_id" value="{{$add->id}}">
        <option value="{{$add->name}} {{$add->address1}} {{$add->address2}} {{$add->city}} {{$add->state}} {{$add->postalcode}} {{$add->country}} {{$add->number}}">{{$add->name}} {{$add->number}}</option>
        @endforeach
    </datalist>
    <!-- <input type="hidden" name="active" value="2"> -->
</div>
