<?php

namespace App\Http\Controllers;

use App\Models\Problem;
use App\Models\Project;
use App\Models\Kanban;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

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
            ->with(['project', 'kanban', 'location', 'reporter'])
            ->orderBy('id_problem', 'desc');
        if ($request->filled('project_id')) $q->where('id_project', $request->integer('project_id'));
        if ($request->filled('kanban_id')) $q->where('id_kanban', $request->integer('kanban_id'));
        if ($request->filled('type')) $q->where('type', $request->string('type'));

        return response()->json($q->get()->map(function ($p) {
            return [
                'id_problem' => $p->id_problem,
                'created_at' => $p->created_at,
                'project' => $p->project?->project_name,
                'kanban' => $p->kanban?->kanban_name,
                'item' => $p->item,
                'location' => $p->location?->location_name,
                'type' => $p->type === 'manufacturing' ? 'Manufacturing' : strtoupper($p->type),
                'problem' => $p->problem,
                'cause' => $p->cause,
                'curative' => $p->curative,
                'attachment' => $p->attacment,
                'status' => $p->status ?? 'dispatched', // Fallback for existing null records
                'reporter' => $p->reporter?->fullname ?? $p->reporter?->username,
            ];
        }));
    }

    public function store(Request $request)
    {
        $rules = [
            'item' => 'required|string|max:100',
            'id_location' => 'required|integer|exists:locations,id_location',
            'type' => 'required|in:manufacturing,ks,kd,sk',
            'problem' => 'required|string',
            'cause' => 'nullable|string',
            'curative' => 'nullable|string',
            'attachment' => 'nullable|file|image|max:4096',
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

        $validated = $request->validate($rules);

        // Handle Project Creation
        $projectId = $request->input('id_project');
        if ($request->filled('new_project_name')) {
            $project = Project::create([
                'project_name' => $request->input('new_project_name'),
                'description' => 'Created via Problem'
            ]);
            $projectId = $project->id_project;
        }

        // Handle Kanban Creation
        $kanbanId = $request->input('id_kanban');
        if ($request->filled('new_kanban_name')) {
            $kanban = Kanban::create([
                'project_id' => $projectId,
                'kanban_name' => $request->input('new_kanban_name')
            ]);
            $kanbanId = $kanban->id_kanban;
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('attachments', 'public');
        }

        $problem = Problem::create([
            'id_project' => $projectId,
            'id_kanban' => $kanbanId,
            'item' => $validated['item'],
            'id_location' => $validated['id_location'],
            'type' => $validated['type'],
            'problem' => $validated['problem'],
            'cause' => $validated['cause'] ?? '',
            'curative' => $validated['curative'] ?? '',
            'attacment' => $path,
            'id_user' => Auth::user()->id_user,
        ]);
        return response()->json(['success' => true, 'data' => $problem]);
    }

    public function destroy(int $id)
    {
        $p = Problem::findOrFail($id);
        if ($p->attacment) {
            try {
                Storage::disk('public')->delete($p->attacment);
            } catch (\Throwable $e) {
            }
        }
        $p->delete();
        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:dispatched,in_progress,closed'
        ]);

        $p = Problem::findOrFail($id);
        $p->status = $request->status;
        $p->save();

        return response()->json(['success' => true]);
    }

    public function export(int $id)
    {
        try {
            $problem = Problem::with(['project', 'kanban', 'location', 'reporter'])->findOrFail($id);

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // --- Styles ---
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

            $lastColumn = Coordinate::stringFromColumnIndex(1);


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
            // $sheet->setCellValue('K6', 'Process Name'); // Placeholder
            $sheet->mergeCells('J7:M7');
            $sheet->setCellValue('J7', 'Nama Part');
            // $sheet->setCellValue('K7', 'Part Name'); // Placeholder

            $sheet->setCellValue('N5', 'No Part');
            $sheet->mergeCells('O5:R5');
            // $sheet->setCellValue('O5', 'Part No'); // Placeholder
            $sheet->setCellValue('N6', 'Nama Proses');
            $sheet->mergeCells('O6:R6');
            // $sheet->setCellValue('O6', 'Process Name'); // Placeholder
            $sheet->mergeCells('N7:R7');
            // $sheet->setCellValue('O7', 'PANEL FR FENDER R/L'); // Placeholder

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

            if ($problem->attacment) {
                $imagePath = storage_path('app/public/' . $problem->attacment);
                if (file_exists($imagePath)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Attachment');
                    $drawing->setDescription('Problem Attachment');
                    $drawing->setPath($imagePath);
                    $drawing->setCoordinates('B12');
                    // Optional: limit size to fit roughly in the box
                    $drawing->setHeight(300);
                    $drawing->setWorksheet($sheet);
                } else {
                    $sheet->setCellValue('B12', 'Image file not found');
                }
            } else {
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

            $passThrough = ['DF', 'PM', 'DD', 'MCH', 'CC', 'ASSY', 'DBSCA', 'TO'];
            $row = 19;
            $col = 31; // Column AE (index 31 in 1-based? No, A=1. AE=31)
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


            $writer = new Xlsx($spreadsheet);
            $fileName = 'problem_' . $problem->project?->project_name . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . urlencode($fileName) . '"');
            $writer->save('php://output');
            exit;
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
