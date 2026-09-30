@extends('layouts.fmaster')

@section('title', 'Parcel & Package Forwarding Service | International Mail Forwarding')

@section('meta_description', 'Affordable parcel and package forwarding service with free US, UK and Europe address. Shop internationally, consolidate packages, and ship worldwide at the cheapest rates.')
<!--@section('keywords', 'parcel forwarding, package forwarding, international parcel forwarding, mail forwarding service, reshipping service, shop and ship, buy and ship, uk package forwarding, parcel reshipper, proxy buyer, free US address, free UK address, Europe reshipper, UAE parcel forwarding, Shipito alternative, Forward2me alternative, Shippn alternative, cross border shopping, package consolidation, forwarding address, international shipping address, package receiving service, shipping consolidator')-->
@section('keywords','international parcel forwarding ,package forwarding,freight forwarder,forwarded,newegg,forwarding address,where does newegg ship from, Forward2me, shop for me service, Netherlands forwarding address, Shippn alternative, parcel consolidation, Reship review, shop forwarding, best mail forwarding, remailing, Europe address, forwarding to you, reship service, Planet express ship, ship to uk, ship to germany, package hooper, best uk proxy service, premium package forwarding, reship it, forwarding service requested, ship me, third party shipping, buy and ship, forward parcel, forwarding agent, virtual free address, can i forward my parcel online, reshipper, Vyking ship, forwarder in france, forward via, premium mail, ship it to us, Reddit Shippn, Shapito, usa adresse,mail forwarding service, reshipping service, shop and ship, buy and ship, uk package forwarding, parcel reshipper, proxy buyer, free US address, free UK address, Europe reshipper, UAE parcel forwarding, Shipito alternative, Forward2me alternative, Shippn alternative, cross border shopping, package consolidation, forwarding address, international shipping address, package receiving service, shipping consolidator')
@section('og_title', 'Best package forwarding service | International Mail Forwarding')
@section('og_description', 'Affordable parcel and package forwarding service with free US,UK & EU address. Shop internationally,consolidate packages and ship worldwide at cheapest rates')

@push('head')
{{-- Flag-icon-css stylesheet now loaded sitewide in fcommon/head.blade.php
     (the header language switcher needs it on every page, not just here). --}}
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
        {"@type":"Question","name":"What is parcel forwarding used for?","acceptedAnswer":{"@type":"Answer","text":"Parcel forwarding is mostly used for online shopping in another country via websites that do not deliver directly to your country. It also saves on shipping costs when you combine multiple shipments into one parcel."}},
        {"@type":"Question","name":"Is parcel forwarding safe?","acceptedAnswer":{"@type":"Answer","text":"Yes, most parcel forwarding companies are safe. It is relatively safe and reliable, especially when dealing with a reputable company. Always check customer reviews before subscribing to a service."}},
        {"@type":"Question","name":"How much does a parcel forwarding service cost?","acceptedAnswer":{"@type":"Answer","text":"Most are free to sign up. The overall cost depends on the size of your parcel, weight, and how many packages you combine."}},
        {"@type":"Question","name":"What is the cheapest package forwarding service?","acceptedAnswer":{"@type":"Answer","text":"Delivering Parcel is one of the cheapest package forwarding services, offering no membership or monthly fees, tax-free shipping, and free 45-day package storage."}},
        {"@type":"Question","name":"What is parcel forwarding?","acceptedAnswer":{"@type":"Answer","text":"Package forwarding gives you a free local address in the US, UK, Europe or Australia and forwards your purchases to your location worldwide."}},
        {"@type":"Question","name":"What is the \"Buy For Me\" service?","acceptedAnswer":{"@type":"Answer","text":"If a store won't let you check out from your country, or won't accept your card, tell us what you want and we'll purchase it on your behalf using your Delivering Parcel address, then forward it to you like any other order."}},
        {"@type":"Question","name":"Can Delivering Parcel handle customs declarations?","acceptedAnswer":{"@type":"Answer","text":"Yes. Every shipment leaves with an accurate customs declaration handled for you, and consolidating multiple purchases into one shipment can also help reduce the total customs cost you pay."}}
    ]
}
</script>
@endpush

@section('content')

