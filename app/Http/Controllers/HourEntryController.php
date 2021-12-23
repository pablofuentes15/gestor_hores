<?php

namespace App\Http\Controllers;

use App\Models\HourEntry;
use App\Models\UsersProject;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Requests\EditHourEntryRequest;
use DB;
use App\Http\Requests\CreateHourEntryRequest;
use Illuminate\Support\Facades\App;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HourEntryExport;

class HourEntryController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {

        $values_before_edit_json = null;
        
        $show_filters = false;
        $show_create_edit = false;

        if ($request->has('_token') && $request->has('entry_hour_id')) {
            $show_create_edit = true;

            $hour_entry = HourEntry::find($request['entry_hour_id']);

            $day = $hour_entry->day;
            $hours = $hour_entry->hours;
            $hours_imputed = $hour_entry->hours_imputed;
            $description = $hour_entry->description;
            $isValidated = $hour_entry->validate;

            $user_project_id = $hour_entry->user_project_id;

            $user_id = UsersProject::find($user_project_id)->user_id;

            $project_id = UsersProject::find($user_project_id)->project_id;

            $customer_id = Project::find($project_id)->customer_id;
            
            $bag_hour = false;
            
            if (DB::table('bag_hours')->where('project_id', $project_id)->exists()) {
                $bag_hour = true;
            }

            $values_before_edit_json = [
                'hour_entry_id' => $request['entry_hour_id'],
                'day' => \Carbon\Carbon::parse($day)->format('d/m/Y'),
                'hours' => $hours,
                'hours_imputed' => $hours_imputed,
                'description' => $description,
                'user_project_id' => $user_project_id,
                'user_id' => $user_id,
                'project_id' => $project_id,
                'customer_id' => $customer_id,
                'bag_hour' => $bag_hour,
                'isValidated' => $isValidated
            ];
        }


        if ($request->has('_token') && $request->has('select_filter_name')) {
            $show_filters = true;
            session(['hour_entry_user' => $request['select_filter_name']]);
            session(['hour_entry_customer' => $request['select_filter_customers']]);
            session(['hour_entry_project' => $request['select_filter_projects']]);
        }

        
        $dates = getIntervalDates($request, 'hour_entry');
        $date_from = $dates[0];
        $date_to = $dates[1];

        $user_id = session('hour_entry_user', "%");
        $customer_id = session('hour_entry_customer', "%");
        $project_id = session('hour_entry_project', "%");
        $orderby = session('hour_entry_orderby', "hours_entry.created_at");
        $order = session('hour_entry_order', "desc");   
        $pagination = session('hour_entry_num_records', 10);

        if ($user_id == '') {
            $user_id = "%";
        }
        if ($customer_id == '') {
            $customer_id = "%";
        }
        if ($project_id == '') {
            $project_id = "%";
        }

        if ($pagination == 'all') {
            $pagination = DB::table('hours_entry')->count();
        }

        $old_data = [];

        $old_days = old('days');

        if ($old_days != null) {
            $old_data = [
                'old_days' => $old_days,
                'old_hours' => old('hours'),
                'old_users' => old('users'),
                'old_customers' => old('customers'),
                'old_projects' => old('projects'),
                'old_inputed_hours' => old('inputed_hours'),
                'old_desc' => old('desc'),
            ];
        }

        $lang = setGetLang();

        //Vigilar variables $orderby
        $data = HourEntryController::getBDInfo($user_id, $customer_id ,$project_id)
                ->whereBetween('hours_entry.created_at', [$date_from, $date_to])
                ->orderBy($orderby, $order)
                ->paginate($pagination);

        $join = DB::table('hours_entry')->leftJoin('bag_hours', 'hours_entry.bag_hours_id', '=', 'bag_hours.id')->leftJoin('type_bag_hours', 'bag_hours.type_id', '=', 'type_bag_hours.id')->select('type_bag_hours.name')->get();

        //Create json with the info of DB, need for selects user, project and bag of hours. This work with JavaScript
        $users_info = [];
        $users_data = DB::table('users_projects')->distinct()->select('user_id')->get();

