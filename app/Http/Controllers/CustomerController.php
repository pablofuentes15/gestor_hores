<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\EditCustomerRequest;
use Illuminate\Support\Facades\App;
use DB;

class CustomerController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {

        $lang = setGetLang();
        
        $customer_to_edit = null;

        $show_filters = false;
        $show_create_edit = false;
        
        if ($request->has('_token') && $request->has('customer_id')) {
            
            $customer_to_edit = Customer::where('id', $request['customer_id'])->first();

            $show_create_edit = true;
        }
  
        if ($request->has('_token')) {

            $show_filters = true;

            ($request['name'] == "") ? session(['customer_name' => '%']) : session(['customer_name' => $request['name']]);

            ($request['email'] == "") ? session(['customer_email' => '%']) : session(['customer_email' => $request['email']]);

            ($request['phone'] == "") ? session(['customer_phone' => '%']) : session(['customer_phone' => $request['phone']]);

            ($request['tax_number'] == "") ? session(['customer_tax_number' => '%']) : session(['customer_tax_number' => $request['tax_number']]);

            ($request['contact_person'] == "") ? session(['customer_contact_person' => '%']) : session(['customer_contact_person' => $request['contact_person']]);

            ($request['bag'] == "") ? session(['customer_bag' => '%']) : session(['customer_bag' => $request['bag']]);

            ($request['address'] == "") ? session(['customer_address' => '%']) : session(['customer_address' => $request['address']]);

            ($request['postal_code'] == "") ? session(['customer_postal_code' => '%']) : session(['customer_postal_code' => $request['postal_code']]);

            ($request['iban'] == "") ? session(['customer_iban' => '%']) : session(['customer_iban' => $request['iban']]);

            ($request['country'] == "") ? session(['customer_country' => '%']) : session(['customer_country' => $request['country']]);

            ($request['province'] == "") ? session(['customer_province' => '%']) : session(['customer_province' => $request['province']]);

            ($request['municipality'] == "") ? session(['customer_municipality' => '%']) : session(['customer_municipality' => $request['municipality']]);

            session(['customer_num_records' => $request['num_records']]);
        }

        $dates = getIntervalDates($request, 'customer');
        $date_from = $dates[0];
        $date_to = $dates[1];

        $name = session('customer_name', "%");
        $email = session('customer_email', "%");
        $phone = session('customer_phone', "%");
        $tax_number = session('customer_tax_number', "%");
        $contact_person = session('customer_contact_person', "%");
        $bag = session('customer_bag', "%");
        $address = session('customer_address', "%");
        $postal_code = session('customer_postal_code', "%");
        $iban = session('customer_iban', "%");
        $country = session('customer_country', "%");
        $province = session('customer_province', "%");
        $municipality = session('customer_municipality', "%");
        $orderby = session('customer_orderby', "created_at");
        $order = session('customer_order', "desc");
        $num_records = session('customer_num_records', 10);

        if ($province == '') {
            $province = "%";
        }
        if ($municipality == '') {
            $municipality = "%";
        }
        if ($num_records == 'all') {
            $num_records = Customer::count();
        }

        $com = '>=';
		if ($bag == "yes"){
			$com = '>';
		}
		elseif($bag == "no"){
			$com = '=';
		}
		
        $data = Customer::
                leftJoin('countries', 'customers.country', '=' , 'countries.code')
                ->leftJoin('provinces', 'customers.province', '=' , 'provinces.id')
				->leftJoin('projects', 'customers.id', '=' , 'projects.customer_id')
		        ->leftJoin('bag_hours', 'projects.id', '=' , 'bag_hours.project_id')
				->select('customers.*', 'countries.name as country_name', 'provinces.name as province_name')
				->selectRaw('count(bag_hours.id) as bag')
                ->where('customers.name', 'like', "%" . $name . "%")
                ->where('customers.email', 'like', "%" . $email . "%")
                ->where('customers.phone', 'like', "%" . $phone . "%")
                ->where('customers.tax_number', 'like', $tax_number)
                ->where('customers.contact_person', 'like', "%" . $contact_person . "%")
                ->where('customers.address', 'like', "%" . $address . "%")
                ->where('customers.postal_code', 'like', "%" . $postal_code . "%")
                ->where('customers.iban', 'like', "%" . $iban . "%")
                ->where('customers.country', 'like', $country )
                ->where('customers.province', 'like', $province)
                ->where('customers.municipality', 'like', $municipality)
			    ->whereBetween('customers.created_at', [$date_from, $date_to])
				->groupBy('customers.id')
                ->orderBy('customers.'.$orderby, $order)
				->having('bag', $com , 0)
                ->paginate($num_records);

