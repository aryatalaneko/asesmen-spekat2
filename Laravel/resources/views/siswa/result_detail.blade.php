@extends('layouts.app')
@section('header_title','Hasil Ujian')
@section('content')

@php
    $r        = $result;
    $schedule = $r->schedule;
    $kkm      = $schedule->kkm ?? 75;
    $passed   = $r->status === 'lulus';

    // Pisahkan jawaban essay untuk tampilan detail
    $essayAnswers = $r->studentAnswers->filter(fn($a) => $a->question && $a->question->type === 'essay');
@endphp

{{-- Header Hasil --}}
<div style="background:linear-gradient(135deg,{{ $passed ? '#f0fdf4,#d1fae5' : '#fef2f2,#fee2e2' }});border:2px solid {{ $passed ? '#10b981' : '#ef4444' }};border-radius:16px;padding:2rem;text-align:center;margin-bottom:1.5rem;">
    <div style="font-size:3rem;margin-bottom:0.5rem;">{{ $passed ? '🎉' : '📚' }}</div>
    <h2 style="font-weight:900;font-size:2.5rem;color:{{ $passed ? '#065f46' : '#991b1b' }};margin:0;">
        {{ round($r->final_score) }}
    </h2>
    <p style="font-size:1rem;color:{{ $passed ? '#065f46' : '#991b1b' }};font-weight:600;margin:0.25rem 0 0;">
        {{ $passed ? '✅ LULUS — Selamat!' : '❌ Belum Lulus — Tetap Semangat!' }}
    </p>
    <p style="font-size:0.85rem;color:#666;margin:0.5rem 0 0;">KKM: {{ $kkm }} | Mata Pelajaran: {{ $schedule->subject->name ?? '-' }}</p>
</div>

{{-- Progress Bar Nilai --}}
<div class="card" style="margin-bottom:1.5rem;">
    <div style="display:flex;justify-content:space-between;font-size:0.85rem;font-weight:600;margin-bottom:0.5rem;">
        <span>Nilai Anda</span>
        <span>{{ round($r->final_score) }} / 100</span>
    </div>
    <div style="background:#f3f4f6;border-radius:999px;height:16px;overflow:hidden;">
        <div style="height:100%;width:{{ min($r->final_score,100) }}%;background:{{ $passed ? '#10b981' : '#ef4444' }};border-radius:999px;transition:width 1s ease;"></div>
    </div>
    @if(!$passed)
    <p style="font-size:0.8rem;color:#ef4444;margin-top:0.5rem;">Kurang <strong>{{ $kkm - round($r->final_score) }}</strong> poin dari KKM.</p>
    @endif
</div>

{{-- Statistik Breakdown --}}
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;margin-bottom:1.5rem;">

    {{-- PG Stats --}}
    <div class="card" style="border-top:4px solid #1d4ed8;">
        <h4 style="font-weight:700;font-size:1rem;margin-bottom:1.25rem;color:#1d4ed8;">📌 Soal Pilihan Ganda (PG)</h4>
        <div style="display:flex;justify-content:space-around;text-align:center;">
            <div>
                <div style="font-size:2rem;font-weight:900;color:#10b981;">{{ $r->pg_correct }}</div>
                <div style="font-size:0.8rem;color:#666;font-weight:600;">Benar</div>
            </div>
            <div style="width:1px;background:#e5e7eb;"></div>
            <div>
                <div style="font-size:2rem;font-weight:900;color:#ef4444;">{{ $r->pg_wrong }}</div>
                <div style="font-size:0.8rem;color:#666;font-weight:600;">Salah</div>
            </div>
            <div style="width:1px;background:#e5e7eb;"></div>
            <div>
                <div style="font-size:2rem;font-weight:900;color:#1d4ed8;">{{ round($r->pg_score) }}</div>
                <div style="font-size:0.8rem;color:#666;font-weight:600;">Nilai PG</div>
            </div>
        </div>
    </div>

    {{-- Essay Stats --}}
    <div class="card" style="border-top:4px solid #c2410c;">
        <h4 style="font-weight:700;font-size:1rem;margin-bottom:1.25rem;color:#c2410c;">📝 Soal Essay (AI Scoring)</h4>
        <div style="display:flex;justify-content:space-around;text-align:center;margin-bottom:1rem;">
            <div>
                <div style="font-size:2rem;font-weight:900;color:#10b981;">{{ $r->essay_correct }}</div>
                <div style="font-size:0.8rem;color:#666;font-weight:600;">≥75% (Benar)</div>
            </div>
            <div style="width:1px;background:#e5e7eb;"></div>
            <div>
                <div style="font-size:2rem;font-weight:900;color:#ef4444;">{{ $r->essay_wrong }}</div>
                <div style="font-size:0.8rem;color:#666;font-weight:600;">Salah/Tidak Cukup</div>
            </div>
            <div style="width:1px;background:#e5e7eb;"></div>
            <div>
                <div style="font-size:2rem;font-weight:900;color:#c2410c;">{{ round($r->essay_score) }}</div>
                <div style="font-size:0.8rem;color:#666;font-weight:600;">Nilai Essay</div>
            </div>
        </div>
        <div style="background:#fff7ed;border-radius:8px;padding:0.6rem;font-size:0.78rem;color:#92400e;">
            🤖 AI menilai kemiripan jawaban. <strong>≥75%</strong> = poin penuh. &lt;75% = poin parsial.
        </div>
    </div>
