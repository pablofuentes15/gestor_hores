<?php

function setActive($routeName) {
    return request()->routeIs($routeName) ? 'font-weight-bold' : '';
}

function setActiveLang($lang) {
    if (\Request::is($lang . '/*')) {
        return 'selected';
    } else {
        return '';
    }
}

function setGetLang() {
    if (\Request::is('en/*')) {
        App::setLocale('en');
        return "en";
    } else if (\Request::is('es/*')) {
        App::setLocale('es');
        return "es";
    } else if (\Request::is('ca/*')) {
        App::setLocale('ca');
        return "ca";
    }
}

function getLang() {
    if (\Request::is('en/*')) {
        return "en";
    } else if (\Request::is('es/*')) {
        return "es";
    } else if (\Request::is('ca/*')) {
        return "ca";
    }
}

function setActiveSelect($option, $work_sector) {
    if ($option == $work_sector) {
        return 'selected';
    } else {
        return '';
    }
}

function getIntervalDates($request, $section) {
    if ($request->has('_token')) {
        if (DateTime::createFromFormat('d/m/Y', $request['date_from']) !== false) {
            $date = DateTime::createFromFormat('d/m/Y', $request['date_from'])->format('d/m/Y');
            session([$section.'_date_from' => $date]);
        } else {
            session([$section.'_date_from' => ""]);
        }

        if (DateTime::createFromFormat('d/m/Y', $request['date_to']) !== false) {
            $date = DateTime::createFromFormat('d/m/Y', $request['date_to'])->format('d/m/Y');
            session([$section.'_date_to' => $date]);
        } else {
            session([$section.'_date_to' => ""]);
        }
    }
    
    
    $date_from = session($section.'_date_from', "");

    if ($date_from == "") {
        $date = new DateTime('2021-04-10');
        $date_from = $date->format('Y-m-d');
    } else {
        $date_from = DateTime::createFromFormat('d/m/Y', $date_from)->format('Y-m-d');
    }

    //echo $date_from;

    $date_to = session($section.'_date_to', "");

    if ($date_to == "") {
        $date = new DateTime('NOW +1 day', new DateTimeZone('Europe/Madrid'));
        $date_to = $date->format('Y-m-d');
    } else {
        $date_to = DateTime::createFromFormat('d/m/Y', $date_to)->modify('+1 day')->format('Y-m-d');
    }
    
    return [$date_from, $date_to];
}
