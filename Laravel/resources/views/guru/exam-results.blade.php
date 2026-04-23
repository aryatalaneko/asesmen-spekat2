@extends('layouts.app')
@section('header_title', 'Rekap Nilai — ' . ($schedule->subject->name ?? 'Ujian'))
@section('content')

@php
    $kkm        = $schedule->kkm ?? 75;
    $total      = $results->count();
    $lulus      = $results->where('status', 'lulus')->count();
    $tidakLulus = $total - $lulus;
    $avgScore   = $total > 0 ? round($results->avg('final_score'), 1) : 0;
    $highest    = $total > 0 ? round($results->max('final_score'), 1) : 0;
    $lowest     = $total > 0 ? round($results->min('final_score'), 1) : 0;
@endphp

{{-- ===== HEADER UJIAN ===== --}}
<div style="background:linear-gradient(135deg,#1e3a8a,#1d4ed8);padding:1.25rem 1.5rem;border-radius:12px;color:white;margin-bottom:1.5rem;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:1rem;">
    <div>
        <h2 style="font-weight:800;font-size:1.15rem;margin:0;">📊 Rekap Nilai Ujian</h2>
        <p style="opacity:0.85;font-size:0.85rem;margin:0.3rem 0 0;">
            <strong>{{ $schedule->subject->name ?? '-' }}</strong> &nbsp;|&nbsp;
            Kelas: <strong>{{ $schedule->classRoom->name ?? '-' }}</strong> &nbsp;|&nbsp;
            KKM: <strong>{{ $kkm }}</strong>
        </p>
    </div>
    <div style="display:flex;gap:0.65rem;flex-wrap:wrap;">
        <a href="{{ route('guru.exam-results.export', $schedule->id) }}"
           style="background:#eab308;color:#0f172a;padding:0.5rem 1.2rem;border-radius:8px;font-weight:700;font-size:0.82rem;text-decoration:none;display:flex;align-items:center;gap:0.4rem;">
            📥 Export Excel (Raport)
        </a>
        <a href="{{ route('guru.schedules.index') }}"
           style="background:rgba(255,255,255,0.2);color:white;padding:0.5rem 1.2rem;border-radius:8px;font-weight:600;font-size:0.82rem;text-decoration:none;">
            ← Kembali
        </a>
    </div>
</div>

