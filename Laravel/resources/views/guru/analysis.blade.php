@extends('layouts.app')
@section('header_title','Dashboard Guru')
@section('content')

<div style="background:linear-gradient(135deg,#1e1b4b,#312e81);padding:1.5rem;border-radius:12px;color:white;margin-bottom:1.5rem;display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h2 style="font-weight:800;font-size:1.25rem;margin:0;">🤖 Deteksi Dini & Analisis Hasil Ujian</h2>
        <p style="opacity:0.7;font-size:0.875rem;margin:0.25rem 0 0;">Evaluasi komprehensif performa siswa berbasis K-Means Clustering dan Analitik Item Soal</p>
    </div>
    <div style="background:rgba(255,255,255,0.1);border-radius:8px;padding:0.75rem 1rem;font-size:0.8rem;">
        🐍 Flask API: <strong>{{ env('FLASK_URL','http://127.0.0.1:5000') }}</strong>
    </div>
</div>

{{-- Top Summary: Pass/Fail and Early Detection Warning --}}
<div style="display:grid;grid-template-columns:1fr 2fr;gap:1.5rem;margin-bottom:1.5rem;">
    {{-- Pass/Fail Chart --}}
    <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;">
        <h3 style="font-weight:700;font-size:1rem;margin-bottom:1rem;align-self:flex-start;">📊 Distribusi Kelulusan</h3>
        @php
            $totalResults = $passCount + $failCount;
            $passPerc = $totalResults > 0 ? round(($passCount/$totalResults)*100) : 0;
            $failPerc = $totalResults > 0 ? round(($failCount/$totalResults)*100) : 0;
        @endphp
        
        @if($totalResults > 0)
        {{-- CSS Pie chart representation --}}
        <div style="position:relative;width:120px;height:120px;border-radius:50%;background:conic-gradient(#16a34a {{ $passPerc }}%, #dc2626 0);margin-bottom:1rem;"></div>
        <div style="display:flex;gap:1.5rem;font-size:0.85rem;">
            <div><span style="display:inline-block;width:12px;height:12px;background:#16a34a;border-radius:3px;"></span> Lulus: <strong>{{ $passCount }}</strong> ({{ $passPerc }}%)</div>
            <div><span style="display:inline-block;width:12px;height:12px;background:#dc2626;border-radius:3px;"></span> Tidak: <strong>{{ $failCount }}</strong> ({{ $failPerc }}%)</div>
        </div>
        @else
        <div style="color:#aaa;text-align:center;padding:1rem;">Belum ada data evaluasi.</div>
        @endif
    </div>

    {{-- Early Detection Alert List --}}
    <div class="card" style="height:250px;overflow-y:auto;padding-right:0;">
        <h3 style="font-weight:700;font-size:1rem;margin-bottom:1rem;">🚨 Deteksi Dini: Prediksi Ujian Berikutnya</h3>
        <p style="font-size:0.8rem;color:#666;margin-bottom:1rem;">Berdasarkan riwayat clustering terakhir setiap siswa di kelas Anda.</p>
        <div style="padding-right:1.5rem;">
            @forelse($earlyDetection as $ed)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:0.75rem;border-bottom:1px solid #f3f4f6;background:{{ $ed->bg_color }};border-radius:8px;margin-bottom:0.5rem;border-left:4px solid {{ $ed->text_color }};">
                    <div>
                        <div style="font-weight:700;font-size:0.9rem;color:#1f2937;">{{ $ed->student_name }}</div>
                        <div style="font-size:0.75rem;color:#4b5563;">Klaster Terakhir: <strong>{{ $ed->last_cluster }}</strong></div>
                    </div>
                    <span style="color:{{ $ed->text_color }};font-weight:800;font-size:0.8rem;">Prediksi: {{ $ed->prediction }}</span>
                </div>
            @empty
                <div style="color:#aaa;text-align:center;padding:1rem;">Belum ada cukup data histori untuk melakukan deteksi dini.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Top 5 Kesalahan Soal --}}