        /*
          //Create json to storage users with projects, there customers and there projects
          $json = [];
          $users_data = DB::table('users_projects')
          ->join('users', 'users_projects.user_id', '=', 'users.id')->distinct()
          ->select(['users.id', 'users.nickname', 'users.name', 'users.surname', 'users.email', 'users.phone', 'users.role'])->get();

          foreach ($users_data as $user) {

          //Get the customers of the users (users with projects)
          $customers_in_user_data = DB::table('users_projects')
          ->join('projects', 'users_projects.id', '=', 'projects.id')
          ->join('customers', 'projects.customer_id', '=', 'customers.id')->distinct()
          ->where('users_projects.user_id', $user->id)
          ->select('customers.id AS customer_id', 'customers.name AS customer_name')
          ->get();

          $json[] = [
          'user_id' => $user->id,
          'user_nickname' => $user->nickname,
          'user_name' => $user->name,
          'user_surname' => $user->surname,
          'user_email' => $user->email,
          'user_phone' => $user->phone,
          'user_role' => $user->role,
          'customers' => $customers_in_user_data,
          ];
          }

          //        $results=DB::select(DB::raw("
          //            select * from users_projects
          //                INNER JOIN projects
          //            ON `users_projects`.`project_id` = `projects`.`id`
          //                WHERE `users_projects`.`user_id`=3"));

          $customers_in_user_data = DB::table('users')
          ->join('users_projects', 'users.id', '=', 'users_projects.user_id')
          ->join('projects', 'users_projects.project_id', '=', 'projects.id')
          ->join('customers', 'projects.customer_id', '=', 'customers.id')->distinct()
          ->select('customers.name')
          ->where('users.id', '=', 2)
          ->get();

          return $customers_in_user_data; */



        foreach ($users_data as $user) {

            $user_id = $user->user_id;
            $projects_users_data = DB::table('users_projects')
                    ->join('projects', 'users_projects.project_id', '=', 'projects.id')
                    ->join('customers', 'projects.customer_id', '=', 'customers.id')
                    ->where('users_projects.user_id', $user_id)
                    ->where('projects.active', 1)
                    ->select('projects.id AS project_id', 'projects.name AS project_name', 'customers.id AS customer_id', 'customers.name AS customer_name')
                    ->get();

            $users_projects = [];
            foreach ($projects_users_data as $project_in_user) {

                $bag_hour;

                if (DB::table('bag_hours')->where('project_id', $project_in_user->project_id)->exists()) {
                    $bag_hour = true;
                } else {
                    $bag_hour = false;
                }

                $users_projects[] = [
                    'project_id' => $project_in_user->project_id,
                    'project_name' => $project_in_user->project_name,
                    'customer_id' => $project_in_user->customer_id,
                    'customer_name' => $project_in_user->customer_name,
                    'bag_hour' => $bag_hour,
                ];
            }

            $users_info[] = [
                'user_id' => $user->user_id,
                'user_projects' => $users_projects
            ];
        }


        //Create the JSON of the relation of users and customers
        $users_customers = [];
        $users_with_projects = DB::table('users')
                        ->join('users_projects', 'users.id', '=', 'users_projects.user_id')
                        ->distinct()->get(['users.id', 'users.nickname', 'users.name', 'users.surname', 'users.email', 'users.phone', 'users.role']);

        foreach ($users_with_projects as $user) {

//            $users_customers_data = DB::table('users_projects')
//                    ->join('projects', 'users_projects.id', '=', 'projects.id')
//                    ->join('customers', 'projects.customer_id', '=', 'customers.id')->distinct()
//                    ->where('users_projects.user_id', $user->id)
//                    ->select('customers.id AS customer_id', 'customers.name AS customer_name')
//                    ->get();

            $users_customers_data = DB::table('users')
                    ->join('users_projects', 'users.id', '=', 'users_projects.user_id')
                    ->rightJoin('projects', 'users_projects.project_id', '=', 'projects.id')
                    ->join('customers', 'projects.customer_id', '=', 'customers.id')->distinct()
                    ->select('customers.id AS customer_id', 'customers.name AS customer_name')
                    ->where('users.id', '=', $user->id)
                    ->get();

            $users_customers[] = [
                'user_id' => $user->id,
                'user_nickname' => $user->nickname,
                'user_name' => $user->name,
                'user_surname' => $user->surname,
                'user_email' => $user->email,
                'user_phone' => $user->phone,
                'user_role' => $user->role,
                'customers' => $users_customers_data,
            ];
        }

