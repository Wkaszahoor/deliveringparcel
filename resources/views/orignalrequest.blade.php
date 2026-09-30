@extends('layouts.master')

@section('title','Special Request')
@section('content')
    <section class="container special-request">
        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card">
                <div class=" border-transparent">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs profile-tab" role="tablist">
                        <li class="nav-item"> <a class="nav-link active" data-toggle="tab" href="#address" role="tab">Need an Address Only</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#purchase" role="tab">Need an address + Purchase Assistance</a> </li>
                        <li class="nav-item"> <a class="nav-link" data-toggle="tab" href="#shipping" role="tab">Need an address + Purchase Assistance + Shipping</a> </li>
                    </ul>
                </div>
                <!-- Tab panes -->
                <div class="tab-content">
                    <div class="tab-pane active" id="address" role="tabpanel">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        @if ($message = Session::get('success'))
                            
                            <div class="text-white text-center px-6 py-4  rounded relative mb-4  bg-success border border-blue-400 ">
                                <span class="text-xl inline-block mr-1 align-middle">
                                    <i class="fa fa-bell"></i>
                                </span>
                                <span class="inline-block align-middle mr-8">
                                    <b class="capitalize">{{ $message }}</b> 
                                </span>
                                
                            </div>

                            @elseif ($message = Session::get('error'))
                                <div class="text-white text-center px-6 py-4 border-0 rounded relative mb-4 bg-danger">
                                    <span class="text-xl inline-block mr-1 align-middle">
                                        <i class="fa fa-bell"></i>
                                    </span>
                                    <span class="inline-block align-middle mr-8">
                                        <b class="capitalize">{{ $message }}</b> 
                                    </span>
                                    
                                </div>
                        @endif
                        <div class="card-body">
                            <form action="{{route('needaddress.store')}}" name="myform" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    @csrf
                                    @if(Auth::user())
                                        
                                          
                                        <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
                                      
                                        @else
                                            <div class="form-group col-md-12 col-sm-12">
                                                <label for="name">Name</label>
                                                <input type="text" class="form-control" name="name" id="name" value="" placeholder="Name" required>
                                            </div>
                                            <div class="form-group col-md-12 col-sm-12">
                                                <label for="email">Email</label>
                                                <input type="email" class="form-control" name="email" id="email"  value="" placeholder="Email" required>
                                            </div>
                                            <div class="form-group col-md-12 col-sm-12">
                                                <label for="contactnumber">Contact Number</label>
                                                <input type="text" class="form-control" name="number" id="contactnumber"  value="" placeholder="Please add a country code as well" required>
                                            </div>
                                            <!-- <input id="password" type="hidden" class="form-control @error('password') is-invalid @enderror" name="password" value="" required autocomplete="new-password"> -->
                                            <input type="hidden" name="user_id" value="user-invalid">
                                    @endif
                                    
                                    <div class="form-group  col-md-12 col-sm-12">
                                        <label for="shipfrom">Ship From</label>
                                        <select name="shipfrom" id="shipfrom" class="form-control" required>
                                            <!-- <option value="" disabled >Choose Employee</option> -->
                                            <option disabled selected>Select Country</option>
                                            <option value="Australia">AUSTRALIA</option>
                                            <option value="Brazil">BRAZIL</option>
                                            <option value="China">CHINA</option>
                                            <option value="Canada">CANADA</option>
                                            <option value="finland">FINLAND</option>
                                            <option value="France">FRANCE</option>
                                            <option value="Germeny">GERMENY</option>
                                            <option value="India">INDIA</option>
                                            <option value="Italy">ITALY</option>
                                            <option value="Mexico">MEXICO</option>
                                            <option value="Norway">NORWAY</option>
                                            <option value="Spain">SPAIN</option>
                                            <option value="Sweden">SWEDEN</option>
                                            <option value="Turkey">TURKEY</option>
                                            <option value="Uae">UNITED ARAB EMIRATE (UAE)</option>
                                            <option value="Usa">UNITED STATE OF AMERICA (USA)</option>
                                            <option value="Uk">UNITED KINGDOM (UK)</option>
                                            <option value="Ukrine">UKRINE</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <label for="shipto">Ship to</label>
                                        <input type="text" class="form-control" name="shipto"  id="shipto" value="" placeholder="Country Name" required>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <label for="postalcode">Postal Code</label>
                                        <input type="text" class="form-control" name="postalcode" id="postalcode" value="" placeholder="Postal code of destination country" required >
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <label for="address">Address</label>
                                        <input type="text" class="form-control" name="address" id="address" value="" placeholder="" required>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <table class="table table-bordered" id="dynamicTable">  
                                            <tr>
                                                <th>Product Name</th>
                                                <th>Product URL</th>
                                                <th>Product Quantity</th>
                                                <th>Product Price</th>
                                                <th>Add More</th>
                                                
                                            </tr>
                                            <tr> 
                                                <td><input type="text" name="addmore[0][productname]" placeholder="Product Name" class="form-control"/></td>
                                                <td><input type="text" name="addmore[0][producturl]" placeholder="Product URL" class="form-control"/></td>  
                                                <td><input type="text" name="addmore[0][productquantity]" placeholder="Product Quantity" class="form-control"/></td>
                                                <td><input type="text" name="addmore[0][productprice]" placeholder="Product Price" class="form-control"/></td>
                                                  
                                                <td class="text-center"><button type="button" name="add" id="add" class="btn btn-success"><i class="far fa-plus"></i></button></td>  
                                            </tr>  
                                        </table> 
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <label for="name">Total Approximate Weight</label>
                                        <input type="text" class="form-control" name="approximate_weight"  value="" placeholder="Enter Approximate weight" required>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 check">
                                        <input name="product_photo" value="0" type="hidden" />
                                        <input name="product_photo" value="3" type="checkbox" id="address_1" onclick="total_address()" class="example" /> <label for="address_1">Product photo  –  $3</label>  
                                        <br>
                                        <input name="product_customs" value="0" type="hidden" />
                                        <input name="product_customs" value="4" type="checkbox" id="address_2" onclick="total_address()" class="example" /> <label for="address_2">Customs Declaration  –  $4</label> 
                                        <br>
                                        <input name="product_check" value="0" type="hidden" />
                                        <input name="product_check" value="3" type="checkbox" id="address_3" onclick="total_address()" class="example" /> <label for="address_3">Content Check  –  $3</label>
                                        <br>
                                        <input name="product_prohibited" value="0" type="hidden" />
                                        <input name="product_prohibited" value="1" type="checkbox" id="address_4" onclick="total_address()" class="example" /> <label for="address_4">Removal of Prohibited Items  –  $1</label> 
                                        <br>
                                        <input name="product_disinfection" value="0" type="hidden" />
                                        <input name="product_disinfection" value="1" type="checkbox" id="address_5" onclick="total_address()" class="example" /> <label for="address_5">Disinfection  –  $1</label> 
                                        <br>
                                        
                                        <h5>Package Consolidation</h5>
                                        <input name="product_consolidation" value="6" type="checkbox" id="address_6" onclick="total_address()" class="example" required /> <label for="address_6">Package Consolidation  –  $6</label> 
                                        <br>
                                        <h5>Forwarding Service Fee: $9</h5>
                                        <input name="product_services" value="9" type="checkbox" id="address_7" onclick="total_address()" class="example" required /> <label for="address_7">Forwarding Service Fee –  $9</label> 
                                        <br>
                                        <input type="checkbox" value="" readonly="readonly" name="" id="agree_1" required/> <label for="agree_1">By clicking the tick button, I hereby agree and consent to the terms of business, its policies, and the Privacy Policy.</label>
                                        <br>
                                        <br> 
                                        <input value="$0" readonly="readonly" type="text" id="paynow" name="total" class="text-center p-2 total"/>
                                        <button type="submit" class="btn placeorder" onClick="randomPassword(10);" >Place Order</button>
                                    </div>
                                </div>
                            </form>
                                        
                        </div>
                    </div>
                    <div class="tab-pane" id="purchase" role="tabpanel">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form action="{{route('purchase.store')}}" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    @csrf
                                     @if(Auth::user())
                                        
                                          
                                        <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
                                      
                                        @else
                                            <div class="form-group col-md-12 col-sm-12">
                                                <label for="name">Name</label>
                                                <input type="text" class="form-control" name="name" id="purchase_name" value="" placeholder="Name" required>
                                            </div>
                                            <div class="form-group col-md-12 col-sm-12">
                                                <label for="email">Email</label>
                                                <input type="email" class="form-control" name="email" id="purchase_email"  value="" placeholder="Email" required>
                                            </div>
                                            <div class="form-group col-md-12 col-sm-12">
                                                <label for="purchase_contactnumber">Contact Number</label>
                                                <input type="text" class="form-control" name="number" id="purchase_contactnumber"  value="" placeholder="Please add a country code as well" required>
                                            </div>
                                            <!-- <input id="password" type="hidden" class="form-control @error('password') is-invalid @enderror" name="password" value="" required autocomplete="new-password"> -->
                                            <input type="hidden" name="user_id" value="user-invalid">
                                    @endif
                                    
                                    <div class="form-group  col-md-12 col-sm-12">
                                        <label for="purchase_shipfrom">Ship From</label>
                                        <select name="purchase_shipfrom" id="purchase_shipfrom" class="form-control" required>
                                            <!-- <option value="" disabled >Choose Employee</option> -->
                                            <option disabled selected>Select Country</option>
                                            <option value="Australia">AUSTRALIA</option>
                                            <option value="Brazil">BRAZIL</option>
                                            <option value="China">CHINA</option>
                                            <option value="Canada">CANADA</option>
                                            <option value="Finland">FINLAND</option>
                                            <option value="France">FRANCE</option>
                                            <option value="Germeny">GERMENY</option>
                                            <option value="India">INDIA</option>
                                            <option value="Italy">ITALY</option>
                                            <option value="Mexico">MEXICO</option>
                                            <option value="Norway">NORWAY</option>
                                            <option value="Spain">SPAIN</option>
                                            <option value="Sweden">SWEDEN</option>
                                            <option value="Turkey">TURKEY</option>
                                            <option value="Uae">UNITED ARAB EMIRATE (UAE)</option>
                                            <option value="Usa">UNITED STATE OF AMERICA (USA)</option>
                                            <option value="Uk">UNITED KINGDOM (UK)</option>
                                            <option value="Ukrine">UKRINE</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <label for="shipto">Ship to</label>
                                        <input type="text" class="form-control" name="purchase_shipto"  id="purchase_shipto" value="" placeholder="Country Name" required>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <label for="postalcode">Postal Code</label>
                                        <input type="text" class="form-control" name="purchase_postalcode" id="purchase_postalcode" value="" placeholder="Postal code of destination country" required>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <label for="address">Address</label>
                                        <input type="text" class="form-control" name="purchase_address" id="purchase_address" value="" placeholder="" required>
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <table class="table table-bordered" id="purchaseTable">  
                                            <tr>
                                                <th>Product Name</th>
                                                <th>Product URL</th>
                                                <th>Product Quantity</th>
                                                <th>Add More</th>
                                            </tr>
                                            <tr> 
                                                <td><input type="text" name="addmore[0][purchase_productname]" placeholder="Product Name" class="form-control" required/></td>
                                                <td><input type="text" name="addmore[0][purchase_producturl]" placeholder="Product URL" class="form-control" required/></td>  
                                                <td><input type="text" name="addmore[0][purchase_productquantity]" placeholder="Product Quantity" class="form-control" required/></td>  
                                                <td><button type="button" name="add" id="purchase_add" class="btn btn-success"><i class="far fa-plus"></i></button></td>  
                                            </tr>  
                                        </table> 
                                    </div>
                                    
                                    <div class="form-group col-md-12 col-sm-12">
                                        <label for="name">Total Approximate Weight</label>
                                        <input type="number" class="form-control" name="purchase_approx_weight"  value="" placeholder="enter total item of first">
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12 check">
                                        <input name="purchase_photo" value="0" type="hidden" />  
                                        <input name="purchase_photo" value="3" type="checkbox"  id="purchase_1" onclick="total_purchase()" class="purchase"/> <label for="purchase_1"> Product photo  –  $3</label>
                                        <br>
                                        <input name="purchase_custom" value="0" type="hidden" />
                                        <input name="purchase_custom" value="4" type="checkbox"  id="purchase_2" onclick="total_purchase()" class="purchase"/> <label for="purchase_2">  Customs Declaration  –  $4</label>
                                        <br>
                                        <input name="purchase_check" value="0" type="hidden" />
                                        <input name="purchase_check" value="3" type="checkbox"  id="purchase_3" onclick="total_purchase()" class="purchase"/> <label for="purchase_3">  Content Check  –  $3</label>
                                        <br>
                                        <input name="purchase_prohibited" value="0" type="hidden" />
                                        <input name="purchase_prohibited" value="1" type="checkbox"  id="purchase_4" onclick="total_purchase()" class="purchase"/> <label for="purchase_4">  Removal of Prohibited Items  –  $1</label>
                                        <br>
                                        <input name="purchase_disinfect" value="0" type="hidden" />
                                        <input name="purchase_disinfect" value="1" type="checkbox"  id="purchase_5" onclick="total_purchase()" class="purchase"/> <label for="purchase_5">  Disinfection  –  $1</label>
                                        <br>
                                        <input name="purchase_consolidation" value="0" type="hidden" />
                                        <h5>Package Consolidation</h5>
                                        <input name="purchase_consolidation" value="6" type="checkbox"  id="purchase_6" onclick="total_purchase()" class="purchase"/> <label for="purchase_6">  Package Consolidation  –  $6</label>
                                        <br>
                                        <h5>Purchase Assistance: $10</h5>
                                        <input name="purchase_assistence" value="10" type="checkbox"  id="purchase_7" onclick="total_purchase()" class="purchase" required/> <label for="purchase_7">  Purchase Assistance - $10</label>
                                        <h5>Forwarding Service Fee: $9</h5>
                                        <input name="purchase_services" value="9" type="checkbox"  id="purchase_8" onclick="total_purchase()" class="purchase" required /> <label for="purchase_8">  Forwarding Service Fee - $9</label>
                                        <br>
                                        <input type="checkbox" value="" readonly="readonly" name=""  id="agree_2"/> <label for="agree_2">By clicking the tick button, I hereby agree and consent to the terms of business, its policies, and the Privacy Policy.</label> 
                                        <br>
                                        <br> 
                                        <input type="text" value="$0" readonly="readonly"  id="purchase_paynow" name="total_pur" class="text-center p-2 total" />
                                        <button type="submit" class="btn placeorder">Place order</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="tab-pane" id="shipping" role="tabpanel">
                        <h2 class="text-center">Request a free quote</h2>
                        <div class="card-body">
                             @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form action="{{route('shipping.store')}}" method="post" enctype="multipart/form-data">
                                <div class="row">
                                    @csrf
                                    @if(Auth::user())
                                     <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
                                        @else
                                            <div class="form-group col-md-6 col-sm-12">
                                                <label for="shipping_name">Full Name</label>
                                                <input type="text" class="form-control" name="shipping_name" id="shipping_name" value="" placeholder="Name" data-height="40">
                                            </div>
                                            <div class="form-group col-md-6 col-sm-12">
                                                <label for="shipping_email">Email</label>
                                                <input type="email" class="form-control" name="shipping_email" id="shipping_email"  value="" placeholder="Email">
                                            </div>
                                            <div class="form-group col-md-6 col-sm-12">
                                                <label for="shipping_phone">Phone Number</label>
                                                <input type="text" class="form-control" name="shipping_phone"  id="shipping_phone" value="" placeholder="Country Name">
                                            </div>
                                            <!-- <input id="password" type="hidden" class="form-control @error('password') is-invalid @enderror" name="password" value="" required autocomplete="new-password"> -->
                                            <input type="hidden" name="user_id" value="user-invalid">
                                    @endif
                                    
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="shipping_cargotype">Cargo Type</label>
                                        <input type="text" class="form-control" name="shipping_cargotype" id="shipping_cargotype" value="" placeholder="">
                                    </div>
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="shipping_country">Country of Origin</label>
                                        <input type="text" class="form-control" name="shipping_country" id="shipping_country" value="" placeholder="">
                                    </div>
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="shipping_Destination">Destination</label>
                                        <input type="text" class="form-control" name="shipping_Destination" id="shipping_Destination" value="" placeholder="">
                                    </div>
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="shipping_Quantity">Quantity</label>
                                        <input type="number" class="form-control" name="shipping_Quantity" id="shipping_Quantity"  value="" placeholder="">
                                    </div>
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="shipping_Weight">Weight</label>
                                        <input type="text" class="form-control" name="shipping_Weight" id="shipping_Weight"  value="" placeholder="2 kg">
                                    </div> 
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="shipping_Width">Width</label>
                                        <input type="text" class="form-control" name="shipping_Width" id="shipping_Width"  value="" placeholder="cm/mm/m">
                                    </div>
                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="shipping_Height">Height</label>
                                        <input type="text" class="form-control" name="shipping_Height" id="shipping_Height" value="" placeholder="cm/mm/m">
                                    </div>
                                    <div class="form-group col-md-12 col-sm-12">
                                        <label for="shipping_detail">Please describe your requirement in detail including Product URL, special needs and handling related to the request</label><br>
                                        <textarea name="shipping_detail" class="form-control" id="shipping_detail"></textarea>
                                        
                                    </div>
                                    <div class="col-md-12 col-sm-12">
                                        <button type="submit" class="btn shipping_btn">SEND</button>
                                    </div>
                                    
                                </div>
                            </form>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>       
        function total_address() {
            // var input = document.getElementsByName("product_photo");
            var input = document.getElementsByClassName("example");
            var total = 0;
            for (var i = 0; i < input.length; i++) {
                if (input[i].checked) {
                total += parseFloat(input[i].value);
                }
            }
            document.getElementsByName("total")[0].value =total.toFixed(2);
            }

        function total_purchase() {
            // var input = document.getElementsByName("purchase");
            var input = document.getElementsByClassName("purchase");
            var total_pur = 0;
            for (var i = 0; i < input.length; i++) {
                if (input[i].checked) {
                total_pur += parseFloat(input[i].value);
                }
            }
            document.getElementsByName("total_pur")[0].value ="$" + total_pur.toFixed(2);
            }

    </script>
    <script type="text/javascript">
        var i = 0;  
        $("#add").click(function(){
            ++i;
            $("#dynamicTable").append('<tr><td><input type="text" name="addmore['+i+'][productname]" placeholder="Product Name" class="form-control" /></td><td><input type="text" name="addmore['+i+'][producturl]" placeholder="Product URL" class="form-control" /></td><td><input type="text" name="addmore['+i+'][productquantity]" placeholder="Product Quantity" class="form-control" /></td><td><input type="text" name="addmore['+i+'][productprice]" placeholder="Product Price" class="form-control"/></td><td class="text-center"><button type="button" class="btn btn-danger remove-tr"><i class="far fa-minus"></button></td></tr>');

        });
        $(document).on('click', '.remove-tr', function(){  

            $(this).parents('tr').remove();

        });  
    </script>

    <script type="text/javascript">
        var i = 0;  
        $("#purchase_add").click(function(){
            ++i;
            $("#purchaseTable").append('<tr><td><input type="text" name="addmore['+i+'][purchase_productname]" placeholder="Product Name" class="form-control" /></td><td><input type="text" name="addmore['+i+'][purchase_producturl]" placeholder="Product URL" class="form-control" /></td><td><input type="text" name="addmore['+i+'][purchase_productquantity]" placeholder="Product Quantity" class="form-control" /></td><td><button type="button" class="btn btn-danger remove_purchase"><i class="far fa-minus"></button></td></tr>');

        });
        
        $(document).on('click', '.remove_purchase', function(){  

            $(this).parents('tr').remove();

        });  
    </script>
    <!-- <script>
        function randomPassword(length) {
            var chars = "abcdefghijklmnopqrstuvwxyABCDEFGHIJKLMNOP1234567890";
            var pass = "";
            for (var x = 0; x < length; x++) {
                var i = Math.floor(Math.random() * chars.length);
                pass += chars.charAt(i);
            }
            myform.password.value = pass;
        }
    </script> -->
@endsection