<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use Illuminate\Http\Request;
use App\Http\Requests\CreateProviderRequest;
use App\Http\Requests\EditProviderRequest;
use Illuminate\Support\Facades\App;
use DB;

class ProviderController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {

        $lang = setGetLang();
        
        $provider_to_edit = null;

        $show_filters = false;
        $show_create_edit = false;
        
        if ($request->has('_token') && $request->has('provider_id')) {
            
            $provider_to_edit = Provider::where('id', $request['provider_id'])->first();

            $show_create_edit = true;
        }
  
        if ($request->has('_token')) {

            $show_filters = true;

            ($request['name'] == "") ? session(['provider_name' => '%']) : session(['provider_name' => $request['name']]);

            ($request['email'] == "") ? session(['provider_email' => '%']) : session(['provider_email' => $request['email']]);

            ($request['phone'] == "") ? session(['provider_phone' => '%']) : session(['provider_phone' => $request['phone']]);

            ($request['tax_number'] == "") ? session(['provider_tax_number' => '%']) : session(['provider_tax_number' => $request['tax_number']]);

            ($request['contact_person'] == "") ? session(['provider_contact_person' => '%']) : session(['provider_contact_person' => $request['contact_person']]);

            ($request['bag'] == "") ? session(['provider_bag' => '%']) : session(['provider_bag' => $request['bag']]);

            ($request['address'] == "") ? session(['provider_address' => '%']) : session(['provider_address' => $request['address']]);

            ($request['postal_code'] == "") ? session(['provider_postal_code' => '%']) : session(['provider_postal_code' => $request['postal_code']]);

            ($request['iban'] == "") ? session(['provider_iban' => '%']) : session(['provider_iban' => $request['iban']]);

            ($request['country'] == "") ? session(['provider_country' => '%']) : session(['provider_country' => $request['country']]);

            ($request['province'] == "") ? session(['provider_province' => '%']) : session(['provider_province' => $request['province']]);

            ($request['municipality'] == "") ? session(['provider_municipality' => '%']) : session(['provider_municipality' => $request['municipality']]);

            session(['provider_num_records' => $request['num_records']]);
        }

        $dates = getIntervalDates($request, 'provider');
        $date_from = $dates[0];
        $date_to = $dates[1];

        $name = session('provider_name', "%");
        $email = session('provider_email', "%");
        $phone = session('provider_phone', "%");
        $tax_number = session('provider_tax_number', "%");
        $contact_person = session('provider_contact_person', "%");
        $bag = session('provider_bag', "%");
        $address = session('provider_address', "%");
        $postal_code = session('provider_postal_code', "%");
        $iban = session('provider_iban', "%");
        $country = session('provider_country', "%");
        $province = session('provider_province', "%");
        $municipality = session('provider_municipality', "%");
        $orderby = session('provider_orderby', "created_at");
        $order = session('provider_order', "desc");
        $num_records = session('provider_num_records', 10);

        if ($num_records == 'all') {
            $num_records = Provider::count();
        }

        $com = '>=';
		if ($bag == "yes"){
			$com = '>';
		}
		elseif($bag == "no"){
			$com = '=';
		}
		
        $data = Provider::
                leftJoin('countries', 'providers.country', '=' , 'countries.code')
                ->leftJoin('provinces', 'providers.province', '=' , 'provinces.id')
				->select('providers.*', 'countries.name as country_name', 'provinces.name as province_name')
                ->where('providers.name', 'like', "%" . $name . "%")
                ->where('providers.email', 'like', "%" . $email . "%")
                ->where('providers.phone', 'like', "%" . $phone . "%")
                ->where('providers.tax_number', 'like', $tax_number)
                ->where('providers.contact_person', 'like', "%" . $contact_person . "%")
                ->where('providers.address', 'like', "%" . $address . "%")
                ->where('providers.postal_code', 'like', "%" . $postal_code . "%")
                ->where('providers.iban', 'like', "%" . $iban . "%")
                ->where('providers.country', 'like', $country )
                ->where('providers.province', 'like', $province)
                ->where('providers.municipality', 'like', $municipality)
			    ->whereBetween('providers.created_at', [$date_from, $date_to])
				->groupBy('providers.id')
                ->orderBy('providers.'.$orderby, $order)
                ->paginate($num_records);

