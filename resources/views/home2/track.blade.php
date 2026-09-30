@extends('layouts.fmaster')

@section('title', 'Track Order & Shipment Status | Delivering Parcel')
@section('meta_description', 'Track your parcel forwarding order, check live carrier status, and follow package transit updates with Delivering Parcel.')
@section('keywords', 'track order, track parcel, package tracking, shipment status, parcel forwarding tracking')

@section('content')
<div class="min-h-screen bg-slate-50/70 pt-28 pb-20 sm:pt-32 sm:pb-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="text-center max-w-3xl mx-auto mb-10" data-aos="fade-up">
            <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/80 mb-4 shadow-xs">
                <i class="fas fa-satellite-dish text-blue-600 text-xs"></i> Live Carrier Tracking
            </span>
            <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                Track Your Shipment
            </h1>
            <p class="mt-4 text-base sm:text-lg text-slate-600 leading-relaxed max-w-2xl mx-auto">
                Enter your order reference ID and email address to view live tracking milestones and international carrier status.
            </p>
        </div>

        {{-- Tracking Lookup Card --}}
        <div class="max-w-2xl mx-auto bg-white rounded-3xl border border-slate-200/90 shadow-xl p-6 sm:p-10 mb-10" data-aos="fade-up">
            <form method="POST" action="{{ route('track.legacy.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="track_ref" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Order Reference or Tracking Number <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 w-10 flex items-center justify-center pointer-events-none text-slate-400">
                            <i class="fas fa-barcode"></i>
                        </span>
                        <input id="track_ref" type="text" name="ref" value="{{ old('ref', $ref ?? '') }}"
                               placeholder="e.g. DP-10842 or 1Z9999999999999999" required maxlength="100"
                               class="w-full pl-11 pr-4 py-3 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-xs">
                    </div>
                </div>

                <div>
                    <label for="track_email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                        Account Email Address <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 w-10 flex items-center justify-center pointer-events-none text-slate-400">
                            <i class="fas fa-envelope"></i>
                        </span>
                        <input id="track_email" type="email" name="email" value="{{ old('email', $email ?? '') }}"
                               placeholder="e.g. your.email@example.com" required maxlength="255"
                               class="w-full pl-11 pr-4 py-3 bg-slate-50/50 hover:bg-white focus:bg-white border border-slate-300 rounded-xl text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition shadow-xs">
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-6 rounded-xl shadow-lg shadow-blue-600/25 transition-all text-sm flex items-center justify-center gap-2 cursor-pointer transform hover:-translate-y-0.5">
                    <i class="fas fa-magnifying-glass text-xs"></i> Track Shipment
                </button>
            </form>

            {{-- Supported Courier Badges --}}
            <div class="mt-8 pt-6 border-t border-slate-100 flex flex-wrap items-center justify-center gap-4 text-xs font-medium text-slate-400">
                <span class="text-[11px] uppercase tracking-wider font-semibold text-slate-400">Integrated Carriers:</span>
                <span class="inline-flex items-center gap-1 bg-slate-100 px-2.5 py-1 rounded-md text-slate-700 font-semibold"><i class="fas fa-truck-fast text-blue-500"></i> DHL</span>
                <span class="inline-flex items-center gap-1 bg-slate-100 px-2.5 py-1 rounded-md text-slate-700 font-semibold"><i class="fas fa-plane text-purple-500"></i> FedEx</span>
                <span class="inline-flex items-center gap-1 bg-slate-100 px-2.5 py-1 rounded-md text-slate-700 font-semibold"><i class="fas fa-box text-amber-600"></i> UPS</span>
                <span class="inline-flex items-center gap-1 bg-slate-100 px-2.5 py-1 rounded-md text-slate-700 font-semibold"><i class="fas fa-shield text-emerald-600"></i> Royal Mail</span>
                <span class="inline-flex items-center gap-1 bg-slate-100 px-2.5 py-1 rounded-md text-slate-700 font-semibold"><i class="fas fa-paper-plane text-rose-500"></i> DPD</span>
            </div>
        </div>

        {{-- Tracking Results Showcase --}}
        @if (isset($result) && $result)
            @php
                $statusLower = strtolower($result->order_status ?? 'processing');
                $isDelivered = str_contains($statusLower, 'deliver') || str_contains($statusLower, 'complete');
                $isDispatched = str_contains($statusLower, 'dispatch') || str_contains($statusLower, 'transit') || str_contains($statusLower, 'ship') || $isDelivered;
                $isProcessing = true; // Order exists
            @endphp
            <div class="max-w-3xl mx-auto bg-white rounded-3xl border border-slate-200/90 shadow-xl p-6 sm:p-10 mb-12" data-aos="fade-up">
                {{-- Result Header --}}
                <div class="flex flex-wrap items-center justify-between gap-4 pb-6 border-b border-slate-100">
                    <div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Shipment Status</span>
                        <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 mt-0.5">
                            Order #{{ $result->order_id }}
                        </h2>
                    </div>

                    <div>
                        @if ($isDelivered)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fas fa-check-circle"></i> Delivered
                            </span>
                        @elseif ($isDispatched)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                <i class="fas fa-truck-fast"></i> In Transit
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fas fa-clock"></i> {{ $result->order_status ?: 'Processing' }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Visual Milestones Stepper --}}
                <div class="my-8 py-4">
                    <div class="grid grid-cols-3 relative text-center">
                        {{-- Connecting line --}}
                        <div class="absolute top-1/2 left-0 right-0 -translate-y-1/2 h-1 bg-slate-100 z-0"></div>
                        <div class="absolute top-1/2 left-0 -translate-y-1/2 h-1 bg-blue-600 transition-all duration-500 z-0"
                             style="width: {{ $isDelivered ? '100%' : ($isDispatched ? '50%' : '15%') }}"></div>

                        {{-- Step 1 --}}
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm bg-blue-600 text-white ring-4 ring-white shadow-xs">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <span class="text-xs font-bold text-slate-800 mt-2">Order Confirmed</span>
                            <span class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($result->created_at)->format('M d, Y') }}</span>
                        </div>

                        {{-- Step 2 --}}
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm {{ $isDispatched ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600' }} ring-4 ring-white shadow-xs">
                                <i class="fas fa-truck-fast text-xs"></i>
                            </div>
                            <span class="text-xs font-bold {{ $isDispatched ? 'text-slate-800' : 'text-slate-400' }} mt-2">Dispatched</span>
                            <span class="text-[10px] text-slate-400">{{ $result->companyname ?: 'In transit' }}</span>
                        </div>

                        {{-- Step 3 --}}
                        <div class="relative z-10 flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm {{ $isDelivered ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-600' }} ring-4 ring-white shadow-xs">
                                <i class="fas fa-house-chimney text-xs"></i>
                            </div>
                            <span class="text-xs font-bold {{ $isDelivered ? 'text-emerald-700' : 'text-slate-400' }} mt-2">Delivered</span>
                            <span class="text-[10px] text-slate-400">{{ $isDelivered ? 'Complete' : 'Pending' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Shipment Details Table / Cards --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-slate-100">
                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/60">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Assigned Carrier</span>
                        <p class="text-sm font-bold text-slate-900 mt-1 flex items-center gap-1.5">
                            <i class="fas fa-truck text-blue-600 text-xs"></i> {{ $result->companyname ?: 'International Courier' }}
                        </p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50/80 border border-slate-200/60">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Date Placed</span>
                        <p class="text-sm font-bold text-slate-900 mt-1 flex items-center gap-1.5">
                            <i class="fas fa-calendar-alt text-slate-500 text-xs"></i> {{ $result->created_at }}
                        </p>
                    </div>

                    @if ($result->trackingid)
                        <div class="sm:col-span-2 p-4 rounded-2xl bg-blue-50/50 border border-blue-200/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                            <div>
                                <span class="text-[11px] font-bold text-blue-600 uppercase tracking-wider block">Carrier Tracking Number</span>
                                <p class="text-base font-mono font-bold text-slate-900 mt-0.5 select-all">
                                    {{ $result->trackingid }}
                                </p>
                            </div>
                            @if (preg_match('~^https?://~i', (string) $result->trackinglink))
                                <a href="{{ $result->trackinglink }}" target="_blank" rel="noopener nofollow"
                                   class="inline-flex items-center justify-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-xs transition">
                                    Track on {{ $result->companyname ?: 'Carrier' }} Website <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                {{-- Assistance row --}}
                <div class="mt-6 pt-4 text-center">
                    <p class="text-xs text-slate-500">
                        Need assistance or special delivery instructions for this order?
                        <a href="{{ url('contact-details') }}" class="font-bold text-blue-600 hover:underline">Contact Customer Support</a>.
                    </p>
                </div>
            </div>
        @elseif (isset($searched))
            {{-- Error State --}}
            <div class="max-w-2xl mx-auto bg-white rounded-3xl border border-rose-200 p-8 text-center shadow-md mb-12" data-aos="fade-up">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-rose-50 text-rose-500 flex items-center justify-center text-2xl mb-4">
                    <i class="fas fa-triangle-exclamation"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900 mb-1">No Shipment Found</h3>
                <p class="text-sm text-slate-600 max-w-md mx-auto mb-6">
                    We could not find an order matching that reference number and email address. Please verify your order number in your confirmation email.
                </p>
                <div class="flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ url('track-order') }}" class="bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs px-4 py-2.5 rounded-xl transition">
                        Try Again
                    </a>
                    <a href="{{ url('contact-details') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs px-4 py-2.5 rounded-xl transition">
                        Contact Support
                    </a>
                </div>
            </div>
        @endif

        {{-- Tracking FAQ Section --}}
        <div class="max-w-3xl mx-auto mt-16 bg-white rounded-3xl border border-slate-200/90 p-8 sm:p-10 shadow-sm" data-aos="fade-up">
            <h3 class="text-xl font-extrabold text-slate-900 mb-6 flex items-center gap-2">
                <i class="fas fa-circle-question text-blue-600 text-base"></i> Frequently Asked Tracking Questions
            </h3>

            <div class="space-y-6">
                <div>
                    <h4 class="text-sm font-bold text-slate-900">How long does it take for tracking information to appear?</h4>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed">
                        Tracking links usually become active within 12 to 24 hours after your consolidated parcel is handed over to the courier (DHL, FedEx, UPS, or DPD).
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <h4 class="text-sm font-bold text-slate-900">Can I update my destination shipping address?</h4>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed">
                        You can update your shipping address anytime before your parcel is dispatched from our international warehouse. Once shipped, address changes depend on carrier re-routing policies.
                    </p>
                </div>

                <div class="pt-4 border-t border-slate-100">
                    <h4 class="text-sm font-bold text-slate-900">What if my tracking status hasn't updated in several days?</h4>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1 leading-relaxed">
                        International packages undergoing customs clearance at port of entry may pause status updates for 24-48 hours. If there is no update for more than 5 business days, please open a support inquiry.
                    </p>
                </div>
            </div>
        </div>

        {{-- Bottom High-Impact Conversion CTA --}}
        <div class="relative overflow-hidden rounded-3xl bg-slate-900 border border-slate-800 p-8 sm:p-14 text-center mt-16 shadow-2xl">
            <div class="absolute -top-24 -left-24 w-72 h-72 rounded-full bg-blue-500/15 blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-72 h-72 rounded-full bg-orange-500/15 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 text-orange-400 border border-white/15 mb-4">
                    <i class="fas fa-boxes-packing text-xs"></i> Ready for Your Next Order?
                </span>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
                    Forward another package with Delivering Parcel
                </h2>
                <p class="mt-3 text-slate-300 text-sm sm:text-base leading-relaxed">
                    Get instant quotes, consolidate packages, and enjoy discounted international shipping rates across all global destinations.
                </p>
                <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                    <a href="/request"
                       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm px-6 py-3.5 rounded-xl shadow-lg shadow-orange-500/25 hover:shadow-orange-500/35 transition transform hover:-translate-y-0.5">
                        Get a Free Quote <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                    <a href="{{ url('services') }}"
                       class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/15 text-white font-semibold text-sm px-6 py-3.5 rounded-xl border border-white/15 transition">
                        Explore Services
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