{{-- ===== RINGKASAN STATISTIK ===== --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:1rem;margin-bottom:1.5rem;">
    <div class="card" style="text-align:center;border-top:4px solid #1d4ed8;padding:1rem;">
        <div style="font-size:2rem;font-weight:900;color:#1d4ed8;">{{ $total }}</div>
        <div style="font-size:0.75rem;color:#6b7280;font-weight:600;margin-top:0.2rem;">Total Peserta</div>
    </div>
    <div class="card" style="text-align:center;border-top:4px solid #10b981;padding:1rem;">
        <div style="font-size:2rem;font-weight:900;color:#10b981;">{{ $lulus }}</div>
        <div style="font-size:0.75rem;color:#6b7280;font-weight:600;margin-top:0.2rem;">✅ Lulus KKM</div>
    </div>
    <div class="card" style="text-align:center;border-top:4px solid #ef4444;padding:1rem;">
        <div style="font-size:2rem;font-weight:900;color:#ef4444;">{{ $tidakLulus }}</div>
        <div style="font-size:0.75rem;color:#6b7280;font-weight:600;margin-top:0.2rem;">❌ Tidak Lulus</div>
    </div>
    <div class="card" style="text-align:center;border-top:4px solid #f59e0b;padding:1rem;">
        <div style="font-size:2rem;font-weight:900;color:#f59e0b;">{{ $avgScore }}</div>
        <div style="font-size:0.75rem;color:#6b7280;font-weight:600;margin-top:0.2rem;">Rata-rata</div>
    </div>
    <div class="card" style="text-align:center;border-top:4px solid #8b5cf6;padding:1rem;">
        <div style="font-size:2rem;font-weight:900;color:#8b5cf6;">
            {{ $total > 0 ? round(($lulus/$total)*100) : 0 }}%
        </div>
        <div style="font-size:0.75rem;color:#6b7280;font-weight:600;margin-top:0.2rem;">Kelulusan</div>
    </div>
    <div class="card" style="text-align:center;border-top:4px solid #06b6d4;padding:1rem;">
        <div style="font-size:1.4rem;font-weight:900;color:#06b6d4;">{{ $highest }}</div>
        <div style="font-size:0.75rem;color:#6b7280;font-weight:600;margin-top:0.2rem;">🏆 Tertinggi</div>
    </div>
    <div class="card" style="text-align:center;border-top:4px solid #f97316;padding:1rem;">
        <div style="font-size:1.4rem;font-weight:900;color:#f97316;">{{ $lowest }}</div>
        <div style="font-size:0.75rem;color:#6b7280;font-weight:600;margin-top:0.2rem;">📉 Terendah</div>
    </div>
</div>

{{-- ===== KETERANGAN PENGGUNAAN ===== --}}
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.82rem;color:#92400e;">
    📋 <strong>Untuk Raport:</strong> Nilai Akhir (Skala 100) pada tabel di bawah dapat langsung digunakan untuk pengisian raport manual.
    Klik <strong>Export Excel</strong> untuk mendapatkan file yang bisa dibuka di Microsoft Excel.
</div>

{{-- ===== TABEL NILAI SISWA ===== --}}
<div class="card" style="padding:0;overflow:hidden;">
    <div style="padding:1rem 1.5rem;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.5rem;">
        <span style="font-weight:700;color:#1e3a8a;font-size:0.95rem;">
            📋 Daftar Nilai Siswa
            <span style="font-weight:400;font-size:0.78rem;color:#9ca3af;margin-left:0.5rem;">(diurutkan: nilai tertinggi)</span>
        </span>
        <div style="display:flex;gap:0.5rem;align-items:center;">
            <span style="font-size:0.78rem;color:#9ca3af;">KKM: {{ $kkm }}</span>
            <span style="display:inline-block;background:#dcfce7;color:#166534;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.72rem;font-weight:700;">
                ✅ = ≥ {{ $kkm }}
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:36px;text-align:center;">#</th>
                    <th>Nama Siswa</th>
                    <th class="hide-mobile" style="width:80px;text-align:center;">Nilai PG</th>
                    <th class="hide-mobile" style="width:90px;text-align:center;">Nilai Essay</th>
                    <th style="width:110px;text-align:center;">
                        Nilai Akhir<br>
                        <span style="font-size:0.65rem;font-weight:400;color:#9ca3af;">(Skala 100)</span>
                    </th>
                    <th style="width:110px;text-align:center;">Status</th>
                    <th class="hide-mobile" style="width:100px;text-align:center;">Raport</th>
                </tr>
            </thead>
            <tbody>
            @forelse($results as $i => $r)
                @php
                    $passed   = $r->status === 'lulus';
                    $score    = round($r->final_score, 1);
                    $pgScore  = round($r->pg_score, 1);
                    $esScore  = round($r->essay_score, 1);
                    // Warna baris berdasarkan nilai
                    $rowBg = $passed ? '' : 'background:#fef9f9;';
                @endphp
                <tr style="{{ $rowBg }}">
                    <td style="text-align:center;color:#9ca3af;font-size:0.85rem;font-weight:600;">{{ $i + 1 }}</td>
                    <td>
                        <div style="font-weight:700;font-size:0.9rem;">{{ $r->student->name ?? '(Siswa Dihapus)' }}</div>
                        {{-- Mobile: tampilkan breakdown nilai --}}
                        <div class="show-mobile" style="font-size:0.75rem;color:#9ca3af;margin-top:2px;">
                            PG: {{ $pgScore }} | Essay: {{ $esScore }}
                        </div>
                    </td>
                    <td class="hide-mobile" style="text-align:center;">
                        <span style="font-weight:700;color:#1d4ed8;font-size:0.95rem;">{{ $pgScore }}</span>
                    </td>
                    <td class="hide-mobile" style="text-align:center;">
                        <span style="font-weight:700;color:#c2410c;font-size:0.95rem;">{{ $esScore }}</span>
                    </td>
                    <td style="text-align:center;">
                        <div style="font-size:1.5rem;font-weight:900;line-height:1;color:{{ $passed ? '#16a34a' : '#dc2626' }};">
                            {{ $score }}
                        </div>
                        {{-- Progress bar --}}
                        <div style="background:#f3f4f6;border-radius:999px;height:6px;margin-top:5px;overflow:hidden;position:relative;">
                            <div style="height:100%;width:{{ min($score, 100) }}%;background:{{ $passed ? '#10b981' : '#ef4444' }};border-radius:999px;"></div>
                            {{-- KKM marker --}}
                            <div style="position:absolute;top:0;left:{{ $kkm }}%;width:2px;height:100%;background:#374151;opacity:0.4;"></div>
                        </div>
                        <div style="font-size:0.62rem;color:#9ca3af;margin-top:2px;">KKM {{ $kkm }}</div>
                    </td>
                    <td style="text-align:center;">
                        <span style="padding:0.3rem 0.7rem;border-radius:999px;font-size:0.75rem;font-weight:700;display:inline-block;
                            background:{{ $passed ? '#f0fdf4' : '#fef2f2' }};
                            color:{{ $passed ? '#16a34a' : '#dc2626' }};
                            border:1px solid {{ $passed ? '#86efac' : '#fca5a5' }};">
                            {{ $passed ? '✅ Lulus' : '❌ Tidak Lulus' }}
                        </span>
                    </td>
                    {{-- Kolom Raport: nilai angka bulat untuk isi raport --}}
                    <td class="hide-mobile" style="text-align:center;">
                        <span style="font-size:1.2rem;font-weight:900;color:#374151;
                            background:#f9fafb;border:2px solid {{ $passed ? '#86efac' : '#fca5a5' }};
                            padding:0.2rem 0.6rem;border-radius:8px;display:inline-block;">
                            {{ round($score) }}
                        </span>
                        <div style="font-size:0.62rem;color:#9ca3af;margin-top:2px;">Nilai Raport</div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:3rem;color:#9ca3af;">
                        <div style="font-size:2rem;margin-bottom:0.5rem;">📭</div>
                        Belum ada siswa yang mengumpulkan jawaban untuk ujian ini.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Footer: ringkasan nilai untuk raport --}}
    @if($results->isNotEmpty())
    <div style="padding:0.85rem 1.5rem;background:#f9fafb;border-top:1px solid #e5e7eb;display:flex;flex-wrap:wrap;gap:1rem;align-items:center;font-size:0.82rem;color:#6b7280;">
        <span>📊 <strong>Nilai Tertinggi:</strong> {{ $highest }}</span>
        <span>|</span>
        <span>📉 <strong>Nilai Terendah:</strong> {{ $lowest }}</span>
        <span>|</span>
        <span>📐 <strong>Rata-rata Kelas:</strong> {{ $avgScore }}</span>
        <span>|</span>
        <span>🎓 <strong>% Lulus:</strong> {{ $total > 0 ? round(($lulus/$total)*100) : 0 }}%</span>
    </div>
    @endif
</div>

<style>
@media (max-width:640px) { .hide-mobile { display:none !important; } }
.show-mobile { display:none; }
@media (max-width:640px) { .show-mobile { display:block; } }
</style>
@endsection
