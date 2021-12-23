<?php

namespace App\Exports;

use App\Models\UsersProject;
use App\Http\Controllers\HourEntryController;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;


class HourEntryExport implements ShouldAutoSize, FromCollection, WithHeadings
{
    public function __construct(string  $user_id,string  $customer_id,string  $project_id)
    {
        $this->user_id = $user_id;
        $this->customer_id = $customer_id;
        $this->project_id = $project_id;
    }

    public function headings(): array
    {
        return [
            'user_nick',
            'user_name',
            'user_surname',
            'project_name',
            'customer_name',
            'type_bag_hour_name',
            'hour_entryhours',
            'hour_entryhoursimputed',
            'hour_entryvalidate',
            'hour_entrycreated_at',
            'bag_hourid',
            'hours_entryday'
        ];
    }

    public function collection()
    {
        $data = UsersProject::->join('users', 'users_projects.user_id', '=', 'users.id')
        ->join('projects', 'users_projects.project_id', '=', 'projects.id')
        ->join('customers', 'projects.customer_id', '=', 'customers.id')
        ->join('hours_entry', 'users_projects.id', '=', 'hours_entry.user_project_id')
        ->leftJoin('bag_hours', 'hours_entry.bag_hours_id', '=', 'bag_hours.id')
        ->leftJoin('type_bag_hours', 'bag_hours.type_id', '=', 'type_bag_hours.id')
        ->select('users.nickname AS user_nickname', 'users.name AS user_name', 'users.surname AS user_surname',
                'projects.name AS project_name', 'customers.name AS customer_name', 'type_bag_hours.name AS type_bag_hour_name',
                'hours_entry.hours AS hour_entry_hours', 'hours_entry.hours_imputed AS hour_entry_hours_imputed', 'hours_entry.validate AS hour_entry_validate',
                'hours_entry.created_at AS hour_entry_created_at', 'bag_hours.id AS bag_hour_id', 'hours_entry.day AS hours_entry_day')
        ->where('users.id', 'like', $this->user_id)
        ->where('customers.id', 'like', $this->customer_id)
        ->where('projects.id', 'like', $this->project_id);
        /*$data = new HourEntryController();
        $info_for_table = $data->getBDInfo($this->user_id, $this->customer_id , $this->project_id);
        return $info_for_table;*/
        //return $data;
    }
}
