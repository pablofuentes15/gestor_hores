<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\CreateUserRequest;
use App;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $lang = setGetLang();
        
        return view('auth.register', compact('lang'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(CreateUserRequest $request,$lang)
    {
        /*App::setLocale($lang);
        
        $request->validate([
            'nickname' => 'unique:users',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'unique:users',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = User::create([
            'nickname' => $request->nickname,
            'name' => $request->name,
            'surname' => $request->surname,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        event(new Registered($user));

        //Auth::login($user);

        return redirect(route($lang.'_users.index'))
                ->with('success', __('message.user')." ".$request->nickname." ".__('message.created') ); */
        
        App::setLocale($lang);
        
        $validated = $request->validated();
        $validated["phone"] = str_replace(' ','',$validated["phone"]);
        $validated["phone"] = str_replace('-','',$validated["phone"]);
		
        $user = User::create($validated);

        event(new Registered($user));

        //Auth::login($user);

        return redirect(route($lang.'_users.index'))
                ->with('success', __('message.user')." ".$request->nickname." ".__('message.created') );
    }
}