<div class="card" style="margin-bottom:1.5rem;">
    <h3 style="font-weight:700;font-size:1rem;margin-bottom:0.5rem;">⚠️ Evaluasi Pedagogik: Top 5 Soal Paling Banyak Salah</h3>
    <p style="font-size:0.8rem;color:#666;margin-bottom:1rem;">Berdasarkan total jawaban salah yang diinput seluruh siswa di kelas Anda.</p>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Gagal dijawab</th>
                    <th>Tipe</th>
                    <th>Konteks Mapel</th>
                    <th>Snippet Pertanyaan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topWrongQuestions as $twq)
                <tr>
                    <td><span style="font-weight:800;color:#dc2626;background:#fef2f2;padding:0.2rem 0.6rem;border-radius:6px;">{{ $twq->wrong_count }} siswa</span></td>
                    <td><span style="font-size:0.75rem;font-weight:700;background:#f1f5f9;color:#475569;padding:0.2rem 0.5rem;border-radius:4px;">{{ strtoupper($twq->type) }}</span></td>
                    <td style="font-size:0.8rem;">{{ $twq->subject_name }}</td>
                    <td style="font-size:0.9srem;font-weight:600;">{{ Str::limit($twq->question_text, 80) }}</td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;padding:2rem;color:#aaa;">Belum ada pencatatan kesalahan siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Pilih jadwal untuk dianalisis --}}
<div class="card" style="margin-bottom:1.5rem;">
    <h3 style="font-weight:700;margin-bottom:1rem;">🚀 Jalankan Clustering K-Means Baru</h3>
    @if($schedules->isEmpty())
        <p style="color:#aaa;text-align:center;padding:1rem;">Belum ada jadwal ujian yang tersedia di kelas Anda.</p>
    @else
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:1rem;">
            @foreach($schedules as $s)
            <div style="border:1px solid #e5e7eb;border-radius:12px;padding:1.25rem;">
                <div style="font-weight:700;font-size:0.95rem;margin-bottom:0.5rem;">📋 {{ $s->subject->name ?? '-' }}</div>
                <div style="font-size:0.8rem;color:#666;margin-bottom:0.75rem;">
                    Kelas: <strong>{{ $s->classRoom->name ?? '-' }}</strong><br>
                    Peserta: <strong>{{ $s->results->count() ?? 0 }}</strong> siswa
                </div>
                <form method="POST" action="{{ route('guru.analysis.run', $s->id) }}" onsubmit="return confirm('Jalankan analisis K-Means untuk ujian ini?')">
                    @csrf
                    <button type="submit" class="btn btn-primary" style="width:100%;background:#312e81;">
                        🤖 Analisis Sekarang
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Riwayat Hasil Clustering --}}
<div class="card">
    <h3 style="font-weight:700;margin-bottom:1.25rem;">📊 Data Latih: Riwayat Clustering</h3>
    <p style="font-size:0.8rem;color:#666;margin-bottom:1rem;">Data ini digunakan oleh sistem sebagai parameter perhitungan deteksi dini.</p>
    @forelse($histories as $scheduleId => $clusterGroup)
        @php
            $firstItem = $clusterGroup->first();
            $schedule  = $firstItem->schedule ?? null;
        @endphp
        <div style="margin-bottom:1.5rem;padding-bottom:1.5rem;border-bottom:1px solid #f3f4f6;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <div>
                    <strong>{{ $schedule->subject->name ?? 'Jadwal #'.$scheduleId }}</strong>
                    <span style="background:#eff6ff;color:#1d4ed8;padding:0.15rem 0.5rem;border-radius:6px;font-size:0.8rem;font-weight:600;margin-left:0.5rem;">{{ $schedule->classRoom->name ?? '' }}</span>
                </div>
                <span style="font-size:0.78rem;color:#888;">Dianalisis: {{ $firstItem->analyzed_at?->format('d M Y H:i') }}</span>
            </div>

            {{-- Klaster-klaster --}}
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem;margin-bottom:1rem;">
                @foreach([['aman','#f0fdf4','#16a34a','✅'],['bimbingan','#fff7ed','#c2410c','⚠️'],['risiko_tinggi','#fef2f2','#dc2626','🔴']] as [$clusterKey,$bg,$color,$icon])
                    @php $group = $clusterGroup->where('cluster', $clusterKey); @endphp
                    <div style="background:{{ $bg }};border-radius:10px;padding:1rem;">
                        <div style="font-weight:800;font-size:0.9rem;color:{{ $color }};margin-bottom:0.5rem;">{{ $icon }} {{ ucwords(str_replace('_',' ', $clusterKey)) }} ({{ $group->count() }})</div>
                        @foreach($group as $cr)
                            <div style="font-size:0.8rem;display:flex;justify-content:space-between;padding:0.2rem 0;border-bottom:1px solid rgba(0,0,0,0.05);">
                                <span>{{ $cr->student->name ?? '-' }}</span>
                                <strong>{{ number_format($cr->nilai_akhir,1) }}</strong>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <p style="text-align:center;color:#aaa;padding:2rem;">Belum ada riwayat analisis K-Means.</p>
    @endforelse
