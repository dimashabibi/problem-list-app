<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Project;
use App\Models\Item;
use App\Models\Kanban;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Conditional;

class ProblemController extends Controller
{
    public function index()
    {
        return view('admin.problem.index');
    }

    public function table()
    {
        return view('admin.problem.table');
    }

    public function gallery()
    {
        return view('admin.problem.gallery');
    }

    public function list(Request $request)
    {
        $q = Problem::query()
            ->with(['project', 'kanban', 'location', 'reporter', 'item', 'attachments', 'machine', 'seksiInCharge', 'curatives.pic', 'preventives'])
            ->orderBy('id_problem', 'desc');
        if ($request->filled('item_id')) $q->where('id_item', $request->integer('item_id'));
        if ($request->filled('project_id')) $q->where('id_project', $request->integer('project_id'));
        if ($request->filled('kanban_id')) $q->where('id_kanban', $request->integer('kanban_id'));
        if ($request->filled('type')) $q->where('type', $request->string('type'));
        if ($request->filled('group_code')) $q->where('group_code', $request->string('group_code'));
        if ($request->filled('start_date')) $q->whereDate('created_at', '>=', $request->string('start_date'));
        if ($request->filled('end_date')) $q->whereDate('created_at', '<=', $request->string('end_date'));

        return response()->json($q->get()->map(function ($p) {
            return [
                'id_problem' => $p->id_problem,
                'created_at' => $p->created_at,
                'project' => $p->project?->project_name,
                'id_project' => $p->id_project,
                'id_kanban' => $p->id_kanban,
                'id_item' => $p->id_item,
                'id_location' => $p->id_location,
                'kanban' => $p->kanban?->kanban_name,
                'item' => $p->item?->item_name ?? $p->item, // Fallback to raw value if relation fails
                'location' => $p->location?->location_name,
                'type' => $p->type === 'manufacturing' ? 'Manufacturing' : strtoupper($p->type),
                'raw_type' => $p->type,
                'group_code' => $p->group_code,
                'group_code_norm' => $p->group_code_norm,
                'problem' => $p->problem,
                'cause' => $p->cause,
                'curatives' => $p->curatives,
                'preventives' => $p->preventives,
                'attachments' => $p->attachments->map(fn($a) => $a->file_path)->toArray(),
                'status' => $p->status ?? 'dispatched', // Fallback for existing null records
                'reporter' => $p->reporter?->fullname ?? $p->reporter?->username,
                'id_machine' => $p->id_machine,
                'machine' => $p->machine?->name_machine,
                'type_saibo' => $p->type_saibo,
                'classification' => $p->classification,
                'stage' => $p->stage,
                'id_seksi_in_charge' => $p->id_seksi_in_charge,
            ];
        }));
    }

    public function problemCodes(Request $request)
    {
        $type = $request->query('type');
        $projectId = $request->query('id_project');
        $kanbanId = $request->query('id_kanban');

        $query = Problem::query()
            ->selectRaw('MAX(group_code) as code, group_code_norm as code_norm, COUNT(*) as total_problems, MAX(created_at) as last_used')
            ->whereNotNull('group_code_norm')
            ->where('group_code_norm', '<>', '');

        if ($type) {
            $query->where('type', strtolower($type));
        }
        if ($projectId) {
            $query->where('id_project', $projectId);
        }
        if ($kanbanId) {
            $query->where('id_kanban', $kanbanId);
        }

        $codes = $query
            ->groupBy('group_code_norm')
            ->orderByDesc('last_used')
            ->get()
            ->map(function ($row) {
                return [
                    'code' => $row->code,
                    'code_norm' => $row->code_norm,
                    'total_problems' => (int) $row->total_problems,
                    'last_used' => $row->last_used instanceof \Carbon\Carbon
                        ? $row->last_used->toDateTimeString()
                        : (string) $row->last_used,
                ];
            });

        if ($codes->isEmpty()) {
            Log::info('problem-codes empty', [
                'type' => $type,
                'project_id' => $projectId,
                'kanban_id' => $kanbanId,
            ]);
        }

        return response()->json($codes);
    }

    private function typeShort(string $type): string
    {
        $map = [
            'manufacturing' => 'MFG',
            'kentokai' => 'KTC',
            'ks' => 'KS',
            'kd' => 'KD',
            'sk' => 'SK',
            'buyoff' => 'BO',
        ];

        $type = strtolower($type);
        return $map[$type] ?? strtoupper(substr($type, 0, 3));
    }

    private function normalizeForCode(string $name): string
    {
        $name = strtoupper(trim($name));
        $name = preg_replace('/\s+/', ' ', $name);
        $name = str_replace(' ', '-', $name);
        $name = preg_replace('/[^A-Z0-9\-]/', '', $name);

        return $name;
    }

    private function parseHour($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $str = str_replace(',', '.', trim((string) $value));
        if ($str === '') {
            return null;
        }

        if (!is_numeric($str)) {
            return null;
        }

        return round((float) $str, 2);
    }

