<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function storeAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|max:10000'
        ]);
        $user = auth()->user();

        $filename = $user->id . '-' . uniqid() . '.jpg';
        $imgData = Image::make($request->file('avatar'))->fit(120)->encode('jpg');
        Storage::put('public/avatars/' . $filename, $imgData);


        $oldAvatar = $user->avatar;

        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != "/fallback-avatar.jpg") {
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));

        }

        return back()->with('success', 'Your avatar as been successfully uploaded.');
    }
    public function showAvatarForm()
    {
        return view('avatar-form');
    }

    private function getSharedData($user)
    {
        $currentlyFollowing = 0;
        if (auth()->check()) {
            $currentlyFollowing = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->count();

        }

        View::share('sharedData', ['currentlyFollowing' => $currentlyFollowing, 'avatar' => $user->avatar, 'username' => $user->username, 'postCount' => $user->posts()->count(), 'followerCount' => $user->followers()->count(), 'followingCount' => $user->following()->count()]);


    }
    public function profile(User $user)
    {
        $this->getSharedData($user);
        return view('profile-posts', ['posts' => $user->posts()->latest()->get()]);
    }

    public function profileRaw(User $user)
    {
        return response()->json(['theHTML' => view('profile-posts-only', ["posts" => $user->posts()->latest()->get()])->render(), 'docTitle' => $user->username . "'s profile"]);
    }


    public function profileFollowers(User $user)
    {
        $this->getSharedData($user);
        return view('profile-followers', ['followers' => $user->followers()->latest()->get()]);
    }
    public function profileFollowersRaw(User $user)
    {
        return response()->json(['theHTML' => view('profile-followers-only', ["followers" => $user->followers()->latest()->get()])->render(), 'docTitle' => $user->username . "'s followers"]);

    }


    public function profileFollowing(User $user)
    {
        $this->getSharedData($user);
        return view('profile-following', ['following' => $user->Following()->latest()->get()]);
    }

    public function profileFollowingRaw(User $user)
    {
        return response()->json(['theHTML' => view('profile-following-only', ["following" => $user->following()->latest()->get()])->render(), 'docTitle' => 'Who ' . $user->username . " follows"]);

    }



    public function logout()
    {
        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out, see you later!');
    }
    public function showCorrectHomepage()
    {
        if (auth()->check()) {
            return view('homepage-feed', ['posts' => auth()->user()->feedPosts()->latest()->paginate(6)]);
        } else {
            $postCount = Cache::remember('postCount', 20, function () {
                return Post::count();
            });
            return view('homepage', ['postCount' => $postCount]);
        }
    }

    public function loginApi(Request $request)
    {

        $incomingFields = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (auth()->attempt($incomingFields)) {
            $user = User::where('username', $incomingFields['username'])->first();
            $token = $user->createToken('ourapptoken')->plainTextToken;
            return $token;
        }
        return '';

    }
    public function login(Request $request)
    {
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'
        ]);

        if (auth()->attempt(['username' => $incomingFields['loginusername'], 'password' => $incomingFields['loginpassword']])) {
            $request->session()->regenerate();
            return redirect('/')->with('success', 'You have successfully logged in Parrot.');
        } else {
            return redirect('/')->with('failure', 'Invalid login.');
        }
    }
    public function register(Request $request)
    {
        $incomingFields = $request->validate([
            'username' => ['required', 'min:3', 'max: 15', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed']

        ]);

        $incomingFields['password'] = bcrypt($incomingFields['password']);

        $user = User::create($incomingFields);
        auth()->login($user);


        return redirect('/')->with('success', 'Thank you for joining ParrotGram!');
    }
}