<?php

namespace App\Http\Controllers;

use App\Models\EntryHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Auth;
use DB;

class EntryHoursController extends Controller {

    public function __construct() {
        $this->middleware('user');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        $lang = setGetLang();

        $user_id = Auth::user()->getUserId();
        
        $json_data = [];

        $user_customers_data = DB::table('users_projects')
                ->join('projects', 'users_projects.project_id', '=', 'projects.id')
                ->join('customers', 'projects.customer_id', '=', 'customers.id')->distinct()
                ->where('users_projects.user_id', $user_id)
                ->select('customers.id AS customer_id', 'customers.name AS customer_name')
                ->get();
        
        foreach($user_customers_data as $customer){
            $user_customer_projects = DB::table('users_projects')
                ->join('projects', 'users_projects.project_id', '=', 'projects.id')
                ->join('customers', 'projects.customer_id', '=', 'customers.id')
                ->where('users_projects.user_id', $user_id)
                ->where('customers.id', $customer->customer_id)
                ->where('projects.active', 1)
                ->select('projects.id AS project_id', 'projects.name AS project_name')
                ->get();

            $json_data[] = [
                'customer_id' => $customer->customer_id,
                'customer_name' => $customer->customer_name,
                'customer_projects' => $user_customer_projects,
            ];
        }

        return view('entry_hours_worker.index', compact(['lang', 'json_data']));

        /* $data = DB::table('users_projects')
          ->join('projects', 'users_projects.project_id', '=', 'projects.id')
          ->join('customers', 'projects.customer_id', '=', 'customers.id')
          ->where('users_projects.user_id', $user_id)
          ->select('projects.id AS project_id', 'projects.name AS project_name', 'customers.name AS customer_name')
          ->get();

          if ($request->has('_token')) {

          echo "<script>";
          echo "window.onload = function () {";
          echo "document.getElementById('secondForm').classList.remove('invisible')";
          echo "};";
          echo "</script>";

          $project_id = $request['projects'];

          $customer_name = DB::table('customers')
          ->join('projects', 'customers.id', '=', 'projects.customer_id')
          ->where('projects.id', $project_id)
          ->select('customers.name AS customer_name')
          ->first();

          $bag_hours = DB::table('projects')
          ->join('bag_hours', 'projects.id', '=', 'bag_hours.project_id')
          ->join('type_bag_hours', 'bag_hours.type_id', '=', 'type_bag_hours.id')
          ->where('projects.id', $project_id)
          ->select('type_bag_hours.name AS type_bag_hour_name', 'bag_hours.id AS bag_hour_id', 'projects.name AS project_name')
          ->get();

          return view('entry_hours_worker.index', compact(['lang', 'data', 'bag_hours', 'user_id', 'project_id', 'customer_name']));
          }
          $bag_hours = [];
          $project_id = "";
          $customer_name = "";
          return view('entry_hours_worker.index', compact(['lang', 'data', 'bag_hours', 'user_id', 'project_id', 'customer_name'])); */
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        $user_project_id = DB::table('users_projects')
                ->where('user_id', $request['user_id'])
                ->where('project_id', $request['project_id'])
                ->select('id')
                ->first();

        $bag_hour_id = $request['bag_hour_in_project'];

        $hours = $request['hours_worked'];

        DB::table('hours_entry')->insert([
            'user_project_id' => $user_project_id->id,
            'bag_hours_id' => $bag_hour_id,
            'hours' => $hours,
            'validate' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $lang = setGetLang();

        return view('entry_hours_worker.success', compact('lang'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\EntryHours  $entryHours
     * @return \Illuminate\Http\Response
     */
    public function show(EntryHours $entryHours) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\EntryHours  $entryHours
     * @return \Illuminate\Http\Response
     */
    public function edit(EntryHours $entryHours) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\EntryHours  $entryHours
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, EntryHours $entryHours) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\EntryHours  $entryHours
     * @return \Illuminate\Http\Response
     */
    public function destroy(EntryHours $entryHours) {
        //
    }

}