</div>
{{-- ===== REKAP NILAI SISWA PER UJIAN ===== --}}
<div class="card" style="margin-top:1.5rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem;flex-wrap:wrap;gap:0.5rem;">
        <div>
            <h3 style="font-weight:700;font-size:1rem;margin:0;">📋 Rekap Nilai Siswa (Semua Ujian)</h3>
            <p style="font-size:0.78rem;color:#6b7280;margin:0.25rem 0 0;">Nilai ini dapat digunakan untuk pengisian raport manual.</p>
        </div>
    </div>

    @if($allResults->isEmpty())
        <div style="text-align:center;padding:2.5rem;color:#9ca3af;">
            <div style="font-size:2rem;margin-bottom:0.5rem;">📭</div>
            Belum ada siswa yang menyelesaikan ujian.
        </div>
    @else
        @php
            $grouped = $allResults->groupBy('schedule_id');
        @endphp

        @foreach($grouped as $schedId => $rows)
            @php
                $first   = $rows->first();
                $mapel   = $first->schedule->subject->name  ?? 'Ujian';
                $kelas   = $first->schedule->classRoom->name ?? '-';
                $kkm     = $first->schedule->kkm ?? 75;
                $total   = $rows->count();
                $lulus   = $rows->where('status','lulus')->count();
                $avg     = $total > 0 ? round($rows->avg('final_score'), 1) : 0;
            @endphp

            <div style="margin-bottom:2rem;border:1px solid #e5e7eb;border-radius:12px;overflow:hidden;">
                {{-- Header ujian --}}
                <div style="background:linear-gradient(135deg,#1e3a8a,#1d4ed8);padding:0.85rem 1.25rem;display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:0.75rem;">
                    <div>
                        <div style="font-weight:800;color:white;font-size:0.95rem;">📚 {{ $mapel }} — Kelas {{ $kelas }}</div>
                        <div style="font-size:0.75rem;color:rgba(255,255,255,0.75);margin-top:2px;">KKM: {{ $kkm }}</div>
                    </div>
                    <div style="display:flex;gap:1rem;">
                        <div style="text-align:center;">
                            <div style="font-size:1.2rem;font-weight:900;color:#c6ff00;">{{ $total }}</div>
                            <div style="font-size:0.65rem;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.05em;">Peserta</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:1.2rem;font-weight:900;color:#86efac;">{{ $lulus }}</div>
                            <div style="font-size:0.65rem;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.05em;">Lulus</div>
                        </div>
                        <div style="text-align:center;">
                            <div style="font-size:1.2rem;font-weight:900;color:#fde68a;">{{ $avg }}</div>
                            <div style="font-size:0.65rem;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.05em;">Rata-rata</div>
                        </div>
                        <div style="text-align:center;">
                            <a href="{{ route('guru.exam-results', $schedId) }}"
                               style="background:rgba(255,255,255,0.2);color:white;padding:0.3rem 0.8rem;border-radius:6px;font-size:0.75rem;font-weight:700;text-decoration:none;white-space:nowrap;">
                                📊 Detail
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Tabel nilai --}}
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.84rem;">
                        <thead>
                            <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
                                <th style="padding:0.6rem 1rem;text-align:left;color:#374151;font-weight:700;">#</th>
                                <th style="padding:0.6rem 1rem;text-align:left;color:#374151;font-weight:700;">Nama Siswa</th>
                                <th style="padding:0.6rem 1rem;text-align:center;color:#1d4ed8;font-weight:700;">Nilai PG</th>
                                <th style="padding:0.6rem 1rem;text-align:center;color:#c2410c;font-weight:700;">Nilai Essay</th>
                                <th style="padding:0.6rem 1rem;text-align:center;color:#374151;font-weight:700;">
                                    Nilai Akhir<br><span style="font-size:0.65rem;font-weight:400;color:#9ca3af;">(Skala 100)</span>
                                </th>
                                <th style="padding:0.6rem 1rem;text-align:center;color:#166534;font-weight:700;">
                                    Nilai Raport<br><span style="font-size:0.65rem;font-weight:400;color:#9ca3af;">(Dibulatkan)</span>
                                </th>
                                <th style="padding:0.6rem 1rem;text-align:center;color:#374151;font-weight:700;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $ri => $r)
                                @php
                                    $passed = $r->status === 'lulus';
                                    $score  = round($r->final_score, 1);
                                @endphp
                                <tr style="border-bottom:1px solid #f3f4f6;{{ !$passed ? 'background:#fef9f9;' : '' }}">
                                    <td style="padding:0.5rem 1rem;color:#9ca3af;font-size:0.8rem;">{{ $ri + 1 }}</td>
                                    <td style="padding:0.5rem 1rem;font-weight:600;">{{ $r->student->name ?? '(Dihapus)' }}</td>
                                    <td style="padding:0.5rem 1rem;text-align:center;color:#1d4ed8;font-weight:700;">{{ round($r->pg_score, 1) }}</td>
                                    <td style="padding:0.5rem 1rem;text-align:center;color:#c2410c;font-weight:700;">{{ round($r->essay_score, 1) }}</td>
                                    <td style="padding:0.5rem 1rem;text-align:center;">
                                        <span style="font-size:1.15rem;font-weight:900;color:{{ $passed ? '#16a34a' : '#dc2626' }};">{{ $score }}</span>
                                        <div style="background:#f3f4f6;border-radius:999px;height:4px;margin-top:4px;overflow:hidden;position:relative;">
                                            <div style="height:100%;width:{{ min($score,100) }}%;background:{{ $passed ? '#10b981' : '#ef4444' }};border-radius:999px;"></div>
                                        </div>
                                    </td>
                                    <td style="padding:0.5rem 1rem;text-align:center;">
                                        <span style="font-size:1.2rem;font-weight:900;color:#374151;background:#f0fdf4;border:2px solid {{ $passed ? '#86efac' : '#fca5a5' }};padding:0.15rem 0.75rem;border-radius:8px;display:inline-block;">
                                            {{ round($score) }}
                                        </span>
                                    </td>
                                    <td style="padding:0.5rem 1rem;text-align:center;">
                                        <span style="padding:0.25rem 0.65rem;border-radius:999px;font-size:0.72rem;font-weight:700;
                                            background:{{ $passed ? '#f0fdf4' : '#fef2f2' }};
                                            color:{{ $passed ? '#16a34a' : '#dc2626' }};
                                            border:1px solid {{ $passed ? '#86efac' : '#fca5a5' }};">
                                            {{ $passed ? '✅ Lulus' : '❌ Tidak Lulus' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:#f9fafb;border-top:2px solid #e5e7eb;">
                                <td colspan="4" style="padding:0.6rem 1rem;font-size:0.78rem;color:#6b7280;">
                                    Rata-rata kelas: <strong>{{ $avg }}</strong> &nbsp;|&nbsp;
                                    Lulus: <strong style="color:#16a34a;">{{ $lulus }}</strong> &nbsp;|&nbsp;
                                    Tidak Lulus: <strong style="color:#dc2626;">{{ $total - $lulus }}</strong>
                                </td>
                                <td colspan="3" style="padding:0.6rem 1rem;text-align:right;">
                                    <a href="{{ route('guru.exam-results.export', $schedId) }}"
                                       style="background:#eab308;color:#0f172a;padding:0.35rem 0.85rem;border-radius:6px;font-size:0.75rem;font-weight:700;text-decoration:none;">
                                        📥 Export Excel
                                    </a>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach
    @endif
</div>
@endsection
