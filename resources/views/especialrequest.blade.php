@extends('layouts.master')
@section('title','Special Request')
@section('keywords', 'Special Request for parcel delivery')
@section('content')
<section class="container special-request">
    <div class="col-xl-12 col-lg-12 col-md-12">
        <div class="card">
            <div class="tab-pane active" id="address" role="tabpanel">
                <h2 class="text-center">Place a Request</h2>
                <p class="text-center style-p text-color">“Please provide full details of the item, URL, quantities, prices and services needed for forwarding from deliveringparcel”.</p>
                @include('flash-message')
                <div class="card-body">
                    <form action="{{route('orders.store')}}" name="myform" method="post" id="request-form" enctype="multipart/form-data">
                        <div class="row">
                            @csrf
                            @if(Auth::user())
                            <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
                            @else
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="name">Name</label>
                                <input type="text" class="form-control" name="name" id="name" value="" placeholder="Name" required />
                            </div>
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" name="email" id="email" value="" placeholder="Email" required />
                            </div>
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="contactnumber">Contact Number</label>
                                <input type="text" class="form-control" name="number" id="contactnumber" value="" placeholder="Please add a country code as well" required />
                            </div>
                            <!-- <input id="password" type="hidden" class="form-control @error('password') is-invalid @enderror" name="password" value="" required autocomplete="new-password"> -->
                            <input type="hidden" name="user_id" value="user-invalid">
                            @endif
                            <div class="form-group  col-md-12 col-sm-12">
                                <label for="shipfrom">Ship From</label>
                                <select name="shipfrom" id="shipfrom" class="form-control" required>
                                    <!-- <option value="" disabled >Choose Employee</option> -->
                                    
                                    @if(isset($data['from']))
                                    <option value="{{ $data['from'] }}" selected>{{$data['from']}}</option>
                                    @else
                                    <option value="" disabled selected>Select country</option>
                                    @endif
                                    <option value="Afghanistan">Afghanistan</option>
                                    <option value="Åland Islands">Åland Islands</option>
                                    <option value="Albania">Albania</option>
                                    <option value="Algeria">Algeria</option>
                                     <option value="American Samoa">American Samoa</option>
                                    <option value="Andorra">Andorra</option>
                                    <option value="Angola">Angola</option>
                                    <option value="Anguilla">Anguilla</option>
                                    <option value="Antarctica">Antarctica</option>
                                    <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                    <option value="Argentina">Argentina</option>
                                     <option value="Armenia">Armenia</option>
                                                            <option value="Aruba">Aruba</option>
                                                            <option value="Australia">Australia</option>
                                                            <option value="Austria">Austria</option>
                                                            <option value="Azerbaijan">Azerbaijan</option>
                                                            <option value="Bahamas">Bahamas</option>
                                                            <option value="Bahrain">Bahrain</option>
                                                            <option value="Bangladesh">Bangladesh</option>
                                                            <option value="Barbados">Barbados</option>
                                                            <option value="Belarus">Belarus</option>
                                                            <option value="Belgium">Belgium</option>
                                                            <option value="Belize">Belize</option>
                                                            <option value="Benin">Benin</option>
                                                            <option value="Bermuda">Bermuda</option>
                                                            <option value="Bhutan">Bhutan</option>
                                                            <option value="Bolivia">Bolivia</option>
                                                            <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                                            <option value="Botswana">Botswana</option>
                                                            <option value="Bouvet Island">Bouvet Island</option>
                                                            <option value="Brazil">Brazil</option>
                                                            <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                                            <option value="Brunei Darussalam">Brunei Darussalam</option>
                                                            <option value="Bulgaria">Bulgaria</option>
                                                            <option value="Burkina Faso">Burkina Faso</option>
                                                            <option value="Burundi">Burundi</option>
                                                            <option value="Cambodia">Cambodia</option>
                                                            <option value="Cameroon">Cameroon</option>
                                                            <option value="Canada">Canada</option>
                                                            <option value="Cape Verde">Cape Verde</option>
                                                            <option value="Cayman Islands">Cayman Islands</option>
                                                            <option value="Central African Republic">Central African Republic</option>
                                                            <option value="Chad">Chad</option>
                                                            <option value="Chile">Chile</option>
                                                            <option value="China">China</option>
                                                            <option value="Christmas Island">Christmas Island</option>
                                                            <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                                            <option value="Colombia">Colombia</option>
                                                            <option value="Comoros">Comoros</option>
                                                            <option value="Congo">Congo</option>
                                                            <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                                            <option value="Cook Islands">Cook Islands</option>
                                                            <option value="Costa Rica">Costa Rica</option>
                                                            <option value="Cote D'ivoire">Cote D'ivoire</option>
                                                            <option value="Croatia">Croatia</option>
                                                            <option value="Cuba">Cuba</option>
                                                            <option value="Cyprus">Cyprus</option>
                                                            <option value="Czech Republic">Czech Republic</option>
                                                            <option value="Denmark">Denmark</option>
                                                            <option value="Djibouti">Djibouti</option>
                                                            <option value="Dominica">Dominica</option>
                                                            <option value="Dominican Republic">Dominican Republic</option>
                                                            <option value="Ecuador">Ecuador</option>
                                                            <option value="Egypt">Egypt</option>
                                                            <option value="El Salvador">El Salvador</option>
                                                            <option value="Equatorial Guinea">Equatorial Guinea</option>
                                                            <option value="Eritrea">Eritrea</option>
                                                            <option value="Estonia">Estonia</option>
                                                            <option value="Ethiopia">Ethiopia</option>
                                                            <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                                            <option value="Faroe Islands">Faroe Islands</option>
                                                            <option value="Fiji">Fiji</option>
                                                            <option value="Finland">Finland</option>
                                                            <option value="France">France</option>
                                                            <option value="French Guiana">French Guiana</option>
                                                            <option value="French Polynesia">French Polynesia</option>
                                                            <option value="French Southern Territories">French Southern Territories</option>
                                                            <option value="Gabon">Gabon</option>
                                                            <option value="Gambia">Gambia</option>
                                                            <option value="Georgia">Georgia</option>
                                                            <option value="Germany">Germany</option>
                                                            <option value="Ghana">Ghana</option>
                                                            <option value="Gibraltar">Gibraltar</option>
                                                            <option value="Greece">Greece</option>
                                                            <option value="Greenland">Greenland</option>
                                                            <option value="Grenada">Grenada</option>
                                                            <option value="Guadeloupe">Guadeloupe</option>
                                                            <option value="Guam">Guam</option>
                                                            <option value="Guatemala">Guatemala</option>
                                                            <option value="Guernsey">Guernsey</option>
                                                            <option value="Guinea">Guinea</option>
                                                            <option value="Guinea-bissau">Guinea-bissau</option>
                                                            <option value="Guyana">Guyana</option>
                                                            <option value="Haiti">Haiti</option>
                                                            <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                                            <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                                            <option value="Honduras">Honduras</option>
                                                            <option value="Hong Kong">Hong Kong</option>
                                                            <option value="Hungary">Hungary</option>
                                                            <option value="Iceland">Iceland</option>
                                                            <option value="India">India</option>
                                                            <option value="Indonesia">Indonesia</option>
                                                            <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                                            <option value="Iraq">Iraq</option>
                                                            <option value="Ireland">Ireland</option>
                                                            <option value="Isle of Man">Isle of Man</option>
                                                            <option value="Israel">Israel</option>
                                                            <option value="Italy">Italy</option>
                                                            <option value="Jamaica">Jamaica</option>
                                                            <option value="Japan">Japan</option>
                                                            <option value="Jersey">Jersey</option>
                                                            <option value="Jordan">Jordan</option>
                                                            <option value="Kazakhstan">Kazakhstan</option>
                                                            <option value="Kenya">Kenya</option>
                                                            <option value="Kiribati">Kiribati</option>
                                                            <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                                            <option value="Korea, Republic of">Korea, Republic of</option>
                                                            <option value="Kuwait">Kuwait</option>
                                                            <option value="Kyrgyzstan">Kyrgyzstan</option>
                                                            <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                                            <option value="Latvia">Latvia</option>
                                                            <option value="Lebanon">Lebanon</option>
                                                            <option value="Lesotho">Lesotho</option>
                                                            <option value="Liberia">Liberia</option>
                                                            <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                                            <option value="Liechtenstein">Liechtenstein</option>
                                                            <option value="Lithuania">Lithuania</option>
                                                            <option value="Luxembourg">Luxembourg</option>
                                                            <option value="Macao">Macao</option>
                                                            <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                                            <option value="Madagascar">Madagascar</option>
                                                            <option value="Malawi">Malawi</option>
                                                            <option value="Malaysia">Malaysia</option>
                                                            <option value="Maldives">Maldives</option>
                                                            <option value="Mali">Mali</option>
                                                            <option value="Malta">Malta</option>
                                                            <option value="Marshall Islands">Marshall Islands</option>
                                                            <option value="Martinique">Martinique</option>
                                                            <option value="Mauritania">Mauritania</option>
                                                            <option value="Mauritius">Mauritius</option>
                                                            <option value="Mayotte">Mayotte</option>
                                                            <option value="Mexico">Mexico</option>
                                                            <option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
                                                            <option value="Moldova, Republic of">Moldova, Republic of</option>
                                                            <option value="Monaco">Monaco</option>
                                                            <option value="Mongolia">Mongolia</option>
                                                            <option value="Montenegro">Montenegro</option>
                                                            <option value="Montserrat">Montserrat</option>
                                                            <option value="Morocco">Morocco</option>
                                                            <option value="Mozambique">Mozambique</option>
                                                            <option value="Myanmar">Myanmar</option>
                                                            <option value="Namibia">Namibia</option>
                                                            <option value="Nauru">Nauru</option>
                                                            <option value="Nepal">Nepal</option>
                                                            <option value="Netherlands">Netherlands</option>
                                                            <option value="Netherlands Antilles">Netherlands Antilles</option>
                                                            <option value="New Caledonia">New Caledonia</option>
                                                            <option value="New Zealand">New Zealand</option>
                                                            <option value="Nicaragua">Nicaragua</option>
                                                            <option value="Niger">Niger</option>
                                                            <option value="Nigeria">Nigeria</option>
                                                            <option value="Niue">Niue</option>
                                                            <option value="Norfolk Island">Norfolk Island</option>
                                                            <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                                            <option value="Norway">Norway</option>
                                                            <option value="Oman">Oman</option>
                                                            <option value="Pakistan">Pakistan</option>
                                                            <option value="Palau">Palau</option>
                                                            <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                                            <option value="Panama">Panama</option>
                                                            <option value="Papua New Guinea">Papua New Guinea</option>
                                                            <option value="Paraguay">Paraguay</option>
                                                            <option value="Peru">Peru</option>
                                                            <option value="Philippines">Philippines</option>
                                                            <option value="Pitcairn">Pitcairn</option>
                                                            <option value="Poland">Poland</option>
                                                            <option value="Portugal">Portugal</option>
                                                            <option value="Puerto Rico">Puerto Rico</option>
                                                            <option value="Qatar">Qatar</option>
                                                            <option value="Reunion">Reunion</option>
                                                            <option value="Romania">Romania</option>
                                                            <option value="Russian Federation">Russian Federation</option>
                                                            <option value="Rwanda">Rwanda</option>
                                                            <option value="Saint Helena">Saint Helena</option>
                                                            <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                                            <option value="Saint Lucia">Saint Lucia</option>
                                                            <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                                            <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                                            <option value="Samoa">Samoa</option>
                                                            <option value="San Marino">San Marino</option>
                                                            <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                                            <option value="Saudi Arabia">Saudi Arabia</option>
                                                            <option value="Senegal">Senegal</option>
                                                            <option value="Serbia">Serbia</option>
                                                            <option value="Seychelles">Seychelles</option>
                                                            <option value="Sierra Leone">Sierra Leone</option>
                                                            <option value="Singapore">Singapore</option>
                                                            <option value="Slovakia">Slovakia</option>
                                                            <option value="Slovenia">Slovenia</option>
                                                            <option value="Solomon Islands">Solomon Islands</option>
                                                            <option value="Somalia">Somalia</option>
                                                            <option value="South Africa">South Africa</option>
                                                            <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                                            <option value="Spain">Spain</option>
                                                            <option value="Sri Lanka">Sri Lanka</option>
                                                            <option value="Sudan">Sudan</option>
                                                            <option value="Suriname">Suriname</option>
                                                            <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                                            <option value="Swaziland">Swaziland</option>
                                                            <option value="Sweden">Sweden</option>
                                                            <option value="Switzerland">Switzerland</option>
                                                            <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                                            <option value="Taiwan">Taiwan</option>
                                                            <option value="Tajikistan">Tajikistan</option>
                                                            <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                                            <option value="Thailand">Thailand</option>
                                                            <option value="Timor-leste">Timor-leste</option>
                                                            <option value="Togo">Togo</option>
                                                            <option value="Tokelau">Tokelau</option>
                                                            <option value="Tonga">Tonga</option>
                                                            <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                                            <option value="Tunisia">Tunisia</option>
                                                            <option value="Turkey">Turkey</option>
                                                            <option value="Turkmenistan">Turkmenistan</option>
                                                            <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                                            <option value="Tuvalu">Tuvalu</option>
                                                            <option value="Uganda">Uganda</option>
                                                            <option value="Ukraine">Ukraine</option>
                                                            <option value="United Arab Emirates">United Arab Emirates</option>
                                                            <option value="United Kingdom">United Kingdom</option>
                                                            <option value="United States">United States</option>
                                                            <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                                            <option value="Uruguay">Uruguay</option>
                                                            <option value="Uzbekistan">Uzbekistan</option>
                                                            <option value="Vanuatu">Vanuatu</option>
                                                            <option value="Venezuela">Venezuela</option>
                                                            <option value="Viet Nam">Viet Nam</option>
                                                            <option value="Virgin Islands, British">Virgin Islands, British</option>
                                                            <option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
                                                            <option value="Wallis and Futuna">Wallis and Futuna</option>
                                                            <option value="Western Sahara">Western Sahara</option>
                                                            <option value="Yemen">Yemen</option>
                                                            <option value="Zambia">Zambia</option>
                                                            <option value="Zimbabwe">Zimbabwe</option>

                                </select>
                            </div>
                            <div class="form-group  col-md-12 col-sm-12">
                                <label for="shipto">Ship To</label>
                                <select name="shipto" id="shipto" class="form-control" required>
                                    <!-- <option value="" disabled >Choose Employee</option> -->
                                    @if(isset($data['from']))
                                    <option value="{{ $data['to']}}" selected>{{$data['to']}}</option>
                                    @else
                                    <option value="" disabled selected>Select country</option>
                                    @endif

                                    <option value="Afghanistan">Afghanistan</option>
                                    <option value="Åland Islands">Åland Islands</option>
                                    <option value="Albania">Albania</option>
                                    <option value="Algeria">Algeria</option>
                                    <option value="American Samoa">American Samoa</option>
                                    <option value="Andorra">Andorra</option>
                                    <option value="Angola">Angola</option>
                                    <option value="Anguilla">Anguilla</option>
                                    <option value="Antarctica">Antarctica</option>
                                    <option value="Antigua and Barbuda">Antigua and Barbuda</option>
                                    <option value="Argentina">Argentina</option>
                                    <option value="Armenia">Armenia</option>
                                    <option value="Aruba">Aruba</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Austria">Austria</option>
                                    <option value="Azerbaijan">Azerbaijan</option>
                                    <option value="Bahamas">Bahamas</option>
                                    <option value="Bahrain">Bahrain</option>
                                    <option value="Bangladesh">Bangladesh</option>
                                    <option value="Barbados">Barbados</option>
                                    <option value="Belarus">Belarus</option>
                                    <option value="Belgium">Belgium</option>
                                    <option value="Belize">Belize</option>
                                    <option value="Benin">Benin</option>
                                    <option value="Bermuda">Bermuda</option>
                                    <option value="Bhutan">Bhutan</option>
                                    <option value="Bolivia">Bolivia</option>
                                    <option value="Bosnia and Herzegovina">Bosnia and Herzegovina</option>
                                    <option value="Botswana">Botswana</option>
                                    <option value="Bouvet Island">Bouvet Island</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                    <option value="Brunei Darussalam">Brunei Darussalam</option>
                                    <option value="Bulgaria">Bulgaria</option>
                                    <option value="Burkina Faso">Burkina Faso</option>
                                    <option value="Burundi">Burundi</option>
                                    <option value="Cambodia">Cambodia</option>
                                    <option value="Cameroon">Cameroon</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Cape Verde">Cape Verde</option>
                                    <option value="Cayman Islands">Cayman Islands</option>
                                    <option value="Central African Republic">Central African Republic</option>
                                    <option value="Chad">Chad</option>
                                    <option value="Chile">Chile</option>
                                    <option value="China">China</option>
                                    <option value="Christmas Island">Christmas Island</option>
                                    <option value="Cocos (Keeling) Islands">Cocos (Keeling) Islands</option>
                                    <option value="Colombia">Colombia</option>
                                    <option value="Comoros">Comoros</option>
                                    <option value="Congo">Congo</option>
                                    <option value="Congo, The Democratic Republic of The">Congo, The Democratic Republic of The</option>
                                    <option value="Cook Islands">Cook Islands</option>
                                    <option value="Costa Rica">Costa Rica</option>
                                    <option value="Cote D'ivoire">Cote D'ivoire</option>
                                    <option value="Croatia">Croatia</option>
                                    <option value="Cuba">Cuba</option>
                                    <option value="Cyprus">Cyprus</option>
                                    <option value="Czech Republic">Czech Republic</option>
                                    <option value="Denmark">Denmark</option>
                                    <option value="Djibouti">Djibouti</option>
                                    <option value="Dominica">Dominica</option>
                                    <option value="Dominican Republic">Dominican Republic</option>
                                    <option value="Ecuador">Ecuador</option>
                                    <option value="Egypt">Egypt</option>
                                    <option value="El Salvador">El Salvador</option>
                                    <option value="Equatorial Guinea">Equatorial Guinea</option>
                                    <option value="Eritrea">Eritrea</option>
                                    <option value="Estonia">Estonia</option>
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="Falkland Islands (Malvinas)">Falkland Islands (Malvinas)</option>
                                    <option value="Faroe Islands">Faroe Islands</option>
                                    <option value="Fiji">Fiji</option>
                                    <option value="Finland">Finland</option>
                                    <option value="France">France</option>
                                    <option value="French Guiana">French Guiana</option>
                                    <option value="French Polynesia">French Polynesia</option>
                                    <option value="French Southern Territories">French Southern Territories</option>
                                    <option value="Gabon">Gabon</option>
                                    <option value="Gambia">Gambia</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="Ghana">Ghana</option>
                                    <option value="Gibraltar">Gibraltar</option>
                                    <option value="Greece">Greece</option>
                                    <option value="Greenland">Greenland</option>
                                    <option value="Grenada">Grenada</option>
                                    <option value="Guadeloupe">Guadeloupe</option>
                                    <option value="Guam">Guam</option>
                                    <option value="Guatemala">Guatemala</option>
                                    <option value="Guernsey">Guernsey</option>
                                    <option value="Guinea">Guinea</option>
                                    <option value="Guinea-bissau">Guinea-bissau</option>
                                    <option value="Guyana">Guyana</option>
                                    <option value="Haiti">Haiti</option>
                                    <option value="Heard Island and Mcdonald Islands">Heard Island and Mcdonald Islands</option>
                                    <option value="Holy See (Vatican City State)">Holy See (Vatican City State)</option>
                                    <option value="Honduras">Honduras</option>
                                    <option value="Hong Kong">Hong Kong</option>
                                    <option value="Hungary">Hungary</option>
                                    <option value="Iceland">Iceland</option>
                                    <option value="India">India</option>
                                    <option value="Indonesia">Indonesia</option>
                                    <option value="Iran, Islamic Republic of">Iran, Islamic Republic of</option>
                                    <option value="Iraq">Iraq</option>
                                    <option value="Ireland">Ireland</option>
                                    <option value="Isle of Man">Isle of Man</option>
                                    <option value="Israel">Israel</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Jamaica">Jamaica</option>
                                    <option value="Japan">Japan</option>
                                    <option value="Jersey">Jersey</option>
                                    <option value="Jordan">Jordan</option>
                                    <option value="Kazakhstan">Kazakhstan</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Kiribati">Kiribati</option>
                                    <option value="Korea, Democratic People's Republic of">Korea, Democratic People's Republic of</option>
                                    <option value="Korea, Republic of">Korea, Republic of</option>
                                    <option value="Kuwait">Kuwait</option>
                                    <option value="Kyrgyzstan">Kyrgyzstan</option>
                                    <option value="Lao People's Democratic Republic">Lao People's Democratic Republic</option>
                                    <option value="Latvia">Latvia</option>
                                    <option value="Lebanon">Lebanon</option>
                                    <option value="Lesotho">Lesotho</option>
                                    <option value="Liberia">Liberia</option>
                                    <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                    <option value="Liechtenstein">Liechtenstein</option>
                                    <option value="Lithuania">Lithuania</option>
                                    <option value="Luxembourg">Luxembourg</option>
                                    <option value="Macao">Macao</option>
                                    <option value="Macedonia, The Former Yugoslav Republic of">Macedonia, The Former Yugoslav Republic of</option>
                                    <option value="Madagascar">Madagascar</option>
                                    <option value="Malawi">Malawi</option>
                                    <option value="Malaysia">Malaysia</option>
                                    <option value="Maldives">Maldives</option>
                                    <option value="Mali">Mali</option>
                                    <option value="Malta">Malta</option>
                                    <option value="Marshall Islands">Marshall Islands</option>
                                    <option value="Martinique">Martinique</option>
                                    <option value="Mauritania">Mauritania</option>
                                    <option value="Mauritius">Mauritius</option>
                                    <option value="Mayotte">Mayotte</option>
                                    <option value="Mexico">Mexico</option>
                                    <option value="Micronesia, Federated States of">Micronesia, Federated States of</option>
                                    <option value="Moldova, Republic of">Moldova, Republic of</option>
                                    <option value="Monaco">Monaco</option>
                                    <option value="Mongolia">Mongolia</option>
                                    <option value="Montenegro">Montenegro</option>
                                    <option value="Montserrat">Montserrat</option>
                                    <option value="Morocco">Morocco</option>
                                    <option value="Mozambique">Mozambique</option>
                                    <option value="Myanmar">Myanmar</option>
                                    <option value="Namibia">Namibia</option>
                                    <option value="Nauru">Nauru</option>
                                    <option value="Nepal">Nepal</option>
                                    <option value="Netherlands">Netherlands</option>
                                    <option value="Netherlands Antilles">Netherlands Antilles</option>
                                    <option value="New Caledonia">New Caledonia</option>
                                    <option value="New Zealand">New Zealand</option>
                                    <option value="Nicaragua">Nicaragua</option>
                                    <option value="Niger">Niger</option>
                                    <option value="Nigeria">Nigeria</option>
                                    <option value="Niue">Niue</option>
                                    <option value="Norfolk Island">Norfolk Island</option>
                                    <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                    <option value="Norway">Norway</option>
                                    <option value="Oman">Oman</option>
                                    <option value="Pakistan">Pakistan</option>
                                    <option value="Palau">Palau</option>
                                    <option value="Palestinian Territory, Occupied">Palestinian Territory, Occupied</option>
                                    <option value="Panama">Panama</option>
                                    <option value="Papua New Guinea">Papua New Guinea</option>
                                    <option value="Paraguay">Paraguay</option>
                                    <option value="Peru">Peru</option>
                                    <option value="Philippines">Philippines</option>
                                    <option value="Pitcairn">Pitcairn</option>
                                    <option value="Poland">Poland</option>
                                    <option value="Portugal">Portugal</option>
                                    <option value="Puerto Rico">Puerto Rico</option>
                                    <option value="Qatar">Qatar</option>
                                    <option value="Reunion">Reunion</option>
                                    <option value="Romania">Romania</option>
                                    <option value="Russian Federation">Russian Federation</option>
                                    <option value="Rwanda">Rwanda</option>
                                    <option value="Saint Helena">Saint Helena</option>
                                    <option value="Saint Kitts and Nevis">Saint Kitts and Nevis</option>
                                    <option value="Saint Lucia">Saint Lucia</option>
                                    <option value="Saint Pierre and Miquelon">Saint Pierre and Miquelon</option>
                                    <option value="Saint Vincent and The Grenadines">Saint Vincent and The Grenadines</option>
                                    <option value="Samoa">Samoa</option>
                                    <option value="San Marino">San Marino</option>
                                    <option value="Sao Tome and Principe">Sao Tome and Principe</option>
                                    <option value="Saudi Arabia">Saudi Arabia</option>
                                    <option value="Senegal">Senegal</option>
                                    <option value="Serbia">Serbia</option>
                                    <option value="Seychelles">Seychelles</option>
                                    <option value="Sierra Leone">Sierra Leone</option>
                                    <option value="Singapore">Singapore</option>
                                    <option value="Slovakia">Slovakia</option>
                                    <option value="Slovenia">Slovenia</option>
                                    <option value="Solomon Islands">Solomon Islands</option>
                                    <option value="Somalia">Somalia</option>
                                    <option value="South Africa">South Africa</option>
                                    <option value="South Georgia and The South Sandwich Islands">South Georgia and The South Sandwich Islands</option>
                                    <option value="Spain">Spain</option>
                                    <option value="Sri Lanka">Sri Lanka</option>
                                    <option value="Sudan">Sudan</option>
                                    <option value="Suriname">Suriname</option>
                                    <option value="Svalbard and Jan Mayen">Svalbard and Jan Mayen</option>
                                    <option value="Swaziland">Swaziland</option>
                                    <option value="Sweden">Sweden</option>
                                    <option value="Switzerland">Switzerland</option>
                                    <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                    <option value="Taiwan">Taiwan</option>
                                    <option value="Tajikistan">Tajikistan</option>
                                    <option value="Tanzania, United Republic of">Tanzania, United Republic of</option>
                                    <option value="Thailand">Thailand</option>
                                    <option value="Timor-leste">Timor-leste</option>
                                    <option value="Togo">Togo</option>
                                    <option value="Tokelau">Tokelau</option>
                                    <option value="Tonga">Tonga</option>
                                    <option value="Trinidad and Tobago">Trinidad and Tobago</option>
                                    <option value="Tunisia">Tunisia</option>
                                    <option value="Turkey">Turkey</option>
                                    <option value="Turkmenistan">Turkmenistan</option>
                                    <option value="Turks and Caicos Islands">Turks and Caicos Islands</option>
                                    <option value="Tuvalu">Tuvalu</option>
                                    <option value="Uganda">Uganda</option>
                                    <option value="Ukraine">Ukraine</option>
                                    <option value="United Arab Emirates">United Arab Emirates</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States">United States</option>
                                    <option value="United States Minor Outlying Islands">United States Minor Outlying Islands</option>
                                    <option value="Uruguay">Uruguay</option>
                                    <option value="Uzbekistan">Uzbekistan</option>
                                    <option value="Vanuatu">Vanuatu</option>
                                    <option value="Venezuela">Venezuela</option>
                                    <option value="Viet Nam">Viet Nam</option>
                                    <option value="Virgin Islands, British">Virgin Islands, British</option>
                                    <option value="Virgin Islands, U.S.">Virgin Islands, U.S.</option>
                                    <option value="Wallis and Futuna">Wallis and Futuna</option>
                                    <option value="Western Sahara">Western Sahara</option>
                                    <option value="Yemen">Yemen</option>
                                    <option value="Zambia">Zambia</option>
                                    <option value="Zimbabwe">Zimbabwe</option>
                                </select>
                            </div>
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="postalcode">Postal Code</label>
                                <input type="text" class="form-control" name="postalcode" id="postalcode" value="" placeholder="Postal code of destination country" required />
                            </div>
                            <div class="form-group col-md-12 col-sm-12">
                                <label for="address">Address</label>
                                <input type="text" class="form-control" name="address" id="address" value="" placeholder="Address" required />
                            </div>
                            <!-- Please do not edit this code - https://xeconvert.com/currency-converter-widget starts here --><iframe src="https://xeconvert.com/widget1?from=eur&to=usd&lang=&theme=blue&font=12" width="100%" height="300" frameborder="0" scrolling="no"></iframe><div style="font-size:12px;font-family:arial;text-align:right;"><a target="_blank" href="https://xeconvert.com/" style="text-decoration:none;color:#999;">Currency converter</a></div><!-- https://xeconvert.com/currency-converter-widget ends here -->
                            <div class="form-group col-md-12 col-sm-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="dynamicTable">
                                        <tr>
                                            <th>Product Name</th>
                                            <th>Product Description</th>
                                            <th>Product URL</th>
                                            <th>Product Quantity</th>
                                            <th>Price Per Unit(USD$)</th>
                                            <th>Weight Per Unit (Gram)</th>
                                            <th>Total Price</th>
                                            <th>Add More</th>
                                        </tr>
                                        <tr>
                                            <td><input type="text" name="addmore[0][productname]" placeholder="Name" class="form-control" required /></td>
                                            <td><input type="text" name="addmore[0][product_description]" placeholder="Description" class="form-control" required /></td>
                                             <td><input type="text" name="addmore[0][producturl]" placeholder="URL" class="form-control" required /></td>
                                            <td><input type="number" name="addmore[0][productquantity]" placeholder="Qty" class="form-control quantity" min="0" required /></td>
                                            <td><input type="number" name="addmore[0][productprice]" placeholder="Price" class="form-control price" min="0" required /></td>
                                            <td><input type="number" name="addmore[0][productweight]" placeholder="weight" class="form-control weight" min="0" required /></td>
                                            <td><input type="number" name="addmore[0][producttotal]" placeholder="Total Price" class="form-control producttotal" min="0" readonly required /></td>
                                            <td class="hide"><input type="number" name="addmore[0][product_weight]" class="form-control product_weight" /></td>
                                            <td class="text-center"><button type="button" name="add" id="add" class="btn btn-success"><i class="fa fa-plus"></i></button></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Total Approximate Weight in Gram</label>
                                <input type="text" class="form-control net_weight" name="approximate_weight" value="" placeholder="Approximate weight" readonly />
                            </div>
                            <div class="form-group col-md-6 col-sm-12">
                                <label for="name">Products Total Price</label>
                                <input type="number" class="form-control net_total" name="total_price" value="" placeholder="Product total price" min="0" readonly>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-10 check">
                               <!--  </--> <input name="product_consolidation" value="6" type="checkbox" id="address_6" onclick="total_address()" class="example" required /> <label for="address_6 " class="important">Package Consolidation </label>
                                <br>
                                <input name="product_services" value="9" type="checkbox" id="address_7" onclick="total_address()" class="example" required /> <label for="address_7" class="important">Forwarding Service Fee</label>
                                <br>
                                <input name="purchase_assistence" value="0" type="hidden" />
                                <input name="purchase_assistence" value="10" type="checkbox" id="purchase_7" class="example" /> <label for="purchase_7" class="important"> Purchase Assistance </label>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 check">
                                <input name="product_photo" value="0" type="hidden" />
                                <input name="product_photo" value="3" type="checkbox" id="address_1" onclick="total_address()" class="example" /> <label for="address_1">Product photo </label>
                                <br>
                                <input name="product_customs" value="0" type="hidden" />
                                <input name="product_customs" value="4" type="checkbox" id="address_2" onclick="total_address()" class="example" /> <label for="address_2">Customs Declaration </label>
                                <br>
                                <input name="product_check" value="0" type="hidden" />
                                <input name="product_check" value="3" type="checkbox" id="address_3" onclick="total_address()" class="example" /> <label for="address_3">Content Check </label>
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 check">
                                <input name="product_prohibited" value="0" type="hidden" />
                                <input name="product_prohibited" value="1" type="checkbox" id="address_4" onclick="total_address()" class="example" /> <label for="address_4">Removal of Prohibited Items </label>
                                <br>
                                <input name="product_disinfection" value="0" type="hidden" />
                                <input name="product_disinfection" value="1" type="checkbox" id="address_5" onclick="total_address()" class="example" /> <label for="address_5">Disinfection </label>
                            </div>
                            <div class="col-md-12 col-sm-12 check">
                                <input type="checkbox" value="1" name="terms" id="agree_1" required /> <label for="agree_1">By clicking the tick button, I hereby agree and consent to the terms of business, its policies, and the Privacy Policy.</label>
                                <p class="style-p mt-2">Your request will be directly send over to our administration , who will promptly go through all the details provided , and will offer you the best price and cost for services including "shipping fees".</p>
                                <p class="style-p">Please keep an eye on your dashboard and email notification.<br> You will not pay any thing at this stage of the request.<br> We try our best to resolve the query and offer to the asked request within 1 hour.</p>
                                <br>
                                <input value="$0" readonly="readonly" type="hidden" id="paynow" name="total" class="text-center p-2 total" />
                                <!-- <input value="$0" readonly="readonly" type="text" id="paynow" name="total" class="text-center p-2 total" /> -->
                                <button type="submit" id='btnAddProfile' class="btn placeorder ">Place Request</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div id="loader"></div>
    </div>
