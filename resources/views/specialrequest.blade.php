@extends('layouts.fmaster')

@section('title','Request |Best Parcel Forwarding Service')
@section('keywords', 'uk parcel forwarding service,forward parcel,how to forward a package,reshipping,best package forwarding germany,repackaging services,repackaging company,repackaging,shipandshop,')
@section('meta_description','consolidate packages mean,parcel receiving service,we ship worldwide,ship only,ship from,australia forwarding service,get an american address,forward packaging,euro address,best uk proxy service')

@section('content')
{{-- Was `.dp-tw`, a bare wrapper with no CSS rule of its own anywhere in
     app.css — meaning `dp-display` on this page's own h1 never actually
     applied (that font/weight/letter-spacing is scoped `.dp-home .dp-display`
     only), so this page has never once rendered in the brand's own display
     typeface, unlike literally every other page. Switching to `.dp-home`
     gives it the exact same type, ink color and paper background as the
     rest of the site — reusing what the system already owns, not adding
     anything new. --}}
<div class="dp-home">
<section class="container mx-auto px-4 max-w-3xl pt-28 pb-16">
    {{-- One entrance for the whole card (this page has zero motion
         otherwise, standing out as flat next to every other page's single
         authored moment) — reusing the sitewide .dp-hero-in fade-up rather
         than inventing a new keyframe. Above-the-fold, so it plays on load,
         not on scroll. --}}
    <div class="dp-req-card dp-hero-in relative overflow-hidden bg-white rounded-2xl border shadow-xl" style="border-color:var(--dp-line); box-shadow:0 20px 50px -24px rgba(20,23,26,.3)">
        {{-- Signature top bar: the same accent-to-cyan sweep already used
             for icon badges elsewhere (e.g. the region globe chips) —
             this page's one confident brand mark, not a new device. --}}
        <div class="absolute inset-x-0 top-0 h-1.5" style="background:linear-gradient(90deg, var(--dp-accent), var(--dp-cyan))" aria-hidden="true"></div>
        <div class="dp-req-body p-5 sm:p-8 sm:pt-9">
            <h1 class="dp-display text-3xl sm:text-4xl text-center mb-2">Place a Request</h1>
            <p class="text-center font-semibold mb-1" style="color:var(--dp-ink)">Welcome to Delivering Parcel, Reliable Package Forwarding and Parcel Reshipping Service Worldwide</p>
            <p class="text-center text-sm mb-1" style="color:var(--dp-ink-soft)">Please try to provide full Description of item like size, colour etc.</p>
            <p class="text-center text-sm mb-6" style="color:var(--dp-ink-soft)">Please try to include the exact URL of the item or good which will be ordered or Forwarded. You are required to mention the weight of the item as this might effect Shipping Cost.</p>

            @include('flash-message')

            {{-- Purely a display/navigation layer (see dpRequestWizard in
                 app-public.js): every field below stays exactly where it
                 was, in the same order, with the same name/id/required
                 attributes — only which step's <div> is visible changes.
                 The jQuery totals math and the NaN-guard submit validation
                 further down never look at "which step" anything is in, so
                 neither one needed to change. Steps 2-4 carry x-cloak (this
                 page already leans on Alpine for its whole interaction
                 model, same as the rest of the site); step 1 doesn't, so
                 there's no blank instant before Alpine hydrates. --}}
            <form action="{{route('orders.store')}}" name="myform" method="post" id="request-form" enctype="multipart/form-data" x-data="dpRequestWizard">
                <div x-ref="dpWizardTop"></div>
                {{-- ======= Step progress ======= --}}
                <ol class="dp-wizard-steps mb-8" role="list">
                    @foreach ([
                        ['n' => 1, 'label' => 'Shipment'],
                        ['n' => 2, 'label' => 'Products'],
                        ['n' => 3, 'label' => 'Add-ons'],
                        ['n' => 4, 'label' => 'Review'],
                    ] as $s)
                        <li class="dp-wizard-step" :class="{ 'is-active': step === {{ $s['n'] }}, 'is-done': step > {{ $s['n'] }} }">
                            <button type="button" class="dp-wizard-dot" @click="goTo({{ $s['n'] }})" :disabled="{{ $s['n'] }} >= step" :aria-current="step === {{ $s['n'] }} ? 'step' : false">
                                <i class="fa-solid fa-check dp-wizard-check" aria-hidden="true"></i>
                                <span class="dp-wizard-num">{{ $s['n'] }}</span>
                            </button>
                            <span class="dp-wizard-label">{{ $s['label'] }}</span>
                        </li>
                    @endforeach
                </ol>

                @csrf
                <div x-ref="dpStep1" x-show="step === 1">
                @if(Auth::user())
                <input type="hidden" name="user_id" value="{{Auth::user()->id}}">
                @else
                <div class="mb-4">
                    <label for="name" class="block text-sm font-semibold mb-1.5">Name</label>
                    <input type="text" class="dp-req-field" name="name" maxlength="22" id="name" value="" placeholder="Name" required />
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-sm font-semibold mb-1.5">Email</label>
                    <input type="email" class="dp-req-field" name="email" id="email" value="" placeholder="Email" required />
                </div>
                <div class="mb-4">
                    <label for="contactnumber" class="block text-sm font-semibold mb-1.5">Contact Number</label>
                    <input type="text" class="dp-req-field" name="number" id="contactnumber" value="" placeholder="Please add a country code as well" required />
                </div>
                <input type="hidden" name="user_id" value="user-invalid">
                @endif

                <h2 class="dp-display text-lg mb-4 pb-2 border-b" style="border-color:var(--dp-line)">Shipment details</h2>
                <div class="mb-4">
                    <label for="shipfrom" class="block text-sm font-semibold mb-1.5">Ship From</label>
                    <select name="shipfrom" id="shipfrom" class="dp-req-field" required>
                        @if(isset($data['from']))
                        <option value="{{ $data['from'] }}" selected>{{$data['from']}}</option>
                        @else
                        <option value="" disabled selected>Select country</option>
                        @endif
                        @foreach (\Illuminate\Support\Facades\DB::table('countries')->where('allow_request_from', 1)->orderBy('name')->get(['name']) as $rc)
                        <option value="{{ $rc->name }}">{{ strtoupper($rc->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="shipto" class="block text-sm font-semibold mb-1.5">Ship To</label>
                    <select name="shipto" id="shipto" class="dp-req-field" required>
                        @if(isset($data['from']))
                        <option value="{{ $data['to']}}" selected>{{$data['to']}}</option>
                        @else
                        <option value="" disabled selected>Select country</option>
                        @endif
                        @foreach (\Illuminate\Support\Facades\DB::table('countries')->where('allow_delivery_to', 1)->orderBy('name')->get(['name']) as $rc)
                        <option value="{{ $rc->name }}">{{ strtoupper($rc->name) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="postalcode" class="block text-sm font-semibold mb-1.5">Postal Code</label>
                    <input type="text" class="dp-req-field" name="postalcode" id="postalcode" value="" placeholder="Postal code of destination country" required />
                </div>
                <div class="mb-6">
                    <label for="address" class="block text-sm font-semibold mb-1.5">Address</label>
                    <input type="text" class="dp-req-field" name="address" id="address" value="" placeholder="Address" required />
                </div>
                <div class="dp-wizard-nav">
                    <span></span>
                    <button type="button" class="dp-btn dp-btn-accent" @click="next()">Next: Products <i class="fas fa-arrow-right text-sm"></i></button>
                </div>
                </div>{{-- /step 1 --}}

                <div x-ref="dpStep2" x-show="step === 2" x-cloak>
                <h2 class="dp-display text-lg mb-4 pb-2 border-b" style="border-color:var(--dp-line)">Products</h2>
                <div class="mb-2 overflow-x-auto">
                    <table class="w-full text-sm border-collapse" id="dynamicTable">
                        <tr>
                            <th class="text-left text-xs font-semibold uppercase tracking-wide pb-2 border-b" style="color:var(--dp-ink-soft,#5c6066); border-color:var(--dp-line,#e5e3de)">Product Name</th>
                            <th class="text-left text-xs font-semibold uppercase tracking-wide pb-2 border-b" style="color:var(--dp-ink-soft,#5c6066); border-color:var(--dp-line,#e5e3de)">Product Description</th>
                            <th class="text-left text-xs font-semibold uppercase tracking-wide pb-2 border-b" style="color:var(--dp-ink-soft,#5c6066); border-color:var(--dp-line,#e5e3de)">Product URL</th>
                            <th class="text-left text-xs font-semibold uppercase tracking-wide pb-2 border-b" style="color:var(--dp-ink-soft,#5c6066); border-color:var(--dp-line,#e5e3de)">Product Quantity</th>
                            <th class="text-left text-xs font-semibold uppercase tracking-wide pb-2 border-b" style="color:var(--dp-ink-soft,#5c6066); border-color:var(--dp-line,#e5e3de)">Price Per Unit(USD$)</th>
                            <th class="text-left text-xs font-semibold uppercase tracking-wide pb-2 border-b" style="color:var(--dp-ink-soft,#5c6066); border-color:var(--dp-line,#e5e3de)">Weight Per Unit (Gram)</th>
                            <th class="text-left text-xs font-semibold uppercase tracking-wide pb-2 border-b" style="color:var(--dp-ink-soft,#5c6066); border-color:var(--dp-line,#e5e3de)">Total Price</th>
                            <th class="text-left text-xs font-semibold uppercase tracking-wide pb-2 border-b" style="color:var(--dp-ink-soft,#5c6066); border-color:var(--dp-line,#e5e3de)">Add More</th>
                        </tr>
                        <tr>
                            <td data-label="Product name" class="py-2 pr-2"><input type="text" name="addmore[0][productname]" placeholder="Name" class="dp-req-field" required /></td>
                            <td data-label="Description" class="py-2 pr-2"><input type="text" name="addmore[0][product_description]" placeholder=" Description" class="dp-req-field" required /></td>
                            <td data-label="Product URL" class="py-2 pr-2"><input type="text" name="addmore[0][producturl]" placeholder=" URL" class="dp-req-field" required /></td>
                            <td data-label="Quantity" class="py-2 pr-2"><input type="number" name="addmore[0][productquantity]" placeholder="Qty" class="dp-req-field quantity" min="0" required /></td>
                            <td data-label="Price per unit (USD)" class="py-2 pr-2"><input type="number" name="addmore[0][productprice]" placeholder="Price" class="dp-req-field price" min="0" required /></td>
                            <td data-label="Weight per unit (g)" class="py-2 pr-2"><input type="number" name="addmore[0][productweight]" placeholder="weight" class="dp-req-field weight" min="0" required /></td>
                            <td data-label="Line total" class="py-2 pr-2"><input type="number" name="addmore[0][producttotal]" placeholder="Total Price" class="dp-req-field producttotal" min="0" readonly required /></td>
                            <td class="hide"><input type="number" name="addmore[0][product_weight]" class="dp-req-field product_weight" /></td>
                            <td class="text-center py-2"><button type="button" name="add" id="add" class="dp-req-icon-btn dp-req-icon-add"><i class="fa fa-plus"></i></button></td>
                        </tr>
                    </table>
                </div>
                <button type="button" class="dp-btn dp-btn-outline w-full md:hidden mb-6" onclick="jQuery('#add').click()" style="border-style:dashed;border-width:2px;">
                    <i class="fa fa-plus"></i> Add another product
                </button>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Total Approximate Weight in Gram</label>
                        <input type="text" class="dp-req-field net_weight" name="approximate_weight" value="" placeholder="Approximate weight" readonly />
                    </div>
                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Products Total Price</label>
                        <input type="number" class="dp-req-field net_total" name="total_price" value="" placeholder="Product total price" min="0" readonly>
                    </div>
                </div>
                <div class="dp-wizard-nav">
                    <button type="button" class="dp-btn dp-btn-outline" @click="back()"><i class="fas fa-arrow-left text-sm"></i> Back</button>
                    <button type="button" class="dp-btn dp-btn-accent" @click="next()">Next: Add-ons <i class="fas fa-arrow-right text-sm"></i></button>
                </div>
                </div>{{-- /step 2 --}}

                <div x-ref="dpStep3" x-show="step === 3" x-cloak>
                <h2 class="dp-display text-lg mb-4 pb-2 border-b" style="border-color:var(--dp-line)">Add-on services</h2>
                {{-- `has-[:checked]:` styles the chip from its own checkbox's
                     state — Tailwind v4 ships this variant by default, so
                     no JS is added just to toggle a selected look; the
                     jQuery totals logic still only reads `.example`/checked/
                     value, untouched. --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-sm">
                    <div class="space-y-2">
                        <label class="flex items-start gap-2 rounded-xl border p-3 transition-colors has-[:checked]:border-[color:var(--dp-accent)] has-[:checked]:bg-[var(--dp-accent-soft)]" style="border-color:var(--dp-line)"><input name="product_check" value="1" type="checkbox" id="address_6" onclick="total_address()" class="example mt-0.5" required /> <span>Content Check</span></label>
                        <label class="flex items-start gap-2 rounded-xl border p-3 transition-colors has-[:checked]:border-[color:var(--dp-accent)] has-[:checked]:bg-[var(--dp-accent-soft)]" style="border-color:var(--dp-line)"><input name="product_services" value="9" type="checkbox" id="address_7" onclick="total_address()" class="example mt-0.5" required /> <span class="font-semibold">Forwarding Service Fee</span></label>
                    </div>
                    <div class="space-y-2">
                        <input name="product_photo" value="0" type="hidden" />
                        <label class="flex items-start gap-2 rounded-xl border p-3 transition-colors has-[:checked]:border-[color:var(--dp-accent)] has-[:checked]:bg-[var(--dp-accent-soft)]" style="border-color:var(--dp-line)"><input name="product_photo" value="3" type="checkbox" id="address_1" onclick="total_address()" class="example mt-0.5" /> <span>Product photo</span></label>
                        <input name="product_customs" value="0" type="hidden" />
                        <label class="flex items-start gap-2 rounded-xl border p-3 transition-colors has-[:checked]:border-[color:var(--dp-accent)] has-[:checked]:bg-[var(--dp-accent-soft)]" style="border-color:var(--dp-line)"><input name="product_customs" value="4" type="checkbox" id="address_2" onclick="total_address()" class="example mt-0.5" /> <span>Customs Declaration</span></label>
                    </div>
                    <div class="space-y-2">
                        <input name="product_prohibited" value="0" type="hidden" />
                        <label class="flex items-start gap-2 rounded-xl border p-3 transition-colors has-[:checked]:border-[color:var(--dp-accent)] has-[:checked]:bg-[var(--dp-accent-soft)]" style="border-color:var(--dp-line)"><input name="product_prohibited" value="1" type="checkbox" id="address_4" onclick="total_address()" class="example mt-0.5" /> <span>Removal of Prohibited Items</span></label>
                        <input name="product_consolidation" value="0" type="hidden" />
                        <input name="product_disinfection" value="0" type="hidden" />
                        <input name="purchase_assistence" value="0" type="hidden" />
                        <label class="flex items-start gap-2 rounded-xl border p-3 transition-colors has-[:checked]:border-[color:var(--dp-accent)] has-[:checked]:bg-[var(--dp-accent-soft)]" style="border-color:var(--dp-line)"><input name="purchase_assistence" value="10" type="checkbox" id="purchase_7" class="example mt-0.5" /> <span class="font-semibold">Purchase Assistance</span></label>
                    </div>
                </div>
                <div class="dp-wizard-nav">
                    <button type="button" class="dp-btn dp-btn-outline" @click="back()"><i class="fas fa-arrow-left text-sm"></i> Back</button>
                    <button type="button" class="dp-btn dp-btn-accent" @click="next()">Next: Review <i class="fas fa-arrow-right text-sm"></i></button>
                </div>
                </div>{{-- /step 3 --}}

                <div x-ref="dpStep4" x-show="step === 4" x-cloak>
                <h2 class="dp-display text-lg mb-4 pb-2 border-b" style="border-color:var(--dp-line)">Review &amp; submit</h2>
                <div class="mb-2">
                    <label class="flex items-start gap-2 text-sm mb-4">
                        <input type="checkbox" value="1" name="terms" id="agree_1" required class="mt-0.5" />
                        <span>By clicking the tick button, I hereby agree and consent to the terms of business, its policies, and the Privacy Policy.</span>
                    </label>
                    {{-- Same icon-chip device as "Nine things you get" on the
                         homepage (accent-soft rounded box + FA icon) instead
                         of a bare 16px inline SVG — the system's own way of
                         giving a text list some presence. --}}
                    <div class="space-y-3 text-sm" style="color:var(--dp-ink-soft)">
                        @foreach ([
                            'Please be noted that once you submit a Request, You will be offered by Delivering Parcel registered Shippers. Shopper can communicate with Shipper through Message tab after submitting request.',
                            'You will be Offered Shortly by Our Shippers, offer include Shipping Cost which means there is no hidden Cost unless Weight Varies from the mentioned one in Request.',
                            'Delivering Parcel Offers Full Money refund incase the item is not available or Services are not Provided by Shipper.',
                            'You will not pay any thing at this stage of the request.',
                            'Shopper can Create a Free Request as there is no payment required at this time. Please revert to Your mailbox once you Submit a Request to gain Access to your Dashboard.',
                        ] as $note)
                            <div class="flex items-start gap-3">
                                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg" style="background:var(--dp-accent-soft)">
                                    <i class="fa-solid fa-check text-xs" style="color:var(--dp-accent-dark)" aria-hidden="true"></i>
                                </span>
                                <span class="pt-0.5">{{ $note }}</span>
                            </div>
                        @endforeach
                    </div>

                    <input value="$0" readonly="readonly" type="hidden" id="paynow" name="total" class="total" />

                    <div class="flex flex-col items-center gap-4 mt-6">
                        @if(\App\Models\Setting::getBool('api_turnstile_enabled', true))
                        <div class="cf-turnstile" data-sitekey="0x4AAAAAABdxU2xNnEwFvrQl"></div>
                        @endif
                        <button type="submit" id="btnAddProfile" class="dp-btn dp-btn-accent placeorder dp-req-submit group w-full sm:w-auto px-10 py-3 text-base">
                            Place Request <i class="fas fa-arrow-right text-sm transition-transform group-hover:translate-x-1"></i>
                        </button>
                        <button type="button" class="dp-btn dp-btn-outline" @click="back()"><i class="fas fa-arrow-left text-sm"></i> Back</button>
                    </div>
                </div>
                </div>{{-- /step 4 --}}
            </form>
        </div>
    </div>
    <div id="loader"></div>
</section>
</div>

<style>
/* ===== Multi-step wizard: progress rail + nav row =====
   Four fixed steps, so this is written directly rather than as a generic
   N-step component — the fewer moving parts, the less that can drift out
   of sync with dpRequestWizard's own hardcoded totalSteps: 4. */
.dp-wizard-steps {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    list-style: none;
    padding: 0;
    margin: 0;
    position: relative;
}
.dp-wizard-steps::before {
    content: '';
    position: absolute;
    top: 16px;
    left: 16px;
    right: 16px;
    height: 2px;
    background: var(--dp-line, #e5e3de);
    z-index: 0;
}
.dp-wizard-step {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: .5rem;
    position: relative;
    z-index: 1;
    text-align: center;
}
.dp-wizard-dot {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 999px;
    border: 2px solid var(--dp-line, #e5e3de);
    background: #fff;
    color: var(--dp-ink-soft, #5c6066);
    font-weight: 700;
    font-size: .85rem;
    cursor: pointer;
    transition: border-color .2s ease, background-color .2s ease, color .2s ease;
}
.dp-wizard-dot:disabled { cursor: default; }
.dp-wizard-check { display: none; font-size: .7rem; }
.dp-wizard-step.is-active .dp-wizard-dot {
    border-color: var(--dp-accent, #f5951e);
    background: var(--dp-accent-soft, #fdecd8);
    color: var(--dp-accent-dark, #d97a0e);
}
.dp-wizard-step.is-done .dp-wizard-dot {
    border-color: var(--dp-accent, #f5951e);
    background: var(--dp-accent, #f5951e);
    color: #fff;
}
.dp-wizard-step.is-done .dp-wizard-num { display: none; }
.dp-wizard-step.is-done .dp-wizard-check { display: block; }
.dp-wizard-label {
    font-size: .72rem;
    font-weight: 600;
    color: var(--dp-ink-soft, #5c6066);
}
.dp-wizard-step.is-active .dp-wizard-label { color: var(--dp-ink, #14171a); }
.dp-wizard-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 1.75rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--dp-line, #e5e3de);
}

/* Tailwind-only page: a plain input/select style reused everywhere via
   .dp-req-field, since this page has no <select>/<input> component classes
   of its own yet (unlike the hero's .dp-console-field). */
.dp-req-field {
    width: 100%;
    border: 1px solid var(--dp-line, #e5e3de);
    border-radius: 8px;
    padding: .6rem .85rem;
    font-size: 16px; /* iOS: prevents auto-zoom on focus */
    color: var(--dp-ink, #14171a);
    background: #fff;
}
.dp-req-field:focus {
    outline: none;
    border-color: var(--dp-accent, #f5951e);
    box-shadow: 0 0 0 3px var(--dp-accent-soft, #fdecd8);
}
.dp-req-icon-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 2.25rem; height: 2.25rem; border-radius: 999px; color: #fff;
}
.dp-req-icon-add { background: #16a34a; }
.dp-req-icon-add:hover { background: #15803d; }
.dp-req-icon-remove { background: #dc2626; }
.dp-req-icon-remove:hover { background: #b91c1c; }

#loader {
    visibility: hidden;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    width: 100%;
    background: rgba(0,0,0,0.75) url('{{ asset('images/Spinner-2.gif') }}') no-repeat center center;
    z-index: 10000;
}

/* Same pulse as .get-a-quote-btn in the header (dpCtaPulse, defined once in
   app.css) — reused, not redeclared, so "Place Request" reads as the same
   family of confident CTA rather than a one-off. */
.dp-req-submit { animation: dpCtaPulse 3.2s ease-in-out infinite; }
.dp-req-submit:hover { animation-play-state: paused; }

/* ===== Mobile UX: product cards instead of a horizontal-scroll table =====
   Was #0d2a52/#e3e6ea/#6c757d — an off-brand navy/gray pair that appears
   nowhere else on the site — swapped for the real dp-ink/dp-line tokens so
   this, the one part of the page that already had some visual structure,
   actually matches the brand instead of inventing its own. */
@media (max-width: 767.98px) {
    #dynamicTable { display: block; width: 100%; counter-reset: dpProd; }
    #dynamicTable > tr:first-child { display: none; } /* header row */
    #dynamicTable tr { display: block; background: #fff; border: 1px solid var(--dp-line, #e5e3de); border-radius: 14px;
        padding: 12px 14px 10px; margin: 0 0 14px; box-shadow: 0 2px 10px rgba(20,23,26,.06); counter-increment: dpProd; position: relative; }
    #dynamicTable tr::before { content: "Product " counter(dpProd); display: block; font-weight: 700;
        color: var(--dp-ink, #14171a); font-size: .95rem; margin-bottom: 8px; }
    #dynamicTable th { display: none; }
    #dynamicTable td { display: block; width: 100%; border: none; padding: .3rem 0; }
    #dynamicTable td.hide { display: none !important; }
    #dynamicTable td::before { content: attr(data-label); display: block; font-size: .7rem; text-transform: uppercase;
        letter-spacing: .06em; color: var(--dp-ink-soft, #5c6066); font-weight: 700; margin-bottom: 3px; }
    #dynamicTable td.text-center { display: flex; gap: 10px; width: auto; padding-top: 10px; }
    #dynamicTable td.text-center::before { content: none !important; }
    .placeorder { position: sticky; bottom: 12px; z-index: 40; box-shadow: 0 10px 26px rgba(20,23,26,.35); }
}
/* visible up/down arrows for Qty / Price / Weight — overlaid inside the
   input's right edge (native-spinner style). The input stays a DIRECT
   child of its <td>: the page's totals math navigates via parent/next. */
td.dp-has-step { position: relative; }
.dp-spin-input { padding-right: 30px !important; }
.dp-arrow { position: absolute; right: 4px; width: 24px; height: 50%;
    border: none; background: var(--dp-accent-soft, #fdecd8); color: var(--dp-accent-dark, #d97a0e); font-size: .62rem;
    line-height: 1; cursor: pointer; padding: 0; }
.dp-arrow.dp-up { top: 4px; border-radius: 4px 4px 0 0; }
.dp-arrow.dp-down { bottom: 4px; border-radius: 0 0 4px 4px; }
.dp-arrow:hover { background: var(--dp-accent, #f5951e); color: #fff; }
.dp-arrow:active { background: var(--dp-accent-dark, #d97a0e); color: #fff; }
</style>

{{-- jQuery: fmaster (Alpine-based) doesn't load it, and this form's
     calculator/validation logic below is written entirely in jQuery.
     That logic is left untouched — restyling to Tailwind is a markup/CSS
     change, not a rewrite of working, business-critical totals math. --}}
<script src="{{ url('dashbord/plugins/jquery/jquery-3.6.0.min.js') }}"></script>

<script>
/* Up/down arrows for quantity / price / weight. Buttons are added INSIDE
   the same <td> (absolutely positioned over the input) so the legacy
   totals handlers — which navigate parent td → next td — keep working. */
(function () {
    var STEPS = { quantity: 1, price: 1, weight: 100 };
    function decorate(input) {
        if (input.getAttribute('data-dp-step')) return;
        input.setAttribute('data-dp-step', '1');
        var cls = input.classList.contains('quantity') ? 'quantity'
                : (input.classList.contains('weight') ? 'weight' : 'price');
        var step = STEPS[cls] || 1;
        input.classList.add('dp-spin-input');
        var td = input.parentNode;
        td.classList.add('dp-has-step');
        function makeArrow(dir) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'dp-arrow ' + (dir > 0 ? 'dp-up' : 'dp-down');
            b.innerHTML = dir > 0 ? '▲' : '▼';
            b.setAttribute('aria-label', (dir > 0 ? 'Increase ' : 'Decrease ') + cls);
            b.addEventListener('click', function () {
                var v = parseFloat(input.value) || 0;
                v = Math.max(0, +(v + dir * step).toFixed(2));
                input.value = v;
                ['input', 'change', 'keyup'].forEach(function (ev) {
                    input.dispatchEvent(new Event(ev, { bubbles: true }));
                });
            });
            td.appendChild(b);
        }
        makeArrow(+1);
        makeArrow(-1);
    }
    function scan() {
        document.querySelectorAll('#request-form .quantity, #request-form .price, #request-form .weight')
            .forEach(decorate);
    }
    document.addEventListener('DOMContentLoaded', function () {
        scan();
        var t = document.getElementById('dynamicTable');
        if (t && 'MutationObserver' in window) {
            new MutationObserver(function () { scan(); }).observe(t, { childList: true, subtree: true });
        }
    });
})();
</script>
<script>
    $(document).ready(function() {
        $('.hide').hide();
        var grand_total = 0;
        $('.example').each(function() {
            if ($(this).prop("checked")) {
                grand_total += parseFloat($(this).val());
            }
        })
        $('input[name="total"]').val(grand_total);
        $(document).on("change keyup input", ".quantity", function() {
            var total = 0;
            var net_total = 0;
            var net_weight = 0;
            var price = parseFloat($(this).parent().next().find($('.price')).val());
            var weight = parseFloat($(this).parent().next().next().find($('.weight')).val());
            var quantity = parseFloat($(this).val());
            if (isNaN(quantity)) { quantity = 0; }
            if (isNaN(price)) { price = 0; }
            if (isNaN(weight)) { weight = 0; }
            total = price * quantity;
            var each_weight = quantity * weight;
            $(this).parent().next().next().next().find($('.producttotal')).val(total);
            $(this).parent().next().next().next().next().find($('.product_weight')).val(each_weight);
            $('.producttotal').each(function() { net_total += parseFloat($(this).val()); });
            $('.net_total').val(net_total);
            $('.product_weight').each(function() { net_weight += parseFloat($(this).val()); });
            $('.net_weight').val(net_weight);
            if ($('#purchase_7').prop("checked") && $(".net_total").val() != '') {
                var grand_total = 0;
                grand_total += parseFloat($(".net_total").val());
                $('.example').each(function() {
                    if ($(this).prop("checked")) { grand_total += parseFloat($(this).val()); }
                })
                $('input[name="total"]').val(grand_total);
            }
        });
        $(document).on("change keyup input", ".weight", function() {
            var net_weight = 0;
            var quantity = parseFloat($(this).parent().prev().prev().find($('.quantity')).val());
            var weight = parseFloat($(this).val());
            if (isNaN(weight)) { weight = 0; }
            if (isNaN(quantity)) { quantity = 0; }
            var each_weight = weight * quantity;
            $(this).parent().next().next().find($('.product_weight')).val(each_weight);
            $('.product_weight').each(function() { net_weight += parseFloat($(this).val()); });
            $('.net_weight').val(net_weight);
        });
        $(document).on("change keyup input", ".price", function() {
            var total = 0;
            var net_total = 0;
            var quantity = parseFloat($(this).parent().prev().find($('.quantity')).val());
            var price = parseFloat($(this).val());
            if (isNaN(quantity)) { quantity = 0; }
            total = price * quantity;
            $(this).parent().next().next().find($('.producttotal')).val(total);
            $('.producttotal').each(function() { net_total += parseFloat($(this).val()); });
            $('.net_total').val(net_total);
            if ($('#purchase_7').prop("checked") && $(".net_total").val() != '') {
                var grand_total = 0;
                grand_total += parseFloat($(".net_total").val());
                $('.example').each(function() {
                    if ($(this).prop("checked")) { grand_total += parseFloat($(this).val()); }
                })
                $('input[name="total"]').val(grand_total);
            }
        }); //price end
    });
    function total_address() {
        var input = document.getElementsByClassName("example");
        var total = 0;
        for (var i = 0; i < input.length; i++) {
            if (input[i].checked) { total += parseFloat(input[i].value); }
        }
        if ($('#purchase_7').prop("checked") && $(".net_total").val() != '') {
            total += parseFloat($(".net_total").val());
        }
        document.getElementsByName("total")[0].value = total.toFixed(2);
    }
    $('#purchase_7').on('change', function() {
        var total;
        var services_total = 0;
        $('.example').each(function() {
            if ($(this).prop("checked")) { services_total += parseFloat($(this).val()); }
        });
        if ($(this).prop("checked") && $(".net_total").val() != '') {
            var products_total = parseFloat($(".net_total").val());
            total = products_total + services_total;
        } else {
            total = services_total;
        }
        $('input[name="total"]').val(total);
    });
</script>
<script type="text/javascript">
    var i = 0;
    function dpRowHtml(i) {
        return '<tr>'
            + '<td data-label="Product name" class="py-2 pr-2"><input type="text" name="addmore[' + i + '][productname]" placeholder=" Name" class="dp-req-field" required /></td>'
            + '<td data-label="Description" class="py-2 pr-2"><input type="text" name="addmore[' + i + '][product_description]" placeholder="Description" class="dp-req-field" required /></td>'
            + '<td data-label="Product URL" class="py-2 pr-2"><input type="text" name="addmore[' + i + '][producturl]" placeholder=" URL" class="dp-req-field" required /></td>'
            + '<td data-label="Quantity" class="py-2 pr-2"><input type="text" name="addmore[' + i + '][productquantity]" placeholder=" Quantity" class="dp-req-field quantity" min="0" required /></td>'
            + '<td data-label="Price per unit (USD)" class="py-2 pr-2"><input type="number" name="addmore[' + i + '][productprice]" placeholder=" Price" class="dp-req-field price" min="0" required /></td>'
            + '<td data-label="Weight per unit (g)" class="py-2 pr-2"><input type="number" name="addmore[' + i + '][productweight]" placeholder="Weight / unit" class="dp-req-field weight" min="0" required /></td>'
            + '<td data-label="Line total" class="py-2 pr-2"><input type="number" name="addmore[' + i + '][producttotal]" placeholder="Total Price" class="dp-req-field producttotal" min="0" readonly /></td>'
            + '<td class="hide"><input type="number" name="addmore[' + i + '][product_weight]" class="dp-req-field product_weight" /></td>'
            + '<td class="text-center py-2"><button type="button" name="add" id="add" class="dp-req-icon-btn dp-req-icon-add add"><i class="fa fa-plus"></i></button></td>'
            + '<td class="text-center py-2"><button type="button" class="dp-req-icon-btn dp-req-icon-remove remove-tr"><i class="fa fa-minus"></i></button></td>'
            + '</tr>';
    }
    $("#add").click(function() {
        $('.hide').hide();
        ++i;
        $("#dynamicTable").append(dpRowHtml(i));
    });
    $(document).on('click', '.add', function() {
        ++i;
        $("#dynamicTable").append(dpRowHtml(i));
    });
    $(document).on('click', '.remove-tr', function() {
        var net_total = 0;
        var net_weight = 0;
        $(this).parents('tr').remove();
        $('.producttotal').each(function() { net_total += parseFloat($(this).val()); });
        $('.net_total').val(net_total);
        $('.product_weight').each(function() { net_weight += parseFloat($(this).val()); });
        $('.net_weight').val(net_weight);
        if ($('#purchase_7').prop("checked") && $(".net_total").val() != '') {
            var grand_total = 0;
            grand_total += parseFloat($(".net_total").val());
            $('.example').each(function() {
                if ($(this).prop("checked")) { grand_total += parseFloat($(this).val()); }
            });
            $('input[name="total"]').val(grand_total);
        }
    });
</script>
<script>
    $('#request-form').submit(function() {
        $('#loader').css('visibility', 'visible');
    });
</script>
<script>
    /* DP 2026-09-12 - NaN guard: the computed Total Approximate Weight and
       Products Total Price must be valid numbers before Place Request can be
       clicked. Submit is hard-blocked too, so removing the disabled attribute
       via devtools still cannot submit the form. */
    (function() {
        function validate() {
            var ok = true;
            $('input.net_weight, input.net_total').each(function() {
                var $f = $(this);
                var raw = String($f.val() == null ? '' : $f.val()).trim();
                var v = parseFloat(raw);
                var bad = (raw === '' || isNaN(v) || v < 0);
                var $err = $f.siblings('.dp-nan-error');
                if (bad) {
                    ok = false;
                    if (!$err.length) {
                        $err = $('<div class="dp-nan-error" style="color:#dc3545;font-size:13px;margin-top:4px;"></div>');
                        $f.after($err);
                    }
                    $err.text($f.hasClass('net_weight')
                        ? 'Not a valid number yet - fill quantity and weight for every product row.'
                        : 'Not a valid number yet - fill quantity and price for every product row.');
                } else if ($err.length) {
                    $err.remove();
                }
            });
            $('#btnAddProfile').prop('disabled', !ok);
            return ok;
        }
        $(document).on('change keyup input', 'input', validate); // document-level: runs after the calc handlers
        $('#request-form').on('click', '.add, #add, .remove-tr', function() { setTimeout(validate, 30); });
        $('#request-form').submit(function(e) {
            if (!validate()) {
                e.preventDefault();
                $('#loader').css('visibility', 'hidden');
                return false;
            }
        });
        validate();
    })();
</script>

@if(\App\Models\Setting::getBool('api_turnstile_enabled', true))
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif
@endsection