@php
    // Name/ISO-3166-1 alpha-2 pairs for the hero "ship from" flag picker —
    // same country strings as route('country') already expects, just paired
    // with a code so each row can show a flag-icon-css flag next to the name
    // (native <option> elements can't render background-image flags in any
    // browser, hence the custom Alpine dropdown below instead of a <select>).
    $shipFromCountries = [
        ['name' => 'Afghanistan', 'code' => 'af'],
        ['name' => 'Åland Islands', 'code' => 'ax'],
        ['name' => 'Albania', 'code' => 'al'],
        ['name' => 'Algeria', 'code' => 'dz'],
        ['name' => 'American Samoa', 'code' => 'as'],
        ['name' => 'Andorra', 'code' => 'ad'],
        ['name' => 'Angola', 'code' => 'ao'],
        ['name' => 'Anguilla', 'code' => 'ai'],
        ['name' => 'Antarctica', 'code' => 'aq'],
        ['name' => 'Antigua and Barbuda', 'code' => 'ag'],
        ['name' => 'Argentina', 'code' => 'ar'],
        ['name' => 'Armenia', 'code' => 'am'],
        ['name' => 'Aruba', 'code' => 'aw'],
        ['name' => 'Australia', 'code' => 'au'],
        ['name' => 'Austria', 'code' => 'at'],
        ['name' => 'Azerbaijan', 'code' => 'az'],
        ['name' => 'Bahamas', 'code' => 'bs'],
        ['name' => 'Bahrain', 'code' => 'bh'],
        ['name' => 'Bangladesh', 'code' => 'bd'],
        ['name' => 'Barbados', 'code' => 'bb'],
        ['name' => 'Belarus', 'code' => 'by'],
        ['name' => 'Belgium', 'code' => 'be'],
        ['name' => 'Belize', 'code' => 'bz'],
        ['name' => 'Benin', 'code' => 'bj'],
        ['name' => 'Bermuda', 'code' => 'bm'],
        ['name' => 'Bhutan', 'code' => 'bt'],
        ['name' => 'Bolivia', 'code' => 'bo'],
        ['name' => 'Bosnia and Herzegovina', 'code' => 'ba'],
        ['name' => 'Botswana', 'code' => 'bw'],
        ['name' => 'Bouvet Island', 'code' => 'bv'],
        ['name' => 'Brazil', 'code' => 'br'],
        ['name' => 'British Indian Ocean Territory', 'code' => 'io'],
        ['name' => 'Brunei Darussalam', 'code' => 'bn'],
        ['name' => 'Bulgaria', 'code' => 'bg'],
        ['name' => 'Burkina Faso', 'code' => 'bf'],
        ['name' => 'Burundi', 'code' => 'bi'],
        ['name' => 'Cambodia', 'code' => 'kh'],
        ['name' => 'Cameroon', 'code' => 'cm'],
        ['name' => 'Canada', 'code' => 'ca'],
        ['name' => 'Cape Verde', 'code' => 'cv'],
        ['name' => 'Cayman Islands', 'code' => 'ky'],
        ['name' => 'Central African Republic', 'code' => 'cf'],
        ['name' => 'Chad', 'code' => 'td'],
        ['name' => 'Chile', 'code' => 'cl'],
        ['name' => 'China', 'code' => 'cn'],
        ['name' => 'Christmas Island', 'code' => 'cx'],
        ['name' => 'Cocos (Keeling) Islands', 'code' => 'cc'],
        ['name' => 'Colombia', 'code' => 'co'],
        ['name' => 'Comoros', 'code' => 'km'],
        ['name' => 'Congo', 'code' => 'cg'],
        ['name' => 'Congo, The Democratic Republic of The', 'code' => 'cd'],
        ['name' => 'Cook Islands', 'code' => 'ck'],
        ['name' => 'Costa Rica', 'code' => 'cr'],
        ['name' => "Cote D'ivoire", 'code' => 'ci'],
        ['name' => 'Croatia', 'code' => 'hr'],
        ['name' => 'Cuba', 'code' => 'cu'],
        ['name' => 'Cyprus', 'code' => 'cy'],
        ['name' => 'Czech Republic', 'code' => 'cz'],
        ['name' => 'Denmark', 'code' => 'dk'],
        ['name' => 'Djibouti', 'code' => 'dj'],
        ['name' => 'Dominica', 'code' => 'dm'],
        ['name' => 'Dominican Republic', 'code' => 'do'],
        ['name' => 'Ecuador', 'code' => 'ec'],
        ['name' => 'Egypt', 'code' => 'eg'],
        ['name' => 'El Salvador', 'code' => 'sv'],
        ['name' => 'Equatorial Guinea', 'code' => 'gq'],
        ['name' => 'Eritrea', 'code' => 'er'],
        ['name' => 'Estonia', 'code' => 'ee'],
        ['name' => 'Ethiopia', 'code' => 'et'],
        ['name' => 'Falkland Islands (Malvinas)', 'code' => 'fk'],
        ['name' => 'Faroe Islands', 'code' => 'fo'],
        ['name' => 'Fiji', 'code' => 'fj'],
        ['name' => 'Finland', 'code' => 'fi'],
        ['name' => 'France', 'code' => 'fr'],
        ['name' => 'French Guiana', 'code' => 'gf'],
        ['name' => 'French Polynesia', 'code' => 'pf'],
        ['name' => 'French Southern Territories', 'code' => 'tf'],
        ['name' => 'Gabon', 'code' => 'ga'],
        ['name' => 'Gambia', 'code' => 'gm'],
        ['name' => 'Georgia', 'code' => 'ge'],
        ['name' => 'Germany', 'code' => 'de'],
        ['name' => 'Ghana', 'code' => 'gh'],
        ['name' => 'Gibraltar', 'code' => 'gi'],
        ['name' => 'Greece', 'code' => 'gr'],
        ['name' => 'Greenland', 'code' => 'gl'],
        ['name' => 'Grenada', 'code' => 'gd'],
        ['name' => 'Guadeloupe', 'code' => 'gp'],
        ['name' => 'Guam', 'code' => 'gu'],
        ['name' => 'Guatemala', 'code' => 'gt'],
        ['name' => 'Guernsey', 'code' => 'gg'],
        ['name' => 'Guinea', 'code' => 'gn'],
        ['name' => 'Guinea-bissau', 'code' => 'gw'],
        ['name' => 'Guyana', 'code' => 'gy'],
        ['name' => 'Haiti', 'code' => 'ht'],
        ['name' => 'Heard Island and Mcdonald Islands', 'code' => 'hm'],
        ['name' => 'Holy See (Vatican City State)', 'code' => 'va'],
        ['name' => 'Honduras', 'code' => 'hn'],
        ['name' => 'Hong Kong', 'code' => 'hk'],
        ['name' => 'Hungary', 'code' => 'hu'],
        ['name' => 'Iceland', 'code' => 'is'],
        ['name' => 'India', 'code' => 'in'],
        ['name' => 'Indonesia', 'code' => 'id'],
        ['name' => 'Iran, Islamic Republic of', 'code' => 'ir'],
        ['name' => 'Iraq', 'code' => 'iq'],
        ['name' => 'Ireland', 'code' => 'ie'],
        ['name' => 'Isle of Man', 'code' => 'im'],
        ['name' => 'Israel', 'code' => 'il'],
        ['name' => 'Italy', 'code' => 'it'],
        ['name' => 'Jamaica', 'code' => 'jm'],
        ['name' => 'Japan', 'code' => 'jp'],
        ['name' => 'Jersey', 'code' => 'je'],
        ['name' => 'Jordan', 'code' => 'jo'],
        ['name' => 'Kazakhstan', 'code' => 'kz'],
        ['name' => 'Kenya', 'code' => 'ke'],
        ['name' => 'Kiribati', 'code' => 'ki'],
        ['name' => "Korea, Democratic People's Republic of", 'code' => 'kp'],
        ['name' => 'Korea, Republic of', 'code' => 'kr'],
        ['name' => 'Kuwait', 'code' => 'kw'],
        ['name' => 'Kyrgyzstan', 'code' => 'kg'],
        ['name' => "Lao People's Democratic Republic", 'code' => 'la'],
        ['name' => 'Latvia', 'code' => 'lv'],
        ['name' => 'Lebanon', 'code' => 'lb'],
        ['name' => 'Lesotho', 'code' => 'ls'],
        ['name' => 'Liberia', 'code' => 'lr'],
        ['name' => 'Libyan Arab Jamahiriya', 'code' => 'ly'],
        ['name' => 'Liechtenstein', 'code' => 'li'],
        ['name' => 'Lithuania', 'code' => 'lt'],
        ['name' => 'Luxembourg', 'code' => 'lu'],
        ['name' => 'Macao', 'code' => 'mo'],
        ['name' => 'Macedonia, The Former Yugoslav Republic of', 'code' => 'mk'],
        ['name' => 'Madagascar', 'code' => 'mg'],
        ['name' => 'Malawi', 'code' => 'mw'],
        ['name' => 'Malaysia', 'code' => 'my'],
        ['name' => 'Maldives', 'code' => 'mv'],
        ['name' => 'Mali', 'code' => 'ml'],
        ['name' => 'Malta', 'code' => 'mt'],
        ['name' => 'Marshall Islands', 'code' => 'mh'],
        ['name' => 'Martinique', 'code' => 'mq'],
        ['name' => 'Mauritania', 'code' => 'mr'],
        ['name' => 'Mauritius', 'code' => 'mu'],
        ['name' => 'Mayotte', 'code' => 'yt'],
        ['name' => 'Mexico', 'code' => 'mx'],
        ['name' => 'Micronesia, Federated States of', 'code' => 'fm'],
        ['name' => 'Moldova, Republic of', 'code' => 'md'],
        ['name' => 'Monaco', 'code' => 'mc'],
        ['name' => 'Mongolia', 'code' => 'mn'],
        ['name' => 'Montenegro', 'code' => 'me'],
        ['name' => 'Montserrat', 'code' => 'ms'],
        ['name' => 'Morocco', 'code' => 'ma'],
        ['name' => 'Mozambique', 'code' => 'mz'],
        ['name' => 'Myanmar', 'code' => 'mm'],
        ['name' => 'Namibia', 'code' => 'na'],
        ['name' => 'Nauru', 'code' => 'nr'],
        ['name' => 'Nepal', 'code' => 'np'],
        ['name' => 'Netherlands', 'code' => 'nl'],
        ['name' => 'Netherlands Antilles', 'code' => ''],
        ['name' => 'New Caledonia', 'code' => 'nc'],
        ['name' => 'New Zealand', 'code' => 'nz'],
        ['name' => 'Nicaragua', 'code' => 'ni'],
        ['name' => 'Niger', 'code' => 'ne'],
        ['name' => 'Nigeria', 'code' => 'ng'],
        ['name' => 'Niue', 'code' => 'nu'],
        ['name' => 'Norfolk Island', 'code' => 'nf'],
        ['name' => 'Northern Mariana Islands', 'code' => 'mp'],
        ['name' => 'Norway', 'code' => 'no'],
        ['name' => 'Oman', 'code' => 'om'],
        ['name' => 'Pakistan', 'code' => 'pk'],
        ['name' => 'Palau', 'code' => 'pw'],
        ['name' => 'Palestinian Territory, Occupied', 'code' => 'ps'],
        ['name' => 'Panama', 'code' => 'pa'],
        ['name' => 'Papua New Guinea', 'code' => 'pg'],
        ['name' => 'Paraguay', 'code' => 'py'],
        ['name' => 'Peru', 'code' => 'pe'],
        ['name' => 'Philippines', 'code' => 'ph'],
        ['name' => 'Pitcairn', 'code' => 'pn'],
        ['name' => 'Poland', 'code' => 'pl'],
        ['name' => 'Portugal', 'code' => 'pt'],
        ['name' => 'Puerto Rico', 'code' => 'pr'],
        ['name' => 'Qatar', 'code' => 'qa'],
        ['name' => 'Reunion', 'code' => 're'],
        ['name' => 'Romania', 'code' => 'ro'],
        ['name' => 'Russian Federation', 'code' => 'ru'],
        ['name' => 'Rwanda', 'code' => 'rw'],
        ['name' => 'Saint Helena', 'code' => 'sh'],
        ['name' => 'Saint Kitts and Nevis', 'code' => 'kn'],
        ['name' => 'Saint Lucia', 'code' => 'lc'],
        ['name' => 'Saint Pierre and Miquelon', 'code' => 'pm'],
        ['name' => 'Saint Vincent and The Grenadines', 'code' => 'vc'],
        ['name' => 'Samoa', 'code' => 'ws'],
        ['name' => 'San Marino', 'code' => 'sm'],
        ['name' => 'Sao Tome and Principe', 'code' => 'st'],
        ['name' => 'Saudi Arabia', 'code' => 'sa'],
        ['name' => 'Senegal', 'code' => 'sn'],
        ['name' => 'Serbia', 'code' => 'rs'],
        ['name' => 'Seychelles', 'code' => 'sc'],
        ['name' => 'Sierra Leone', 'code' => 'sl'],
        ['name' => 'Singapore', 'code' => 'sg'],
        ['name' => 'Slovakia', 'code' => 'sk'],
        ['name' => 'Slovenia', 'code' => 'si'],
        ['name' => 'Solomon Islands', 'code' => 'sb'],
        ['name' => 'Somalia', 'code' => 'so'],
        ['name' => 'South Africa', 'code' => 'za'],
        ['name' => 'South Georgia and The South Sandwich Islands', 'code' => 'gs'],
        ['name' => 'Spain', 'code' => 'es'],
        ['name' => 'Sri Lanka', 'code' => 'lk'],
        ['name' => 'Sudan', 'code' => 'sd'],
        ['name' => 'Suriname', 'code' => 'sr'],
        ['name' => 'Svalbard and Jan Mayen', 'code' => 'sj'],
        ['name' => 'Swaziland', 'code' => 'sz'],
        ['name' => 'Sweden', 'code' => 'se'],
        ['name' => 'Switzerland', 'code' => 'ch'],
        ['name' => 'Syrian Arab Republic', 'code' => 'sy'],
        ['name' => 'Taiwan', 'code' => 'tw'],
        ['name' => 'Tajikistan', 'code' => 'tj'],
        ['name' => 'Tanzania, United Republic of', 'code' => 'tz'],
        ['name' => 'Thailand', 'code' => 'th'],
        ['name' => 'Timor-leste', 'code' => 'tl'],
        ['name' => 'Togo', 'code' => 'tg'],
        ['name' => 'Tokelau', 'code' => 'tk'],
        ['name' => 'Tonga', 'code' => 'to'],
        ['name' => 'Trinidad and Tobago', 'code' => 'tt'],
        ['name' => 'Tunisia', 'code' => 'tn'],
        ['name' => 'Turkey', 'code' => 'tr'],
        ['name' => 'Turkmenistan', 'code' => 'tm'],
        ['name' => 'Turks and Caicos Islands', 'code' => 'tc'],
        ['name' => 'Tuvalu', 'code' => 'tv'],
        ['name' => 'Uganda', 'code' => 'ug'],
        ['name' => 'Ukraine', 'code' => 'ua'],
        ['name' => 'United Arab Emirates', 'code' => 'ae'],
        ['name' => 'United Kingdom', 'code' => 'gb'],
        ['name' => 'United States', 'code' => 'us'],
        ['name' => 'United States Minor Outlying Islands', 'code' => 'um'],
        ['name' => 'Uruguay', 'code' => 'uy'],
        ['name' => 'Uzbekistan', 'code' => 'uz'],
        ['name' => 'Vanuatu', 'code' => 'vu'],
        ['name' => 'Venezuela', 'code' => 've'],
        ['name' => 'Viet Nam', 'code' => 'vn'],
        ['name' => 'Virgin Islands, British', 'code' => 'vg'],
        ['name' => 'Virgin Islands, U.S.', 'code' => 'vi'],
        ['name' => 'Wallis and Futuna', 'code' => 'wf'],
        ['name' => 'Western Sahara', 'code' => 'eh'],
        ['name' => 'Yemen', 'code' => 'ye'],
        ['name' => 'Zambia', 'code' => 'zm'],
        ['name' => 'Zimbabwe', 'code' => 'zw'],
    ];

    // Same four-continent groupings as "Parcel forwarding across four
    // continents" below — defined once, up here, so both that section and
    // the hero's live route-arc canvas (resources/js/hero-route-arc.js)
    // read one single-sourced list instead of two independently maintained
    // ones. Flag codes are given directly per country (not looked up by
    // name against $shipFromCountries) so the display label can use the
    // common name ("Vietnam") without depending on that list's ISO-official
    // spelling ("Viet Nam") to resolve the flag.
    $dpRegions = [
        ['key' => 'europe', 'icon' => 'fa-earth-europe', 'title' => __('Europe'), 'countries' => [
            ['name' => 'United Kingdom', 'code' => 'gb'], ['name' => 'Spain', 'code' => 'es'], ['name' => 'Italy', 'code' => 'it'], ['name' => 'Norway', 'code' => 'no'],
            ['name' => 'Denmark', 'code' => 'dk'], ['name' => 'Sweden', 'code' => 'se'], ['name' => 'Poland', 'code' => 'pl'], ['name' => 'Romania', 'code' => 'ro'],
        ]],
        ['key' => 'asia', 'icon' => 'fa-earth-asia', 'title' => __('Asia'), 'countries' => [
            ['name' => 'Malaysia', 'code' => 'my'], ['name' => 'Taiwan', 'code' => 'tw'], ['name' => 'Vietnam', 'code' => 'vn'], ['name' => 'Singapore', 'code' => 'sg'],
        ]],
        ['key' => 'north-america', 'icon' => 'fa-earth-americas', 'title' => __('North America'), 'countries' => [
            ['name' => 'United States', 'code' => 'us'], ['name' => 'Canada', 'code' => 'ca'],
        ]],
        ['key' => 'oceania', 'icon' => 'fa-earth-oceania', 'title' => __('Australia & beyond'), 'countries' => [
            ['name' => 'Australia', 'code' => 'au'], ['name' => 'New Zealand', 'code' => 'nz'], ['name' => 'Saudi Arabia', 'code' => 'sa'],
            ['name' => 'United Arab Emirates', 'code' => 'ae'], ['name' => 'Kuwait', 'code' => 'kw'],
        ], 'extra' => __('+170 more destinations worldwide')],
    ];

    // Country name -> region key, for the hero's live route-arc canvas.
    // Any country not in one of the four curated regions above (the vast
    // majority of the real $shipFromCountries list) falls back to a fifth
    // "worldwide" anchor in hero-route-arc.js rather than guessing a wrong
    // continent for it.
    $dpCountryRegionMap = [];
    foreach ($dpRegions as $region) {
        foreach ($region['countries'] as $country) {
            $dpCountryRegionMap[$country['name']] = $region['key'];
        }
    }
