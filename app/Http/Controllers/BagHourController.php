<?php

namespace App\Http\Controllers;

use App\Models\BagHour;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests\CreateBagHourRequest;
use App\Http\Requests\EditBagHourRequest;
use Illuminate\Support\Facades\App;
use App\Exports\BagHourExport;
use Maatwebsite\Excel\Facades\Excel;


class BagHourController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $lang = setGetLang();
        
        $bag_hour_to_edit = null;
        
        $show_filters = false;
        $show_create_edit = false;
        
        if ($request->has('_token') && $request->has('edit_bag_hour_id')) {
            $bag_hour_to_edit = BagHour::where('id', $request['edit_bag_hour_id'])->first();
            $show_create_edit = true;
            
        }
        
        $old = null;
        if ($request->has('_token') && $request->has('type')) {
            $show_filters = true;
            
            session(['bag_hour_type_id' => $request['type']]);

            session(['bag_hour_project_id' => $request['project']]);

            session(['bag_hour_customer_id' => $request['customer']]);
            
            $old = $request;
        }
        
        $dates = getIntervalDates($request, 'bag_hour');
        
        $type = session('bag_hour_type_id', "%");
        $project = session('bag_hour_project_id', "%");
        $customer = session('bag_hour_customer_id', "%");
        
        