</div>

{{-- ===== DETAIL JAWABAN ESSAY PER SOAL ===== --}}
@if($essayAnswers->isNotEmpty())
<div class="card" style="margin-bottom:1.5rem;border-top:4px solid #c2410c;">
    <h4 style="font-weight:700;font-size:1rem;margin-bottom:1rem;color:#c2410c;">📊 Detail Penilaian AI per Soal Essay</h4>

    <div style="display:flex;flex-direction:column;gap:1rem;">
        @foreach($essayAnswers as $i => $ans)
        @php
            $sim       = $ans->similarity_score ?? 0;
            $isCorrect = $ans->is_correct;
            $bobot     = $ans->question->weight ?? 0;

            // Tentukan warna berdasarkan status
            if ($isCorrect) {
                $barColor   = '#10b981'; // hijau – benar (≥75%)
                $badgeBg    = '#f0fdf4';
                $badgeColor = '#065f46';
                $statusText = '✅ Benar (Poin Penuh)';
            } elseif ($sim >= 50) {
                $barColor   = '#f59e0b'; // kuning – parsial menengah
                $badgeBg    = '#fffbeb';
                $badgeColor = '#92400e';
                $statusText = '⚠️ Salah/Tidak Cukup';
            } else {
                $barColor   = '#ef4444'; // merah – parsial rendah
                $badgeBg    = '#fef2f2';
                $badgeColor = '#991b1b';
                $statusText = '❌ Salah/Tidak Cukup';
            }
        @endphp

        <div style="background:#f9fafb;border-radius:12px;padding:1rem;border:1px solid #e5e7eb;">
            {{-- Nomor soal & status --}}
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:0.5rem;margin-bottom:0.75rem;">
                <div style="font-weight:700;font-size:0.9rem;color:#374151;">Soal Essay #{{ $i + 1 }}</div>
                <div style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;">
                    {{-- Badge status --}}
                    <span style="background:{{ $badgeBg }};color:{{ $badgeColor }};padding:0.2rem 0.6rem;border-radius:999px;font-size:0.78rem;font-weight:700;">
                        {{ $statusText }}
                    </span>
                    {{-- Persentase kemiripan AI --}}
                    <span style="background:#eff6ff;color:#1e40af;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.78rem;font-weight:700;">
                        🤖 Kemiripan AI: {{ number_format($sim, 1) }}%
                    </span>
                    {{-- Poin yang diraih --}}
                    <span style="background:#f3f4f6;color:#374151;padding:0.2rem 0.6rem;border-radius:999px;font-size:0.78rem;font-weight:700;">
                        Poin: {{ $ans->score }} / {{ $bobot }}
                    </span>
                </div>
            </div>

            {{-- Bar kemiripan --}}
            <div style="margin-bottom:0.6rem;">
                <div style="display:flex;justify-content:space-between;font-size:0.75rem;color:#6b7280;margin-bottom:0.25rem;">
                    <span>Tingkat Kemiripan dengan Kunci Jawaban</span>
                    <span style="font-weight:700;color:{{ $barColor }};">{{ number_format($sim, 1) }}%</span>
                </div>
                <div style="background:#e5e7eb;border-radius:999px;height:8px;overflow:hidden;">
                    <div style="height:100%;width:{{ min($sim, 100) }}%;background:{{ $barColor }};border-radius:999px;transition:width 0.8s ease;"></div>
                </div>
                {{-- Garis ambang batas 75% --}}
                <div style="position:relative;height:0;">
                    <div style="position:absolute;left:75%;top:-10px;border-left:2px dashed #6b7280;height:12px;"></div>
                </div>
                <div style="font-size:0.7rem;color:#9ca3af;text-align:right;margin-top:0.2rem;">
                    Ambang batas: 75% (garis putus-putus)
                </div>
            </div>

            {{-- Pertanyaan --}}
            @if($ans->question)
            <div style="font-size:0.82rem;color:#4b5563;margin-bottom:0.4rem;">
                <strong>Pertanyaan:</strong> {{ Str::limit($ans->question->question_text, 150) }}
            </div>
            @endif

            {{-- Jawaban siswa --}}
            <div style="font-size:0.82rem;color:#374151;background:white;border-radius:8px;padding:0.5rem 0.75rem;border:1px solid #e5e7eb;">
                <strong>Jawaban Anda:</strong> {{ $ans->student_answer ?? '(tidak dijawab)' }}
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

<div style="text-align:center;">
    <a href="{{ route('siswa.results.index') }}" class="btn btn-primary" style="padding:0.75rem 2rem;">← Kembali ke Riwayat</a>
    <a href="{{ route('siswa.exams.index') }}" style="display:inline-block;margin-left:1rem;padding:0.75rem 2rem;border-radius:8px;border:1px solid #e5e7eb;color:#666;font-weight:600;">Lihat Ujian Lain</a>
</div>
@endsection
