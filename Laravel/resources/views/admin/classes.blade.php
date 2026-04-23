@extends('layouts.app')
@section('header_title','Data Kelas')
@section('content')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div class="card">
        <h3 style="font-weight:700;margin-bottom:1rem;">🏫 Daftar Kelas</h3>
        <table>
            <thead><tr><th>#</th><th>Nama Kelas</th><th>Level</th><th>Jumlah Siswa</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($classes as $i => $c)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td><strong>{{ $c->name }}</strong></td>
                    <td>{{ $c->level ?? '-' }}</td>
                    <td>{{ $c->students_count }} siswa</td>
                    <td>
                        <form method="POST" action="{{ route('admin.classes.destroy', $c) }}" onsubmit="return confirm('Hapus kelas ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger" style="padding:0.2rem 0.6rem;font-size:0.8rem;">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#aaa;">Belum ada kelas.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card">
        <h3 style="font-weight:700;margin-bottom:1rem;">➕ Tambah Kelas Baru</h3>
        <form method="POST" action="{{ route('admin.classes.store') }}">
            @csrf
            <div class="input-group">
                <label class="input-label">Nama Kelas</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: IX-A" required>
            </div>
            <div class="input-group">
                <label class="input-label">Level (Opsional)</label>
                <input type="text" name="level" class="form-control" placeholder="Contoh: IX">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Simpan</button>
        </form>
    </div>
</div>
@endsection
