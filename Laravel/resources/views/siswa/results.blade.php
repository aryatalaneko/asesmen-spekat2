@extends('layouts.app')
@section('header_title','Riwayat Nilai')
@section('content')

<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:1rem 1.5rem;border-bottom:1px solid #e5e7eb;font-weight:700;">🎯 Riwayat Ujian Saya</div>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#</th><th>Mata Pelajaran</th><th>Tanggal</th>
                    <th>Nilai PG</th><th>Nilai Essay</th><th>Nilai Akhir</th><th>Status</th><th>Detail</th>
                </tr>
            </thead>
            <tbody>
            @forelse($results as $i => $r)
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td><strong>{{ $r->schedule->subject->name ?? '-' }}</strong></td>
                    <td style="font-size:0.85rem;">{{ $r->created_at->format('d M Y') }}</td>
                    <td>{{ round($r->pg_score) }}</td>
                    <td>{{ round($r->essay_score) }}</td>
                    <td>
                        <span style="font-size:1.1rem;font-weight:800;color:{{ $r->status==='lulus' ? '#16a34a' : '#dc2626' }};">
                            {{ round($r->final_score) }}
                        </span>
                    </td>
                    <td>
                        <span style="padding:0.25rem 0.75rem;border-radius:999px;font-size:0.8rem;font-weight:700;
                            background:{{ $r->status==='lulus' ? '#f0fdf4' : '#fef2f2' }};
                            color:{{ $r->status==='lulus' ? '#16a34a' : '#dc2626' }};">
                            {{ $r->status === 'lulus' ? '✅ Lulus' : '❌ Tidak Lulus' }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('siswa.results.show', $r->id) }}" class="btn btn-primary" style="padding:0.25rem 0.75rem;font-size:0.8rem;">Detail</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:#aaa;">Belum ada riwayat ujian.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
