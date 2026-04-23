<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;

class AdminSubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::withCount('questions')->orderBy('name')->get();
        return view('admin.subjects', ['subjects' => $subjects, 'title' => 'Data Mata Pelajaran']);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:subjects,name']);
        
        $code = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $request->name), 0, 3)) . rand(100, 999);
        
        Subject::create([
            'name' => $request->name,
            'code' => $code
        ]);
        
        return back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();
        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