        return view('entry_hours.index', compact(['lang', 'data', 'users_data', 'users_info', 'users_customers', 'old_data', 'users_with_projects', 'values_before_edit_json', 'show_filters', 'show_create_edit']))
                        ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function getBDInfo($user_id, $customers_id, $project_id) {
        $data = UsersProject::join('users', 'users_projects.user_id', '=', 'users.id')
                ->join('projects', 'users_projects.project_id', '=', 'projects.id')
                ->join('customers', 'projects.customer_id', '=', 'customers.id')
                ->join('hours_entry', 'users_projects.id', '=', 'hours_entry.user_project_id')
                ->leftJoin('bag_hours', 'hours_entry.bag_hours_id', '=', 'bag_hours.id')
                ->leftJoin('type_bag_hours', 'bag_hours.type_id', '=', 'type_bag_hours.id')
                ->select('users.id AS user_id', 'users.nickname AS user_nickname', 'users.name AS user_name', 'users.surname AS user_surname', 'projects.id AS project_id', 'projects.name AS project_name', 'customers.id AS customer_id', 'customers.name AS customer_name',
                        'type_bag_hours.name AS type_bag_hour_name', 'hours_entry.bag_hours_id AS hours_entry_bag_hours_id', 'hours_entry.hours AS hour_entry_hours', 'hours_entry.hours_imputed AS hour_entry_hours_imputed', 'hours_entry.validate AS hour_entry_validate',
                        'hours_entry.created_at AS hour_entry_created_at', 'bag_hours.id AS bag_hour_id', 'hours_entry.id AS hours_entry_id',
                        'hours_entry.day AS hours_entry_day')
                ->where('users.id', 'like', $user_id)
                ->where('customers.id', 'like', $customers_id)
                ->where('projects.id', 'like', $project_id);

        return $data;
    }

    //https://hores.atotarreu.com/control-panel/time-entries/filteruser/20/lang/ca
    public function filterUser($user_id,$lang) {

        session(['hour_entry_user' => $user_id]);
        session(['hour_entry_project' => "%"]);
        session(['hour_entry_customer' => "%"]);
        session(['hour_entry_date_from' => ""]);
        session(['hour_entry_date_to' => ""]);

        return redirect()->route($lang . '_time_entries.index');
    }

    //https://hores.atotarreu.com/control-panel/time-entries/filterproject/11/lang/ca
    public function filterProject($project_id,$lang) {

        session(['hour_entry_user' => "%"]);
        session(['hour_entry_project' => $project_id]);
        session(['hour_entry_customer' => "%"]);
        session(['hour_entry_date_from' => ""]);
        session(['hour_entry_date_to' => ""]);

        return redirect()->route($lang . '_time_entries.index');
    }

    //https://hores.atotarreu.com/control-panel/time-entries/filtercustomer/9/lang/ca
    public function filterCustomer($customer_id,$lang) {

        session(['hour_entry_user' => "%"]);
        session(['hour_entry_project' => "%"]);
        session(['hour_entry_customer' => $customer_id]);
        session(['hour_entry_date_from' => ""]);
        session(['hour_entry_date_to' => ""]);

        return redirect()->route($lang . '_time_entries.index');
    }

    public function orderBy($camp, $lang) {

        if(session('hour_entry_orderby')!=$camp){
            session(['hour_entry_orderby' => $camp]);
            session(['hour_entry_order' => "desc"]);
        }
        else{
            session(['hour_entry_order' => "asc"]);
        }        

        return redirect()->route($lang . '_time_entries.index');
    }

    public function deleteFilters($lang) {

        session(['hour_entry_user' => "%"]);
        session(['hour_entry_project' => "%"]);
        session(['hour_entry_date_from' => ""]);
        session(['hour_entry_date_to' => ""]);
        session(['hour_entry_orderby' => "hours_entry.created_at"]);
        session(['hour_entry_order' => "desc"]);

        return redirect()->route($lang . '_time_entries.index');
    }

    public function validateEntryHour($hours_entry_id, $lang) {
        App::setLocale($lang);
        DB::statement("UPDATE hours_entry SET validate = 1 where id = " . $hours_entry_id);

//        DB::table('hours_entry')
//                ->where('hours_entry.id', $hour_entry_id)
//                ->update(['validate' => 1]);

        return redirect()->route($lang . '_time_entries.index')
                        ->with('success', __('message.time_entry') . " " . __('message.validated'));
    }