@endphp

<div class="dp-home">

    {{-- ======= Hero Section ======= --}}
    <section id="hero" class="dp-grain relative overflow-hidden pt-36 pb-10 lg:pt-44 lg:pb-16">
        <div class="dp-noise absolute inset-0 -z-10"></div>
        <div class="dp-hero-topband absolute inset-x-0 top-0 -z-10"></div>
        <div class="dp-blob dp-blob-1 absolute -right-24 -top-24 -z-10 h-[26rem] w-[26rem] opacity-70" style="background: radial-gradient(circle, var(--dp-accent-soft), transparent 70%)"></div>
        <div class="dp-blob dp-blob-2 absolute -left-32 top-1/3 -z-10 h-[22rem] w-[22rem] opacity-60" style="background: radial-gradient(circle, var(--dp-cyan-soft), transparent 70%)"></div>

        {{-- Recent completed orders — real rows only (HomeController@index),
             never sample/placeholder data. Fully self-contained CSS (its own
             keyframe/classes, not shared with the courier-logo marquee
             further down the page) so nothing there can cross-affect this
             bar. Deliberately NOT paused by prefers-reduced-motion, unlike
             this page's purely decorative motion (blobs, Ken Burns, etc.):
             this ticker is live information, not decoration — same reasoning
             as the six-steps ticker's state-advancing interval elsewhere on
             this page. Fixed above the header/logo/nav — see the
             `body.dp-has-orders-bar` rules in app.css that push the header
             and this hero section's own top padding down by exactly this
             bar's height. --}}
        {{-- Always rendered on the homepage (see body.dp-has-orders-bar in
             app.css, which reserves this row's height whether or not there
             are orders to show): it now also carries the language switcher,
             pinned to the right in its own non-scrolling cell so a visitor
             can find it above the header on the one page that already has
             room reserved for a top utility row. Other pages keep the
             switcher inside the header itself (fmaster.blade.php) rather
             than growing every page's header offset for an unverified
             sitewide layout change. --}}
        <div class="dp-orders-bar" id="dp-orders-bar">
            <div class="dp-orders-scroll overflow-hidden">
                @if ($recentOrders->isNotEmpty())
                    <div class="dp-orders-fade left-0" style="background: linear-gradient(to right, var(--dp-navy), transparent)"></div>
                    <div class="dp-orders-fade right-0" style="background: linear-gradient(to left, var(--dp-navy), transparent)"></div>
                    <div class="dp-orders-track">
                        @for ($rep = 0; $rep < 2; $rep++)
                            @foreach ($recentOrders as $order)
                                <span class="dp-orders-item inline-flex shrink-0 items-center gap-2">
                                    <span class="dp-orders-badge flex shrink-0 items-center justify-center rounded-full" aria-hidden="true">
                                        <i class="fa-solid fa-box text-[9px]"></i>
                                    </span>
                                    <span class="flag-icon flag-icon-{{ $order['from_code'] }} rounded-xs shrink-0" style="width:1.1em;height:.8em"></span>
                                    <span>{{ $order['from'] }}</span>
                                    <i class="fas fa-arrow-right-long text-[10px]" style="color:var(--dp-accent)" aria-hidden="true"></i>
                                    <span class="flag-icon flag-icon-{{ $order['to_code'] }} rounded-xs shrink-0" style="width:1.1em;height:.8em"></span>
                                    <span>{{ $order['to'] }}</span>
                                    <span class="dp-orders-dot" aria-hidden="true"></span>
                                    <i class="fas fa-circle-check text-[10px]" style="color:#4ade80" aria-hidden="true"></i>
                                    <span>{{ __('Completed') }}</span>
                                    <span class="dp-orders-price">${{ number_format($order['price'], 2) }}</span>
                                </span>
                            @endforeach
                        @endfor
                    </div>
                @endif
            </div>
            <div class="dp-orders-lang">
                @include('fcommon.lang-switcher', ['dpLangVariant' => 'bar'])
            </div>
        </div>

        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-y-10 lg:gap-x-8 items-center">

                <div class="lg:col-span-7 order-2 lg:order-1 flex flex-col justify-center">
                    <h1 class="dp-hero-in dp-hero-in-1 text-[2.5rem] leading-[1.05] tracking-tight sm:text-5xl lg:text-[3.6rem]">
                        {{ __('Shop anywhere.') }}<br>{{ __('Ship to') }}
                        <span class="align-bottom" style="color:var(--dp-accent)">{{ __('Everywhere.') }}</span>
                    </h1>
                    <p class="dp-hero-in dp-hero-in-2 mt-5 max-w-none text-base leading-relaxed sm:text-[1.05rem]" style="color:var(--dp-ink-soft)">
                        <strong style="color:var(--dp-ink)">{{ __('Welcome to Delivering Parcel — your trusted parcel forwarding company.') }}</strong>
                        {{ __('Delivering Parcel makes global shopping and shipping easier than ever. With a wide range of services designed to meet all your parcel forwarding needs, we provide a seamless process for individuals and businesses alike. Wherever you shop, we ensure your purchases reach you on time, anywhere in the world.') }}
                    </p>

                    <form action="{{ route('country') }}" class="dp-console dp-hero-in dp-hero-in-3 mt-8 p-5" method="GET"
                          x-data="dpHeroForm"
                          @submit="trySubmit($event)">
                        @csrf
                        <div class="mb-4 flex items-center justify-between px-0.5">
                            <span class="dp-console-readout">{{ __('Origin') }} <i class="fas fa-arrow-right-long mx-1.5" style="font-size:.6rem"></i> {{ __('Destination') }}</span>
                            <span class="flex items-center gap-1.5">
                                <span class="dp-console-dot"></span>
                                <span class="dp-console-readout">{{ __('Live rates') }}</span>
                            </span>
                        </div>
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                            {{-- x-data is a bare component reference, not an inline object with
                                 getters/arrow functions — those aren't parseable by the public
                                 site's CSP-safe Alpine build (resources/js/app-public.js). The
                                 country list is handed over via data-countries instead. --}}
                            <div class="relative w-full flex-1 min-w-0"
                                 x-data="dpShipFromPicker" data-countries="{{ json_encode($shipFromCountries) }}"
                                 @click.outside="close()">
                                {{-- Real <select> is the single source of truth for the "shipfrom"
                                     field and works with no JS at all — the flag-icon dropdown below
                                     is a progressive enhancement layered on top of it, not a
                                     replacement for it. It renders visible by default (native
                                     dropdown, fully submittable) and Alpine hides it only once it has
                                     actually mounted; if Alpine fails to load, this stays the field
                                     the visitor uses. --}}
                                <select name="shipfrom" required x-model="selected" x-show="false"
                                        class="dp-console-native-select w-full border-0 py-3.5 px-4 text-base"
                                        aria-label="Select shipping location">
                                    <option value="">{{ __('Where are you shopping from?') }}</option>
                                    @foreach ($shipFromCountries as $c)
                                        <option value="{{ $c['name'] }}">{{ $c['name'] }}</option>
                                    @endforeach
                                </select>

                                <button type="button" @click="open = !open" x-show="true"
                                        class="dp-console-field w-full flex items-center justify-between gap-3 border-0 py-3.5 px-4 text-base text-left focus:outline-none focus:ring-2 transition cursor-pointer"
                                        style="display:none; --tw-ring-color:var(--dp-accent-soft)"
                                        :aria-expanded="open" aria-haspopup="listbox" aria-label="Select shipping location">
                                    <div class="flex items-center gap-2.5 min-w-0 overflow-hidden">
                                        <template x-if="selectedFlag">
                                            <span class="flag-icon rounded-xs shrink-0 shadow-2xs" :class="'flag-icon-' + selectedFlag" style="width:1.4em;height:1em"></span>
                                        </template>
                                        <template x-if="!selectedFlag">
                                            <i class="fas fa-location-dot shrink-0 text-sm" style="color:var(--dp-console-muted)"></i>
                                        </template>
                                        <span class="truncate" :class="selected ? 'font-semibold' : ''" :style="selected ? 'color:var(--dp-console-text)' : 'color:var(--dp-console-muted)'" x-text="selected || '{{ __('Where are you shopping from?') }}'"></span>
                                    </div>
                                    <i class="fas fa-chevron-down pointer-events-none shrink-0 text-xs transition-transform duration-200"
                                       :style="open ? 'color:var(--dp-accent)' : 'color:var(--dp-console-muted)'"
                                       :class="open ? 'rotate-180' : ''"></i>
                                </button>

                                <div x-show="open" x-cloak x-transition
                                     class="absolute left-0 right-0 top-full z-30 mt-2 overflow-hidden rounded-xl border bg-white shadow-2xl"
                                     style="border-color:var(--dp-line)">
                                    <div class="border-b p-2.5" style="border-color:var(--dp-line); background:var(--dp-console-field)">
                                        <input type="text" x-model="query" placeholder="{{ __('Search country…') }}" @click.stop
                                               class="w-full rounded-lg border-0 px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                               style="background:#fff; color:var(--dp-console-text); --tw-ring-color:var(--dp-accent-soft)">
                                    </div>
                                    <ul class="max-h-64 overflow-y-auto py-1" role="listbox">
                                        <template x-for="c in filtered" :key="c.name">
                                            <li>
                                                <button type="button" role="option"
                                                        class="flex w-full items-center gap-2.5 px-4 py-2 text-left text-sm transition cursor-pointer hover:bg-black/[.04]"
                                                        @click="choose(c)">
                                                    <template x-if="c.code">
                                                        <span class="flag-icon rounded-xs shrink-0" :class="'flag-icon-' + c.code" style="width:1.4em;height:1em"></span>
                                                    </template>
                                                    <span style="color:var(--dp-console-text)" x-text="c.name"></span>
                                                </button>
                                            </li>
                                        </template>
                                        <li x-show="filtered.length === 0" class="px-4 py-3 text-sm" style="color:var(--dp-console-muted)">{{ __('No countries found.') }}</li>
                                    </ul>
                                </div>
                            </div>

                            <button type="submit"
                                    class="dp-console-submit w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-2 whitespace-nowrap px-8 py-3.5 text-base font-bold transition-all duration-200 transform hover:-translate-y-0.5 cursor-pointer">
                                <span>{{ __('Get Started') }}</span> <i class="fas fa-arrow-right text-xs"></i>
                            </button>
                        </div>
                        <p x-show="tried" x-cloak class="mt-2 px-0.5 text-sm font-medium" style="color:#c0392b">
                            {{ __('Please choose a country to continue.') }}
                        </p>
                    </form>

                    <div class="dp-hero-in dp-hero-in-4 mt-5 [&_.justify-center]:justify-start">
                        @include('partials.review-badges')
                    </div>

                    <div class="dp-hero-in dp-hero-in-5 mt-8 flex flex-wrap gap-x-10 gap-y-5 border-t pt-6" style="border-color:var(--dp-line)">
                        <div>
                            <p class="dp-display text-2xl" style="font-weight:250">170+</p>
                            <p class="text-xs" style="color:var(--dp-ink-soft)">{{ __('Ship-to destinations') }}</p>
                        </div>
                        <div>
                            <p class="dp-display text-2xl" style="font-weight:250">{{ __('45 days') }}</p>
                            <p class="text-xs" style="color:var(--dp-ink-soft)">{{ __('Free warehousing') }}</p>
                        </div>
                        <div>
                            <p class="dp-display text-2xl" style="font-weight:250">70%</p>
                            <p class="text-xs" style="color:var(--dp-ink-soft)">{{ __('Saved via consolidation') }}</p>
                        </div>
                        <div>
                            <p class="dp-display text-2xl" style="font-weight:250">£0</p>
                            <p class="text-xs" style="color:var(--dp-ink-soft)">{{ __('Membership fees') }}</p>
                        </div>
                    </div>
                </div>

                <div class="dp-hero-in dp-hero-in-art lg:col-span-5 order-1 lg:order-2 relative">
                    <div class="dp-hero-art-glow absolute inset-6 -z-10 rounded-[2rem]"></div>

                    {{-- User-supplied illustration, background removed (flood-fill from
                         edges against the flat backdrop colour, via Pillow — see
                         .impeccable/surfaces/home.md). The globe gets a subtle 3D turn;
                         see .dp-globe-turn in app.css for why it's a bounded tilt, not a
                         full spin (a flat raster spinning 360° on its vertical axis goes
                         edge-on and vanishes at 90°/270° — this keeps it always visible). --}}
                    <div class="dp-globe-turn-stage" id="dp-hero-route-stage" data-region-map="{{ json_encode($dpCountryRegionMap) }}">
                        <img src="{{ asset('frontend/assets/img/hero-shopper-globe.png') }}"
                             class="dp-globe-turn relative w-full h-auto"
                             alt="A shopper browsing international retailers on her laptop in front of a world globe, surrounded by store logos"
                             width="1024" height="559" fetchpriority="high">
                        {{-- Live route-arc overlay: an animated parcel travels from a
                             continent anchor to the shopper's laptop, matching whichever
                             country she's picked in the console's "ship from" field (or
                             cycling through all four while she hasn't chosen yet). Pure
                             enhancement — the static illustration above already stands on
                             its own with no canvas support, JS failure, or reduced motion.
                             See resources/js/hero-route-arc.js. --}}
                        <canvas class="dp-route-canvas absolute inset-0 h-full w-full" aria-hidden="true"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <main id="main">

        {{-- ======= About Section ======= --}}
        <section id="about" class="relative pt-20 pb-16">
            <div class="container mx-auto px-4 relative" data-aos="fade-up">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center relative">
                    <div class="relative self-start order-2 lg:order-2">
                        <div class="dp-card dp-about-frame overflow-hidden relative">
                            <img src="{{ asset('frontend/assets/img/about-delivery.png') }}" class="dp-about-anim w-full h-auto block"
                                 alt="{{ __('A courier handing a parcel to a customer at his front door, with Delivering Parcel branded vans waiting on the street') }}" loading="lazy"
                                 width="1024" height="765">
                            <div class="dp-about-sheen" aria-hidden="true"></div>
                            <a href="https://www.youtube.com/watch?v=ub3EjKpKA30" class="glightbox play-btn"
                               aria-label="{{ __('Watch how Delivering Parcel works on YouTube') }}"></a>
                        </div>
                    </div>
                    <div class="order-1 lg:order-1">
                        <h2 class="text-3xl sm:text-4xl mb-4">{{ __('What is parcel forwarding?') }}</h2>
                        <p class="mb-8" style="color:var(--dp-ink-soft)">{{ __('Package forwarding — also called mail forwarding or reshipping — is an international shipping service for cross-border online shoppers. We receive your packages locally, then forward them anywhere in the world.') }}</p>
                        {{-- No per-item data-aos: this list is inside the section's outer
                             container, which already reveals as one fade-up. A second,
                             separately-timed reveal nested inside the first is what caused
                             sections elsewhere on this page to need extra scrolling before
                             their content would actually appear. --}}
                        <ul class="space-y-5">
                            <li class="flex gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background:var(--dp-accent-soft)"><i class="fas fa-diagram-project" style="color:var(--dp-accent-dark)"></i></span>
                                <div>
                                    <h3 class="dp-display text-base mb-1">{{ __('Shop from anywhere, ship everywhere') }}</h3>
                                    <p class="text-sm" style="color:var(--dp-ink-soft)">{{ __('Shop stores in the USA, UK, EU, Australia and Asia and ship to any country in the world, with a free residential address of your own.') }}</p>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background:var(--dp-accent-soft)"><i class="fas fa-cart-shopping" style="color:var(--dp-accent-dark)"></i></span>
                                <div>
                                    <h3 class="dp-display text-base mb-1">{{ __('Purchase assistance') }}</h3>
                                    <p class="text-sm" style="color:var(--dp-ink-soft)">{{ __("Can't check out from an international store? Our Buy For Me service purchases it for you and reships it securely to your address.") }}</p>
                                </div>
                            </li>
                            <li class="flex gap-4">
                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl" style="background:var(--dp-accent-soft)"><i class="fas fa-truck-fast" style="color:var(--dp-accent-dark)"></i></span>
                                <div>
                                    <h3 class="dp-display text-base mb-1">{{ __('Built to be the cheapest forwarder') }}</h3>
                                    <p class="text-sm" style="color:var(--dp-ink-soft)">{{ __('Save up to 70% on shipping with custom-tailored consolidation and reshipping rates.') }}</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= Featured Services Section ======= --}}
        <section id="featured-services" class="py-16" style="background:#fff">
            <div class="container mx-auto px-4">
                <div class="max-w-xl mb-10" data-aos="fade-up">
                    <h2 class="text-3xl sm:text-4xl">{{ __('Nine things you get, at no extra cost') }}</h2>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2">
                    @php
                        $dpServices = [
                            ['icon' => 'fa-user-plus', 'title' => __('No sign-up fees'), 'desc' => __('Create a free request to shop and ship from anywhere to everywhere — no monthly subscriptions, no hidden fees.')],
                            ['icon' => 'fa-cart-shopping', 'title' => __('Free shopping &amp; shipping address'), 'desc' => __('A real residential address in the USA, UK, Australia, Europe or Asia — free, for shopping and shipping.')],
                            ['icon' => 'fa-warehouse', 'title' => __('Free warehousing and storage'), 'desc' => __('Shop tax-free from any country and get free warehousing on your parcels for up to 30 days.')],
                            ['icon' => 'fa-box-open', 'title' => __('Repackaging &amp; consolidation'), 'desc' => __('Shop stores across the USA, UK, EU and Australia and have it all consolidated into one shipment.')],
                            ['icon' => 'fa-image', 'title' => __('Free photos'), 'desc' => __('Get free photos of your products before they ship, so you always know what you\'re getting.')],
                            ['icon' => 'fa-file-invoice', 'title' => __('Custom declaration'), 'desc' => __('Every shipment leaves with accurate customs declarations handled for you.')],
                            ['icon' => 'fa-truck-ramp-box', 'title' => __('Cheap international shipping'), 'desc' => __('Save up to 70% with world-leading couriers — DHL, UPS, FedEx, USPS, Royal Mail and Australia Post.')],
                            ['icon' => 'fa-sack-dollar', 'title' => __('Money-back guarantee'), 'desc' => __('Full refund if an item sells out or a shipper falls through. Shop with confidence.')],
                            ['icon' => 'fa-headset', 'title' => __('Shopper assistance'), 'desc' => __('Personal shopping and shipping assistance, 24/7, from a real international shipper.')],
                        ];
                    @endphp
                    @foreach ($dpServices as $i => $svc)
                        <div class="flex items-start gap-4 border-t py-6 {{ $i % 2 === 0 ? 'lg:pr-10' : 'lg:pl-10' }}" style="border-color:var(--dp-line)" data-aos="fade-up" data-aos-delay="{{ 30 * ($i % 3) }}">
                            <i class="fa-solid {{ $svc['icon'] }} mt-1 w-5 text-center" style="color:var(--dp-accent)" aria-hidden="true"></i>
                            <div>
                                <h3 class="dp-display text-lg mb-1">{!! $svc['title'] !!}</h3>
                                <p class="text-sm leading-relaxed" style="color:var(--dp-ink-soft)">{!! $svc['desc'] !!}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ======= Dynamic Services Section (from /services & CMS) ======= --}}
        @if (isset($services) && $services->isNotEmpty())
        <section id="services-catalog" class="py-16" style="background:var(--dp-paper)">
            <div class="container mx-auto px-4" data-aos="fade-up">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                    <div>
                        <h2 class="text-3xl sm:text-4xl mb-2">{{ __('Our shipping & forwarding services') }}</h2>
                        <p style="color:var(--dp-ink-soft)">{{ __('Specialized solutions for international shoppers, businesses, and collectors.') }}</p>
                    </div>
                    <a href="{{ url('services') }}" class="dp-btn dp-btn-outline self-start sm:self-auto">{{ __('All services') }} <i class="fas fa-arrow-right text-xs"></i></a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($services as $s)
                        <a class="dp-card flex flex-col p-6 hover:shadow-lg transition-shadow group" href="{{ $s->url }}">
                            <div class="flex h-11 w-11 items-center justify-center rounded-xl mb-4" style="background:var(--dp-accent-soft)">
                                <i class="fa-solid {{ $s->meta['icon'] ?? 'fa-box' }}" style="color:var(--dp-accent-dark)" aria-hidden="true"></i>
                            </div>
                            <h3 class="dp-display text-xl mb-2 font-bold group-hover:text-brand transition-colors">{{ $s->title }}</h3>
                            <p class="text-sm leading-relaxed flex-1" style="color:var(--dp-ink-soft)">
                                {{ \Str::limit(strip_tags((string) $s->smart_excerpt), 120) }}
                            </p>
                            <span class="mt-4 inline-flex items-center text-xs font-semibold text-brand">
                                {{ __('Learn more') }} <i class="fas fa-arrow-right ml-1 text-[10px]"></i>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- ======= Countries We Operate In ======= --}}
        <section id="countries" class="py-16" style="background:var(--dp-paper)">
            <div class="container mx-auto px-4" data-aos="fade-up">
                <div class="max-w-xl mb-10">
                    <h2 class="text-3xl sm:text-4xl">{{ __('Parcel forwarding across four continents') }}</h2>
                    <p class="mt-4 text-base" style="color:var(--dp-ink-soft)">{{ __('Whether you need a proxy shipping address, a reshipping service, or free package storage, Delivering Parcel operates in these countries and more.') }}</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    {{-- $dpRegions is defined once, at the top of this file
                         alongside $shipFromCountries, so the hero's live
                         route-arc canvas can share the exact same groupings. --}}
                    {{-- No per-card data-aos: nested inside the section's already
                         fade-up container, a second reveal here was the same bug as
                         the "What is parcel forwarding?" list and the steps section. --}}
                    @foreach ($dpRegions as $i => $region)
                        <div class="dp-card p-6">
                            <div class="dp-region-badge relative flex h-14 w-14 items-center justify-center rounded-2xl mb-4">
                                <span class="dp-region-badge-ping absolute inset-0 rounded-2xl" aria-hidden="true"></span>
                                <i class="fa-solid {{ $region['icon'] }} dp-region-globe text-xl" aria-hidden="true"></i>
                            </div>
                            <h3 class="dp-display text-base mb-3">{{ $region['title'] }}</h3>
                            <ul class="flex flex-wrap gap-1.5">
                                @foreach ($region['countries'] as $country)
                                    <li class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs" style="border-color:var(--dp-line); color:var(--dp-ink-soft)">
                                        <span class="flag-icon flag-icon-{{ $country['code'] }} rounded-xs shrink-0" style="width:1.2em;height:.9em"></span>
                                        {{ $country['name'] }}
                                    </li>
                                @endforeach
                            </ul>
                            @if (!empty($region['extra']))
                                <p class="mt-2.5 text-xs font-semibold" style="color:var(--dp-accent-dark)">{{ $region['extra'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- ======= How to Become a Shopper — improved ======= --}}
        <section id="service" class="relative py-20 overflow-hidden" style="background:var(--dp-paper)">
            <div class="dp-blob absolute -right-28 top-10 -z-0 h-[24rem] w-[24rem] opacity-50" style="background: radial-gradient(circle, var(--dp-cyan-soft), transparent 70%)"></div>

            {{-- One authored entrance for the whole section, riding on the outer
                 container's own single data-aos scroll trigger: the image reveals
                 via a left-to-right clip-path wipe (echoing unwrapping a parcel),
                 a trust badge pops onto its corner right after, and the copy
                 settles in on its own beat — all gated behind the `.aos-animate`
                 class AOS already adds here, not a second, independently-timed
                 data-aos nested inside it (see the bug fixed elsewhere on this
                 page). With AOS/JS unavailable, nothing here is ever assigned an
                 animation at all, so every element simply renders in its plain,
                 fully visible resting state. --}}
            <div class="container mx-auto px-4 relative dp-shopper-section" data-aos="fade-up">

                <div class="max-w-xl relative">
                    <h2 class="text-3xl sm:text-4xl mb-4">{{ __('How to become a shopper') }}</h2>
                    <p class="text-base" style="color:var(--dp-ink-soft)">
                        {{ __('Delivering Parcel is the most affordable and reliable package forwarding company internationally — shoppers get the best shipping service from any country in the world.') }}
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-14 mt-12 items-center relative">

                    {{-- Left: main image. No longer sticky — that only earned its
                         keep next to a tall multi-card column, which this section
                         no longer has. A soft pulsing glow sits behind the card,
                         echoing the illustration's own glowing network lines and
                         live-data chat bubble — a second authored motion for this
                         section, distinct from the one-time clip-path reveal. --}}
                    <div class="relative">
                        <div class="dp-shopper-glow absolute inset-6 -z-10 rounded-[2rem]" aria-hidden="true"></div>
                        <div class="dp-card dp-shopper-clip overflow-hidden">
                            <img src="{{ asset('frontend/assets/img/become-a-shopper-hero.jpg') }}"
                                 alt="{{ __('A shopper browsing a vibrant shopping app on her phone, adding items to cart from a summer sale') }}" class="w-full aspect-[4/3] object-cover object-center block"
                                 loading="lazy" width="640" height="480">
                        </div>
                        <div class="dp-shopper-badge absolute -bottom-6 left-6 right-6 sm:right-auto flex items-center gap-3 rounded-2xl bg-white px-4 py-3 shadow-xl">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full" style="background: linear-gradient(135deg, var(--dp-accent), var(--dp-cyan))">
                                <i class="fa-solid fa-shield-heart text-sm text-white" aria-hidden="true"></i>
                            </span>
                            <span class="text-sm font-semibold leading-snug" style="color:var(--dp-ink)">{{ __('Join thousands of shoppers already shipping worldwide') }}</span>
                        </div>
                    </div>

                    {{-- Right: what makes it worth doing, not a restatement of "Nine
                         things you get" above — the fee/address/storage/guarantee
                         list already lives there, so this stays narrative rather
                         than re-tiling the same four claims a second time. --}}
                    <div class="dp-shopper-copy mt-4 lg:mt-0">
                        <p class="text-base leading-relaxed" style="color:var(--dp-ink-soft)">
                            <strong style="color:var(--dp-ink)">{{ __("Every request goes to a network of real, active shippers already living in the country you're shopping from") }}</strong>
                            — {{ __('you pick the best offer, they buy or receive on your behalf, and your parcel is on its way. No app to install, no waiting on a call center: just a request, a reply, and a tracked shipment.') }}
                        </p>
                        <div class="mt-7 flex items-center gap-4 flex-wrap">
                            <a href="{{ route('country') }}" class="dp-shopper-cta dp-btn dp-btn-ink group">
                                {{ __('Get Started') }} <i class="fas fa-arrow-right text-sm dp-btn-arrow transition-transform group-hover:translate-x-1"></i>
                            </a>
                            <a href="{{ route('become-a-shopper') }}" class="dp-btn dp-btn-outline group">
                                {{ __('More info') }} <i class="fas fa-circle-info text-sm transition-transform group-hover:scale-110"></i>
                            </a>
                        </div>
                        <p class="mt-3 text-xs font-semibold" style="color:var(--dp-ink-soft)">{{ __('Free to join — no monthly fees') }}</p>
                    </div>

                </div>
            </div>
        </section>

        {{-- ======= Steps Section ======= ---
             A journey, not a card grid: one continuous line runs the length
             of the six steps with a marching-dash texture and a single glowing
             dot travelling top-to-bottom on an endless loop — the one
             authored motion for this section, standing in for the literal
             parcel travelling from cart to door. Icon nodes carry the step,
             text does the telling; the small corner number is secondary,
             here only because the sequence itself is information the reader
             needs (do this, then this, then this). --}}
        <section class="relative py-20" id="features" style="background:#fff">
            <div class="container mx-auto px-4 relative" data-aos="fade-up">
                <div class="max-w-xl mb-14 relative">
                    <h2 class="text-3xl sm:text-4xl">{{ __('Six steps from cart to your door') }}</h2>
                </div>

                @php
                    $dpSteps = [
                        ['icon' => 'fa-clipboard-list', 'title' => __('Create a request'), 'desc' => __('Item details, shop country, ship-to country, product URL, weight and quantity. Choose forwarding only, or add purchase assistance.')],
                        ['icon' => 'fa-comments-dollar', 'title' => __('Get an offer'), 'desc' => __("Receive competitive offers from available shippers in the country you're shopping from. Accept or reject freely.")],
                        ['icon' => 'fa-cart-shopping', 'title' => __('Order the goods'), 'desc' => __('Shop Amazon, eBay, Walmart or anywhere else using your Delivering Parcel address. Consolidate multiple stores into one.')],
                        ['icon' => 'fa-handshake', 'title' => __('Buy for me service'), 'desc' => __("Can't pay internationally? We purchase on your behalf and add the cost to your offer.")],
                        ['icon' => 'fa-box-open', 'title' => __('Package received'), 'desc' => __('We notify you on arrival. Request free photos, content checks or custom declaration before it ships.')],
                        ['icon' => 'fa-plane-departure', 'title' => __('Parcel delivered'), 'desc' => __('Ships via DHL, FedEx, UPS, Royal Mail or USPS — save up to 70% with consolidation.')],
                    ];
                @endphp
                {{-- x-data spans the ticker card and both step layouts below, since
                     the :class bindings on each step node/block reference this same
                     component's `current`/`phase` state to highlight the matching
                     step in sync with the ticker — one shared Alpine scope, not a
                     separate component per layout. --}}
                <div x-data="dpStepsTicker" data-steps="{{ json_encode(array_map(fn ($s) => ['icon' => $s['icon'], 'title' => $s['title']], $dpSteps)) }}">
                {{-- No data-aos here — same nested-reveal bug as elsewhere on this
                     page; the section's outer container already reveals this. --}}
                <div class="mb-12 flex justify-center">
                    <div class="dp-ticker inline-flex items-center gap-3 rounded-full px-4 py-2.5 sm:px-5">
                        <span class="dp-ticker-icon flex h-8 w-8 shrink-0 items-center justify-center rounded-full">
                            <i class="fa-solid text-sm" :class="currentStep.icon" aria-hidden="true"></i>
                        </span>
                        <span class="text-sm font-semibold whitespace-nowrap" style="color:var(--dp-ink)">
                            <span x-text="'{{ __('Step') }} ' + (current + 1) + ':'"></span>
                            <span x-text="currentStep.title"></span>
                        </span>
                        <span class="dp-ticker-status-progress inline-flex items-center gap-1.5" x-show="phase === 'progress'" x-transition.opacity.duration.300ms>
                            <span class="dp-ticker-dot" aria-hidden="true"></span> {{ __('In Progress') }}
                        </span>
                        <span class="dp-ticker-status-done inline-flex items-center gap-1.5" x-show="phase === 'done'" x-cloak x-transition.opacity.duration.300ms>
                            <i class="fa-solid fa-check text-xs" aria-hidden="true"></i> {{ __('Done') }}
                        </span>
                    </div>
                </div>
                {{-- Mobile/tablet: the vertical journey line. A 6-wide pointer-flow
                     ribbon (below) has no graceful way to reflow under ~1024px
                     without either crushing the labels unreadably or abandoning
                     the alternating up/down connectors that make it legible, so
                     narrower viewports get this dedicated stacked treatment
                     instead of a squeezed shrink of the desktop one. --}}
                <div class="relative mx-auto max-w-2xl lg:hidden">
                    <div class="dp-journey-line absolute left-7 top-2 bottom-2 w-0.5" aria-hidden="true"></div>
                    <span class="dp-journey-dot absolute left-7" aria-hidden="true"></span>

                    <div class="flex flex-col gap-10 sm:gap-12">
                        @foreach ($dpSteps as $i => $step)
                            {{-- No data-aos here: the section's outer container (top of this
                                 section) already reveals as one fade-up. A second, separately
                                 timed AOS reveal nested inside it — one scroll-triggered
                                 animation waiting on another — was exactly what made these
                                 nodes take extra scrolling to actually appear. --}}
                            <div class="relative flex items-start gap-5 sm:gap-6">
                                <div class="dp-journey-node relative z-10 flex h-14 w-14 shrink-0 items-center justify-center rounded-full"
                                     :class="{ 'dp-step-ticker-progress': current === {{ $i }} && phase === 'progress', 'dp-step-ticker-done': current === {{ $i }} && phase === 'done' }">
                                    <i class="fa-solid {{ $step['icon'] }} text-lg" aria-hidden="true"></i>
                                    <span class="dp-journey-step-num">{{ $i + 1 }}</span>
                                </div>
                                <div class="pt-2">
                                    <h3 class="dp-display text-lg mb-1.5">{{ $step['title'] }}</h3>
                                    <p class="text-sm leading-relaxed" style="color:var(--dp-ink-soft)">{{ $step['desc'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Desktop: pointer-flow ribbon (user-pinned reference layout) —
                     six colour blocks in one continuous row, each carrying its
                     step number and icon, alternating a label above (odd steps)
                     or below (even steps) via a triangular pointer that grows
                     out of the block toward it. One 6-column grid holds all
                     three rows (above-labels / blocks / below-labels) so the
                     columns can't drift out of alignment with each other.
                     Colours are a deliberate warm-to-cool sweep anchored on the
                     two real brand hues (accent orange, secondary cyan) rather
                     than an arbitrary six-colour rainbow. --}}
                <div class="dp-flow hidden lg:grid grid-cols-6 gap-x-3 mx-auto max-w-6xl">
                    @php
                        $dpFlowColors = ['#f5951e', '#e2703f', '#4f9d6a', '#2fa9a0', '#29abe2', '#3d6fd0'];
                    @endphp
                    @foreach ($dpSteps as $i => $step)
                        <div class="text-center px-2 pb-5 {{ $i % 2 === 0 ? '' : 'invisible' }}">
                            <h3 class="dp-display text-base mb-1">{{ $step['title'] }}</h3>
                            <p class="text-xs leading-relaxed" style="color:var(--dp-ink-soft)">{{ \Illuminate\Support\Str::limit($step['desc'], 70) }}</p>
                        </div>
                    @endforeach
                    {{-- No data-aos on these blocks (see the mobile journey nodes above
                         for why): the outer section container already reveals the whole
                         thing as one fade-up, so the blocks render with everything else
                         instead of waiting on their own separate, independently-timed
                         scroll trigger nested inside it. --}}
                    @foreach ($dpSteps as $i => $step)
                        <div class="dp-flow-block relative flex items-center justify-between px-4 {{ $i % 2 === 0 ? 'dp-flow-up' : 'dp-flow-down' }}"
                             style="--dp-flow-color: {{ $dpFlowColors[$i] }}"
                             :class="{ 'dp-step-ticker-progress': current === {{ $i }} && phase === 'progress', 'dp-step-ticker-done': current === {{ $i }} && phase === 'done' }">
                            <span class="dp-flow-num">{{ sprintf('%02d', $i + 1) }}</span>
                            <i class="fa-solid {{ $step['icon'] }} dp-flow-icon" aria-hidden="true"></i>
                        </div>
                    @endforeach
                    @foreach ($dpSteps as $i => $step)
                        <div class="text-center px-2 pt-5 {{ $i % 2 !== 0 ? '' : 'invisible' }}">
                            <h3 class="dp-display text-base mb-1">{{ $step['title'] }}</h3>
                            <p class="text-xs leading-relaxed" style="color:var(--dp-ink-soft)">{{ \Illuminate\Support\Str::limit($step['desc'], 70) }}</p>
                        </div>
                    @endforeach
                </div>
                </div>

                <div class="text-center mt-14">
                    <a href="{{ route('country') }}" class="dp-btn dp-btn-accent">{{ __('Get Started') }} <i class="fas fa-arrow-right text-sm"></i></a>
                </div>
            </div>
        </section>

        {{-- ======= Customer Testimonials (latest published, from Admin → Tools → Testimonials) ======= --}}
        <section class="relative py-16" style="background:var(--dp-paper)">
            <div class="container mx-auto px-4 relative" data-aos="fade-up">
                <div class="text-center mb-10">
                    <h2 class="text-3xl sm:text-4xl mb-3">{{ __('What our customers say') }}</h2>
                    <p class="mb-6" style="color:var(--dp-ink-soft)">{{ __('Real feedback from Delivering Parcel customers around the world.') }}</p>
                    @include('partials.review-badges')
                </div>

                @if($homeTestimonials->isNotEmpty())
                {{-- x-data is a bare component reference — the autoplay/advance logic
                     now lives in resources/js/app-public.js's dpTestimonialSlider,
                     since the CSP-safe Alpine build can't parse an inline method
                     definition or a setInterval/matchMedia call inside x-init. --}}
                <div class="relative" x-data="dpTestimonialSlider"
                     @mouseenter="paused = true" @mouseleave="paused = false">

                    <div class="flex items-center justify-end gap-2 mb-4">
                        <button type="button" @click="advance(-1)" aria-label="{{ __('Previous testimonials') }}"
                                class="flex h-10 w-10 items-center justify-center rounded-full border transition hover:-translate-y-0.5 cursor-pointer"
                                style="border-color:var(--dp-line); color:var(--dp-ink)">
                            <i class="fas fa-chevron-left text-sm" aria-hidden="true"></i>
                        </button>
                        <button type="button" @click="advance(1)" aria-label="{{ __('Next testimonials') }}"
                                class="flex h-10 w-10 items-center justify-center rounded-full border transition hover:-translate-y-0.5 cursor-pointer"
                                style="border-color:var(--dp-line); color:var(--dp-ink)">
                            <i class="fas fa-chevron-right text-sm" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div x-ref="dpTestimonialTrack" class="dp-testimonial-track flex gap-5 overflow-x-auto pb-2" tabindex="0" role="region" aria-label="{{ __('Customer testimonials, scrollable') }}">
                    @foreach($homeTestimonials as $t)
                    @php
                        $sourceBadges = [
                            'google' => ['label' => 'Google', 'icon' => 'fab fa-google', 'classes' => 'bg-blue-50 text-blue-700'],
                            'trustpilot' => ['label' => 'Trustpilot', 'icon' => 'fas fa-star', 'classes' => 'bg-emerald-50 text-emerald-700'],
                            'sitejabber' => ['label' => 'SiteJabber', 'icon' => 'fas fa-star', 'classes' => 'bg-orange-50 text-orange-700'],
                            'manual' => ['label' => __('Verified Review'), 'icon' => 'fas fa-shield-halved', 'classes' => 'bg-slate-100 text-slate-700'],
                        ];
                        $sb = $sourceBadges[$t->source] ?? null;
                    @endphp
                    <div class="dp-testimonial-card dp-card p-6 flex flex-col shrink-0 w-[85%] sm:w-[60%] lg:w-[32%]">
                        <div class="flex items-center justify-between mb-3">
                            @if($t->rating)
                            <div class="text-sm" style="color:var(--dp-accent)">{{ str_repeat('★', $t->rating) }}<span style="color:var(--dp-line)">{{ str_repeat('★', 5 - $t->rating) }}</span></div>
                            @else
                            <span></span>
                            @endif
                            @if($sb)
                            <span class="inline-flex items-center gap-1 rounded-full {{ $sb['classes'] }} px-2 py-0.5 text-xs font-medium"><i class="{{ $sb['icon'] }}"></i> {{ $sb['label'] }}</span>
                            @endif
                        </div>
                        <p class="mb-5 flex-1 text-sm leading-relaxed" style="color:var(--dp-ink-soft)">“{{ \Illuminate\Support\Str::limit($t->content, 220) }}”</p>
                        <div class="flex items-center gap-3 pt-4 border-t" style="border-color:var(--dp-line)">
                            @if($t->user_avatar)
                            <img src="{{ asset($t->user_avatar) }}" alt="{{ $t->user_name }}" width="40" height="40" class="rounded-full" style="object-fit:cover">
                            @else
                            <img src="{{ asset('images/deliveringlogo.png') }}" alt="" width="40" height="40" class="rounded-full" style="object-fit:cover">
                            @endif
                            <div>
                                <p class="dp-display text-sm">{{ $t->user_name }}</p>
                                @if($t->country)
                                    <p class="text-xs" style="color:var(--dp-ink-soft)"><i class="fas fa-map-marker-alt"></i> {{ $t->country }}</p>
                                @elseif($t->role_or_company)
                                    <p class="text-xs" style="color:var(--dp-ink-soft)">{{ $t->role_or_company }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    </div>
                </div>
                <div class="text-center mt-10">
                    <a href="{{ route('testimonials') }}" class="dp-btn dp-btn-outline">{{ __('See all testimonials') }} <i class="fas fa-arrow-right text-sm"></i></a>
                </div>
                @endif
            </div>
        </section>

        {{-- ======= Courier / Store Brands Section ======= --}}
        {{-- NOTE: Run the cwebp commands below to create the .webp versions first,
             then these <picture> tags will serve WebP to modern browsers and
             fall back to PNG for older browsers. No visual change whatsoever.

             Commands (run once on server):
             cd public/frontend/assets/img
             cwebp -q 80 amazon-png-logo-vector-6701.png -o amazon-png-logo-vector-6701.webp
             cwebp -q 80 33.png  -o zara.webp
             cwebp -q 80 17.png  -o etsy.webp
             cwebp -q 80 19.png  -o etsy-uk.webp
             cwebp -q 80 ebay.png -o ebay.webp
             cwebp -q 80 alibaba.png -o alibaba.webp
             cwebp -q 80 2.png   -o carrefour.webp
             cwebp -q 80 7.png   -o store2.webp
             cwebp -q 80 8.png   -o lazada.webp
        --}}
        <section class="dp-grain relative py-16 overflow-hidden" style="background:var(--dp-navy)">
            <div class="dp-blob dp-blob-1 absolute -left-20 top-1/2 -z-0 h-96 w-96 -translate-y-1/2 opacity-20" style="background: radial-gradient(circle, var(--dp-accent), transparent 70%)"></div>
            <div class="container mx-auto px-4 relative mb-10">
                <div class="flex justify-center" data-aos="fade-up">
                    <div class="lg:w-8/12 w-full mx-auto text-center">
                        <h2 class="dp-display text-2xl sm:text-3xl text-white mb-3">{{ __("Shop any store. We'll get it to you.") }}</h2>
                        <p class="text-white/60 text-sm max-w-lg mx-auto">{{ __('Delivering Parcel makes it easy to shop any popular brand from the USA, UK, Australia and beyond — to over 220 countries.') }}</p>
                    </div>
                </div>
            </div>

            @php
                $courierLogos = [
                    ['href' => 'https://www.amazon.com/', 'img' => 'amazon-png-logo-vector-6701', 'alt' => 'Shop on Amazon and ship internationally'],
                    ['href' => 'https://www.zara.com/uk/', 'img' => '33', 'alt' => 'Shop on Zara and reship worldwide'],
                    ['href' => 'https://www.etsy.com/', 'img' => '17', 'alt' => 'Shop on Etsy and forward packages'],
                    ['href' => 'https://www.etsy.com/uk?locale_override=GBP%7Cen-GB%7CGB', 'img' => '19', 'alt' => 'Parcel forwarding from UK stores'],
                    ['href' => 'https://www.walmart.com/', 'img' => 'ebay', 'alt' => 'Shop on eBay and ship worldwide'],
                    ['href' => 'https://offer.alibaba.com/cps/e1f5f93f?bm=cps&src=saf&tp1=5c675efbcb5bc', 'img' => 'alibaba', 'alt' => 'Shop on Alibaba and forward packages', 'nowebp' => true],
                    ['href' => 'https://www.carrefour.com/en', 'img' => '2', 'alt' => 'Shop on Carrefour and ship internationally'],
                    ['href' => 'https://www.flipkart.com/', 'img' => '7', 'alt' => 'International package forwarding'],
                    ['href' => 'https://www.lazada.com/en/', 'img' => '8', 'alt' => 'Shop on Lazada and ship worldwide'],
                ];
            @endphp
            <div class="dp-marquee relative overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                <div class="dp-marquee-fade left-0" style="background: linear-gradient(to right, var(--dp-navy), transparent)"></div>
                <div class="dp-marquee-fade right-0" style="background: linear-gradient(to left, var(--dp-navy), transparent)"></div>
                <div class="dp-marquee-track">
                    @for ($rep = 0; $rep < 2; $rep++)
                        @foreach ($courierLogos as $logo)
                            <a href="{{ $logo['href'] }}" target="_blank" rel="noopener noreferrer" class="curier-img mx-2.5 shrink-0">
                                @if (!empty($logo['nowebp']))
                                    <img src="{{ asset('frontend/assets/img/' . $logo['img'] . '.png') }}" alt="{{ $logo['alt'] }}" loading="lazy" width="160" height="50">
                                @else
                                    <picture>
                                        <source srcset="{{ asset('frontend/assets/img/' . $logo['img'] . '.webp') }}" type="image/webp">
                                        <img src="{{ asset('frontend/assets/img/' . $logo['img'] . '.png') }}" alt="{{ $logo['alt'] }}" loading="lazy" width="160" height="50">
                                    </picture>
                                @endif
                            </a>
                        @endforeach
                    @endfor
                </div>
            </div>
        </section>

        {{-- ======= How to Become a Shipper Section ======= --}}
        <section id="shipper" class="relative py-16" style="background:var(--dp-paper)">
            <div class="container mx-auto px-4 relative" data-aos="fade-up">
                {{-- No data-aos here — same nested-reveal bug; the section's outer
                     container already reveals this whole card as one fade-up. --}}
                <div class="dp-card overflow-hidden grid grid-cols-1 lg:grid-cols-2 relative">
                    <img src="{{ asset('frontend/assets/img/become-a-shipper-hero.jpg') }}"
                         alt="{{ __('A shopper browsing a store on her phone next to an oversized mockup of the checkout screen, representing the shipper-fulfilled order experience') }}"
                         class="h-64 lg:h-full w-full object-cover" loading="lazy" width="640" height="360">
                    <div class="p-8 sm:p-10 flex flex-col justify-center">
                        <h2 class="text-3xl sm:text-4xl mb-4">{{ __('Become a shipper') }}</h2>
                        <p class="mb-3 text-sm leading-relaxed" style="color:var(--dp-ink-soft)">{{ __('Shippers are package forwarders who provide their own local address and services to shoppers — an integral part of the Delivering Parcel team helping us grow internationally.') }}</p>
                        <p class="mb-6 text-sm leading-relaxed" style="color:var(--dp-ink-soft)">{{ __('Want to join the Delivering Parcel Shipper Program? Get in touch below.') }}</p>
                        <div class="flex items-center gap-4 flex-wrap">
                            <a href="{{ route('shipper.register.form') }}" class="dp-btn dp-btn-accent">{{ __('Contact Us') }} <i class="fas fa-arrow-right text-sm"></i></a>
                            <a href="{{ route('shipper.guide') }}" class="dp-btn dp-btn-outline group">
                                {{ __('More Detail') }} <i class="fas fa-arrow-up-right-from-square text-xs transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ======= From Our Blog Section ======= --}}
        @if (isset($posts) && $posts->isNotEmpty())
        <section id="blog-preview" class="relative py-16" style="background:#fff">
            <div class="container mx-auto px-4" data-aos="fade-up">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-10 gap-4">
                    <div>
                        <h2 class="text-3xl sm:text-4xl mb-2">{{ __('From our blog') }}</h2>
                        <p style="color:var(--dp-ink-soft)">{{ __('Guides, shipping tips, and international shopping updates.') }}</p>
                    </div>
                    <a href="{{ url('blog') }}" class="dp-btn dp-btn-outline self-start sm:self-auto">{{ __('All articles') }} <i class="fas fa-arrow-right text-xs"></i></a>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($posts as $p)
                        <a class="dp-card flex flex-col overflow-hidden group hover:shadow-lg transition-shadow" href="{{ route('cms.blog.show', $p->slug) }}">
                            @if ($p->featured_image_url)
                                <div class="overflow-hidden aspect-video bg-slate-100">
                                    <img class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" src="{{ $p->featured_image_url }}" alt="{{ $p->title }}" loading="lazy">
                                </div>
                            @endif
                            <div class="p-6 flex flex-col flex-1">
                                <span class="text-sm font-bold mb-2" style="color:var(--dp-accent-dark)">
                                    {{ optional($p->categories->first())->name ?? __('Guide') }}
                                </span>
                                <h3 class="dp-display text-lg mb-4 font-bold leading-snug flex-1" style="color:var(--dp-ink)">{{ $p->title }}</h3>
                                <div class="flex items-center gap-4 text-xs pt-4" style="color:var(--dp-ink-soft); border-top:1px solid var(--dp-line)">
                                    <span class="inline-flex items-center gap-1.5"><i class="fas fa-calendar-days" aria-hidden="true"></i> {{ optional($p->published_at)->format('M d, Y') }}</span>
                                    <span class="inline-flex items-center gap-1.5"><i class="fas fa-clock" aria-hidden="true"></i> {{ $p->reading_time }} {{ __('min') }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- ======= FAQ Section ======= --}}
        <section id="faq" class="relative py-16" style="background:#fff">
            <div class="container mx-auto px-4 relative" data-aos="fade-up">
                <div class="max-w-xl mb-10 relative">
                    <h2 class="text-3xl sm:text-4xl">{{ __('Frequently asked questions') }}</h2>
                </div>
                {{-- No data-aos here — same nested-reveal bug as elsewhere on this
                     page; the section's outer container already reveals this.
                     English strings here are kept identical to the FAQPage
                     JSON-LD block in @push('head') above — that structured-data
                     copy stays in English regardless of locale (no hreflang
                     alternates are set up in this pilot), so translating just
                     this visible accordion is a deliberate, known mismatch
                     between what a non-English visitor reads and what search
                     engines are told the page says. --}}
                <div class="flex justify-center">
                    @php
                        $homeFaqs = [
                            ['q' => __('What is parcel forwarding used for?'), 'a' => __("Parcel forwarding is mostly used for online shopping in another country via websites that don't deliver directly to your country. It also saves on shipping costs when you combine multiple shipments into one parcel. US package forwarding services help international shoppers ship purchases from the US to their home address, or place direct orders with US retailers on their behalf.")],
                            ['q' => __('Is parcel forwarding safe?'), 'a' => __('Yes, most parcel forwarding companies are safe. It is relatively safe and reliable, especially when dealing with a reputable company. Always check customer reviews before subscribing to a service. Avoid companies whose offers sound too good to be true or that have very few or poor ratings.')],
                            ['q' => __('How much does a parcel forwarding service cost?'), 'a' => __('Most are free to sign up. The overall cost depends on the size of your parcel, weight and how many packages you consolidate. Using consolidation can save significant money on international shipping.')],
                            ['q' => __('What is the cheapest package forwarding service?'), 'a' => __('Delivering Parcel is one of the cheapest package forwarding services available, offering no membership or monthly fees, tax-free shipping and free 45-day package storage. When shipping internationally, items are subject to customs charges — but using a forwarding service that consolidates shipments can help reduce the total customs cost.')],
                            ['q' => __('What is parcel forwarding?'), 'a' => __('Package forwarding, also called parcel forwarding, is an international shipping service offered to online shoppers who want to do cross-border shopping. A parcel forwarding service gives you a free local address in the US, UK, Europe or Australia. Your purchases are shipped to that address and then forwarded on to your actual location anywhere in the world.')],
                            ['q' => __('What is the "Buy For Me" service?'), 'a' => __("If a store won't let you check out from your country, or won't accept your card, tell us what you want and we'll purchase it on your behalf using your Delivering Parcel address, then forward it to you like any other order.")],
                            ['q' => __('Can Delivering Parcel handle customs declarations?'), 'a' => __('Yes. Every shipment leaves with an accurate customs declaration handled for you, and consolidating multiple purchases into one shipment can also help reduce the total customs cost you pay.')],
                        ];
                    @endphp
                    {{-- x-data is a bare component reference (see resources/js/app-public.js);
                         the questions/answers are handed over via data-faqs since the CSP-safe
                         Alpine build can't parse an inline array-of-objects literal here. --}}
                    <div class="lg:w-9/12 w-full" x-data="dpFaqAccordion" data-faqs="{{ json_encode($homeFaqs) }}">
                        <div class="flex flex-col gap-3">
                            <template x-for="(faq, index) in faqs" :key="index">
                                <div class="dp-card overflow-hidden">
                                    <button type="button" class="w-full text-left flex items-center justify-between gap-3 px-5 py-4"
                                            @click="open = (open === index ? null : index)"
                                            :aria-expanded="open === index">
                                        <span class="dp-display text-sm sm:text-base" x-text="faq.q"></span>
                                        <i class="fas text-sm shrink-0 transition-transform" :class="open === index ? 'fa-minus' : 'fa-plus'" style="color:var(--dp-accent)"></i>
                                    </button>
                                    <div x-show="open === index" x-transition x-cloak class="px-5 pb-5 text-sm leading-relaxed" style="color:var(--dp-ink-soft)" x-text="faq.a"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

</div>{{-- /.dp-home --}}

@endsection