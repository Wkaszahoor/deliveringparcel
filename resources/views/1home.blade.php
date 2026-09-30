@extends('layouts.master')
@section('title','Home')
@section('keywords', 'Christmas shopping and shipping , Anywhere to everywhere shop to ship , ship to anywhere')
@section('content')
<section class="container-fluid topbanner">
    <div class="row m-0">
        <div class="col-xl-5 col-lg-11 col-sm-10 left-col m-auto topbanner-col">
            <h1 class="style-h1 medium fontsize pt-2">Anywhere to <br> Everywhere. </h1>
            <!--<h2 class="style-h2 medium"> Shop to Ship</h2>-->
            <h2 class="style-h4 medium color-h2 pt-1 pb-1">Shop from anywhere in the world and get it ship everywhere.</h2>
            <!--<p class="style-p medium">Enjoy the easiest way to get products and items that don't ship to your country!</p>-->
            <h3 class="style-h4 medium color-p pt-1 pb-1">Shop online stores and websites in the world to get products and items that don’t ship to your country.</h3>
            <div>
                <form class="form-group" action="{{route('country')}}" method="get" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class=" col-lg-6 col-md-6 col-12">
                            <span class="label" for="from">From:</span>
                            <select name="from" id="from" class="form-control select" required>
                                <!-- <option value="" disabled >Choose Employee</option> -->
                                <option disabled value="" selected>Select Country</option>
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
                            <!-- <span class="label">From:</span><input type="text" name="from" placeholder="" class="form-control form"> -->
                        </div>
                        <div class="col-lg-6 col-md-6 col-12">
                            <span for="to" class="label">To:</span>
                            <select name="to" id="to" class="form-control select" required>
                                <!-- <option value="" disabled >Choose Employee</option> -->
                                <option value="" disabled selected>Select country</option>
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
                            <!-- <input type="text" name="to" placeholder="" class="form-control form" required /> -->
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-lg-6 button">
                            <a href=""> <button type="submit" class="btn btn-block">Create a request</button></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="col-xl-5 col-lg-11 col-sm-10 text-center topbanner-col right-col m-auto">
            <img src="{{asset('images/house@2x.png')}}" alt="house image">
            <h1 class="style-h1 pt-5">Free residential address in every country for shopping and shipping,</h1>
            <h1 class="pt-3 style-h1 color"> parcel forwarding services as low as </h1>
            <span>9 $</span>
        </div>
    </div>
</section>
<section class="work-heading m-0">
    <div class="container">
        <h1 class="text-center style-h1">How does Deliveringparcel work?</h1>
    </div>
</section>
<section class="card-section">
    <div class="container">
        <div class="row">
            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 m-auto">
                <div class="card">
                    <img src="{{asset('images/delivery-woman.png')}}" class="card-img img-fluid" alt="delivery woman image">
                    <div class="card-body">
                        <h2 class="card-title">Get an address in the country where you want to shop from</h2>
                        <!--<p class="card-text style-p">Deliveringparcel.com is an online service that helps you get goods, which are not available in your country.</p>-->
                        <p class="card-text style-p">Shop from any country in the world like US, UK, AUSTRALIA, GERMANY, SPAIN, ITALY, JAPAN, EU, ASIA and get a free address for shopping and shipping.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 d-block m-auto">
                <div class="card">
                    <img src="{{asset('images/iStock_000053559384_Medium.png')}}" class="card-img img-fluid" alt="istock medium image">
                    <div class="card-body">
                        <h2 class="card-title">Shop US, UK, AUSTRALIA, EUROPE, ASIA STORES AND Websites Online and Ship Worldwide</h2>
                        <p class="card-text style-p">Shop online stores and websites like amazon, apple, Walmart, Woolworths, Tesco, carrefour, eBay, AliExpress and any stores and websites in the country and get it shipped to your address. Get a free residential address and start shopping US, UK, Australia, Europe online stores and get your goods and item tax free</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 d-block m-auto">
                <div class="card">
                    <img src="{{asset('images/mn.png')}}" class="card-img img-fluid" alt="printing to send one image">
                    <div class="card-body">
                        <h2 class="card-title">Parcel Forwarding and Reshipping Services Worldwide</h2>
                        <p class="card-text style-p">Best parcel and package forwarding services worldwide. Shop from popular stores and websites in US, UK, Australia, Canada, Europe, Asia and get it reshipped worldwide.</p>
                        <!--<p class="card-text style-p">Our address and service points are all available for collection at any point. Our in-house Members can print the labels and Do custom declarations and attach them to your package, making It all ready and available for collection before the set date. </p>-->
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 d-block m-auto">
                <div class="card">
                    <img src="{{asset('images/750px.png')}}" class="card-img img-fluid" alt="purchase assistence image">
                    <div class="card-body">
                        <h2 class="card-title">Purchase Assistance</h2>
                        <p class="card-text style-p">We buy for you and ship worldwide in case the store Does not accept international payments, or it does not allow international billing </p>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 d-block m-auto">
                <div class="card">
                    <img src="{{asset('images/order-consolidation.png')}} " class="card-img img-fluid" alt="Package consalidation image">
                    <div class="card-body">
                        <h2 class="card-title ">Package consalidation</h2>
                        <p class="card-text style-p">To reduce the weight of the actual package, we can find the best solution for the packaging and reduce the shipping cost to almost half. Best communication will be established so that you should know what exactly is going around. Photos of packages are provided to satisfy the shopper.</p>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-lg-6 col-md-6 col-sm-12 col-12 d-block m-auto">
                <div class="card">
                    <img src="{{asset('images/adobestock_24172267_resized.png')}}" class="card-img img-fluid" alt="Custom Declaration image">
                    <div class="card-body">
                        <h2 class="card-title">Custom Declaration</h2>
                        <p class="card-text style-p">You can save up to 70 % with us with the help of our members and shippers. We can do the customs declaration in the best of your resources and clearance. Our ‘Residential addresses can be a big factor in custom clearance.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="text-center">
            <a href="{{route('request')}}"><button type="button" class="btn btn-lg custom-btn"> Create a Request</button></a>
        </div>

    </div>

