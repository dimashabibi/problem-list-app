<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Project;
use App\Models\Kanban;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

    public function list(Request $request)
    {
        $q = Problem::query()
            ->with(['project', 'kanban', 'location', 'reporter', 'item', 'attachments'])
            ->orderBy('id_problem', 'desc');
        if ($request->filled('item_id')) $q->where('id_item', $request->integer('item_id'));
        if ($request->filled('project_id')) $q->where('id_project', $request->integer('project_id'));
        if ($request->filled('kanban_id')) $q->where('id_kanban', $request->integer('kanban_id'));
        if ($request->filled('type')) $q->where('type', $request->string('type'));

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
                'curative' => $p->curative,
                'attachment' => $p->attachment,
                'attachments' => $p->attachments->map(fn($a) => $a->file_path)->toArray(),
                'status' => $p->status ?? 'dispatched', // Fallback for existing null records
                'reporter' => $p->reporter?->fullname ?? $p->reporter?->username,
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

    public function store(Request $request)
    {
        $rules = [
            'id_location' => 'required|integer|exists:locations,id_location',
            'type' => 'required|in:manufacturing,ks,kd,sk,kentokai,buyoff',
            'problem' => 'required|string',
            'cause' => 'nullable|string',
            'curative' => 'nullable|string',
            'attachment' => 'nullable|array',
            'attachment.*' => 'image|max:4096',
            'group_code' => 'required|string|max:100',
            'group_code_mode' => 'required|in:existing,new',
            'group_code_existing' => 'nullable|string|max:100|required_if:group_code_mode,existing',
            'group_code_suffix' => 'nullable|string|max:100|required_if:group_code_mode,new',
        ];

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
                if (!$mainAttachmentPath) {
                    $mainAttachmentPath = $path;
                }
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
            'problem' => $request->input('problem'),
            'cause' => $request->input('cause'),
            'curative' => $request->input('curative'),
            'attachment' => $mainAttachmentPath,
            'status' => 'dispatched',
            'id_user' => Auth::id() ?? 1,
            'group_code' => $groupCode,
            'group_code_norm' => $groupCodeNorm,
        ]);

        $problem = Problem::create($problemData);

        foreach ($attachmentPaths as $path) {
            \App\Models\ProblemAttachment::create([
                'problem_id' => $problem->id_problem,
                'file_path' => $path
            ]);
        }

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
            'problem' => 'required|string',
            'cause' => 'nullable|string',
            'curative' => 'nullable|string',
        ];

        $validated = $request->validate($rules);

        $problem->update([
            'id_project' => $request->input('id_project'),
            'id_kanban' => $request->input('id_kanban'),
            'id_item' => $request->input('id_item'),
            'id_location' => $request->input('id_location'),
            'type' => $request->input('type'),
            'problem' => $request->input('problem'),
            'cause' => $request->input('cause'),
            'curative' => $request->input('curative'),
        ]);

        return response()->json(['success' => true]);
    }

    public function destroy(int $id)
    {
        $p = Problem::with('attachments')->findOrFail($id);

        // Delete main attachment if exists (though it should be in attachments table too if created new)
        // But for old records, we check attachment column
        if ($p->attachment) {
            try {
                Storage::disk('public')->delete($p->attachment);
            } catch (\Throwable $e) {
            }
        }

        // Delete all related attachments
        foreach ($p->attachments as $attachment) {
            try {
                // Check if it is different from main attachment to avoid double delete attempt? 
                // Storage delete doesn't throw if file missing usually, or we catch it.
                if ($attachment->file_path !== $p->attachment) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
            } catch (\Throwable $e) {
            }
        }

        $p->delete(); // Cascade deletes attachment records from DB
        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, int $id)
    {
        // Validasi status yang dikirimkan
        $request->validate([
            'status' => 'required|in:dispatched,in_progress,closed'
        ]);

        // Menemukan problem berdasarkan ID
        $problem = Problem::findOrFail($id);

        // Mengubah status sesuai dengan request
        $problem->status = $request->status;
        $problem->save();

        // Mengembalikan response sukses
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

        $query = Problem::with(['project', 'kanban', 'location', 'reporter', 'item', 'attachments'])
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
            'ks' => $this->exportFormatKs($problems, $fileName),
            'kd' => $this->exportFormatKd($problems, $fileName),
            'sk' => $this->exportFormatSk($problems, $fileName),
            'buyoff' => $this->exportFormatBuyoff($problems, $fileName),
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

        foreach ($problems as $problem);

        // --- Logo & Title Header ---
        // Assuming TMMIN logo is text for now or simple placeholder
        $sheet->mergeCells('B1:D3');
        $sheet->setCellValue('B1', 'TMMIN'); // Placeholder for Logo
        $sheet->getStyle('B1')->getFont()->setBold(true)->setSize(24)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED));
        $sheet->getStyle('B1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->mergeCells('E1:L1');
        $sheet->setCellValue('E1', 'Production Engineering & Tooling Div.');
        $sheet->mergeCells('E2:L2');
        $sheet->setCellValue('E2', 'Dies & Jig Planning Control & Adm. Dept.');
        $sheet->mergeCells('E3:L3');
        $sheet->setCellValue('E3', 'Dies Planning & Engineering Sec.');

        $sheet->mergeCells('M1:AC3');
        $sheet->setCellValue('M1', 'Lembar Informasi Masalah Manufakturing');
        $sheet->getStyle('M1')->applyFromArray($headerStyle);

        // Date & Problem No
        $sheet->mergeCells('AE1:AG1');
        $sheet->setCellValue('AE1', 'Tanggal');
        $sheet->getStyle('AE1')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AH1:AI1');
        $sheet->setCellValue('AH1', 'No Masalah');
        $sheet->getStyle('AH1')->applyFromArray($blueHeaderStyle);

        $sheet->mergeCells('AE2:AG3');
        $sheet->setCellValue('AE2', $problem->created_at->format('d-M-Y'));
        $sheet->getStyle('AE2')->applyFromArray($centerStyle);
        $sheet->mergeCells('AH2:AI3');
        $sheet->setCellValue('AH2', $problem->id_problem);
        $sheet->getStyle('AH2')->applyFromArray($centerStyle);

        // --- Info Section 1 (Kanban, Item, Project...) ---
        $sheet->mergeCells('B5:D5');
        $sheet->setCellValue('B5', 'Kanban');
        $sheet->getStyle('B5')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('E5:I5');
        $sheet->setCellValue('E5', 'Item');
        $sheet->getStyle('E5')->applyFromArray($blueHeaderStyle);

        $sheet->mergeCells('B6:D7');
        $sheet->setCellValue('B6', $problem->kanban?->kanban_name);
        $sheet->getStyle('B6')->applyFromArray($centerStyle);
        $sheet->mergeCells('E6:I7');
        $sheet->setCellValue('E6', $problem->item);
        $sheet->getStyle('E6')->applyFromArray($centerStyle);

        // Project Info Table
        $sheet->setCellValue('J5', 'Proyek');
        $sheet->mergeCells('K5:M5');
        $sheet->setCellValue('K5', $problem->project?->project_name);
        $sheet->setCellValue('J6', 'Proses');
        $sheet->mergeCells('K6:M6');
        $sheet->mergeCells('J7:M7');
        $sheet->setCellValue('J7', 'Nama Part');

        $sheet->setCellValue('N5', 'No Part');
        $sheet->mergeCells('O5:R5');
        $sheet->setCellValue('N6', 'Nama Proses');
        $sheet->mergeCells('O6:R6');
        $sheet->mergeCells('N7:R7');

        // --- Signatures ---
        $sheet->mergeCells('S5:U5');
        $sheet->setCellValue('S5', 'DpH Eng.');
        $sheet->getStyle('S5')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('V5:X5');
        $sheet->setCellValue('V5', 'SH Eng.');
        $sheet->getStyle('V5')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('Y5:AC5');
        $sheet->setCellValue('Y5', 'Staff Engineering');
        $sheet->getStyle('Y5')->applyFromArray($blueHeaderStyle);

        $sheet->mergeCells('S6:U7');
        $sheet->setCellValue('S6', 'Daniel W'); // Static per template example
        $sheet->getStyle('S6')->applyFromArray($centerStyle);
        $sheet->getStyle('S6')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('V6:X7');
        $sheet->setCellValue('V6', 'Mamat R'); // Static
        $sheet->getStyle('V6')->applyFromArray($centerStyle);
        $sheet->getStyle('V6')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('Y6:AC7');
        $sheet->getStyle('Y6')->applyFromArray($centerStyle);
        $sheet->getStyle('Y6')->applyFromArray($boldTextStyle);

        // --- Problem & Cause Headers ---
        $sheet->mergeCells('B9:O9');
        $sheet->setCellValue('B9', 'Masalah');
        $sheet->getStyle('B9')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('P9:AC9');
        $sheet->setCellValue('P9', 'Penyebab Masalah');
        $sheet->getStyle('P9')->applyFromArray($blueHeaderStyle);

        // --- Content Areas ---
        // Masalah
        $sheet->mergeCells('B10:O11');
        $sheet->setCellValue('B10', $problem->problem);
        $sheet->getStyle('B10')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        // Penyebab
        $sheet->mergeCells('P10:AC11');
        $sheet->setCellValue('P10', $problem->cause);
        $sheet->getStyle('P10')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

        // Image Area
        $sheet->mergeCells('B12:AC28');
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

        if (!$hasDetailImage && $problem->attachment) {
            $imagePath = storage_path('app/public/' . $problem->attachment);
            if (file_exists($imagePath)) {
                $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                $drawing->setName('Attachment');
                $drawing->setDescription('Problem Attachment');
                $drawing->setPath($imagePath);
                $drawing->setCoordinates('B12');
                $drawing->setHeight(250);
                $drawing->setOffsetX(10);
                $drawing->setOffsetY(10);
                $drawing->setWorksheet($sheet);
                $hasDetailImage = true;
            }
        }

        if (!$hasDetailImage) {
            $sheet->setCellValue('B12', 'No Attachment');
        }

        // --- Right Side Panels (Location, Class, etc) ---
        // Location
        $sheet->mergeCells('AE9:AI9');
        $sheet->setCellValue('AE9', 'Lokasi');
        $sheet->getStyle('AE9')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AE10:AI11');
        $sheet->setCellValue('AE10', $problem->location?->location_name);
        $sheet->getStyle('AE10')->applyFromArray($centerStyle);
        $sheet->getStyle('AE10')->applyFromArray($boldTextStyle);

        $sheet->mergeCells('AE12:AF12');
        $sheet->setCellValue('AE12', 'Mesin');
        $sheet->getStyle('AE12')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AG12:AI12');
        $sheet->setCellValue('AG12', 'Stage');
        $sheet->getStyle('AG12')->applyFromArray($blueHeaderStyle);

        $sheet->mergeCells('AE13:AF14');
        $sheet->mergeCells('AG13:AI14');
        $sheet->setCellValue('AG13', $problem->type);
        $sheet->getStyle('AG13')->applyFromArray($centerStyle);
        $sheet->getStyle('AG13')->applyFromArray($boldTextStyle);

        // Classification
        $sheet->mergeCells('AE15:AF15');
        $sheet->setCellValue('AE15', 'Klasifikasi');
        $sheet->getStyle('AE15')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AG15:AI15');
        $sheet->setCellValue('AG15', 'Tipe');
        $sheet->getStyle('AG15')->applyFromArray($blueHeaderStyle);

        $sheet->mergeCells('AE16:AF17');
        $sheet->setCellValue('AE16', 'Konst.'); // Placeholder
        $sheet->getStyle('AE16')->applyFromArray($centerStyle);
        $sheet->mergeCells('AG16:AI17');
        $sheet->setCellValue('AG16', 'Baru'); // Placeholder
        $sheet->getStyle('AG16')->applyFromArray($centerStyle);

        // Pass Through
        $sheet->mergeCells('AE18:AI18');
        $sheet->setCellValue('AE18', 'Pass Through');
        $sheet->getStyle('AE18')->applyFromArray($blueHeaderStyle);

        // Manual mapping
        $sheet->setCellValue('AE19', 'DF');
        $sheet->setCellValue('AG19', 'PM');
        $sheet->setCellValue('AE20', 'DD');
        $sheet->setCellValue('AG20', 'MCH');
        $sheet->setCellValue('AE21', 'CC');
        $sheet->setCellValue('AG21', 'ASSY');
        $sheet->setCellValue('AE22', 'DBSCA');
        $sheet->setCellValue('AG22', 'TO');

        // Reject / Defect
        $sheet->mergeCells('AE23:AI23');
        $sheet->setCellValue('AE23', 'Reject / defect');
        $sheet->getStyle('AE23')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AE24:AF24');
        $sheet->setCellValue('AE24', 'Problem');
        $sheet->mergeCells('AG24:AI24');
        $sheet->setCellValue('AG24', 'Reject / defect');
        $sheet->getStyle('AG24')->applyFromArray($boldTextStyle);

        // Section In Charge
        $sheet->mergeCells('AE25:AI25');
        $sheet->setCellValue('AE25', 'Seksi In Charge');
        $sheet->getStyle('AE25')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('AE26:AI28');
        $sheet->getStyle('AE26')->applyFromArray($centerStyle);
        $sheet->getStyle('AE26')->applyFromArray($boldTextStyle);

        // Kolom B
        $sheet->mergeCells('B30:AI30');
        $sheet->setCellValue('B30', 'Jadwal Tindakan Koreksi');
        $sheet->getStyle('B30')->applyFromArray($blueHeaderStyle);
        $sheet->mergeCells('B31:B33');
        $sheet->setCellValue('B31', 'NO');
        $sheet->getStyle('B31')->applyFromArray($centerStyle);
        $sheet->getStyle('B31')->applyFromArray($boldTextStyle);

        // fill currative rows
        $sheet->mergeCells('C31:P33');
        $sheet->setCellValue('C31', 'Currative');
        $sheet->getStyle('C31')->applyFromArray($centerStyle);
        $sheet->getStyle('C31')->applyFromArray($boldTextStyle);

        $sheet->mergeCells('C34:P34');
        $sheet->mergeCells('C35:P35');
        $sheet->mergeCells('C36:P36');
        $sheet->mergeCells('C37:P37');
        $sheet->mergeCells('C38:P38');
        $sheet->mergeCells('C39:P39');
        $sheet->mergeCells('C40:P40');
        $sheet->mergeCells('C41:P41');
        $sheet->mergeCells('C42:P42');
        $sheet->mergeCells('C43:P43');
        $sheet->mergeCells('C44:P44');

        // PIC rows
        $sheet->mergeCells('Q31:T33');
        $sheet->setCellValue('Q31', 'PIC');
        $sheet->getStyle('Q31')->applyFromArray($centerStyle);
        $sheet->getStyle('Q31')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('Q34:T34');
        $sheet->mergeCells('Q35:T35');
        $sheet->mergeCells('Q36:T36');
        $sheet->mergeCells('Q37:T37');
        $sheet->mergeCells('Q38:T38');
        $sheet->mergeCells('Q39:T39');
        $sheet->mergeCells('Q40:T40');
        $sheet->mergeCells('Q41:T41');
        $sheet->mergeCells('Q42:T42');
        $sheet->mergeCells('Q43:T43');
        $sheet->mergeCells('Q44:T44');

        $sheet->mergeCells('U31:AF32');
        $sheet->setCellValue('U31', 'Tanggal');
        $sheet->getStyle('U31')->applyFromArray($centerStyle);
        $sheet->mergeCells('U33:V33');
        $sheet->setCellValue('U33', 'Siang');
        $sheet->getStyle('U33')->applyFromArray($centerStyle);
        $sheet->mergeCells('W33:X33');
        $sheet->setCellValue('W33', 'Malam');
        $sheet->getStyle('W33')->applyFromArray($centerStyle);
        $sheet->mergeCells('Y33:Z33');
        $sheet->setCellValue('Y33', 'Siang');
        $sheet->getStyle('Y33')->applyFromArray($centerStyle);
        $sheet->mergeCells('AA33:AB33');
        $sheet->setCellValue('AA33', 'Malam');
        $sheet->getStyle('AA33')->applyFromArray($centerStyle);
        $sheet->mergeCells('AC33:AD33');
        $sheet->setCellValue('AC33', 'Siang');
        $sheet->getStyle('AC33')->applyFromArray($centerStyle);
        $sheet->mergeCells('AE33:AF33');
        $sheet->setCellValue('AE33', 'Malam');
        $sheet->getStyle('AE33')->applyFromArray($centerStyle);
        $sheet->mergeCells('U44:AF44');
        $sheet->setCellValue('U44', 'Total Cost & Cost Material');
        $sheet->getStyle('U44')->applyFromArray($centerStyle);


        $sheet->mergeCells('AG31:AG33');
        $sheet->setCellValue('AG31', 'Hour');
        $sheet->getStyle('AG31')->applyFromArray($centerStyle);
        $sheet->getStyle('AG31')->applyFromArray($boldTextStyle);

        $sheet->mergeCells('AH31:AI33');
        $sheet->mergeCells('AH34:AI34');
        $sheet->mergeCells('AH35:AI35');
        $sheet->mergeCells('AH36:AI36');
        $sheet->mergeCells('AH37:AI37');
        $sheet->mergeCells('AH38:AI38');
        $sheet->mergeCells('AH39:AI39');
        $sheet->mergeCells('AH40:AI40');
        $sheet->mergeCells('AH41:AI41');
        $sheet->mergeCells('AH42:AI42');
        $sheet->mergeCells('AH43:AI43');
        $sheet->mergeCells('AH44:AI44');
        // End Kolom B

        // KOLOM C
        $sheet->mergeCells('B46:T46');
        $sheet->setCellValue('B46', 'Analisa Sebab Akibat');
        $sheet->getStyle('B46')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('B46')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('B47:Q66');
        $sheet->mergeCells('R47:T59');

        $sheet->mergeCells('U46:AI46');
        $sheet->setCellValue('U46', 'Perbaikan');
        $sheet->getStyle('U46')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('U46')->applyFromArray($boldTextStyle);

        $sheet->mergeCells('U47:V47');
        $sheet->setCellValue('U47', 'No');
        $sheet->getStyle('U47')->applyFromArray($centerStyle);
        $sheet->getStyle('U47')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('U48:V48');
        $sheet->mergeCells('U49:V49');
        $sheet->mergeCells('U50:V50');
        $sheet->mergeCells('U51:V51');
        $sheet->mergeCells('U52:V52');
        $sheet->mergeCells('U53:V53');
        $sheet->mergeCells('U54:V54');
        $sheet->mergeCells('U55:V55');
        $sheet->mergeCells('U56:V56');
        $sheet->mergeCells('U57:V57');
        $sheet->mergeCells('U58:V58');
        $sheet->mergeCells('U59:V59');

        // penanggulangan
        $sheet->mergeCells('W47:AG47');
        $sheet->setCellValue('W47', 'Penanggulangan');
        $sheet->getStyle('W47')->applyFromArray($centerStyle);
        $sheet->getStyle('W47')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('W48:AG48');
        $sheet->mergeCells('W49:AG49');
        $sheet->mergeCells('W50:AG50');
        $sheet->mergeCells('W51:AG51');
        $sheet->mergeCells('W52:AG52');
        $sheet->mergeCells('W53:AG53');
        $sheet->mergeCells('W54:AG54');
        $sheet->mergeCells('W55:AG55');
        $sheet->mergeCells('W56:AG56');
        $sheet->mergeCells('W57:AG57');
        $sheet->mergeCells('W58:AG58');
        $sheet->mergeCells('W59:AG59');

        // tanggal
        $sheet->mergeCells('AH47:AI47');
        $sheet->setCellValue('AH47', 'Tanggal');
        $sheet->getStyle('AH47')->applyFromArray($centerStyle);
        $sheet->getStyle('AH47')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('AH48:AI48');
        $sheet->mergeCells('AH49:AI49');
        $sheet->mergeCells('AH50:AI50');
        $sheet->mergeCells('AH51:AI51');
        $sheet->mergeCells('AH52:AI52');
        $sheet->mergeCells('AH53:AI53');
        $sheet->mergeCells('AH54:AI54');
        $sheet->mergeCells('AH55:AI55');
        $sheet->mergeCells('AH56:AI56');
        $sheet->mergeCells('AH57:AI57');
        $sheet->mergeCells('AH58:AI58');
        $sheet->mergeCells('AH59:AI59');

        $sheet->mergeCells('S61:U61');
        $sheet->setCellValue('S61', 'Rank');
        $sheet->getStyle('S61')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('S61')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('S62:U66');

        $sheet->mergeCells('V61:Y61');
        $sheet->setCellValue('V61', 'Klasifikasi');
        $sheet->getStyle('V61')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('V61')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('V62:Y66');

        $sheet->mergeCells('AA61:AC61');
        $sheet->setCellValue('AA61', 'Approved');
        $sheet->getStyle('AA61')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('AA61')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('AA62:AC65');
        $sheet->mergeCells('AA66:AC66');
        $sheet->setCellValue('AA66', 'DpH');
        $sheet->getStyle('AA66')->applyFromArray($centerStyle);


        $sheet->mergeCells('AD61:AF61');
        $sheet->setCellValue('AD61', 'Checked');
        $sheet->getStyle('AD61')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('AD61')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('AD62:AF65');
        $sheet->mergeCells('AD66:AF66');
        $sheet->setCellValue('AD66', 'SH');
        $sheet->getStyle('AD66')->applyFromArray($centerStyle);

        $sheet->mergeCells('AG61:AI61');
        $sheet->setCellValue('AG61', 'Prepared');
        $sheet->getStyle('AG61')->applyFromArray($blueHeaderStyle);
        $sheet->getStyle('AG61')->applyFromArray($boldTextStyle);
        $sheet->mergeCells('AG62:AI65');
        $sheet->mergeCells('AG66:AI66');
        $sheet->getStyle('AI66')->applyFromArray($centerStyle);

        // Apply Borders
        $sheet->getStyle('B1:AC3')->applyFromArray($borderStyle);
        $sheet->getStyle('AE1:AI3')->applyFromArray($borderStyle);
        $sheet->getStyle('B5:AC7')->applyFromArray($borderStyle);
        $sheet->getStyle('B9:AC28')->applyFromArray($borderStyle);
        $sheet->getStyle('AE9:AI28')->applyFromArray($borderStyle);
        $sheet->getStyle('B30:AI44')->applyFromArray($borderStyle);
        $sheet->getStyle('B46:Q66')->applyFromArray($borderStyle);
        $sheet->getStyle('R46:AI59')->applyFromArray($borderStyle);
        $sheet->getStyle('S61:Y66')->applyFromArray($borderStyle);
        $sheet->getStyle('AA61:AI66')->applyFromArray($borderStyle);


        return $this->downloadSpreadsheet($spreadsheet, $fileName);
    }



    private function exportFormatKs($problems, $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('KS List');
        $sheet->setCellValue('A1', 'KS List (Format Pending)');
        return $this->downloadSpreadsheet($spreadsheet, $fileName);
    }

    private function exportFormatKd($problems, $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('KD List');
        $sheet->setCellValue('A1', 'KD List (Format Pending)');
        return $this->downloadSpreadsheet($spreadsheet, $fileName);
    }

    private function exportFormatSk($problems, $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SK List');
        $sheet->setCellValue('A1', 'SK List (Format Pending)');
        return $this->downloadSpreadsheet($spreadsheet, $fileName);
    }

    private function exportFormatBuyoff($problems, $fileName)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Buyoff List');
        $sheet->setCellValue('A1', 'Buyoff List (Format Pending)');
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
            $sheet->setCellValue("P{$r1}", $p->curative ?? '');
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

            // Fallback legacy
            if (!$hasImage && !empty($p->attachment)) {
                // Ensure path is correct. $p->attachment is relative to public disk, e.g. "attachments/xyz.jpg"
                $imagePath = storage_path('app/public/' . ltrim($p->attachment, '/'));

                if (file_exists($imagePath)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Attachment');
                    $drawing->setDescription('Problem Attachment');
                    $drawing->setPath($imagePath);
                    $drawing->setCoordinates("W{$r1}");
                    $drawing->setHeight(120);
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(5);
                    $drawing->setWorksheet($sheet);
                    $hasImage = true;
                }
            }

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
