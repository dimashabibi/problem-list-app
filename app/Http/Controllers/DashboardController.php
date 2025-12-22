<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problem;
use App\Models\Project;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = Problem::query();

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $dataTotal = [
            'totalMfgProblems' => (clone $query)->where('type', 'manufacturing')->count(),
            'totalKsProblems' => (clone $query)->where('type', 'ks')->count(),
            'totalKdProblems' => (clone $query)->where('type', 'kd')->count(),
            'totalSkProblems' => (clone $query)->where('type', 'sk')->count(),
        ];

        $thisWeekProblems = [

            'pie'     => Problem::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->where('problems.type', 'manufacturing')->count(),
            'column1' => Problem::select(DB::raw('count(case when status != "closed" then 1 else null end) as closed'))
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('type', 'manufacturing')
                ->first()->closed,
        ];


        $pieQuery = Problem::select(DB::raw('count(*) as total'), 'projects.project_name')
            ->join('projects', 'problems.id_project', '=', 'projects.id_project')
            ->where('problems.type', 'manufacturing');
        
        if ($startDate && $endDate) {
            $pieQuery->whereBetween('problems.created_at', [$startDate, $endDate]);
        }
            
        $pieData = $pieQuery->groupBy('problems.id_project', 'projects.project_name')
            ->get()
            ->pluck('total', 'project_name');

        $chartDataQuery = Problem::select(
            DB::raw('count(*) as total'),
            DB::raw("sum(case when status = 'closed' then 1 else 0 end) as closed"),
            'locations.location_name'
        )
            ->join('locations', 'problems.id_location', '=', 'locations.id_location')
            ->where('problems.type', 'manufacturing');

        if ($startDate && $endDate) {
            $chartDataQuery->whereBetween('problems.created_at', [$startDate, $endDate]);
        }

        $chartData = $chartDataQuery->groupBy('problems.id_location', 'locations.location_name')
            ->get();

        $columnChart1 = [
            'labels' => $chartData->pluck('location_name'),
            'total' => $chartData->pluck('total'),
            'closed' => $chartData->pluck('closed'),
        ];


        return view('admin.index', compact('pieData', 'columnChart1', 'dataTotal', 'thisWeekProblems', 'startDate', 'endDate'));
    }
}
