<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Problem;
use App\Models\Project;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $startDateInput = $request->input('start_date');
        $endDateInput = $request->input('end_date');
        $periodInput = $request->input('period');

        $startAt = null;
        $endAt = null;
        $period = null;

        if ($startDateInput && $endDateInput) {
            $startAt = Carbon::parse($startDateInput)->startOfDay();
            $endAt = Carbon::parse($endDateInput)->endOfDay();
        } elseif (in_array($periodInput, ['weekly', 'monthly', 'yearly'], true)) {
            $now = Carbon::now();
            $period = $periodInput;

            if ($period === 'weekly') {
                $startAt = $now->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                $endAt = $now->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
            } elseif ($period === 'monthly') {
                $startAt = $now->copy()->startOfMonth()->startOfDay();
                $endAt = $now->copy()->endOfMonth()->endOfDay();
            } elseif ($period === 'yearly') {
                $startAt = $now->copy()->startOfYear()->startOfDay();
                $endAt = $now->copy()->endOfYear()->endOfDay();
            }
        }

        $startDate = $startAt ? $startAt->toDateString() : null;
        $endDate = $endAt ? $endAt->toDateString() : null;

        $query = Problem::query();

        if ($startAt && $endAt) {
            $query->whereBetween('created_at', [$startAt, $endAt]);
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

            'pie'     => Problem::when($startAt && $endAt, function ($q) use ($startAt, $endAt) {
                return $q->whereBetween('created_at', [$startAt, $endAt]);
            })->where('problems.type', 'manufacturing')->count(),
            'column1' => Problem::select(DB::raw('count(case when status != "closed" then 1 else null end) as closed'))
                ->when($startAt && $endAt, function ($q) use ($startAt, $endAt) {
                    return $q->whereBetween('created_at', [$startAt, $endAt]);
                })
                ->where('type', 'manufacturing')
                ->first()->closed,
            'sk' => Problem::select(DB::raw('count(case when status != "closed" then 1 else null end) as closed'))
                ->when($startAt && $endAt, function ($q) use ($startAt, $endAt) {
                    return $q->whereBetween('created_at', [$startAt, $endAt]);
                })
                ->where('type', 'sk')
                ->first()->closed,
            'ks' => Problem::select(DB::raw('count(case when status != "closed" then 1 else null end) as closed'))
                ->when($startAt && $endAt, function ($q) use ($startAt, $endAt) {
                    return $q->whereBetween('created_at', [$startAt, $endAt]);
                })
                ->where('type', 'ks')
                ->first()->closed,
            'kd' => Problem::select(DB::raw('count(case when status != "closed" then 1 else null end) as closed'))
                ->when($startAt && $endAt, function ($q) use ($startAt, $endAt) {
                    return $q->whereBetween('created_at', [$startAt, $endAt]);
                })
                ->where('type', 'kd')
                ->first()->closed,
        ];


        $pieQuery = Problem::select(DB::raw('count(*) as total'), 'projects.project_name')
            ->join('projects', 'problems.id_project', '=', 'projects.id_project')
            ->where('problems.type', 'manufacturing');

        if ($startAt && $endAt) {
            $pieQuery->whereBetween('problems.created_at', [$startAt, $endAt]);
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

        if ($startAt && $endAt) {
            $chartDataQuery->whereBetween('problems.created_at', [$startAt, $endAt]);
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

        if ($startAt && $endAt) {
            $skDataQuery->whereBetween('problems.created_at', [$startAt, $endAt]);
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

        if ($startAt && $endAt) {
            $ksDataQuery->whereBetween('problems.created_at', [$startAt, $endAt]);
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

        if ($startAt && $endAt) {
            $skDataQuery->whereBetween('problems.created_at', [$startAt, $endAt]);
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

        if ($startAt && $endAt) {
            $kdDataQuery->whereBetween('problems.created_at', [$startAt, $endAt]);
        }

        $chartData = $kdDataQuery->groupBy('problems.id_project', 'projects.project_name')
            ->get();

        $columnChartKd = [
            'labels' => $chartData->pluck('project_name'),
            'total' => $chartData->pluck('total'),
            'closed' => $chartData->pluck('closed'),
        ];
        // data chart kd end




        return view('admin.index', compact('pieData', 'columnChart1', 'columnChartSk', 'columnChartKd', 'columnChartKs', 'dataTotal', 'thisWeekProblems', 'startDate', 'endDate', 'period'));
    }
}
