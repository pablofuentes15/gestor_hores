<?php

namespace App\Exports;

use App\Models\BagHour;
use App\Http\Controllers\HourEntryController;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;


class BagHourExport implements ShouldAutoSize, FromQuery, WithHeadings
{
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function headings(): array
    {
        return [
            'id',
            'project',
            'type_bag_houres',
            'contracted_hours',
            'total_price',
            'created_at'
        ];
    }

    public function query()
    {
        return BagHour::query()->join('projects', 'bag_hours.project_id', '=', 'projects.id')
        ->join('type_bag_hours', 'bag_hours.type_id', '=', 'type_bag_hours.id')
        ->select('bag_hours.id','projects.name as pname','type_bag_hours.name as tbname',
        'bag_hours.contracted_hours','bag_hours.total_price','bag_hours.created_at')
        ->where('bag_hours.id', 'like',$this->id);
    }
}
