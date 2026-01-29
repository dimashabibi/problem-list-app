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
            'totalKentokaiProblems' => (clone $query)->where('type', 'kentokai')->count(),
            'totalBuyoffProblems' => (clone $query)->where('type', 'buyoff')->count(),
            'totalKsProblems' => (clone $query)->where('type', 'ks')->count(),
            'totalKdProblems' => (clone $query)->where('type', 'kd')->count(),
            'totalSkProblems' => (clone $query)->where('type', 'sk')->count(),
        ];

        $thisWeekProblems = [

            'pie'     => Problem::when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                return $q->whereBetween('created_at', [$startDate, $endDate]);
            })->where('problems.type', 'manufacturing')->count(),
            'column1' => Problem::select(DB::raw('count(case when status != "closed" then 1 else null end) as closed'))
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->where('type', 'manufacturing')
                ->first()->closed,
            'sk' => Problem::select(DB::raw('count(case when status != "closed" then 1 else null end) as closed'))
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->where('type', 'sk')
                ->first()->closed,
            'ks' => Problem::select(DB::raw('count(case when status != "closed" then 1 else null end) as closed'))
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->where('type', 'ks')
                ->first()->closed,
            'kd' => Problem::select(DB::raw('count(case when status != "closed" then 1 else null end) as closed'))
                ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                    return $q->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->where('type', 'kd')
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

        // Chart data untuk manufacturing
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
        // data chart manufaktur end

        // data chart untuk sk
        $skDataQuery = Problem::select(
            DB::raw('count(*) as total'),
            DB::raw("sum(case when status = 'closed' then 1 else 0 end) as closed"),
            'projects.project_name'
        )
            ->join('projects', 'problems.id_project', '=', 'projects.id_project')
            ->where('problems.type', 'sk');

        if ($startDate && $endDate) {
            $skDataQuery->whereBetween('problems.created_at', [$startDate, $endDate]);
        }

        $chartData = $skDataQuery->groupBy('problems.id_project', 'projects.project_name')
            ->get();

        $columnChartSk = [
            'labels' => $chartData->pluck('project_name'),
            'total' => $chartData->pluck('total'),
            'closed' => $chartData->pluck('closed'),
        ];
        // data chart sk end

        // data chart untuk ks
        $ksDataQuery = Problem::select(
            DB::raw('count(*) as total'),
            DB::raw("sum(case when status = 'closed' then 1 else 0 end) as closed"),
            'projects.project_name'
        )
            ->join('projects', 'problems.id_project', '=', 'projects.id_project')
            ->where('problems.type', 'ks');

        if ($startDate && $endDate) {
            $ksDataQuery->whereBetween('problems.created_at', [$startDate, $endDate]);
        }

        $chartData = $ksDataQuery->groupBy('problems.id_project', 'projects.project_name')
            ->get();

        $columnChartKs = [
            'labels' => $chartData->pluck('project_name'),
            'total' => $chartData->pluck('total'),
            'closed' => $chartData->pluck('closed'),
        ];
        // data chart ks end
        // data chart untuk sk
        $skDataQuery = Problem::select(
            DB::raw('count(*) as total'),
            DB::raw("sum(case when status = 'closed' then 1 else 0 end) as closed"),
            'projects.project_name'
        )
            ->join('projects', 'problems.id_project', '=', 'projects.id_project')
            ->where('problems.type', 'sk');

        if ($startDate && $endDate) {
            $skDataQuery->whereBetween('problems.created_at', [$startDate, $endDate]);
        }

        $chartData = $skDataQuery->groupBy('problems.id_project', 'projects.project_name')
            ->get();

        $columnChartSk = [
            'labels' => $chartData->pluck('project_name'),
            'total' => $chartData->pluck('total'),
            'closed' => $chartData->pluck('closed'),
        ];
        // data chart sk end

        // data chart untuk kd
        $kdDataQuery = Problem::select(
            DB::raw('count(*) as total'),
            DB::raw("sum(case when status = 'closed' then 1 else 0 end) as closed"),
            'projects.project_name'
        )
            ->join('projects', 'problems.id_project', '=', 'projects.id_project')
            ->where('problems.type', 'kd');

        if ($startDate && $endDate) {
            $kdDataQuery->whereBetween('problems.created_at', [$startDate, $endDate]);
        }

        $chartData = $kdDataQuery->groupBy('problems.id_project', 'projects.project_name')
            ->get();

        $columnChartKd = [
            'labels' => $chartData->pluck('project_name'),
            'total' => $chartData->pluck('total'),
            'closed' => $chartData->pluck('closed'),
        ];
        // data chart kd end




        return view('admin.index', compact('pieData', 'columnChart1', 'columnChartSk', 'columnChartKd', 'columnChartKs', 'dataTotal', 'thisWeekProblems', 'startDate', 'endDate'));
    }
}
