<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\EditUserRequest;
use Illuminate\Support\Facades\App;

class UserController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {

        $lang = setGetLang();

        $user_to_edit = null;
        
        $show_create_edit = false;
        $show_filters = false;

        if ($request->has('_token') && $request->has('user_id')) {
            
            $user_to_edit = User::where('id', $request['user_id'])->first();

            $show_create_edit = true;
        }

        if ($request->has('_token') && $request->has('username')) {
            ($request['username'] == "") ? session(['user_username' => '%']) : session(['user_username' => $request['username']]);
            ($request['name'] == "") ? session(['user_name' => '%']) : session(['user_name' => $request['name']]);
            ($request['surname'] == "") ? session(['user_surname' => '%']) : session(['user_surname' => $request['surname']]);
            ($request['email'] == "") ? session(['user_email' => '%']) : session(['user_email' => $request['email']]);
            ($request['phone'] == "") ? session(['user_phone' => '%']) : session(['user_phone' => $request['phone']]);

            session(['user_role' => $request['role']]);
            session(['user_num_records' => $request['num_records']]);
            
            $show_filters = true;
        }

        $dates = getIntervalDates($request, 'user');
        $date_from = $dates[0];
        $date_to = $dates[1];

        $username = session('user_username', "%");
        $name = session('user_name', "%");
        $surname = session('user_surname', "%");
        $email = session('user_email', "%");
        $phone = session('user_phone', "%");
        $role = session('user_role', "%");
        $orderby = session('user_orderby', "created_at");
        $order = session('user_order', "desc");
        $num_records = session('users_num_records', 10);

        if ($role == "all") {
            $role = "%";
        }

        if ($num_records == 'all') {
            $num_records = User::count();
        }
        
        //Solucio per que no mostri el usuaris amb phone null amb recerca que no se especifiqui el phone
        $query = User::where('nickname', 'like', "%" . $username . "%")
                ->where('name', 'like', "%" . $name . "%")
                ->where('surname', 'like', "%" . $surname . "%")
                ->where('email', 'like', "%" . $email . "%")
                ->where('role', 'like', "%" . $role . "%")
                ->whereBetween('created_at', [$date_from, $date_to])
                ->orderBy($orderby, $order);

        if ($phone == "%"){
			$query->where(function ($sub) use ($phone){
               $sub->where('phone', 'like', "%" . $phone . "%")
                     ->orWhere('phone', NULL);
           });
        }
		else{
			$query->where('phone', 'like', "%" . $phone . "%");
        }

        /*$query = User::where('nickname', 'like', "%" . $username . "%")
                ->where('name', 'like', "%" . $name . "%")
                ->where('surname', 'like', "%" . $surname . "%")
                ->where('email', 'like', "%" . $email . "%")
                ->where('phone', 'like', "%" . $phone . "%")
                ->where('role', 'like', "%" . $role . "%")
                ->whereBetween('created_at', [$date_from, $date_to])
                ->orderBy('created_at', $order);

        if ($phone == "%")
            $query->orWhere('phone', NULL);
        */
        $data = $query->paginate($num_records);
        
        return view('users.index', compact(['lang', 'data', 'user_to_edit', 'show_filters', 'show_create_edit']))
                        ->with('i', (request()->input('page', 1) - 1) * $num_records);
    }

    public function deleteFilters($lang) {

        session(['user_username' => '%']);
        session(['user_name' => '%']);
        session(['user_surname' => '%']);
        session(['user_email' => '%']);
        session(['user_phone' => '%']);
        session(['user_role' => '%']);
        session(['user_date_from' => ""]);
        session(['user_date_to' => ""]);
        session(['user_orderby' => "created_at"]);
        session(['user_order' => "desc"]);

        return redirect()->route($lang . '_users.index');
    }

    public function changeNumRecords(Request $request, $lang) {

        session(['users_num_records' => $request['num_records']]);

        return redirect()->route($lang . '_users.index');
    }
    
    public function orderBy($camp, $lang) {

        if(session('user_orderby')!=$camp || session('user_order')=="desc"){
            session(['user_orderby' => $camp]);
            session(['user_order' => "asc"]);
        }
        else{
            session(['user_order' => "desc"]);
        }       

        return redirect()->route($lang . '_users.index');
    }
    
    function cancelEdit($lang) {
        return redirect()->route($lang . '_users.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user) {
        $lang = setGetLang();

        return view('users.edit', compact('user'), compact('lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(EditUserRequest $request, User $user, $lang) {

        //$input = $request->except(['password']);

        if ($request->password == "") {
            $validated = $request->validated();
            $validated["phone"] = str_replace(' ','',$validated["phone"]);
        	$validated["phone"] = str_replace('-','',$validated["phone"]);
            $user->update(collect($validated)->except(['password'])->toArray());
        } else {
            $request->phone = str_replace(' ','',$request->phone);
        	$request->phone = str_replace('-','',$request->phone);
            $user->update($request->validated());
        }
        
        return redirect()->route($lang . '_users.index')
                        ->with('success', __('message.user') . " " . $request->nickname . " " . __('message.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user, $lang) {
        App::setLocale($lang);

        $user->delete();

        return redirect()->route($lang . '_users.index')
                        ->with('success', __('message.user') . " " . __('message.deleted'));
    }

}
