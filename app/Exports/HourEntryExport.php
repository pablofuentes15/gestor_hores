<?php

namespace App\Exports;

use App\Models\UsersProject;
use App\Http\Controllers\HourEntryController;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;


class HourEntryExport implements ShouldAutoSize, FromQuery, WithHeadings
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
            'User_name',
            'User_surname',
            'Project_name',
            'Customer_name',
            'Type_bag_hour_name',
            'Hour_entry_hours',
            'Hour_entry_hours_imputed',
            'Hour_entry_validate',
            'Hour_entry_created_at',
            'Bag_hour_id',
            'Hours_entry_day'
        ];
    }

    public function query()
    {
        $data = UsersProject::query()->join('users', 'users_projects.user_id', '=', 'users.id')
        ->join('projects', 'users_projects.project_id', '=', 'projects.id')
        ->join('customers', 'projects.customer_id', '=', 'customers.id')
        ->join('hours_entry', 'users_projects.id', '=', 'hours_entry.user_project_id')
        ->leftJoin('bag_hours', 'hours_entry.bag_hours_id', '=', 'bag_hours.id')
        ->leftJoin('type_bag_hours', 'bag_hours.type_id', '=', 'type_bag_hours.id')
        ->select('users.name AS user_name', 'users.surname AS user_surname',
                'projects.name AS project_name', 'customers.name AS customer_name', 'type_bag_hours.name AS type_bag_hour_name',
                'hours_entry.hours AS hour_entry_hours', 'hours_entry.hours_imputed AS hour_entry_hours_imputed', 'hours_entry.validate AS hour_entry_validate',
                'hours_entry.created_at AS hour_entry_created_at', 'bag_hours.id AS bag_hour_id', 'hours_entry.day AS hours_entry_day')
        ->where('users.id', 'like', $this->user_id)
        ->where('customers.id', 'like', $this->customer_id)
        ->where('projects.id', 'like', $this->project_id);
        return $data;
    }
}