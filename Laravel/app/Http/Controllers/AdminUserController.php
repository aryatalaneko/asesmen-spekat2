<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Subject;
use App\Models\TeacherClass;
use App\Imports\StudentImport;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminUserController extends Controller
{
    public function index()
    {
        // Load teachers with their mappings to display in table
        $gurus   = User::where('role', 'guru')->with(['teacherClasses.subject', 'teacherClasses.classRoom'])->orderBy('name')->get();
        $siswas  = User::where('role', 'siswa')->with('classRoom')->orderBy('name')->get();
        $classes = ClassRoom::orderBy('name')->get();
        $subjects= Subject::orderBy('name')->get();
        $title   = 'Manajemen Pengguna';
        return view('admin.users', compact('gurus', 'siswas', 'classes', 'subjects', 'title'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:100',
            'password' => 'required|string|min:6',
            'role'     => 'required|in:guru,siswa',
            
            // Validation for Siswa
            'class_id' => 'nullable|exists:classes,id',

            // Validation for Guru mappings
            'mappings' => 'nullable|array',
        ]);

        $user = User::where('name', $request->name)->where('role', $request->role)->first();

        if (!$user) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => strtolower(str_replace(' ', '.', $request->name)) . rand(100,999) . '@stjohanis.edu',
                'password' => Hash::make($request->password),
                'role'     => $request->role,
                'class_id' => $request->role === 'siswa' ? $request->class_id : null,
            ]);
        } else {
            $user->update([
                'password' => Hash::make($request->password),
                'class_id' => $request->role === 'siswa' ? $request->class_id : null,
            ]);
        }

        // Assigment mapping if Guru
        if ($request->role === 'guru' && $request->has('mappings')) {
            foreach ($request->mappings as $map) {
                if (empty($map['subject_id']) || empty($map['classes'])) continue;
                foreach ($map['classes'] as $cid) {
                    TeacherClass::firstOrCreate([
                        'user_id'    => $user->id,
                        'class_id'   => $cid,
                        'subject_id' => $map['subject_id'],
                    ]);
                }
            }
        }

        return back()->with('success', 'Pengguna/Penugasan berhasil disimpan.');
    }

    public function destroy(User $user)
    {
        if ($user->role === 'admin') {
            return back()->with('error', 'Tidak dapat menghapus akun Admin.');
        }
        $user->delete();
        return back()->with('success', 'Pengguna berhasil dihapus.');
    }

    /**
     * Bulk delete: hapus banyak user sekaligus berdasarkan array ID.
     * Dipakai oleh fitur checkbox di halaman Manajemen Pengguna.
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:users,id',
        ]);

        // Pastikan tidak menghapus akun admin
        $deleted = User::whereIn('id', $request->ids)
            ->where('role', '!=', 'admin')
            ->delete();

        if ($deleted === 0) {
            return back()->with('error', 'Tidak ada pengguna yang dihapus. Akun Admin tidak dapat dihapus.');
        }

        return back()->with('success', "✅ {$deleted} akun berhasil dihapus.");
    }

    /**
     * Import siswa dari file Excel.
     * Format kolom: Nama | NIS | Kelas
     */
    public function import(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:5120', // Maks 5MB
        ], [
            'excel_file.required' => 'Pilih file Excel terlebih dahulu.',
            'excel_file.mimes'    => 'Format file harus .xlsx, .xls, atau .csv.',
            'excel_file.max'      => 'Ukuran file maksimal 5MB.',
        ]);

        try {
            $import = new StudentImport();
            Excel::import($import, $request->file('excel_file'));

            return back()->with('success', 'Import siswa dari Excel berhasil diselesaikan!');

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $msgs = [];
            foreach ($failures as $failure) {
                $msgs[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            return back()->with('error', 'Import gagal karena validasi data yang fatal. Mohon cek format Excel Anda.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses file: ' . $e->getMessage());
        }
    }

    /**
     * Unduh template Excel kosong dengan format yang benar.
     */
    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Siswa');

        // ── Header kolom ──────────────────────────────────────────────
        $sheet->setCellValue('A1', 'Nama');
        $sheet->setCellValue('B1', 'NIS');
        $sheet->setCellValue('C1', 'Kelas');

        // ── Style header: bold + background biru ──────────────────────
        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        // ── Lebar kolom otomatis ──────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(30); // Nama
        $sheet->getColumnDimension('B')->setWidth(20); // NIS
        $sheet->getColumnDimension('C')->setWidth(15); // Kelas

        // ── Baris contoh data ─────────────────────────────────────────
        $sheet->setCellValue('A2', 'Contoh: Michael Jordan');
        $sheet->setCellValue('B2', '20240001');
        $sheet->setCellValue('C2', 'IX-A');
        $sheet->getStyle('A2:C2')->getFont()->getColor()->setRGB('9CA3AF'); // abu-abu
        $sheet->getStyle('A2:C2')->getFont()->setItalic(true);

        // ── Catatan di bawah ─────────────────────────────────────────
        $sheet->setCellValue('A4', '⚠ Catatan:');
        $sheet->setCellValue('A5', '- Hapus baris contoh sebelum import.');
        $sheet->setCellValue('A6', '- Kolom "Kelas" harus sama persis dengan nama kelas di sistem (contoh: IX-A, VIII-B).');
        $sheet->setCellValue('A7', '- NIS akan dijadikan password login siswa.');
        $sheet->mergeCells('A4:C4');
        $sheet->mergeCells('A5:C5');
        $sheet->mergeCells('A6:C6');
        $sheet->mergeCells('A7:C7');

        $writer   = new Xlsx($spreadsheet);
        $fileName = 'template_import_siswa.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Cetak Kartu Peserta Ujian (ID Card) untuk semua siswa.
     */
    public function printCards()
    {
        $students = User::where('role', 'siswa')
            ->with('classRoom')
            ->orderBy('name')
            ->get();

        return view('admin.print-cards', compact('students'), [
            'title' => 'Cetak Kartu Peserta'
        ]);
    }
}