    public function store(Request $request)
    {
        $rules = [
            'id_location' => 'required|integer|exists:locations,id_location',
            'type' => 'required|in:manufacturing,ks,kd,sk,kentokai,buyoff',
            'problem' => 'required|string',
            'cause' => 'nullable|string',
            'classification_problem' => 'nullable|string',
            'curatives' => 'nullable|array',
            'curatives.*.curative' => 'nullable|string',
            'curatives.*.id_pic' => 'nullable|integer|exists:locations,id_location',
            'curatives.*.hour' => 'nullable|string',
            'preventives' => 'nullable|array',
            'preventives.*.preventive' => 'nullable|string',
            'curative_actions' => 'nullable|array',
            'curative_actions.*' => 'string|nullable',
            'curative_pics' => 'nullable|array',
            'curative_pics.*' => 'nullable|integer|exists:locations,id_location',
            'curative_hours' => 'nullable|array',
            'curative_hours.*' => 'nullable|string',
            'preventive_actions' => 'nullable|array',
            'preventive_actions.*' => 'string|nullable',
            'attachment' => 'nullable|array',
            'attachment.*' => 'image|max:4096',
            'id_machine' => 'nullable|integer|exists:machines,id_machine',
            'type_saibo' => 'nullable|in:baru,berulang',
            'classification' => 'nullable|in:konst,komp,model',
            'stage' => 'nullable|in:MFG,KS,KD,SK,T0,T1,T2,T3,BUYOFF,LT,HOMELINE',
        ];

        if ($request->input('type') !== 'manufacturing') {
            $rules['group_code'] = 'required|string|max:100';
            $rules['group_code_mode'] = 'required|in:existing,new';
            $rules['group_code_existing'] = 'nullable|string|max:100|required_if:group_code_mode,existing';
            $rules['group_code_suffix'] = 'nullable|string|max:100|required_if:group_code_mode,new';
        }

        // Conditional validation
        if ($request->filled('new_project_name')) {
            $rules['new_project_name'] = 'required|string|max:20|unique:projects,project_name';
            // If new project, kanban MUST be new
            $rules['new_kanban_name'] = 'required|string|max:20';
        } else {
            $rules['id_project'] = 'required|integer|exists:projects,id_project';

            if ($request->filled('new_kanban_name')) {
                $rules['new_kanban_name'] = 'required|string|max:20';
            } else {
                $rules['id_kanban'] = 'required|integer|exists:kanbans,id_kanban';
            }
        }

        if ($request->filled('new_item_name')) {
            $rules['new_item_name'] = 'required|string|max:50|unique:items,item_name';
        } else {
            $rules['id_item'] = 'required|integer|exists:items,id_item';
        }

        $validated = $request->validate($rules);

        $projectId = $request->input('id_project');
        if ($request->filled('new_project_name')) {
            $project = Project::create([
                'project_name' => $request->input('new_project_name'),
                'description' => 'Created via Problem'
            ]);
            $projectId = $project->id_project;
        }

        $kanbanId = $request->input('id_kanban');
        if ($request->filled('new_kanban_name')) {
            $kanban = Kanban::create([
                'project_id' => $projectId,
                'kanban_name' => $request->input('new_kanban_name')
            ]);
            $kanbanId = $kanban->id_kanban;
        }

        $itemId = $request->input('id_item');
        if ($request->filled('new_item_name')) {
            $item = \App\Models\Item::create([
                'item_name' => $request->input('new_item_name'),
                'description' => 'Created via Problem'
            ]);
            $itemId = $item->id_item;
        }

        $mainAttachmentPath = null;
        $attachmentPaths = [];

        if ($request->hasFile('attachment')) {
            foreach ($request->file('attachment') as $file) {
                $path = $file->store('attachments', 'public');
                $attachmentPaths[] = $path;
            }
        }

        $groupCode = null;
        $groupCodeNorm = null;
        $mode = $request->input('group_code_mode');
        $explicitGroupCode = trim((string) $request->input('group_code', ''));
        $type = $request->input('type');
        $project = null;
        $kanban = null;

        if ($type && $projectId) {
            $project = Project::find($projectId);
        }
        if ($type && $kanbanId) {
            $kanban = Kanban::find($kanbanId);
        }

        if ($explicitGroupCode !== '') {
            $groupCode = $explicitGroupCode;
            $groupCodeNorm = strtoupper($groupCode);
        } else {
            if ($mode === 'existing') {
                $code = trim((string) $request->input('group_code_existing', ''));
                if ($code !== '') {
                    $groupCode = $code;
                    $groupCodeNorm = strtoupper($code);
                }
            } elseif ($mode === 'new') {
                $suffix = trim((string) $request->input('group_code_suffix', ''));

                if ($suffix !== '' && $project && $kanban) {
                    $typeShort = $this->typeShort($type);
                    $projectPart = $this->normalizeForCode($project->project_name ?? '');
                    $kanbanPart = $this->normalizeForCode($kanban->kanban_name ?? '');

                    $prefix = $typeShort . '_' . $projectPart . '_' . $kanbanPart . '_';

                    $groupCode = trim($prefix . $suffix);
                    $groupCodeNorm = strtoupper($groupCode);
                }
            }
        }

        $problemData = array_merge($validated, [
            'id_project' => $projectId,
            'id_kanban' => $kanbanId,
            'id_item' => $itemId,
            'id_location' => $request->input('id_location'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'problem' => $request->input('problem'),
            'cause' => $request->input('cause'),
            'status' => 'in_progress',
            'id_user' => Auth::id() ?? 1,
            'group_code' => $groupCode,
            'group_code_norm' => $groupCodeNorm,
            'id_seksi_in_charge' => $request->input('id_seksi_in_charge'),
            'classification_problem' => $request->input('classification_problem'),
        ]);

        DB::transaction(function () use ($problemData, $attachmentPaths, $request) {
            $problem = Problem::create($problemData);

            // Normalize curatives payload (support both formats)
            $curativesNested = $request->input('curatives', []);
            $curativeActions = $request->input('curative_actions', []);
            $curativePics = $request->input('curative_pics', []);
            $curativeHours = $request->input('curative_hours', []);

            if (is_array($curativesNested) && count($curativesNested) > 0) {
                foreach ($curativesNested as $c) {
                    $text = isset($c['curative']) ? trim((string) $c['curative']) : '';
                    if ($text === '') {
                        continue;
                    }

                    $hourValue = isset($c['hour']) ? $this->parseHour($c['hour']) : null;

                    \App\Models\Curative::create([
                        'id_problem' => $problem->id_problem,
                        'curative' => $text,
                        'id_pic' => !empty($c['id_pic']) ? $c['id_pic'] : null,
                        'hour' => $hourValue,
                    ]);
                }
            } elseif (is_array($curativeActions)) {
                foreach ($curativeActions as $index => $action) {
                    $text = trim((string) $action);
                    if ($text === '') {
                        continue;
                    }

                    $rawHour = $curativeHours[$index] ?? null;
                    $hourValue = $this->parseHour($rawHour);

                    \App\Models\Curative::create([
                        'id_problem' => $problem->id_problem,
                        'curative' => $text,
                        'id_pic' => !empty($curativePics[$index]) ? $curativePics[$index] : null,
                        'hour' => $hourValue,
                    ]);
                }
            }

            // Normalize preventives payload (support both formats)
            $preventivesNested = $request->input('preventives', []);
            $preventiveActions = $request->input('preventive_actions', []);

            if (is_array($preventivesNested) && count($preventivesNested) > 0) {
                foreach ($preventivesNested as $p) {
                    $text = isset($p['preventive']) ? trim((string) $p['preventive']) : '';
                    if ($text === '') continue;
                    \App\Models\Preventive::create([
                        'id_problem' => $problem->id_problem,
                        'preventive' => $text,
                    ]);
                }
            } elseif (is_array($preventiveActions)) {
                foreach ($preventiveActions as $action) {
                    $text = trim((string) $action);
                    if ($text === '') continue;
                    \App\Models\Preventive::create([
                        'id_problem' => $problem->id_problem,
                        'preventive' => $text,
                    ]);
                }
            }

            foreach ($attachmentPaths as $path) {
                \App\Models\ProblemAttachment::create([
                    'problem_id' => $problem->id_problem,
                    'file_path' => $path
                ]);
            }
        });

        return response()->json(['success' => true]);
    }

    public function update(Request $request, int $id)
    {
        $problem = Problem::findOrFail($id);

        $rules = [
            'id_project' => 'required|integer|exists:projects,id_project',
            'id_kanban' => 'required|integer|exists:kanbans,id_kanban',
            'id_item' => 'required|integer|exists:items,id_item',
            'id_location' => 'required|integer|exists:locations,id_location',
            'type' => 'required|in:manufacturing,ks,kd,sk,kentokai,buyoff',
            'status' => 'required|in:dispatched,in_progress,closed',
            'problem' => 'required|string',
            'cause' => 'nullable|string',
            'classification_problem' => 'nullable|string',
            // New nested array format
            'curatives' => 'nullable|array',
            'curatives.*.curative' => 'nullable|string',
            'curatives.*.id_pic' => 'nullable|integer|exists:locations,id_location',
            'curatives.*.hour' => 'nullable|string',
            'preventives' => 'nullable|array',
            'preventives.*.preventive' => 'nullable|string',
            // Legacy flat arrays (supported for compatibility)
            'curative_actions' => 'nullable|array',
            'curative_actions.*' => 'string|nullable',
            'curative_pics' => 'nullable|array',
            'curative_pics.*' => 'nullable|integer|exists:locations,id_location',
            'curative_hours' => 'nullable|array',
            'curative_hours.*' => 'nullable|string',
            'preventive_actions' => 'nullable|array',
            'preventive_actions.*' => 'string|nullable',
        ];

        $validated = $request->validate($rules);

        $updateData = [
            'id_project' => $request->input('id_project'),
            'id_kanban' => $request->input('id_kanban'),
            'id_item' => $request->input('id_item'),
            'id_location' => $request->input('id_location'),
            'type' => $request->input('type'),
            'status' => $request->input('status'),
            'problem' => $request->input('problem'),
            'cause' => $request->input('cause'),
            'id_machine' => $request->input('id_machine'),
            'type_saibo' => $request->input('type_saibo'),
            'classification' => $request->input('classification'),
            'stage' => $request->input('stage'),
            'classification_problem' => $request->input('classification_problem'),
        ];

        if ($request->filled('group_code')) {
            $groupCode = $request->input('group_code');
            $updateData['group_code'] = $groupCode;
            $updateData['group_code_norm'] = strtoupper($groupCode);
        }

        DB::transaction(function () use ($problem, $updateData, $request, $id) {
            $problem->update($updateData);

            // Reset curatives/preventives
            \App\Models\Curative::where('id_problem', $id)->delete();
            \App\Models\Preventive::where('id_problem', $id)->delete();

            // Normalize curatives payload (support both formats)
            $curativesNested = $request->input('curatives', []);
            $curativeActions = $request->input('curative_actions', []);
            $curativePics = $request->input('curative_pics', []);
            $curativeHours = $request->input('curative_hours', []);

            if (is_array($curativesNested) && count($curativesNested) > 0) {
                foreach ($curativesNested as $c) {
                    $text = isset($c['curative']) ? trim((string) $c['curative']) : '';
                    if ($text === '') {
                        continue;
                    }

                    $hourValue = isset($c['hour']) ? $this->parseHour($c['hour']) : null;

                    \App\Models\Curative::create([
                        'id_problem' => $problem->id_problem,
                        'curative' => $text,
                        'id_pic' => !empty($c['id_pic']) ? $c['id_pic'] : null,
                        'hour' => $hourValue,
                    ]);
                }
            } elseif (is_array($curativeActions)) {
                foreach ($curativeActions as $index => $action) {
                    $text = trim((string) $action);
                    if ($text === '') {
                        continue;
                    }

                    $rawHour = $curativeHours[$index] ?? null;
                    $hourValue = $this->parseHour($rawHour);

                    \App\Models\Curative::create([
                        'id_problem' => $problem->id_problem,
                        'curative' => $text,
                        'id_pic' => !empty($curativePics[$index]) ? $curativePics[$index] : null,
                        'hour' => $hourValue,
                    ]);
                }
            }

            // Normalize preventives payload (support both formats)
            $preventivesNested = $request->input('preventives', []);
            $preventiveActions = $request->input('preventive_actions', []);

            if (is_array($preventivesNested) && count($preventivesNested) > 0) {
                foreach ($preventivesNested as $p) {
                    $text = isset($p['preventive']) ? trim((string) $p['preventive']) : '';
                    if ($text === '') continue;
                    \App\Models\Preventive::create([
                        'id_problem' => $problem->id_problem,
                        'preventive' => $text,
                    ]);
                }
            } elseif (is_array($preventiveActions)) {
                foreach ($preventiveActions as $action) {
                    $text = trim((string) $action);
                    if ($text === '') continue;
                    \App\Models\Preventive::create([
                        'id_problem' => $problem->id_problem,
                        'preventive' => $text,
                    ]);
                }
            }
        });

        return response()->json(['success' => true]);
    }

    public function destroy(int $id)
    {
        $p = Problem::with('attachments')->findOrFail($id);

        // Delete main attachment if exists (though it should be in attachments table too if created new)
        // But for old records, we check attachment column
        // Delete all related attachments
        foreach ($p->attachments as $attachment) {
            try {
                // Check if it is different from main attachment to avoid double delete attempt? 
                // Storage delete doesn't throw if file missing usually, or we catch it.
                Storage::disk('public')->delete($attachment->file_path);
            } catch (\Throwable $e) {
            }
        }

        $p->delete(); // Cascade deletes attachment records from DB
        return response()->json(['success' => true]);
    }

    public function export(Request $request, int $id)
    {
        $problem = Problem::with(['project', 'kanban', 'location', 'reporter', 'item', 'attachments', 'machine', 'curatives.pic', 'preventives'])->find($id);

        if (!$problem) {
            return response()->json(['message' => 'Problem not found'], 404);
        }

        $type = $problem->type;
        $problems = collect();
        $fileName = "";

        if ($type === 'manufacturing') {
            $fileName = "Problem_{$type}_{$id}_" . date('Ymd_His') . ".xlsx";
            $problems = collect([$problem]);
        } else {
            // Non-Manufacturing: Export based on Group Code
            $groupCode = $problem->group_code;

            if ($groupCode) {
                $problems = Problem::with(['project', 'kanban', 'location', 'reporter', 'item', 'attachments', 'machine'])
                    ->where('type', $type)
                    ->where('group_code', $groupCode)
                    ->orderBy('created_at', 'asc')
                    ->get();

                // Use group code in filename
                $fileName = "Problem_{$type}_{$groupCode}_" . date('Ymd_His') . ".xlsx";
            } else {
                // Fallback: Export single problem if no group code
                $problems = collect([$problem]);
                $fileName = "Problem_{$type}_{$id}_" . date('Ymd_His') . ".xlsx";
            }
        }

        return match (strtolower($type)) {
            'manufacturing' => $this->exportFormatManufacturing($problems, $fileName),
            'kentokai' => $this->exportFormatKentokai($problems, $fileName),
            'ks', 'kd', 'sk', 'buyoff' => $this->exportFormatCommon($problems, $fileName, $type),
            default => response()->json(['message' => "Export format for type '$type' not supported"], 400),
        };
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:dispatched,in_progress,closed',
        ]);

