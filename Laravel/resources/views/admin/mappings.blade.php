@extends('layouts.app')
@section('header_title','Penugasan Guru → Kelas')
@section('content')
<div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;">
    <div class="card">
        <h3 style="font-weight:700;margin-bottom:1rem;">🔗 Daftar Penugasan Aktif</h3>
        <table>
            <thead><tr><th>#</th><th>Guru</th><th>Kelas</th><th>Mata Pelajaran</th><th>Aksi</th></tr></thead>
            <tbody>
            @forelse($mappings as $i => $m)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td>{{ $m->teacher->name ?? '-' }}</td>
                    <td><span style="background:#f0fdf4;color:#16a34a;padding:0.2rem 0.6rem;border-radius:6px;font-weight:600;font-size:0.85rem;">{{ $m->classRoom->name ?? '-' }}</span></td>
                    <td>{{ $m->subject->name ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.mappings.destroy', $m) }}" onsubmit="return confirm('Cabut penugasan ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger" style="padding:0.2rem 0.6rem;font-size:0.8rem;">Cabut</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#aaa;">Belum ada penugasan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card">
        <h3 style="font-weight:700;margin-bottom:1rem;">➕ Tugaskan Guru ke Kelas</h3>
        <form method="POST" action="{{ route('admin.mappings.store') }}">
            @csrf
            <div class="input-group">
                <label class="input-label">Guru</label>
                <select name="user_id" class="form-control" required>
                    <option value="">Pilih guru...</option>
                    @foreach($gurus as $g)
                        <option value="{{ $g->id }}">{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group">
                <label class="input-label">Kelas</label>
                <select name="class_id" class="form-control" required>
                    <option value="">Pilih kelas...</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="input-group">
                <label class="input-label">Mata Pelajaran</label>
                <select name="subject_id" class="form-control" required>
                    <option value="">Pilih mata pelajaran...</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Simpan Penugasan</button>
        </form>
    </div>
</div>
@endsection