        $countries = DB::table('countries')/*->where('code', 'like', "ES")*/->get();
        $provinces = DB::table('provinces')->get();
        $municipalities = DB::table('municipalities')->get();

    /*
        $data = Customer::
                where('name', 'like', "%" . $name . "%")
                ->where('email', 'like', "%" . $email . "%")
                ->where('phone', 'like', "%" . $phone . "%")
                ->where('tax_number', 'like', $tax_number)
                ->where('contact_person', 'like', "%" . $contact_person . "%")
                ->whereBetween('created_at', [$date_from, $date_to])
                ->orderBy('created_at', $order)
                ->paginate($num_records);
        */

        return view('customers.index', compact(['data', 'show_filters', 'show_create_edit', 'customer_to_edit', 'countries', 'provinces', 'municipalities']))
                        ->with('i', (request()->input('page', 1) - 1) * $num_records)->with('lang', $lang);
    }

    public function orderBy($camp, $lang) {

        if(session('customer_orderby')!=$camp || session('customer_order')=="desc"){
            session(['customer_orderby' => $camp]);
            session(['customer_order' => "asc"]);
        }
        else{
            session(['customer_order' => "desc"]);
        }        

        return redirect()->route($lang . '_customers.index');
    }

    public function deleteFilters($lang) {

        session(['customer_name' => '%']);
        session(['customer_email' => '%']);
        session(['customer_phone' => '%']);
        session(['customer_tax_number' => '%']);
        session(['customer_contact_person' => '%']);
        session(['customer_address' => "%"]);
        session(['customer_postal_code'  => "%"]);
        session(['customer_iban'  => "%"]);
        session(['customer_country' => '%']);
        session(['customer_province' => '%']);
        session(['customer_municipality' => '%']);
        session(['customer_date_from' => ""]);
        session(['customer_date_to' => ""]);
        session(['customer_orderby' => "created_at"]);
        session(['customer_order' => "desc"]);

        return redirect()->route($lang . '_customers.index');
    }

    public function changeNumRecords(Request $request, $lang) {

        session(['customers_num_records' => $request['num_records']]);

        return redirect()->route($lang . '_customers.index');
    }
    
    function cancelEdit($lang) {
        return redirect()->route($lang . '_customers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $lang = setGetLang();

        return view('customers.create')->with('lang', $lang);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCustomerRequest $request, $lang) {
        App::setLocale($lang);
        $validated = $request->validated();
        $validated["phone"] = str_replace(' ','',$validated["phone"]);
        $validated["phone"] = str_replace('-','',$validated["phone"]);

        Customer::create($validated);

        return redirect()->route($lang . '_customers.index')
                        ->with('success', __('message.customer') . " " . $request->name . " " . __('message.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show(Customer $customer) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer) {
        $lang = setGetLang();

        return view('customers.edit', compact('customer'), compact('lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(EditCustomerRequest $request, Customer $customer, $lang) {

        $validated = $request->validated();
        $validated["phone"] = str_replace(' ','',$validated["phone"]);
        $validated["phone"] = str_replace('-','',$validated["phone"]);

        $customer->update($validated);

        return redirect()->route($lang . '_customers.index')
                        ->with('success', __('message.customer') . " " . $request->name . " " . __('message.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customer $customer, $lang) {
        App::setLocale($lang);

        $customer->delete();

        return redirect()->route($lang . '_customers.index')
                        ->with('success', __('message.customer') . " " . __('message.deleted'));
    }

}
