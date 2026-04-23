<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassRoom;

class AdminClassController extends Controller
{
    public function index()
    {
        $classes = ClassRoom::withCount('students')->orderBy('name')->get();
        return view('admin.classes', ['classes' => $classes, 'title' => 'Data Kelas']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|string|max:50|unique:classes,name',
            'level' => 'nullable|string|max:20',
        ]);
        ClassRoom::create($request->only('name', 'level'));
        return back()->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function destroy(ClassRoom $adminClass)
    {
        $adminClass->delete();
        return back()->with('success', 'Kelas berhasil dihapus.');
    }
}