</section>

<section class="container-fluid courier">
    <h1 class="text-center style-h1">Choose your own Courier</h1>
    <p class="text-center style-p"> A Very unique but effective service where you take control and monitor each and every step till you get hold on your item. Our members and shippers will be available at your service to make sure your item is safely handled and delivered to you. We gave you all the choice of the world to choose your own shipping company online, use our address for collection and order it</p>
    <div class="container-fluid curier-img">
        <div class="row mt-3">
            <div class=" col-md-2 col-sm-10 ">
                <a href=""><img src="{{asset('images/canada-post.png')}}" class="img-fluid" alt="canada post image"></a>
            </div>

            <div class=" col-md-2 col-sm-10 text-center ">
                <a href=""><img src=" {{asset('images/Fedex-02 [Converted].png')}}" class="img-fluid" alt="fedex image"></a>
            </div>

            <div class=" col-md-2 col-sm-10 text-center">
                <a href=""><img src="{{asset('images/shipp.png')}}" class="img-fluid" alt="shipping image"></a>
            </div>

            <div class=" col-md-2 col-sm-10 text-center">
                <a href=""><img src="{{asset('images/Ups-01 [Converted].png')}}" class="img-fluid" alt="ups image"></a>
            </div>

            <div class=" col-md-2 col-sm-10 ">
                <a href=""><img src="{{asset('images/Dhl-01 [Converted].png')}}" class="img-fluid" alt="DHL image"></a>
            </div>

            <div class=" col-md-2 col-sm-10 ">
                <a href=""><img src="{{asset('images/United-States-Postal-Service-01 [Converted].png')}}" class="img-fluid" alt="united state post image"></a>
            </div>

        </div>
    </div>
</section>
<section class="container-fluid tracking">
    <div class="container">
        <div class="row ">
            <div class="col-md-6 col-sm-8 col-12 m-auto">
                <h2 class="style-h1">Parcel tracking </h2>
                <p class="style-p">ALL THE PACKAGES ARE SEND THROUGH WORLDWIDE TRUSTED LOGISTIC PARTNERS AND COURIERS WITH FULL INSURANCE AND TRACKING, SO THE SHOPPER CAN TRACK THE TRANSIT TIME OF THE DELIVERY.</p>
            </div>
            <div class="col-md-6 col-sm-8 ">
                <img src="{{asset('images/Untitled-2.png')}}" class="img-fluid" alt="parcel tracking">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 col-sm-8 col-12 ">
                <img src="{{asset('images/Group 2397.png')}}" class="img-fluid" alt="secure transaction">
            </div>
            <div class="col-md-6 col-sm-8 m-auto">
                <h2 class="style-h1">Secure Transaction </h2>
                <p class="style-p">ALL THE TRANSACTIONS ARE SECURED BANK PAYMENTS AND FULLY PROTECTED BY INTEGRATED SECURITY SYSTEM AND SELLER PROTECTION WITH MONEY BACK GUARANTEE.</p>
            </div>
        </div>
        <div class="container text-center">
            <a href="{{route('request')}}"><button type="button" class="btn btn-lg custom-btn">Create a Request</button></a>
        </div>
    </div>