//        $hour_price = session('bag_hour_hour_price', "%");
//        
//        if($hour_price != "%"){
//            $hour_price = number_format($hour_price, 2, '.', '');
//        }
//        
//        $total_price = session('bag_hour_total_price', "%");
//        
//        if($total_price != "%"){
//            $total_price = number_format($total_price, 2, '.', '');
//        }
        
        $orderby = session('bag_hour_orderby', "created_at");
        $order = session('bag_hour_order', "desc");
        
        
        $num_records = session('bag_hour_num_records', 10);
        
        if($num_records == 'all'){
            $num_records = BagHour::count();
        }
        //Fer el camp left_hores
        $data = BagHour::join('projects', 'bag_hours.project_id', '=', 'projects.id')
            ->join('type_bag_hours', 'bag_hours.type_id', '=', 'type_bag_hours.id')
            ->join('customers', 'projects.customer_id', '=', 'customers.id')
            ->select('bag_hours.*', 'bag_hours.id as bag_hour_id', 'projects.id AS project_id', 'projects.name AS project_name', 'type_bag_hours.id AS type_id', 'type_bag_hours.name AS type_name', 'type_bag_hours.hour_price AS type_hour_price', 'customers.id as customer_id', 'customers.name as customer_name')
            ->where('type_bag_hours.id', 'like', $type)
            ->where('projects.id', 'like', $project)
            ->where('customers.id', 'like', $customer)
            ->whereBetween('bag_hours.created_at', $dates)
            ->orderBy($orderby, $order)
            ->paginate($num_records);
        
        foreach ($data as $bag_hour) {
            
            $users_projects_ids = DB::table('users_projects')
                    ->select('users_projects.id')
                    ->where('users_projects.project_id', '=', $bag_hour->project_id)
                    ->get();
            
            $users_projects_ids_array = [];
            
            foreach ($users_projects_ids as $user_project) {
                array_push($users_projects_ids_array, $user_project->id);
            }

            $hours_imputed_project = DB::table('hours_entry')
                    ->select('hours_imputed')
                    ->whereIn('user_project_id', $users_projects_ids_array)
                    ->sum('hours_imputed');
            
            $bag_hour->total_hours_project = $hours_imputed_project;
        }
        
        //For create / edit & filters
        $bags_hours_types = DB::table('type_bag_hours')->select("id", "name", "hour_price")->get();
        $projects = DB::table('projects')->select("id", "name")->get();
        
        $customers = DB::table('customers')->select("id", "name")->get();
        
        return view('bag_hours.index', compact(['data' ,'bag_hour_to_edit', 'bags_hours_types', 'projects', 'show_create_edit', 'customers', 'show_filters', 'old']))
                        ->with('i', (request()->input('page', 1) - 1) * $num_records)->with('lang', $lang);
    }
    
    public function orderBy($camp, $lang) {

        if(session('bag_hour_orderby')!=$camp || session('bag_hour_order')=="desc"){
            session(['bag_hour_orderby' => $camp]);
            session(['bag_hour_order' => "asc"]);
        }
        else{
            session(['bag_hour_order' => "desc"]);
        }       

        return redirect()->route($lang . '_bag_hours.index');
    }
    
    public function changeNumRecords(Request $request, $lang) {

        session(['bag_hours_num_records' => $request['num_records']]);

        return redirect()->route($lang . '_bag_hours.index');
    }
    
    function cancelEdit($lang) {
        return redirect()->route($lang . '_projects.index');
    }
    
    public function deleteFilters(Request $request) {
        
        session(['bag_hour_type_id' => '%']);
        session(['bag_hour_project_id' => '%']);
        session(['bag_hour_customer_id' => '%']);
        session(['bag_hour_date_from' => ""]);
        session(['bag_hour_date_to' => ""]);
        session(['bag_hour_orderby' => 'created_at']);
        session(['bag_hour_order' => "desc"]);
        
        $lang = $request->lang;

        return redirect()->route($lang.'_bag_hours.index');

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $lang = setGetLang();
        
        $bags_hours_types = DB::table('type_bag_hours')->select("id", "name", "hour_price")->get();
        $projects = DB::table('projects')->select("id", "name")->get();
        
        return view('bag_hours.create', compact(['lang','bags_hours_types','projects']));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateBagHourRequest $request, $lang)
    {
        App::setLocale($lang);
        
        $request->validated();

        //BagHour::create($request->validated());
        
        DB::table('bag_hours')->insert([
            'project_id' => $request->get("project_id"),
            'type_id' => $request->get("type_id"),
            'contracted_hours' => $request->get("contracted_hours"),
            'total_price' => $request->get("total_price"),
            'created_at' => now(),
            'updated_at' => now(),
            
        ]);
        
        $project_name = DB::table('projects')->where("id", '=', $request->get("project_id"))->select('name')->first();
        $bag_hour_name = DB::table('type_bag_hours')->where("id", '=', $request->get("type_id"))->select('name')->first();
        
        return redirect()->route($lang . '_bag_hours.index')
                        ->with('success', __('message.bag_hour') . " " . $bag_hour_name->name . " " .__('message.in') . " " . __('message.project') . " " . $project_name->name . " " . __('message.created_f'));

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BagHour  $bagHour
     * @return \Illuminate\Http\Response
     */
    public function show(BagHour $bagHour)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BagHour  $bagHour
     * @return \Illuminate\Http\Response
     */
    public function edit(BagHour $bagHour)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BagHour  $bagHour
     * @return \Illuminate\Http\Response
     */
    public function update(EditBagHourRequest $request, BagHour $bagHour, $lang)
    {
        
        BagHour::where('id', $bagHour->id)
        ->update([
            'project_id' => $request->get("project_id"),
            'type_id' => $request->get("type_id"),
            'contracted_hours' => $request->get("contracted_hours"),
            'total_price' => $request->get("total_price"),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $project_name = DB::table('projects')->where("id", '=', $request->get("project_id"))->select('name')->first();
        $bag_hour_name = DB::table('type_bag_hours')->where("id", '=', $request->get("type_id"))->select('name')->first();
        
        return redirect()->route($lang . '_bag_hours.index')
                        ->with('success', __('message.bag_hour') . " " . $bag_hour_name->name . " " .__('message.in') . " " . __('message.project') . " " . $project_name->name . " " . __('message.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BagHour  $bagHour
     * @return \Illuminate\Http\Response
     */
    public function destroy(BagHour $bagHour, $lang)
    {
        App::setLocale($lang);

        $bagHour->delete();

        return redirect()->route($lang . '_bag_hours.index')
                        ->with('success', __('message.bag_hour') . " " . __('message.deleted'));
    }

    public function export() 
    {
        return Excel::download(new BagHourExport(7), 'bag_hours.xlsx');
    }
}