        $problem = Problem::findOrFail($id);

        $status = $request->status;
        $problem->status = $status;

        if ($status === 'dispatched') {
            if (!$problem->dispatched_at) {
                $now = now();
                $problem->dispatched_at = $now;
                if (!$problem->target) {
                    $problem->target = $now->copy()->addDays(5);
                }
            }
        } elseif ($status === 'closed') {
            if (!$problem->closed_at) {
                $problem->closed_at = now();
            }
        }

        $problem->save();

        return response()->json(['success' => true]);
    }



    public function exportGroup(Request $request)
    {
        $type = $request->query('type');
        $groupCode = $request->query('group_code');
        $projectId = $request->query('id_project');
        $kanbanId = $request->query('id_kanban');

        if (!$type) {
            return response()->json(['message' => 'Type is required'], 400);
        }

        $query = Problem::with(['project', 'kanban', 'location', 'reporter', 'item', 'attachments', 'machine', 'curatives', 'preventives'])
            ->where('type', $type);

        if ($groupCode) {
            $query->where('group_code', $groupCode);
        } else {
            if ($projectId) $query->where('id_project', $projectId);
            if ($kanbanId) $query->where('id_kanban', $kanbanId);
        }

        $problems = $query->orderBy('created_at', 'asc')->get();

        if ($problems->isEmpty()) {
            return response()->json(['message' => 'No problems found for the selected criteria'], 404);
        }

        // Generate filename
        $timestamp = date('Ymd_His');
        $baseName = "Problem_List_" . ucfirst($type);
        if ($groupCode) {
            $baseName .= "_{$groupCode}";
        } elseif ($kanbanId) {
            $k = Kanban::find($kanbanId);
            $baseName .= "_" . ($k ? $k->kanban_name : $kanbanId);
        } elseif ($projectId) {
            $p = Project::find($projectId);
            $baseName .= "_" . ($p ? $p->project_name : $projectId);
        }
        $fileName = "{$baseName}_{$timestamp}.xlsx";

        return match (strtolower($type)) {
            'manufacturing' => $this->exportFormatManufacturing($problems, $fileName),
            'kentokai' => $this->exportFormatKentokai($problems, $fileName),
            'ks', 'kd', 'sk', 'buyoff' => $this->exportFormatCommon($problems, $fileName, $type),
            default => response()->json(['message' => "Export format for type '$type' not supported"], 400),
        };
    }

    private function downloadSpreadsheet($spreadsheet, $fileName)
    {
        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function exportFormatManufacturing($problems, $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Manufacturing List');

        $headerStyle = [
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'wrap_text' => true,
        ];
        $borderStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];
        $blueHeaderStyle = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0000FF']],
            'font' => ['color' => ['rgb' => 'FFFFFF'], 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $centerStyle = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $boldTextStyle = [
            'font' => ['bold' => true],
        ];

        $rankStyle = [
            'font' => ['bold' => true, 'size' => 36],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']],
        ];

        // For Manufacturing, we currently only support single problem export (Form format)
        // So we take the first item from the collection.
        $problem = $problems->first();
        if (!$problem) return null; // Should not happen if check is done before

        $spreadsheet->getDefaultStyle()->getFont()
            ->setName('Calibri')
            ->setSize(11);

        $sheet->getDefaultColumnDimension()->setWidth(2.71);
        // --- Logo & Title Header ---
        // Assuming TMMIN logo is text for now or simple placeholder
        $sheet->mergeCells('B1:E3');
        $sheet->setCellValue('B1', 'TMMIN'); // Placeholder for Logo
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(18)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('F1:Q1');
        $sheet->setCellValue('F1', 'Production Engineering & Tooling Div.');
        $sheet->mergeCells('F2:Q2');
        $sheet->setCellValue('F2', 'Dies & Jig Planning Control & Adm. Dept.');
        $sheet->mergeCells('F3:Q3');
        $sheet->setCellValue('F3', 'Dies Planning & Engineering Sec.');

        $sheet->mergeCells('R1:AJ3');
        $sheet->setCellValue('R1', 'Lembar Informasi Masalah Manufakturing');
        $sheet->getStyle('R1')->applyFromArray($headerStyle);

        // Date 
        $sheet->mergeCells('AK1:AN1');
        $sheet->setCellValue('AK1', 'Tanggal');
        $sheet->getStyle('AK1')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AK2:AN3');
        $sheet->setCellValue('AK2', $problem->created_at->format('d-M-Y'));
        $sheet->getStyle('AK2')->applyFromArray($centerStyle);

        // PROBLEM NO
        $sheet->mergeCells('AO1:AS1');
        $sheet->setCellValue('AO1', 'No Masalah');
        $sheet->getStyle('AO1')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AO2:AS3');
        $sheet->setCellValue('AO2', $problem->id_problem);
        $sheet->getStyle('AO2')->applyFromArray($centerStyle);


        // ================================================================ Info Section 1 (Kanban, Item, Project...) ================================================================

        // KANBAN
        $sheet->mergeCells('B5:G5');
        $sheet->setCellValue('B5', 'Kanban');
        $sheet->getStyle('B5')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('B6:G7');
        $sheet->setCellValue('B6', $problem->kanban?->kanban_name);
        $sheet->getStyle('B6')->applyFromArray($centerStyle);

        // ITEM
        $sheet->mergeCells('H5:L5');
        $sheet->setCellValue('H5', 'Item');
        $sheet->getStyle('H5')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('H6:L7');
        $sheet->setCellValue('H6', $problem->item?->item_name);
        $sheet->getStyle('H6')->applyFromArray($centerStyle);

        // Project Info Table (Proyek / Proses / Part)
        $sheet->mergeCells('M5:O5');
        $sheet->setCellValue('M5', 'Proyek');
        $sheet->mergeCells('P5:R5');
        $sheet->setCellValue('P5', $problem->project?->project_name);

        $sheet->mergeCells('M6:O6');
        $sheet->setCellValue('M6', 'Proses');
        $sheet->mergeCells('P6:R6');
        $sheet->setCellValue('P6', $problem->process?->process_name);

        $sheet->mergeCells('S5:W5');
        $sheet->setCellValue('S5', 'No Part');
        $sheet->mergeCells('X5:AB5');
        $sheet->setCellValue('X5', $problem->part?->part_number);
        $sheet->getStyle('X5')->applyFromArray($centerStyle);

        $sheet->mergeCells('S6:W6');
        $sheet->setCellValue('S6', 'Nama Proses');
        $sheet->mergeCells('X6:AB6');
        $sheet->setCellValue('X6', ' ');
        $sheet->getStyle('X6')->applyFromArray($centerStyle);

        $sheet->mergeCells('M7:R7');
        $sheet->setCellValue('M7', 'Nama Part');
        $sheet->mergeCells('S7:AB7');
        $sheet->setCellValue('S7', $problem->part?->part_name);



        // ================================================================ Signatures ================================================================

        // DPH ENG
        $sheet->mergeCells('AD5:AG5');
        $sheet->setCellValue('AD5', 'DpH Eng.');
        $sheet->getStyle('AD5')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AD6:AG7');
        $sheet->setCellValue('AD6', ''); // Static per template example
        $sheet->getStyle('AD6')->applyFromArray($centerStyle);
        $sheet->getStyle('AD6')->applyFromArray($boldTextStyle);

        // SH ENG   
        $sheet->mergeCells('AH5:AL5');
        $sheet->setCellValue('AH5', 'SH Eng.');
        $sheet->getStyle('AH5')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AH6:AL7');
        $sheet->setCellValue('AH6', ''); // Static
        $sheet->getStyle('AH6')->applyFromArray($centerStyle);
        $sheet->getStyle('AH6')->applyFromArray($boldTextStyle);

        // STAFF ENG
        $sheet->mergeCells('AM5:AS5');
        $sheet->setCellValue('AM5', 'Staff Engineering');
        $sheet->getStyle('AM5')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AM6:AS7');
        $sheet->getStyle('AM6')->applyFromArray($centerStyle);
        $sheet->getStyle('AM6')->applyFromArray($boldTextStyle);
        // SIGNATURES END

        // ================================================================ Problem & Cause Headers ================================================================

        // PROBLEM
        $sheet->mergeCells('B9:S9');
        $sheet->setCellValue('B9', 'Masalah');
        $sheet->getStyle('B9')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('B10:S11');
        $sheet->setCellValue('B10', $problem->problem);
        $sheet->getStyle('B10')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        // CAUSE
        $sheet->mergeCells('T9:AJ9');
        $sheet->setCellValue('T9', 'Penyebab Masalah');
        $sheet->getStyle('T9')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('T10:AJ11');
        $sheet->setCellValue('T10', $problem->cause);
        $sheet->getStyle('T10')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        // Image Area
        $sheet->mergeCells('B12:AJ28');
        $sheet->getStyle('B12')->applyFromArray($centerStyle);

        $hasDetailImage = false;
        $offsetX = 10;

        if ($problem->attachments && $problem->attachments->count() > 0) {
            foreach ($problem->attachments as $att) {
                $imagePath = storage_path('app/public/' . ltrim($att->file_path, '/'));
                if (file_exists($imagePath)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Attachment');
                    $drawing->setDescription('Problem Attachment');
                    $drawing->setPath($imagePath);
                    $drawing->setCoordinates('B12');
                    $drawing->setHeight(250);
                    $drawing->setOffsetX($offsetX);
                    $drawing->setOffsetY(10);
                    $drawing->setWorksheet($sheet);

                    $offsetX += 300;
                    $hasDetailImage = true;
                }
            }
        }
        if (!$hasDetailImage) {
            $sheet->setCellValue('B12', 'No Attachment');
        }
        // PROBLEM & CAUSE END

        // --- Right Side Panels (Location, Class, etc) ---
        // Location
        $sheet->mergeCells('AL9:AS9');
        $sheet->setCellValue('AL9', 'Lokasi');
        $sheet->getStyle('AL9')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AL10:AS11');
        $sheet->setCellValue('AL10', $problem->location?->location_name);
        $sheet->getStyle('AL10')->applyFromArray($centerStyle);
        $sheet->getStyle('AL10')->applyFromArray($boldTextStyle);

        // MACHINE
        $sheet->mergeCells('AL12:AO12');
        $sheet->setCellValue('AL12', 'Mesin');
        $sheet->getStyle('AL12')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AL13:AO14');
        $sheet->setCellValue('AL13', $problem->machine?->name_machine);
        $sheet->getStyle('AL13')->applyFromArray($centerStyle);
        $sheet->getStyle('AL13')->applyFromArray($boldTextStyle);

        // STAGE
        $sheet->mergeCells('AP12:AS12');
        $sheet->setCellValue('AP12', 'Stage');
        $sheet->getStyle('AP12')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AP13:AS14');
        $sheet->setCellValue('AP13', $problem->stage);
        $sheet->getStyle('AP13')->applyFromArray($centerStyle);
        $sheet->getStyle('AP13')->applyFromArray($boldTextStyle);

        // Classification
        $sheet->mergeCells('AL15:AO15');
        $sheet->setCellValue('AL15', 'Klasifikasi');
        $sheet->getStyle('AL15')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AL16:AO17');
        $sheet->setCellValue('AL16', $problem->classification); // Placeholder
        $sheet->getStyle('AL16')->applyFromArray($centerStyle);
        $sheet->getStyle('AP13')->applyFromArray($boldTextStyle);

        // TYPE SAIBO
        $sheet->mergeCells('AP15:AS15');
        $sheet->setCellValue('AP15', 'Tipe');
        $sheet->getStyle('AP15')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AP16:AS17');
        $sheet->setCellValue('AP16', $problem->type_saibo); // Placeholder
        $sheet->getStyle('AP16')->applyFromArray($centerStyle);
        $sheet->getStyle('AP13')->applyFromArray($boldTextStyle);

        // Pass Through
        $sheet->mergeCells('AL18:AS18');
        $sheet->setCellValue('AL18', 'Pass Through');
        $sheet->getStyle('AL18')->applyFromArray($blueHeaderStyle);

        // Manual mapping PASS TROUGH
        $sheet->mergeCells('AL19:AN19');
        $sheet->setCellValue('AL19', 'DF');
        $sheet->mergeCells('AP19:AR19');
        $sheet->setCellValue('AP19', 'PM');
        $sheet->mergeCells('AL20:AN20');
        $sheet->setCellValue('AL20', 'DD');
        $sheet->mergeCells('AP20:AR20');
        $sheet->setCellValue('AP20', 'MCH');
        $sheet->mergeCells('AL21:AN21');
        $sheet->setCellValue('AL21', 'CC');
        $sheet->mergeCells('AP21:AR21');
        $sheet->setCellValue('AP21', 'ASSY');
        $sheet->mergeCells('AL22:AN22');
        $sheet->setCellValue('AL22', 'TDA');
        $sheet->mergeCells('AP22:AR22');
        $sheet->setCellValue('AP22', 'TO');

        // Reject / Defect
        $sheet->mergeCells('AL23:AS23');
        $sheet->setCellValue('AL23', 'Reject / defect');
        $sheet->getStyle('AL23')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AL24:AN24');
        $sheet->setCellValue('AL24', 'Problem');
        $sheet->mergeCells('AO24:AS24');
        $sheet->setCellValue('AO24', 'Reject / defect');
        $sheet->getStyle('AO24')->applyFromArray($boldTextStyle);

        // Section In Charge
        $sheet->mergeCells('AL25:AS25');
        $sheet->setCellValue('AL25', 'Seksi In Charge');
        $sheet->getStyle('AL25')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AL26:AS28');
        $sheet->setCellValue('AL26', $problem->seksiInCharge?->location_name);
        $sheet->getStyle('AL26')->applyFromArray($centerStyle);
        $sheet->getStyle('AL26')->applyFromArray($boldTextStyle);

        // Kolom B
        $sheet->mergeCells('B30:AS30');
        $sheet->setCellValue('B30', 'Jadwal Tindakan Koreksi');
        $sheet->getStyle('B30')->applyFromArray($blueHeaderStyle);

        $sheet->mergeCells('B31:B33');
        $sheet->setCellValue('B31', 'NO');
        $sheet->getStyle('B31')->applyFromArray($centerStyle);
        $sheet->getStyle('B31')->applyFromArray($boldTextStyle);

        for ($i = 1; $i < 11; $i++) {
            $row = $i + 34;
            $sheet->setCellValue("B{$row}", $i);
        }

        // fill currative rows
        $sheet->mergeCells('C31:P33');
        $sheet->setCellValue('C31', 'Currative');
        $sheet->getStyle('C31')->applyFromArray($centerStyle);
        $sheet->getStyle('C31')->applyFromArray($boldTextStyle);

        // Curative Data (Rows 34-44)
        $curatives = $problem->curatives;
        for ($i = 0; $i < 11; $i++) {
            $row = 34 + $i;
            $sheet->mergeCells("C{$row}:P{$row}");
            if (isset($curatives[$i])) {
                $sheet->setCellValue("C{$row}", $curatives[$i]->curative);
            }
        }

        // PIC rows
        $sheet->mergeCells('Q31:U33');
        $sheet->setCellValue('Q31', 'PIC');
        $sheet->getStyle('Q31')->applyFromArray($centerStyle);
        $sheet->getStyle('Q31')->applyFromArray($boldTextStyle);

        // PIC Data (Rows 34-44)
        for ($i = 0; $i < 11; $i++) {
            $row = 34 + $i;
            $sheet->mergeCells("Q{$row}:U{$row}");
            if (isset($curatives[$i]) && $curatives[$i]->pic) {
                $sheet->setCellValue("Q{$row}", $curatives[$i]->pic->location_name);
            }
        }

        $sheet->mergeCells('V31:AK31');
        $sheet->setCellValue('V31', 'Tanggal');
        $sheet->getStyle('V31')->applyFromArray($centerStyle);
        $sheet->mergeCells('V32:Y32');
        $sheet->mergeCells('Z32:AC32');
        $sheet->mergeCells('AD32:AG32');
        $sheet->mergeCells('AH32:AK32');

        $sheet->mergeCells('V33:W33');
        $sheet->setCellValue('V33', 'Siang');
        $sheet->getStyle('V33')->applyFromArray($centerStyle);
        $sheet->mergeCells('X33:Y33');
        $sheet->setCellValue('X33', 'Malam');
        $sheet->getStyle('X33')->applyFromArray($centerStyle);
        $sheet->mergeCells('Z33:AA33');
        $sheet->setCellValue('Z33', 'Siang');
        $sheet->getStyle('Z33')->applyFromArray($centerStyle);
        $sheet->mergeCells('AB33:AC33');
        $sheet->setCellValue('AB33', 'Malam');
        $sheet->getStyle('AB33')->applyFromArray($centerStyle);
        $sheet->mergeCells('AD33:AE33');
        $sheet->setCellValue('AD33', 'Siang');
        $sheet->getStyle('AD33')->applyFromArray($centerStyle);
        $sheet->mergeCells('AF33:AG33');
        $sheet->setCellValue('AF33', 'Malam');
        $sheet->getStyle('AF33')->applyFromArray($centerStyle);
        $sheet->mergeCells('AH33:AI33');
        $sheet->setCellValue('AH33', 'Siang');
        $sheet->getStyle('AH33')->applyFromArray($centerStyle);
        $sheet->mergeCells('AJ33:AK33');
        $sheet->setCellValue('AJ33', 'Malam');
        $sheet->getStyle('AJ33')->applyFromArray($centerStyle);
        $sheet->mergeCells('V44:AK44');
        $sheet->setCellValue('V44', 'Total Cost & Cost Material');
        $sheet->getStyle('V44')->applyFromArray($centerStyle);


        $sheet->mergeCells('AL31:AN33');
        $sheet->setCellValue('AL31', 'Hour');
        $sheet->getStyle('AL31')->applyFromArray($centerStyle);
        $sheet->getStyle('AL31')->applyFromArray($boldTextStyle);

        for ($i = 0; $i < 11; $i++) {
            $row = 34 + $i;
            $sheet->mergeCells("AL{$row}:AN{$row}");
            if (isset($curatives[$i]) && $curatives[$i]->pic) {
                $sheet->setCellValue("AL{$row}", $curatives[$i]->hour);
                $sheet->getStyle("AL{$row}")->getNumberFormat()->setFormatCode('0.00');
            }
        }

        $sheet->mergeCells('AL44:AN44');
        $sheet->setCellValue('AL44', '=SUM(AL34:AL43)');

        $sheet->mergeCells('AO31:AS33');


        for ($i = 0; $i < 11; $i++) {
            $row = 34 + $i;
            $sheet->mergeCells("AO{$row}:AS{$row}");
            if (isset($curatives[$i]) && $curatives[$i]->pic) {
                $sheet->setCellValue("AO{$row}", $curatives[$i]->pic->rate * $curatives[$i]->hour);
                $sheet->getStyle("AO{$row}")->getNumberFormat()->setFormatCode('[$Rp-IdID] #,##0');
            }
        }

        $sheet->mergeCells('AO44:AS44');
        $sheet->setCellValue('AO44', '=SUM(AO34:AO43)');
        $sheet->getStyle('AO44')->getNumberFormat()->setFormatCode('[$Rp-IdID] #,##0');
        // End Kolom B

        // KOLOM C
        $sheet->mergeCells('B46:AB46');
        $sheet->setCellValue('B46', 'Analisa Sebab Akibat');
        $sheet->getStyle('B46')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('B46')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('B47:W66');
        $sheet->mergeCells('X47:AB59');

        $sheet->mergeCells('AC46:AS46');
        $sheet->setCellValue('AC46', 'Perbaikan');
        $sheet->getStyle('AC46')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('AC46')->applyFromArray($boldTextStyle);

        $sheet->mergeCells('AC47:AD47');
        $sheet->setCellValue('AC47', 'No');
        $sheet->getStyle('AC47')->applyFromArray($centerStyle);
        $sheet->getStyle('AC47')->applyFromArray($boldTextStyle);

        for ($i = 1; $i < 12; $i++) {
            $row = $i + 48;
            $sheet->mergeCells("AC{$row}:AD{$row}");
            $sheet->setCellValue("AC{$row}", $i);
        }

        // penanggulangan
        $sheet->mergeCells('AE47:AN47');
        $sheet->setCellValue('AE47', 'Penanggulangan');
        $sheet->getStyle('AE47')->applyFromArray($centerStyle);
        $sheet->getStyle('AE47')->applyFromArray($boldTextStyle);
        // Penanggulangan (Preventive) Data (Rows 48-59)
        $preventives = $problem->preventives;
        for ($i = 0; $i < 12; $i++) {
            $row = 48 + $i;
            $sheet->mergeCells("AE{$row}:AN{$row}");
            if (isset($preventives[$i])) {
                $sheet->setCellValue("AE{$row}", $preventives[$i]->preventive);
            }
        }


        // tanggal
        $sheet->mergeCells('AO47:AS47');
        $sheet->setCellValue('AO47', 'Tanggal');
        $sheet->getStyle('AO47')->applyFromArray($centerStyle);
        $sheet->getStyle('AO47')->applyFromArray($boldTextStyle);
        // End Kolom C

        for ($i = 0; $i < 12; $i++) {
            $row = 48 + $i;
            $sheet->mergeCells("AO{$row}:AS{$row}");
            if (isset($preventives[$i])) {
                $sheet->setCellValue("AO{$row}", $preventives[$i]->created_at ? $preventives[$i]->created_at->format('d-M-Y') : '');
            }
        }

        $sheet->mergeCells('Y61:AB61');
        $sheet->setCellValue('Y61', 'Rank');
        $sheet->getStyle('Y61')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('Y61')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('Y62:AB66');
        $sheet->setCellValue('Y62', '=IF(AH44>1000000,"A",IF(AH44>=500000,"B",IF(AH44>=300000,"C","D")))');
        $sheet->getStyle('Y62')->applyFromArray($rankStyle);

        // CONDITIONAL FORMATTING RANK
        $rankRange = 'Y62:AB66';
        $makeCond = function (string $val, string $rgb) {
            $cond = new Conditional();
            $cond->setConditionType(Conditional::CONDITION_CONTAINSTEXT);
            $cond->setOperatorType(Conditional::OPERATOR_CONTAINSTEXT);
            $cond->setText($val);

            $cond->getStyle()->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB($rgb);

            // optional: biar tulisan kebaca
            $cond->getStyle()->getFont()->setBold(true);

            return $cond;
        };

        $condA = $makeCond('A', 'FF0000'); // Merah
        $condB = $makeCond('B', '8B4513'); // Coklat
        $condC = $makeCond('C', 'FFFF00'); // Kuning
        $condD = $makeCond('D', '00B050'); // Hijau

        $sheet->getStyle($rankRange)->setConditionalStyles([$condA, $condB, $condC, $condD]);


        $sheet->mergeCells('AC61:AJ61');
        $sheet->setCellValue('AC61', 'Klasifikasi');
        $sheet->getStyle('AC61')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('AC61')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('AC62:AJ66');

        $sheet->mergeCells('AL61:AN61');
        $sheet->setCellValue('AL61', 'Approved');
        $sheet->getStyle('AL61')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('AL61')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('AL62:AN65'); //FIELD
        $sheet->mergeCells('AL66:AN66');
        $sheet->setCellValue('AL66', 'DpH');
        $sheet->getStyle('AL66')->applyFromArray($centerStyle);


        $sheet->mergeCells('AO61:AQ61');
        $sheet->setCellValue('AO61', 'Checked');
        $sheet->getStyle('AO61')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('AO61')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('AO62:AQ65');
        $sheet->mergeCells('AO66:AQ66');
        $sheet->setCellValue('AO66', 'SH');
        $sheet->getStyle('AO66')->applyFromArray($centerStyle);

        $sheet->mergeCells('AR61:AS61');
        $sheet->setCellValue('AR61', 'Prepared');
        $sheet->getStyle('AR61')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('AR61')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('AR62:AS65');
        $sheet->mergeCells('AR66:AS66');
        $sheet->getStyle('AR66')->applyFromArray($centerStyle);

        // Apply Borders
        $sheet->getStyle('AK1:AS3')->applyFromArray($borderStyle);
        $sheet->getStyle('B5:AB7')->applyFromArray($borderStyle);
        $sheet->getStyle('AD5:AS7')->applyFromArray($borderStyle);
        $sheet->getStyle('B9:AJ28')->applyFromArray($borderStyle);
        $sheet->getStyle('AL9:AS28')->applyFromArray($borderStyle);
        $sheet->getStyle('B30:AS44')->applyFromArray($borderStyle);
        $sheet->getStyle('B46:AS46')->applyFromArray($borderStyle);
        $sheet->getStyle('B47:W66')->applyFromArray($borderStyle);
        $sheet->getStyle('X47:AS59')->applyFromArray($borderStyle);
        $sheet->getStyle('Y61:AJ66')->applyFromArray($borderStyle);
        $sheet->getStyle('AL61:AS66')->applyFromArray($borderStyle);


        return $this->downloadSpreadsheet($spreadsheet, $fileName);
    }
    // ===================================================================================================== EXPORT FORMAT MANUFACTURING END  =====================================================================================================



    // ===================================================================================================== EXPORT FORMAT COMMON (KS, KD, SK, BUYOFF) =====================================================================================================
    private function exportFormatCommon($problems, $fileName, $type)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Issue List');
        $sheet->setShowGridlines(false);

        $typeUpper = strtoupper($type);
        $title = match (strtolower($type)) {
            'ks' => 'PROBLEM KATAKENSA STATIK',
            'kd' => 'PROBLEM KATAKENSA DINAMIK',
            'sk' => 'PROBLEM SEIZO KUNREN',
            'buyoff' => 'PROBLEM BUYOFF',
            default => "PROBLEM {$typeUpper}"
        };

        // =======================
        // Styles
        // =======================
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 22, 'fontname' => 'Berlin Sans FB Demi'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $logoStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ];


        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D6F4ED']],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];

        $cellBorder = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];

        $wrapTop = [
            'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
        ];

        $center = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $yellowHeader = [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF19B']],
            'font' => ['color' => ['rgb' => '000000'], 'bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $problem = $problems->first();
        if (!$problem) return null;

        // =======================
        // Column widths (approx)
        // =======================
        // MARGIN
        $sheet->getColumnDimension('B')->setWidth(3);  // NO
        $sheet->getColumnDimension('C')->setWidth(16);  // NAME
        $sheet->getColumnDimension('D')->setWidth(28);  // FIELD
        $sheet->getColumnDimension('E')->setWidth(3);  // FIELD
        $sheet->getColumnDimension('F')->setWidth(38);  // FIELD
        $sheet->getColumnDimension('G')->setWidth(17);  // FIELD  // TEMUAN
        foreach (range('H', 'I') as $c) $sheet->getColumnDimension($c)->setWidth(9);
        $sheet->getColumnDimension('J')->setWidth(28);  // COUNTERMEASURE
        $sheet->getColumnDimension('K')->setWidth(7);  // KETERANGAN/IMAGE
        $sheet->getRowDimension('10')->setRowHeight(30);  // KETERANGAN/IMAGE
        foreach (range('1', '9') as $row) $sheet->getRowDimension($row)->setRowHeight(22);

        $locationName = $problem->location?->location_name ?? '-';

        // =======================
        // Title / Top row
        // =======================
        $sheet->mergeCells('C1:D2');
        $sheet->setCellValue("C1", "PT. Toyota Motor Mfg. Indonesia 
        Die & Jig Creation Division");
        $sheet->getStyle('C1')->applyFromArray($logoStyle);

        $sheet->mergeCells('E1:H4');
        $sheet->setCellValue("E1", $title);   //TITLE 
        $sheet->getStyle('E1')->applyFromArray($titleStyle);


        // Optional small labels (checked/prepared) like template
        $sheet->setCellValue('C3', 'NO.FORM');
        $sheet->getStyle('C3')->applyFromArray($yellowHeader);
        $sheet->setCellValue('D3', $problem->group_code);
        $sheet->setCellValue('C4', 'DATE');
        $sheet->getStyle('C4')->applyFromArray($yellowHeader);
        $sheet->setCellValue('D4', $problem->created_at->format('d-M-Y'));
        $sheet->setCellValue('C5', 'KANBAN');
        $sheet->getStyle('C5')->applyFromArray($yellowHeader);
        $sheet->setCellValue('D5', $problem->kanban?->kanban_name);
        $sheet->setCellValue('C6', 'NO.PART');
        $sheet->getStyle('C6')->applyFromArray($yellowHeader);
        $sheet->setCellValue('D6', $problem->kanban->part_number ?? '-');
        $sheet->setCellValue('C7', 'PROCESS DIE');
        $sheet->getStyle('C7')->applyFromArray($yellowHeader);
        $sheet->setCellValue('D7', '-');
        $sheet->setCellValue('C8', 'PART NAME');
        $sheet->getStyle('C8')->applyFromArray($yellowHeader);
        $sheet->setCellValue('D8', $problem->kanban->part_name ?? '-');
        $sheet->setCellValue('C9', 'ENGINEER');
        $sheet->getStyle('C9')->applyFromArray($yellowHeader);
        $sheet->setCellValue('D9', '-');

        $sheet->mergeCells('E5:H9');
        $sheet->setCellValue('E5', 'SKETCH :');
        $sheet->getStyle('E5')->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setHorizontal(Alignment::HORIZONTAL_LEFT);

        $sheet->setCellValue('I2', 'DIE SIZE');
        $sheet->getStyle('I2')->applyFromArray($yellowHeader);
        $sheet->setCellValue('J2', '');
        $sheet->setCellValue('I3', 'WEIGHT');
        $sheet->getStyle('I3')->applyFromArray($yellowHeader);
        $sheet->setCellValue('J3', '');
        $sheet->setCellValue('I4', 'DH / SLD');
        $sheet->getStyle('I4')->applyFromArray($yellowHeader);
        $sheet->setCellValue('J4', '');
        $sheet->setCellValue('I5', 'TC / LM');
        $sheet->getStyle('I5')->applyFromArray($yellowHeader);
        $sheet->setCellValue('J5', '');


        // =======================
        // Table Headers
        // =======================
        $sheet->setCellValue('B10', 'NO');
        $sheet->mergeCells('C10:D10');
        $sheet->setCellValue('C10', 'PROBLEM');
        $sheet->getStyle('C10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('E10:F10');
        $sheet->setCellValue('E10', 'COUNTERMEASURE');
        $sheet->getStyle('E10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('G10', 'PIC.REPAIR');
        $sheet->getStyle('G10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('H10', 'PLAN');
        $sheet->getStyle('H10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->setCellValue('I10', 'ACTUAL');
        $sheet->getStyle('I10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->mergeCells('J10:K10');
        $sheet->setCellValue('J10', 'AFTER REPAIR');
        $sheet->getStyle('J10')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('B10:K10')->applyFromArray($headerStyle);

        // =======================
        // Helper: build 1 block per issue (8 rows tall)
        // =======================
        $startRow = 11;
        $blockHeight = 10; // rows 7-14, 15-22, dst.

        foreach ($problems as $i => $p) {
            $r1 = $startRow + ($i * $blockHeight);
            $r2 = $r1 + ($blockHeight - 1);

            $rowProblem = $r1 + 1;
            $imageStart = $r1 + 2;
            $imageEnd = $r2;


            // Merge layout (mirip template)
            $sheet->mergeCells("A{$r1}:A{$r2}");         // MARGIN
            $sheet->mergeCells("B{$r1}:B{$r2}");              // NO
            $sheet->mergeCells("C{$r1}:D{$rowProblem}");        // PROBLEM
            $sheet->mergeCells("C{$imageStart}:D{$imageEnd}");

            // Merge Image area J-K
            $sheet->mergeCells("J{$r1}:K{$r2}");

            // Row heights biar blok keliatan lega
            for ($rr = $r1; $rr <= $r2; $rr++) {
                $sheet->getRowDimension($rr)->setRowHeight(18);
            }

            // Fill values
            $sheet->setCellValue("B{$r1}", $i + 1);

            // Problem
            $sheet->setCellValue("C{$r1}", $p->problem ?? '-');
            $sheet->getStyle("C{$r1}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_CENTER)->setHorizontal(Alignment::HORIZONTAL_LEFT);

            // Curatives
            $curatives = $p->curatives;
            if ($curatives->count() > 0) {
                foreach ($curatives as $idx => $curative) {
                    if ($idx < $blockHeight) {
                        $row = $r1 + $idx;
                        $sheet->setCellValue("E{$row}", $idx + 1);
                        $sheet->setCellValue("F{$row}", $curative->curative);
                        $sheet->setCellValue("G{$row}", $curative->pic?->location_name ?? '-');
                    }
                }
            }

            // Styles
            $sheet->getStyle("C3:D9")->applyFromArray($cellBorder);
            $sheet->getStyle("E3:H9")->applyFromArray($cellBorder);
            $sheet->getStyle("I2:J5")->applyFromArray($cellBorder);
            $sheet->getStyle("B{$r1}:K{$r2}")->applyFromArray($cellBorder);
            $sheet->getStyle("B{$r1}:I{$r2}")->applyFromArray($center);
            $sheet->getStyle("C{$r1}:I{$r2}")->applyFromArray($wrapTop);

            // Embed image into J block (AFTER REPAIR)
            $hasImage = false;
            $offsetX = 5;
            $imageAnchor = "C{$imageStart}";

            // Priority: Attachments > Single Attachment > Null
            $attachments = $p->attachments;
            if ($attachments->count() > 0) {
                foreach ($attachments as $att) {
                    $imagePath = storage_path('app/public/' . ltrim($att->file_path, '/'));
                    // Ensure file exists and is an image
                    if (file_exists($imagePath) && @getimagesize($imagePath)) {
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setName('Attachment');
                        $drawing->setDescription('Problem Attachment');
                        $drawing->setPath($imagePath);
                        $drawing->setCoordinates($imageAnchor);
                        $drawing->setHeight(90); // Fit in block (18*5 = 90)
                        $drawing->setOffsetX($offsetX);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($sheet);

                        $offsetX += 120; // Shift right if multiple (though merged cell is small)
                        $hasImage = true;
                        // Limit to 1 image for now to fit nicely? Or just let them stack.
                        break; // Only show 1 image for now in the block
                    }
                }
            }

            if (!$hasImage) {
                $sheet->setCellValue($imageAnchor, "No Attachment");
            }
        }

        // Freeze header
        $sheet->freezePane('A11');

        // Stream download
        return $this->downloadSpreadsheet($spreadsheet, $fileName);
    }



    private function exportFormatKentokai($problems, $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Issue List');

        // =======================
        // Styles
        // =======================
        $titleStyle = [
            'font' => ['bold' => true, 'size' => 16],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        $logoStyle = [
            'font' => ['bold' => true, 'size' => 10],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true, // Menambahkan wrap text
            ],
        ];


        $headerStyle = [
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID],
            'font' => ['color' => ['rgb' => '000000'], 'bold' => true],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];

        $cellBorder = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ];

        $wrapTop = [
            'alignment' => ['wrapText' => true, 'vertical' => Alignment::VERTICAL_TOP],
        ];

        $center = [
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];

        // status color (opsional)
        $statusFill = function (?string $status) {
            if (!$status) return null;
            $s = strtolower(trim($status));
            return match (true) {
                in_array($s, ['open', 'baru', 'new', 'dispatched']) => 'FF0000',
                in_array($s, ['progress', 'on progress', 'in progress', 'process']) => 'FFC000',
                in_array($s, ['close', 'closed', 'done', 'finish', 'selesai']) => '00B050',
                default => '9E9E9E',
            };
        };

        // =======================
        // Column widths (approx)
        // =======================
        $sheet->getColumnDimension('A')->setWidth(5);   // NO
        $sheet->getColumnDimension('B')->setWidth(18);  // KANBAN/ITEM
        foreach (range('C', 'G') as $c) $sheet->getColumnDimension($c)->setWidth(4);
        $sheet->getColumnDimension('H')->setWidth(12);  // DATE
        $sheet->getColumnDimension('I')->setWidth(4);
        $sheet->getColumnDimension('J')->setWidth(45);  // TEMUAN
        foreach (range('K', 'O') as $c) $sheet->getColumnDimension($c)->setWidth(6);
        $sheet->getColumnDimension('P')->setWidth(35);  // COUNTERMEASURE
        foreach (range('Q', 'V') as $c) $sheet->getColumnDimension($c)->setWidth(6);
        $sheet->getColumnDimension('W')->setWidth(20);  // KETERANGAN/IMAGE
        foreach (range('X', 'AA') as $c) $sheet->getColumnDimension($c)->setWidth(6);

        $locationName = $p->location?->location_name ?? '-';
        // =======================
        // Title / Top row
        // =======================

        $sheet->mergeCells('A1:E4');
        $sheet->setCellValue("A1", "PT TOYOTA MOTOR MFG INDONESIA PRESS & TOOL ENG.DIV LINE : {$locationName}");
        $sheet->getStyle('A1')->applyFromArray($logoStyle);

        $sheet->mergeCells('F1:S4');
        $sheet->setCellValue("F1", "LIST TEMUAN KENTOKAI {$locationName}");   //TITLE 
        $sheet->getStyle('F1')->applyFromArray($titleStyle);

        // Optional small labels (checked/prepared) like template
        $sheet->mergeCells('T1:W1');     //checked label
        $sheet->setCellValue('T1', 'Checked');
        $sheet->mergeCells('T2:W3');
        $sheet->mergeCells('T4:W4');

        $sheet->mergeCells('X1:AA1');     //prepared label
        $sheet->setCellValue('X1', 'Prepared');
        $sheet->mergeCells('X2:AA3');
        $sheet->mergeCells('X4:AA4');

        //style lable
        $sheet->getStyle('T1')->applyFromArray(['font' => ['bold' => true]]);
        $sheet->getStyle('X1')->applyFromArray(['font' => ['bold' => true]]);

        // =======================
        // Table Headers
        // =======================
        $sheet->setCellValue('A5', 'NO');
        $sheet->mergeCells('B5:S5');
        $sheet->setCellValue('B5', 'ITEM TEMUAN');
        $sheet->getStyle('B5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('T5:V6');
        $sheet->setCellValue('T5', 'EVALUASI');
        $sheet->mergeCells('W5:AA6');
        $sheet->setCellValue('W5', 'KETERANGAN');


        $sheet->mergeCells('B6:G6');
        $sheet->setCellValue('B6', 'KANBAN DAN ITEM');
        $sheet->mergeCells('H6:I6');
        $sheet->setCellValue('H6', 'DATE');
        $sheet->mergeCells('J6:O6');
        $sheet->setCellValue('J6', 'TEMUAN');
        $sheet->mergeCells('P6:S6'); // Fix merge range P-S based on request "P-S: CONTERMEASURE"
        $sheet->setCellValue('P6', 'CONTERMEASURE');
        // Wait, user request said "P-S: CONTERMEASURE" but layout seems to use more cols?
        // User request: "P-S: CONTERMEASURE (merge 8 row, wrap top)"
        // But in column width logic I used P (35) and Q-V (6).
        // Let's stick to user request P-S.
        // Wait, T-V is EVALUASI. So P-S is indeed correct for Countermeasure.

        $sheet->getStyle('A5:AA6')->applyFromArray($headerStyle);

        // =======================
        // Helper: build 1 block per issue (8 rows tall)
        // =======================
        $startRow = 7;
        $blockHeight = 8; // rows 7-14, 15-22, dst.

        foreach ($problems as $i => $p) {
            $r1 = $startRow + ($i * $blockHeight);
            $r2 = $r1 + ($blockHeight - 1);

            // Merge layout (mirip template)
            $sheet->mergeCells("A{$r1}:A{$r2}");         // NO
            $sheet->mergeCells("B{$r1}:G{$r2}");         // KANBAN/ITEM
            $sheet->mergeCells("H{$r1}:I{$r2}");         // DATE
            $sheet->mergeCells("J{$r1}:O{$r2}");         // TEMUAN
            $sheet->mergeCells("P{$r1}:S{$r2}");         // COUNTERMEASURE (P-S)
            $sheet->mergeCells("T{$r1}:V{$r2}");         // EVALUASI (T-V)
            $sheet->mergeCells("W{$r1}:AA{$r2}");        // KETERANGAN + IMAGE (W-AA)

            // Row heights biar blok keliatan lega
            for ($rr = $r1; $rr <= $r2; $rr++) {
                $sheet->getRowDimension($rr)->setRowHeight(18);
            }
            // Biar area gambar lebih “tinggi”
            // Adjusting middle rows to make space for image ~120
            // 8 rows * 18 = 144px. So default is enough if image is ~120.
            // But let's follow logic to ensure it fits well.

            // Fill values
            $sheet->setCellValue("A{$r1}", $i + 1);

            // Fix KANBAN/ITEM String Issue
            $kanbanName = $p->kanban?->kanban_name ?? '-';
            // Check if item is an object (relation) or string
            $itemName = '-';
            if ($p->item) {
                if (is_string($p->item)) {
                    $itemName = $p->item;
                } elseif (is_object($p->item)) {
                    // Try common fields
                    $itemName = $p->item->item_name ?? $p->item->name ?? $p->item->code ?? '-';
                }
            }
            $sheet->setCellValue("B{$r1}", "{$kanbanName} / {$itemName}");

            $sheet->setCellValue("H{$r1}", optional($p->created_at)->format('d-m-Y'));
            $sheet->setCellValue("J{$r1}", $p->problem ?? '');
            $sheet->setCellValue("P{$r1}", $p->curatives->pluck('curative')->implode("\n"));
            $sheet->setCellValue("T{$r1}", strtoupper($p->status ?? ''));
            $sheet->setCellValue("W{$r1}", $p->note ?? '');

            // Styles
            $sheet->getStyle("A{$r1}:AA{$r2}")->applyFromArray($cellBorder);
            $sheet->getStyle("A{$r1}:I{$r2}")->applyFromArray($center);
            $sheet->getStyle("J{$r1}:S{$r2}")->applyFromArray($wrapTop); // TEMUAN & CM wrap top
            $sheet->getStyle("T{$r1}:V{$r2}")->applyFromArray($center);
            $sheet->getStyle("W{$r1}:AA{$r2}")->applyFromArray($wrapTop);

            // Status fill
            $fill = $statusFill($p->status ?? null);
            if ($fill) {
                $sheet->getStyle("T{$r1}:V{$r2}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB($fill);
                $sheet->getStyle("T{$r1}:V{$r2}")
                    ->getFont()
                    ->setBold(true)
                    ->getColor()
                    ->setRGB('FFFFFF');
            }

            // Embed image into W block (kalau ada)
            $hasImage = false;
            $offsetX = 5;

            if ($p->attachments && $p->attachments->count() > 0) {
                foreach ($p->attachments as $att) {
                    $imagePath = storage_path('app/public/' . ltrim($att->file_path, '/'));
                    if (file_exists($imagePath)) {
                        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                        $drawing->setName('Attachment');
                        $drawing->setDescription('Problem Attachment');
                        $drawing->setPath($imagePath);
                        $drawing->setCoordinates("W{$r1}");
                        $drawing->setHeight(120);
                        $drawing->setOffsetX($offsetX);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($sheet);

                        $offsetX += 150; // Shift right
                        $hasImage = true;
                    }
                }
            }

            // No legacy fallback; rely solely on attachments relation

            if (!$hasImage) {
                $sheet->setCellValue("W{$r1}", "No Attachment");
            }
        }

        // Freeze header
        $sheet->freezePane('A7');

        // Stream download
        return $this->downloadSpreadsheet($spreadsheet, $fileName);
    }
}