</section>
<script>
    $(document).ready(function() {
        $('.hide').hide();
        var grand_total = 0;
        $('.example').each(function() {
            if ($(this).prop("checked")) {
                grand_total += parseFloat($(this).val());
                // alert(grand_total);
            }
        })
        $('input[name="total"]').val(grand_total);
        // $("#btnAddProfile").html('Place Request ' + '$' + grand_total);
        // Price on the basis of Quantity
        $(document).on("change keyup", ".quantity", function() {
            var total = 0;
            var net_total = 0;
            var each_quantity = 0;
            var each_weight = 0;
            var net_weight = 0;
            var price = parseFloat($(this).parent().next().find($('.price')).val());
            var weight = parseFloat($(this).parent().next().next().find($('.weight')).val());
            var quantity = parseFloat($(this).val());
            if (isNaN(price)) {
                price = 0;
            }
            if (isNaN(weight)) {
                weight = 0;
            }
            total = price * quantity;
            each_weight = quantity * weight;
            console.log(each_weight);
            $(this).parent().next().next().next().find($('.producttotal')).val(total);
            $(this).parent().next().next().next().next().find($('.product_weight')).val(each_weight);
            $('.producttotal').each(function() {
                net_total += parseFloat($(this).val());
            });
            $('.net_total').val(net_total);
            $('.product_weight').each(function() {
                net_weight += parseFloat($(this).val());
            });
            $('.net_weight').val(net_weight);
            // console.log(each_quantity);
            // alert(quantity);
            if ($('#purchase_7').prop("checked") && $(".net_total").val() != '') {
                var grand_total = 0;
                grand_total += parseFloat($(".net_total").val());
                $('.example').each(function() {
                    if ($(this).prop("checked")) {
                        grand_total += parseFloat($(this).val());
                    }
                })
                $('input[name="total"]').val(grand_total);
                // $("#btnAddProfile").html('Place Request ' + '$' + grand_total);
                // $('input[name="total"]').val(grand_total);
            }
        });
        $(document).on("change keyup", ".weight", function() {
            var each_quantity = 0;
            var each_weight = 0;
            var net_weight = 0;
            var quantity = parseFloat($(this).parent().prev().prev().find($('.quantity')).val());

            var weight = parseFloat($(this).val());
            if (isNaN(quantity)) {
                quantity = 0;
            }
            each_weight = weight * quantity;

            $(this).parent().next().next().find($('.product_weight')).val(each_weight);

            $('.product_weight').each(function() {
                net_weight += parseFloat($(this).val());
            });
            $('.net_weight').val(net_weight);

        });
        $(document).on("change keyup", ".price", function() {
            var total = 0;
            var net_total = 0;
            var grand_total = 0;
            var quantity = parseFloat($(this).parent().prev().find($('.quantity')).val());

            var price = parseFloat($(this).val());
            if (isNaN(quantity)) {
                quantity = 0;
            }
            total = price * quantity;

            $(this).parent().next().next().find($('.producttotal')).val(total);

            $('.producttotal').each(function() {
                net_total += parseFloat($(this).val());
            });
            $('.net_total').val(net_total);

            if ($('#purchase_7').prop("checked") && $(".net_total").val() != '') {
                var grand_total = 0;
                grand_total += parseFloat($(".net_total").val());
                $('.example').each(function() {
                    if ($(this).prop("checked")) {
                        grand_total += parseFloat($(this).val());
                    }
                })
                $('input[name="total"]').val(grand_total);
                // $("#btnAddProfile").html('Place Request ' + '$' + grand_total);
                // $('input[name="total"]').val(grand_total);
            }
        }); //price end 

    }); //
    function total_address() {
        // var input = document.getElementsByName("product_photo");
        var input = document.getElementsByClassName("example");
        var total = 0;
        for (var i = 0; i < input.length; i++) {
            if (input[i].checked) {
                total += parseFloat(input[i].value);
            }
        }
        if ($('#purchase_7').prop("checked") && $(".net_total").val() != '') {
            total += parseFloat($(".net_total").val());
        }

        document.getElementsByName("total")[0].value = total.toFixed(2);
        // $("#btnAddProfile").html('Place Request ' + '$' + total.toFixed(2));
        // document.getElementsByName("total")[0].value = total.toFixed(2);
    }
    // total by on purchase assistance
    $('#purchase_7').on('change', function() {
        var total;
        var services_total = 0;
        $('.example').each(function() {
            if ($(this).prop("checked")) {
                services_total += parseFloat($(this).val());
            }
        });
        if ($(this).prop("checked") && $(".net_total").val() != '') {
            var products_total = parseFloat($(".net_total").val());
            total = products_total + services_total;
        } else {
            total = services_total;
        }
        $('input[name="total"]').val(total);
        // $("#btnAddProfile").html('Place Request ' + '$' + total);
        // $('input[name="total"]').val(total);
    });