    public function inValidateEntryHour($hours_entry_id, $lang) {
        App::setLocale($lang);
        DB::statement("UPDATE hours_entry SET validate = 0 where id = " . $hours_entry_id);

//        DB::table('hours_entry')
//                ->where('hours_entry.id', $id)
//                ->update(['validate' => 0]);

        return redirect()->route($lang . '_time_entries.index')
                        ->with('success', __('message.time_entry') . " " . __('message.invalidated'));
    }

    public function validateAllHours($lang) {

        $entries_to_validate = HourEntryController::getBDInfo()->validated()->update(['validate' => 1]);

        return redirect()->route($lang . '_time_entries.index')
                        ->with('success', __('message.time_entries') . " " . __('message.validated'));
    }

    public function changeNumRecords(Request $request, $lang) {

        session(['hour_entry_num_records' => $request['num_records']]);

        return redirect()->route($lang . '_time_entries.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $lang = setGetLang();

        //Create json with the info of DB, need for selects user, project and bag of hours. This work with JavaScript
        $users_info = [];
        $users_data = DB::table('users_projects')->distinct()->select('user_id')->get();

        foreach ($users_data as $user) {

            $user_id = $user->user_id;
            $projects_users_data = DB::table('users_projects')
                    ->join('projects', 'users_projects.project_id', '=', 'projects.id')
                    ->join('customers', 'projects.customer_id', '=', 'customers.id')
                    ->where('users_projects.user_id', $user_id)
                    ->where('projects.active', 1)
                    ->select('projects.id AS project_id', 'projects.name AS project_name', 'customers.id AS customer_id', 'customers.name AS customer_name')
                    ->get();

            $users_projects = [];
            foreach ($projects_users_data as $project_in_user) {

                $bag_hour;

                if (DB::table('bag_hours')->where('project_id', $project_in_user->project_id)->exists()) {
                    $bag_hour = true;
                } else {
                    $bag_hour = false;
                }

                $users_projects[] = [
                    'project_id' => $project_in_user->project_id,
                    'project_name' => $project_in_user->project_name,
                    'customer_id' => $project_in_user->customer_id,
                    'customer_name' => $project_in_user->customer_name,
                    'bag_hour' => $bag_hour,
                ];
            }

            $users_info[] = [
                'user_id' => $user->user_id,
                'user_projects' => $users_projects
            ];
        }


        //Create the JSON of the relation of users and customers
        $users_customers = [];
        $users_with_projects = DB::table('users')
                        ->join('users_projects', 'users.id', '=', 'users_projects.user_id')
                        ->distinct()->get(['users.id', 'users.nickname', 'users.name', 'users.surname', 'users.email', 'users.phone', 'users.role']);

        foreach ($users_with_projects as $user) {

            $users_customers_data = DB::table('users_projects')
                    ->join('projects', 'users_projects.id', '=', 'projects.id')
                    ->join('customers', 'projects.customer_id', '=', 'customers.id')->distinct()
                    ->where('users_projects.user_id', $user->id)
                    ->select('customers.id AS customer_id', 'customers.name AS customer_name')
                    ->get();

            $users_customers[] = [
                'user_id' => $user->id,
                'user_nickname' => $user->nickname,
                'user_name' => $user->name,
                'user_surname' => $user->surname,
                'user_email' => $user->email,
                'user_phone' => $user->phone,
                'user_role' => $user->role,
                'customers' => $users_customers_data,
            ];
        }
        return view('entry_hours.create', compact(['lang', 'users_data', 'users_info', 'users_customers']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateHourEntryRequest $request, $lang) {

        $count_hours_entries = count($request->days);

        session(['count_hours_entries' => $count_hours_entries]);

        if (!$request->validated()) {

            return back()->withInput();
        } else {

            App::setLocale($lang);

            $inputed_hours_index = 0;

            for ($i = 0; $i < $count_hours_entries; $i++) {
                $day = Carbon::createFromFormat('d/m/Y', $request->days[$i])->format('Y-m-d');
                $hours = $request->hours[$i];
                $user = $request->users[$i];
                $customer = $request->customers[$i];
                $project = $request->projects[$i];
                $description = $request->desc[$i];

                $user_project_id = DB::table('users_projects')
                        ->where('user_id', $user)
                        ->where('project_id', $project)
                        ->select('id')
                        ->get();

                $bag_hour_id;
                $inputed_hours;

                if (DB::table('bag_hours')->where('project_id', $project)->select('id')->exists()) {
                    $bag_hour_id = DB::table('bag_hours')->where('project_id', $project)->select('id')->get()[0]->id;
                    $inputed_hours = $request->inputed_hours[$inputed_hours_index];
                    $inputed_hours_index++;
                } else {
                    $bag_hour_id = NULL;
                    $inputed_hours = $request->hours[$i];
                }

                DB::table('hours_entry')->insert([
                    'user_project_id' => $user_project_id[0]->id,
                    'bag_hours_id' => $bag_hour_id,
                    'day' => $day,
                    'hours' => $hours,
                    'hours_imputed' => $inputed_hours,
                    'description' => $description,
                    'validate' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            if(HourEntryController::endhours($project)){
                //Mail
            }

            return redirect()->route($lang . '_time_entries.index')
                            ->with('success', __('message.time_entry') . " " . __('message.created'));
        }
    }

    function cancelEdit($lang) {
        return redirect()->route($lang . '_time_entries.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\HourEntry  $hourEntry
     * @return \Illuminate\Http\Response
     */
    public function show(HourEntry $hourEntry) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\HourEntry  $hourEntry
     * @return \Illuminate\Http\Response
     */
    public function edit(HourEntry $hourEntry) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\HourEntry  $hourEntry
     * @return \Illuminate\Http\Response
     */
    public function update(EditHourEntryRequest $request, HourEntry $hourEntry, $lang) {

        if ($request->validated()) {

            $user_project_id = DB::table('users_projects')
                    ->where('user_id', $request->users[0])
                    ->where('project_id', $request->projects[0])
                    ->select('id')
                    ->first();
            
            $inputed_hours = $request->hours[0];
            
            if(isset($request->inputed_hours[0])){
                $inputed_hours = $request->inputed_hours[0];
            }

            DB::table('hours_entry')
                    ->where('id', $hourEntry->id)
                    ->update([
                        'user_project_id' => $user_project_id->id,
                        'day' => Carbon::createFromFormat('d/m/Y', $request->days[0])->format('Y-m-d'),
                        'hours' => $request->hours[0],
                        'description' => $request->desc[0],
                        'hours_imputed' => $inputed_hours,
                        'validate' => ($request->validate == "on") ? 1 : 0
                    ]);


            return redirect()->route($lang . '_time_entries.index')
                            ->with('success', __('message.time_entry') . " " . $request->name . " " . __('message.updated'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\HourEntry  $hourEntry
     * @return \Illuminate\Http\Response
     */
    public function destroy(HourEntry $hourEntry, $lang) {

        App::setLocale($lang);

        $hourEntry->delete();

        return redirect()->route($lang . '_time_entries.index')
                        ->with('success', __('message.hour_entry') . " " . __('message.deleted'));
    }

    public function endhours($project_id){
        $users_projects_ids = DB::table('users_projects')
            ->select('users_projects.id')
            ->where('users_projects.project_id', '=', $project_id)
            ->get();
            
        $users_projects_ids_array = [];
            
        foreach ($users_projects_ids as $user_project) {
            array_push($users_projects_ids_array, $user_project->id);
        }

        $hours_imputed_project = DB::table('hours_entry')
            ->select('hours_imputed')
            ->whereIn('user_project_id', $users_projects_ids_array)
            ->sum('hours_imputed');

        $project=DB::table('bag_hours')
            ->select('contracted_hours')
            ->where('project_id', $project_id)
            ->get();
        $hours_end=$project[0]->contracted_hours-$hours_imputed_project;
        var_dump($hours_end);
        if($hours_end<=0){
            return true;
        }
        return false;
    }

    public function export() 
    {
        $user_id = session('hour_entry_user', "%");
        $customer_id = session('hour_entry_customer', "%");
        $project_id = session('hour_entry_project', "%");
        if ($user_id == '') {
            $user_id = "%";
        }
        if ($customer_id == '') {
            $customer_id = "%";
        }
        if ($project_id == '') {
            $project_id = "%";
        }
    return Excel::download(new HourEntryExport($user_id,$customer_id,$project_id), 'HourEntry.xlsx');
    }

}