        $countries = DB::table('countries')/*->where('code', 'like', "ES")*/->get();
        $provinces = DB::table('provinces')->get();
        $municipalities = DB::table('municipalities')->get();

    /*
        $data = Provider::
                where('name', 'like', "%" . $name . "%")
                ->where('email', 'like', "%" . $email . "%")
                ->where('phone', 'like', "%" . $phone . "%")
                ->where('tax_number', 'like', $tax_number)
                ->where('contact_person', 'like', "%" . $contact_person . "%")
                ->whereBetween('created_at', [$date_from, $date_to])
                ->orderBy('created_at', $order)
                ->paginate($num_records);
        */

        return view('providers.index', compact(['data', 'show_filters', 'show_create_edit', 'provider_to_edit', 'countries', 'provinces', 'municipalities']))
                        ->with('i', (request()->input('page', 1) - 1) * $num_records)->with('lang', $lang);
    }

    public function orderBy($camp, $lang) {

        if(session('provider_orderby')!=$camp || session('provider_order')=="desc"){
            session(['provider_orderby' => $camp]);
            session(['provider_order' => "asc"]);
        }
        else{
            session(['provider_order' => "desc"]);
        }        

        return redirect()->route($lang . '_providers.index');
    }

    public function deleteFilters($lang) {

        session(['provider_name' => '%']);
        session(['provider_email' => '%']);
        session(['provider_phone' => '%']);
        session(['provider_tax_number' => '%']);
        session(['provider_contact_person' => '%']);
        session(['provider_address' => "%"]);
        session(['provider_postal_code'  => "%"]);
        session(['provider_iban'  => "%"]);
        session(['provider_country' => '%']);
        session(['provider_province' => '%']);
        session(['provider_municipality' => '%']);
        session(['provider_date_from' => ""]);
        session(['provider_date_to' => ""]);
        session(['provider_orderby' => "created_at"]);
        session(['provider_order' => "desc"]);

        return redirect()->route($lang . '_providers.index');
    }

    public function changeNumRecords(Request $request, $lang) {

        session(['providers_num_records' => $request['num_records']]);

        return redirect()->route($lang . '_providers.index');
    }
    
    function cancelEdit($lang) {
        return redirect()->route($lang . '_providers.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $lang = setGetLang();

        return view('providers.create')->with('lang', $lang);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateProviderRequest $request, $lang) {
        App::setLocale($lang);
        $validated = $request->validated();
        $validated["phone"] = str_replace(' ','',$validated["phone"]);
        $validated["phone"] = str_replace('-','',$validated["phone"]);

        Provider::create($validated);

        return redirect()->route($lang . '_providers.index')
                        ->with('success', __('message.provider') . " " . $request->name . " " . __('message.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function show(Provider $provider) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function edit(Provider $provider) {
        $lang = setGetLang();

        return view('providers.edit', compact('provider'), compact('lang'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function update(EditProviderRequest $request, Provider $provider, $lang) {

        $validated = $request->validated();
        $validated["phone"] = str_replace(' ','',$validated["phone"]);
        $validated["phone"] = str_replace('-','',$validated["phone"]);

        $provider->update($validated);

        return redirect()->route($lang . '_providers.index')
                        ->with('success', __('message.provider') . " " . $request->name . " " . __('message.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function destroy(Provider $provider, $lang) {
        App::setLocale($lang);

        $provider->delete();

        return redirect()->route($lang . '_providers.index')
                        ->with('success', __('message.provider') . " " . __('message.deleted'));
    }

}
