@extends('layouts.app')
@section('header_title','Data Mata Pelajaran')
@section('content')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div class="card">
        <h3 style="font-weight:700;margin-bottom:1rem;">📚 Daftar Mata Pelajaran</h3>
        <table>
            <thead><tr><th>#</th><th>Nama</th><th>Jumlah Soal</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($subjects as $i => $s)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $s->name }}</td>
                    <td><span style="background:#eff6ff;color:#1d4ed8;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.8rem;font-weight:600;">{{ $s->questions_count }} soal</span></td>
                    <td>
                        <form method="POST" action="{{ route('admin.subjects.destroy', $s) }}" onsubmit="return confirm('Hapus mata pelajaran ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger" style="padding:0.2rem 0.6rem;font-size:0.8rem;">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" style="text-align:center;color:#aaa;">Belum ada mata pelajaran.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card">
        <h3 style="font-weight:700;margin-bottom:1rem;">➕ Tambah Mata Pelajaran</h3>
        <form method="POST" action="{{ route('admin.subjects.store') }}">
            @csrf
            <div class="input-group">
                <label class="input-label">Nama Mata Pelajaran</label>
                <input type="text" name="name" class="form-control" placeholder="Contoh: Matematika" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem;">Simpan</button>
        </form>
    </div>
</div>
@endsection
