<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use File;
// use Image;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // dd('hello');
        $users = User::find(auth()->user()->id);
        return view("clients.profile", [
            'user' => $users
        ]);
    }

    public function update(Request $request)
    {
        // dd($request);
        $user = User::find(auth()->user()->id);

        // if($request->hasFile('avatar'))
        // {
        //     $usersImage = public_path("uploads/profile/{$user->avatar}"); // get previous image from folder
        //     if (File::exists($usersImage)) { // unlink or remove previous image from folder
        //        unlink($usersImage);
        //     }
        // }

        $filename = 'user.png';
        if ($request->hasfile('avatar')) {
            $filename = $request->file('avatar')->getClientOriginalName();

            //  dd($filename);
            $photo = $request->file('avatar');
            // $request->file('files')->storeAs('uploads',$filename,'public');
            $destinationPath = '../public_html/uploads/profile';
            // Parity port (prod fix): create the target folder (with public/ fallback) before saving.
            if (!is_dir($destinationPath) && !@mkdir($destinationPath, 0775, true) && !is_dir($destinationPath)) {
                $fallback = public_path('uploads/profile');
                if (!is_dir($fallback)) { @mkdir($fallback, 0775, true); }
                $destinationPath = $fallback;
            }
            // intervention/image v3 (Laravel 12 upgrade): ImageManager->read() replaces make();
            // with no image driver installed, store the original file untouched.
            if (class_exists('\Intervention\Image\Drivers\Gd\Driver')) {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
                $manager->read($photo)->fit(200, 200)->save($destinationPath . '/' . $filename);
            } elseif (class_exists('\Intervention\Image\Drivers\Imagick\Driver')) {
                $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Imagick\Driver());
                $manager->read($photo)->fit(200, 200)->save($destinationPath . '/' . $filename);
            } else {
                $photo->move($destinationPath, $filename);
            }
        }
        // dd($filename);
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required',
        ]);
        $user = User::find(auth()->user()->id);
        // dd($request);
        $user->update(array_merge($request->all(), ['avatar' => $filename]));
        return redirect('client_avatar')->with('success', 'Profile updated successfuly');
    }

   
}