</script>
<script type="text/javascript">
    var i = 0;
    $("#add").click(function() {
        $('.hide').hide();
        ++i;
        $("#dynamicTable").append('<tr><td><input type="text" name="addmore[' + i + '][productname]" placeholder=" Name" class="form-control" required /></td><td><input type="text" name="addmore[' + i + '][product_description]" placeholder="Desciption" class="form-control" required /></td><td><input type="text" name="addmore[' + i + '][producturl]" placeholder=" URL" class="form-control" required /></td><td><input type="text" name="addmore[' + i + '][productquantity]" placeholder=" Quantity" class="form-control quantity" min="0" required /></td><td><input type="number" name="addmore[' + i + '][productprice]" placeholder=" Price" class="form-control price" min="0" required /></td><td><input type="number" name="addmore[' + i + '][productweight]" placeholder="Weight/ unit" class="form-control weight" min="0" required /></td><td><input type="number" name="addmore[' + i + '][producttotal]" placeholder="Total Price" class="form-control producttotal" /></td><td class="hide"><input type="number" name="addmore[' + i + '][product_weight]" class="form-control product_weight" /></td> <td class="text-center"><button type="button" name="add" id="add" class="btn btn-success add"><i class="fa fa-plus"></i></button></td><td class="text-center"><button type="button" class="btn btn-danger remove-tr"><i class="fa fa-minus"></button></td></tr>');

    });
    $(document).on('click', '.add', function() {
        ++i;
        $("#dynamicTable").append('<tr><td><input type="text" name="addmore[' + i + '][productname]" placeholder=" Name" class="form-control" required /></td><td><input type="text" name="addmore[' + i + '][product_description]" placeholder="Desciption" class="form-control" required /></td><td><input type="text" name="addmore[' + i + '][producturl]" placeholder=" URL" class="form-control" required /></td><td><input type="text" name="addmore[' + i + '][productquantity]" placeholder=" Quantity" class="form-control quantity" min="0" required /></td><td><input type="number" name="addmore[' + i + '][productprice]" placeholder=" Price" class="form-control price" min="0" required /></td><td><input type="number" name="addmore[' + i + '][productweight]" placeholder="Weight/unit" class="form-control weight" min="0" required /></td><td><input type="number" name="addmore[' + i + '][producttotal]" placeholder="Total Price" class="form-control producttotal" /></td><td class="hide"><input type="number" name="addmore[' + i + '][product_weight]" class="form-control product_weight" /></td> <td class="text-center"><button type="button" name="add" id="add" class="btn btn-success add"><i class="fa fa-plus"></i></button></td><td class="text-center"><button type="button" class="btn btn-danger remove-tr"><i class="fa fa-minus"></button></td></tr>');
    });
    $(document).on('click', '.remove-tr', function() {
        var net_total = 0;
        var net_weight = 0;
        $(this).parents('tr').remove();
        $('.producttotal').each(function() {
            net_total += parseFloat($(this).val());
        });

        $('.net_total').val(net_total);
        $('.product_weight').each(function() {
            net_weight += parseFloat($(this).val());
        });

        $('.net_weight').val(net_weight);

        if ($('#purchase_7').prop("checked") && $(".net_total").val() != '') {
            var grand_total = 0;
            grand_total += parseFloat($(".net_total").val());
            $('.example').each(function() {
                if ($(this).prop("checked")) {
                    grand_total += parseFloat($(this).val());
                }
            });
            $('input[name="total"]').val(grand_total);
            // $("#btnAddProfile").html('Place Request ' + '$' + grand_total);
        }
    });
</script>
<script>
    $('#request-form').submit(function() {
        $('#loader').css('visibility', 'visible');
    });
</script>
@endsection