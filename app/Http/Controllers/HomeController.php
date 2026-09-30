<?php

namespace App\Http\Controllers;

use App\Models\CmsPost;
use App\Models\Country;
use App\Models\HeroSlide;
use App\Models\Orders;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Show the application homepage.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        // Homepage shows more than the old hard 6-item cap now that the
        // slider (dpTestimonialSlider, resources/js/app-public.js) scrolls
        // through as many cards as exist rather than a fixed short row —
        // capped at 12 as a sane ceiling; the full paginated list with
        // source/country filters still lives at /testimonials.
        $homeTestimonials = Testimonial::visible()
            ->orderByDesc('sort')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $slides = HeroSlide::where('is_active', true)->orderBy('position')->orderBy('id')->get();
        // CmsPost (post_type=service) is the live /services/{slug} catalogue — see
        // CmsController::servicesIndex/serviceShow. The old App\Models\Service table
        // is a separate, orphaned demo dataset that no route resolves against any more.
        $services = CmsPost::ofType('service')->published()->topLevel()
            ->orderBy('menu_order')->take(6)->get();
        // CmsPost (post_type=blog_post) is the live /blog/{slug} catalogue — see
        // CmsController::blogIndex/blogShow. App\Models\Blog is the legacy table:
        // only 1 published row remains there vs 93 in CmsPost, so this section was
        // silently starving itself down to a single stale post.
        $posts = CmsPost::ofType('blog_post')->published()
            ->orderByDesc('published_at')->take(4)->get();

        // Hero "recent orders" ticker — real completed shipments only, never
        // synthetic sample rows (this project's standing rule against
        // fabricated homepage content applies here same as testimonials).
        // orders.shipfrom/shipto store full country names, not ISO codes, so
        // they're resolved against the countries table for a flag-icon-css
        // class; a name that doesn't resolve (or a $0 total) is dropped
        // rather than shown with a missing flag or an empty price.
        $countryIso = Country::pluck('iso2', 'name');
        $recentOrders = Orders::query()
            ->whereIn('order_status', ['completed', 'received'])
            ->orderByDesc('updated_at')
            ->limit(20)
            ->get(['shipfrom', 'shipto', 'product_totalprice'])
            ->map(function ($order) use ($countryIso) {
                return [
                    'from'      => $order->shipfrom,
                    'from_code' => strtolower($countryIso[$order->shipfrom] ?? ''),
                    'to'        => $order->shipto,
                    'to_code'   => strtolower($countryIso[$order->shipto] ?? ''),
                    'price'     => (float) $order->product_totalprice,
                ];
            })
            ->filter(fn ($o) => $o['from_code'] !== '' && $o['to_code'] !== '' && $o['price'] > 0)
            ->values();

        return view('home', compact('homeTestimonials', 'slides', 'services', 'posts', 'recentOrders'));
    }
    public function password(){
        $user = User::find(auth()->user()->id);
        return view("clients.profile",[
            'user'=>$user
        ]);
    }
    public function changePassword(Request $request){
        // dd($request->all());
        $this->validate($request, [
            'old_password'     => 'required',
            'new_password'     => 'required|min:6',
            'confirm_password' => 'required|same:new_password',
        ]);
        $data = $request->all();
        $user = User::find(auth()->user()->id);
        // dd($user);
        if(!Hash::check($data['old_password'], $user->password)){
            return back()->with('error','You have Entered Wrong Password');
        }
        else{
            $user->password = Hash::make($request->new_password);
            if ($user->save()) {
                return Redirect("password")->with('success', 'Password is Updated Successfully!');
            } 
        }
    } 

    
}