</section>
<section class="container-fluid testimonials">
    <div class="container text-center">
        <h1 class="style-h1">FULFILLMENT SERVICES</h1>
        <!--<p class="text-center style-p">DeliveringParcel is your one-stop shipping shop, specializing in Ecommerce Fulfilment Services, product management, and storage for online shops, Shopify, Woocommerce, eBay, and Amazon sellers. Our system seamlessly integrates with the most popular online store software as well as eBay fulfillment and Amazon fulfilled by the merchant to automate all your orders from receiving orders to shipping them out we cover the whole process automatically so you can sit back, relax and concentrate on sales.<br>We can also control your stock, send us your stock direct from anywhere in the world and we can help with importation, customs, stock checking, and condition reports on your behalf, we have daily arrivals of containers and couriers from worldwide, we accept deliveries direct from manufacturers. We take the work out of picking, packing, and posting your customers orders and our competitive postage rates ensure a reduction in your postage costs, we can also offer competitive packaging costs with a large stock of bags, boxes, and packing materials always kept close to hand. you can shop to ship anywhere to everywhere.</p>-->
        <p class="text-center style-p">Order anywhere from amazon and get it reshipped to any country. we will deliver the amazon parcel to your door step by forwarding and reshipping it from US, UK, Canada, Australia, Europe and Asia. DeliveringParcel is your one-stop shipping shop, specializing in Ecommerce Fulfilment Services, product management, and storage for online shops, Shopify, Woocommerce, eBay, and Amazon sellers. Our system seamlessly integrates with the most popular online store software as well as eBay fulfillment and Amazon fulfilled by the merchant to automate all your orders from receiving orders to shipping them out we cover the whole process automatically so you can sit back, relax and concentrate on sales.<br>We can also control your stock, send us your stock direct from anywhere in the world and we can help with importation, customs, stock checking, and condition reports on your behalf, we have daily arrivals of containers and couriers from worldwide, we accept deliveries direct from manufacturers. We take the work out of picking, packing, and posting your customers orders and our competitive postage rates ensure a reduction in your postage costs, we can also offer competitive packaging costs with a large stock of bags, boxes, and packing materials always kept close to hand. you can shop to ship anywhere to everywhere.</p>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <div class="profile">
                    <img src="{{asset('images/Screenshot_20210106-001235_WhatsApp-150x150.jpg')}}" class="user" alt="it specialist">
                    <p>I have been using Delivering Parcel for many months now. I found their services on a website by chance and have been using them since. Unreal service, great communication, responsive, and happy to help as much as they can. Items are purchased promptly for me, photos of them are sent and items arrive as expected. Cannot vouch for enough for this service, 10/10</p>
                    <span class="fa fa-star checked "></span>
                    <span class="fa fa-star checked"></span>
                    <span class="fa fa-star checked"></span>
                    <span class="fa fa-star checked"></span>
                    <span class="fa fa-star checked"></span>
                    <h3>LEO JACKSON </h3>
                    <h4>IT Specialist</h4>
                </div>
            </div>

            <div class="col-md-4 text-center  mb-4">
                <div class="profile">
                    <img src="{{asset('images/Screenshot_20201231-182807_WhatsApp-150x150.jpg')}}" class="user" alt="Admin & support">
                    <p>I used "Delivering parcel" services several times and was very pleased with the attention I got. All my questions were answered very efficiently and in a very short time. The parcels were delivered safely and on time. Definitely will use this company's services again..</p>
                    <span class="fa fa-star checked"></span>
                    <span class="fa fa-star checked"></span>
                    <span class="fa fa-star checked"></span>
                    <span class="fa fa-star checked"></span>
                    <span class="fa fa-star checked"></span>
                    <h3>Ella K</h3>
                    <h4>Admin & Support</h4>
                </div>
            </div>
            <div class="col-md-4 text-center mb-4">
                <div class="profile">
                    <img src="{{asset('images/Screenshot_20210117-105317_WhatsApp-150x150.jpg')}}" class="user" alt="logistics manager">
                    <p>Whoever is running your business, you guys are great!! Everything went fast, smooth, with easy and good communication. Fabulous!!! We will be doing business in the future and recommend your company to everybody I know. Thanks again for being easy and uncomplicated</p>
                    <span class="fa fa-star checked "></span>
                    <span class="fa fa-star checked "></span>
                    <span class="fa fa-star checked "></span>
                    <span class="fa fa-star checked"></span>
                    <span class="fa fa-star checked"></span>
                    <h3>SVITLANA </h3>
                    <h4>Logistics Manager</h4>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="estimate">
    <div class="container text-center">
        <h1 class="style-h1">GET A QUICK ESTIMATE</h1>
        <p class="text-center">Get the most economical rates from the most trusted couriers across the globe. We work hard to give you the best solution according to your budget and requirement. Please get in touch with us to get a quick quote through live chat, email, or phone number.</p>
        <form action="">
            <div class="row">
                <div class="col-md-4 col-12 ">
                    <h3>Select Destination</h3>
                </div>
                <div class="col-md-4 col-12 ">
                    <td>
                        <input type="text" name="from" placeholder="UK - Prices from 9.9 $" class="form-control form">
                    </td>
                </div>
                <div class="col-md-4 col-12">
                    <a href=""><button type="button" class="btn btn-lg custom-btn">Submit</button></a>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection