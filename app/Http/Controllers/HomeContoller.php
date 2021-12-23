<?php

namespace App\Http\Controllers;

use App\Models\UsersProject;
use Illuminate\Support\Facades\App;
use DB;

class HomeContoller extends Controller {

    public function index() {
        $lang = setGetLang();

        //Si no agafa el idioma amb setGetLang es que estem entrant per la arrel 
        //(/) del lloc, en aquest cas el idioma es el definit com idioma per defecte a la bd
        if (!isset($lang)) {
            $lang = $default_lang;
        }

        /* $data = HourEntryController::getBDInfo()
          ->paginate(10); */

        $data = new HourEntryController();
        $info_for_table = $data->getBDInfo("%", "%", "%")->validated()->orderBy('hour_entry_created_at', 'desc')->paginate(10);

        return view('home', compact('lang', 'info_for_table'))
                        ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    public function validateEntryHour($id, $lang) {
        App::setLocale($lang);
        DB::table('hours_entry')
                ->where('hours_entry.id', $id)
                ->update(['validate' => 1]);

        return redirect()->route($lang . '_home.index')
                        ->with('success', __('message.time_entry') . " " . __('message.validated'));
    }

    public function validateAllHours($lang) {
        App::setLocale($lang);
        $entries_to_validate = DB::table('hours_entry')->where('validate', 0)->update(['validate' => 1]);

        return redirect()->route($lang . '_home.index')
                        ->with('success', __('message.time_entries') . " " . __('message.validated'));
    }

}
