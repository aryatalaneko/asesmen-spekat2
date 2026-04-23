<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Question;
use App\Models\Subject;
use Illuminate\Support\Facades\Auth;
use App\Imports\QuestionImport;
use App\Exports\QuestionTemplateExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $teacherClasses = Auth::user()->teacherClasses()->with(['subject', 'classRoom'])->get();

        // Jika Guru belum diset sama sekali.
        if ($teacherClasses->isEmpty()) {
            return view('guru.questions_context', compact('teacherClasses'), [
                'title' => 'Pilih Konteks Bank Soal'
            ]);
        }

        $classId = $request->query('class_id');
        $subjectId = $request->query('subject_id');

        // Jika belum ada konteks dipilih dan guru punya lebih dari 1 assignment, 
        // arahkan ke tampilan pemilih context. Atau jika kita bisa gunakan dropdown.
        // Mari kita buat selalu membutuhkan konteks jika > 1, tapi default ke row pertama jika cuman 1.
        if (!$classId || !$subjectId) {
            if ($teacherClasses->count() === 1) {
                $classId = $teacherClasses->first()->class_id;
                $subjectId = $teacherClasses->first()->subject_id;
            } else {
                return view('guru.questions_context', compact('teacherClasses'), [
                    'title' => 'Pilih Konteks Bank Soal'
                ]);
            }
        }

        // Validasi kepemilikan konteks
        $isOwned = $teacherClasses->where('class_id', $classId)->where('subject_id', $subjectId)->isNotEmpty();
        if (!$isOwned) {
            return redirect()->route('guru.questions.index')->with('error', 'Akses ke kelas/mapel ditolak.');
        }

        $currentMap = $teacherClasses->where('class_id', $classId)->where('subject_id', $subjectId)->first();

        $questions = Question::with(['subject', 'classRoom'])
            ->where('subject_id', $subjectId)
            ->where(function($q) use ($classId) {
                $q->whereNull('class_id')->orWhere('class_id', $classId);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Kelas lain yang diajar guru dengan mapel yang sama (untuk fitur salin soal)
        $otherClasses = $teacherClasses
            ->where('subject_id', $subjectId)
            ->where('class_id', '!=', $classId)
            ->values();

        return view('guru.questions', compact('questions', 'currentMap', 'subjectId', 'classId', 'otherClasses'), [
            'title' => 'Bank Soal'
        ]);

    }

    public function store(Request $request)
    {
        $rules = [
            'subject_id'    => 'required|exists:subjects,id',
            'class_id'      => 'nullable|exists:classes,id',
            'type'          => 'required|in:pg,essay',
            'weight'        => 'required|numeric|min:1|max:100',
            'question_text' => 'required|string',
        ];

        if ($request->type === 'pg') {
            $rules['option_a']       = 'required|string';
            $rules['option_b']       = 'required|string';
            $rules['option_c']       = 'required|string';
            $rules['option_d']       = 'required|string';
            $rules['option_e']       = 'nullable|string';  // Opsi E opsional (SMA)
            $rules['correct_option'] = 'required|in:a,b,c,d,e'; // Diperluas ke E
        } else {
            $rules['essay_key'] = 'required|string';
        }

        $request->validate($rules);

        Question::create([
            'subject_id'     => $request->subject_id,
            'class_id'       => $request->class_id,
            'user_id'        => Auth::id(),
            'type'           => $request->type,
            'weight'         => $request->weight,
            'question_text'  => $request->question_text,
            'option_a'       => $request->option_a,
            'option_b'       => $request->option_b,
            'option_c'       => $request->option_c,
            'option_d'       => $request->option_d,
            'option_e'       => $request->option_e ?: null, // null jika kosong
            'correct_option' => $request->correct_option,
            'essay_key'      => $request->essay_key,
        ]);

        return back()->with('success', 'Soal berhasil ditambahkan ke bank soal.');
    }

    public function destroy(Question $question)
    {
        $question->delete();
        return back()->with('success', 'Soal berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:questions,id'
        ]);

        $count = count($request->ids);
        Question::whereIn('id', $request->ids)->delete();

        return back()->with('success', "{$count} soal berhasil dihapus.");
    }

    /**
     * Import soal dari file Excel atau CSV.
     * Format yang diterima: xlsx, xls, csv
     */
    public function importQuestions(Request $request)
    {
        $request->validate([
            'excel_soal'  => 'required|file|mimes:xlsx,xls,csv',
            'subject_id'  => 'required|exists:subjects,id',
            'class_id'    => 'required|exists:classes,id',
        ]);

        try {
            // Catat jumlah soal sebelum import untuk menghitung yang baru masuk
            $countBefore = Question::where('subject_id', $request->subject_id)
                                   ->where('class_id', $request->class_id)
                                   ->count();

            Excel::import(
                new QuestionImport((int) $request->subject_id, (int) $request->class_id),
                $request->file('excel_soal')
            );

            $countAfter = Question::where('subject_id', $request->subject_id)
                                  ->where('class_id', $request->class_id)
                                  ->count();

            $added = $countAfter - $countBefore;

            if ($added === 0) {
                return back()->with('error',
                    'File berhasil dibaca, tetapi tidak ada soal yang ditambahkan. '
                    . 'Pastikan format file sesuai template (kolom Tipe harus berisi "pg" atau "essay", Bobot harus angka).'
                );
            }

            return back()->with('success', "✅ {$added} soal berhasil diimport ke bank soal!");

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return back()->with('error', 'Import gagal: format data tidak valid. Periksa kolom Excel Anda.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }

    /**
     * Download template Excel (.xlsx) untuk panduan import soal.
     * Menggunakan file xlsx asli agar kolom terbaca dengan benar saat diupload kembali.
     */
    public function downloadQuestionTemplate()
    {
        return Excel::download(
            new QuestionTemplateExport(),
            'template_soal.xlsx'
        );
    }
    /**
     * Salin soal yang dipilih (bulk) ke kelas lain.
     * Guru memilih soal via checkbox → pilih kelas tujuan → soal diduplikasi.
     */
    public function copyToClass(Request $request)
    {
        $request->validate([
            'ids'            => 'required|array|min:1',
            'ids.*'          => 'integer|exists:questions,id',
            'target_class_id'=> 'required|integer|exists:classes,id',
            'subject_id'     => 'required|integer|exists:subjects,id',
        ]);

        $guru = Auth::user();

        // Pastikan guru memiliki akses ke kelas tujuan untuk mapel ini
        $hasAccess = $guru->teacherClasses()
            ->where('class_id',   $request->target_class_id)
            ->where('subject_id', $request->subject_id)
            ->exists();

        if (!$hasAccess) {
            return back()->with('error', 'Anda tidak memiliki akses ke kelas tujuan untuk mata pelajaran ini.');
        }

        $questions = Question::whereIn('id', $request->ids)->get();
        $copied    = 0;
        $skipped   = 0;

        foreach ($questions as $q) {
            // Cek apakah soal yang persis sama sudah ada di kelas tujuan
            $exists = Question::where('subject_id',    $request->subject_id)
                ->where('class_id',      $request->target_class_id)
                ->where('question_text', $q->question_text)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            Question::create([
                'subject_id'     => $request->subject_id,
                'class_id'       => $request->target_class_id,
                'user_id'        => $guru->id,
                'type'           => $q->type,
                'weight'         => $q->weight,
                'question_text'  => $q->question_text,
                'option_a'       => $q->option_a,
                'option_b'       => $q->option_b,
                'option_c'       => $q->option_c,
                'option_d'       => $q->option_d,
                'option_e'       => $q->option_e,   // salin opsi E jika ada
                'correct_option' => $q->correct_option,
                'essay_key'      => $q->essay_key,
            ]);
            $copied++;
        }

        $msg = "✅ {$copied} soal berhasil disalin ke kelas tujuan.";
        if ($skipped > 0) {
            $msg .= " ({$skipped} soal dilewati karena sudah ada di kelas tujuan)";
        }

        return back()->with('success', $msg);
    }
}
