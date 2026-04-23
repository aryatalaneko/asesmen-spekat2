<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherClass;
use App\Models\User;
use App\Models\ClassRoom;
use App\Models\Subject;

class AdminMappingController extends Controller
{
    public function index()
    {
        $mappings = TeacherClass::with(['teacher', 'classRoom', 'subject'])->get();
        $gurus    = User::where('role', 'guru')->orderBy('name')->get();
        $classes  = ClassRoom::orderBy('name')->get();
        $subjects = Subject::orderBy('name')->get();
        return view('admin.mappings', compact('mappings', 'gurus', 'classes', 'subjects'), [
            'title' => 'Penugasan Guru'
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|exists:users,id',
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        TeacherClass::firstOrCreate([
            'user_id'    => $request->user_id,
            'class_id'   => $request->class_id,
            'subject_id' => $request->subject_id,
        ]);

        return back()->with('success', 'Penugasan berhasil disimpan.');
    }

    public function destroy(TeacherClass $mapping)
    {
        $mapping->delete();
        return back()->with('success', 'Penugasan berhasil dihapus.');
    }
}
