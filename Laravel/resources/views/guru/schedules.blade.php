@extends('layouts.app')
@section('header_title','Monitoring Ujian')
@section('content')

{{-- Header --}}
<div style="background:linear-gradient(135deg,#1e1b4b,#312e81);padding:1.5rem;border-radius:12px;color:white;margin-bottom:1.5rem;">
    <h2 style="font-weight:800;font-size:1.25rem;margin:0;">🖥️ Monitoring Ujian</h2>
    <p style="opacity:0.7;font-size:0.875rem;margin:0.25rem 0 0;">
        Daftarkan ujian, atur durasi, aktifkan untuk siswa, dan pantau progress secara real-time.
    </p>
</div>

@if(session('success'))
    <div style="background:#dcfce7;color:#166534;border:1px solid #86efac;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;">✅ {{ session('success') }}</div>
@endif
@if(session('error'))
    <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;">❌ {{ session('error') }}</div>
@endif

{{-- ======================== DAFTAR UJIAN ======================== --}}
<div style="display:flex;flex-direction:column;gap:1.5rem;margin-bottom:2rem;">
    @forelse($schedules as $s)
        @php
            $pct       = $s->total_students > 0 ? round(($s->done_students / $s->total_students) * 100) : 0;
            $remaining = $s->total_students - $s->done_students;
        @endphp

        <div id="card-{{ $s->id }}" style="background:white;border-radius:16px;border:1px solid #e5e7eb;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.06);">
            <div style="height:4px;background:{{ $s->is_active ? 'linear-gradient(90deg,#22c55e,#16a34a)' : '#e5e7eb' }};"></div>

            <div style="padding:1.25rem 1.5rem;">
                {{-- ===== BARIS ATAS: Info Ujian + Status + Timer ===== --}}
                <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:1.25rem;">
                    {{-- Kiri: nama mapel dan kelas --}}
                    <div>
                        <div style="font-weight:800;font-size:1.05rem;color:#111827;margin-bottom:0.25rem;">{{ $s->subject->name ?? '-' }}</div>
                        <span style="background:#eff6ff;color:#1d4ed8;padding:0.2rem 0.6rem;border-radius:6px;font-size:0.78rem;font-weight:700;">Kelas {{ $s->classRoom->name ?? '-' }}</span>
                    </div>

                    {{-- Tengah: Timer --}}
                    @if($s->is_active)
                        <div style="display:flex;flex-direction:column;align-items:center;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:0.6rem 1.25rem;min-width:130px;">
                            <div style="font-size:0.65rem;color:#6b7280;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">⏱️ Sisa Waktu</div>
                            <div id="timer-{{ $s->id }}" style="font-weight:900;font-size:1.5rem;color:#16a34a;font-variant-numeric:tabular-nums;letter-spacing:-0.03em;">
                                {{ gmdate('H:i:s', $s->remaining_seconds) }}
                            </div>
                        </div>
                    @else
                        <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:12px;padding:0.6rem 1.25rem;text-align:center;min-width:130px;">
                            <div style="font-size:0.65rem;color:#9ca3af;font-weight:600;text-transform:uppercase;">Durasi</div>
                            <div style="font-weight:800;font-size:1.25rem;color:#374151;">{{ $s->duration }}<span style="font-size:0.75rem;font-weight:500;color:#9ca3af;"> mnt</span></div>
                        </div>
                    @endif

                    {{-- Kanan: Badge status --}}
                    @if($s->is_active)
                        <span style="background:#dcfce7;color:#166534;padding:0.3rem 0.8rem;border-radius:999px;font-size:0.72rem;font-weight:800;border:1px solid #86efac;align-self:flex-start;">🟢 AKTIF</span>
                    @else
                        <span style="background:#f3f4f6;color:#6b7280;padding:0.3rem 0.8rem;border-radius:999px;font-size:0.72rem;font-weight:800;border:1px solid #d1d5db;align-self:flex-start;">⏸️ TIDAK AKTIF</span>
                    @endif
                </div>

                {{-- ===== KKM + Progress ===== --}}
                <div style="display:grid;grid-template-columns:auto 1fr;gap:1.25rem;align-items:center;margin-bottom:1.25rem;">
                    <div style="background:#f9fafb;border-radius:10px;padding:0.5rem 1rem;text-align:center;border:1px solid #e5e7eb;">
                        <div style="font-size:0.65rem;color:#9ca3af;font-weight:600;text-transform:uppercase;">KKM</div>
                        <div style="font-weight:800;font-size:1.1rem;color:#1d4ed8;">{{ $s->kkm }}</div>
                    </div>
                    <div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:0.3rem;">
                            <span style="font-size:0.8rem;font-weight:700;color:#374151;">📊 Progress Pengerjaan</span>
                            <span style="font-size:0.8rem;font-weight:700;">{{ $pct }}%</span>
                        </div>
                        <div style="background:#f3f4f6;border-radius:999px;height:8px;overflow:hidden;">
                            <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#22c55e,#16a34a);border-radius:999px;"></div>
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-top:0.35rem;font-size:0.73rem;color:#6b7280;">
                            <span>👥 Terdaftar: <strong style="color:#111827;">{{ $s->total_students }}</strong></span>
                            <span>✅ <strong style="color:#16a34a;">{{ $s->done_students }}</strong> selesai · ⏳ <strong style="color:#dc2626;">{{ $remaining }}</strong> belum</span>
                        </div>
                    </div>
                </div>

                {{-- ===== ATUR DURASI (hanya jika TIDAK aktif) ===== --}}
                @if(!$s->is_active)
                <div style="background:#f9fafb;border-radius:10px;padding:0.75rem 1rem;margin-bottom:1rem;border:1px solid #e5e7eb;">
                    <div style="font-size:0.75rem;font-weight:700;color:#374151;margin-bottom:0.5rem;">📏 Atur Durasi Sebelum Diaktifkan</div>
                    <form method="POST" action="{{ route('guru.schedules.duration', $s) }}" style="display:flex;align-items:center;gap:0.5rem;">
                        @csrf
                        <button type="button" onclick="adjustDur(this,-10)" style="width:36px;height:36px;border:1px solid #fca5a5;background:#fef2f2;color:#dc2626;border-radius:8px;font-size:1.1rem;font-weight:700;cursor:pointer;flex-shrink:0;">−</button>
                        <input type="number" name="duration" value="{{ $s->duration }}" min="10" max="600"
                            style="width:80px;border:1px solid #d1d5db;border-radius:8px;padding:0.4rem;text-align:center;font-weight:700;font-size:1rem;">
                        <button type="button" onclick="adjustDur(this,10)" style="width:36px;height:36px;border:1px solid #86efac;background:#f0fdf4;color:#16a34a;border-radius:8px;font-size:1.1rem;font-weight:700;cursor:pointer;flex-shrink:0;">+</button>
                        <button type="submit" style="padding:0.4rem 0.8rem;border:1px solid #d1d5db;background:white;color:#374151;border-radius:8px;font-size:0.8rem;font-weight:600;cursor:pointer;">💾 Simpan</button>
                        <span style="font-size:0.72rem;color:#9ca3af;">menit</span>
                    </form>
                </div>
                @endif

                {{-- ===== TAMBAH WAKTU (hanya jika AKTIF) ===== --}}
                @if($s->is_active)
                <div style="background:#fefce8;border-radius:10px;padding:0.75rem 1rem;margin-bottom:1rem;border:1px solid #fde68a;">
                    <div style="font-size:0.75rem;font-weight:700;color:#92400e;margin-bottom:0.5rem;">⚡ Tambah Waktu (Ujian Sedang Berjalan)</div>
                    <form method="POST" action="{{ route('guru.schedules.addtime', $s) }}" style="display:flex;align-items:center;gap:0.5rem;">
                        @csrf
                        <button type="button" onclick="adjustAdd(this,-5)" style="width:36px;height:36px;border:1px solid #fca5a5;background:#fef2f2;color:#dc2626;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;flex-shrink:0;">−</button>
                        <input type="number" name="minutes" value="10" min="1" max="60"
                            style="width:70px;border:1px solid #fde68a;border-radius:8px;padding:0.4rem;text-align:center;font-weight:700;font-size:1rem;background:#fffbeb;">
                        <button type="button" onclick="adjustAdd(this,5)" style="width:36px;height:36px;border:1px solid #86efac;background:#f0fdf4;color:#16a34a;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;flex-shrink:0;">+</button>
                        <button type="submit" style="padding:0.4rem 0.8rem;border:none;background:linear-gradient(90deg,#d97706,#b45309);color:white;border-radius:8px;font-size:0.8rem;font-weight:700;cursor:pointer;">⏰ Tambah Waktu</button>
                        <span style="font-size:0.72rem;color:#92400e;">menit</span>
                    </form>
                    <div style="font-size:0.7rem;color:#b45309;margin-top:0.35rem;">Timer siswa akan ikut menyesuaikan.</div>
                </div>
                @endif

                {{-- ===== DAFTAR SISWA ===== --}}
                @if($s->students_list->count() > 0)
                <div style="border:1px solid #e5e7eb;border-radius:10px;overflow:hidden;margin-bottom:1rem;">
                    <div style="background:#f9fafb;padding:0.6rem 1rem;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:0.78rem;font-weight:700;color:#374151;">👤 Daftar Siswa — Izin Mengikuti Ujian</span>
                        <span style="font-size:0.72rem;color:#9ca3af;">Klik untuk ubah izin</span>
                    </div>
                    <div style="max-height:220px;overflow-y:auto;">
                        @foreach($s->students_list as $student)
                            @php
                                $isAllowed = $s->permissions_map[$student->id] ?? true;
                                $isDone    = $s->results->where('user_id', $student->id)->isNotEmpty();
                            @endphp
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:0.55rem 1rem;border-bottom:1px solid #f3f4f6;{{ !$isAllowed ? 'background:#fef2f2;' : ($isDone ? 'background:#f0fdf4;' : '') }}">
                                <div style="display:flex;align-items:center;gap:0.75rem;">
                                    <div style="width:30px;height:30px;border-radius:50%;background:#eff6ff;display:flex;align-items:center;justify-content:center;font-size:0.75rem;font-weight:700;color:#1d4ed8;">
                                        {{ strtoupper(substr($student->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div style="font-size:0.85rem;font-weight:600;color:#111827;">{{ $student->name }}</div>
                                        @if($isDone)
                                            <span style="font-size:0.7rem;color:#16a34a;font-weight:600;">✅ Sudah mengerjakan</span>
                                        @elseif(!$isAllowed)
                                            <span style="font-size:0.7rem;color:#dc2626;font-weight:600;">🚫 Tidak diizinkan</span>
                                        @else
                                            <span style="font-size:0.7rem;color:#6b7280;">⏳ Belum mengerjakan</span>
                                        @endif
                                    </div>
                                </div>
                                <form method="POST" action="{{ route('guru.schedules.permission', [$s, $student]) }}">
                                    @csrf
                                    <button type="submit"
                                        style="padding:0.3rem 0.65rem;border-radius:6px;font-size:0.72rem;font-weight:700;cursor:pointer;border:1px solid {{ $isAllowed ? '#fca5a5' : '#86efac' }};background:{{ $isAllowed ? '#fef2f2' : '#f0fdf4' }};color:{{ $isAllowed ? '#dc2626' : '#166534' }};">
                                        {{ $isAllowed ? '🚫 Tidak Izinkan' : '✅ Izinkan' }}
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
                @else
                <div style="background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.82rem;color:#92400e;">
                    ⚠️ Tidak ada siswa terdaftar di kelas {{ $s->classRoom->name ?? '-' }}.
                </div>
                @endif

                {{-- ===== TOMBOL AKSI UTAMA ===== --}}
                <div style="display:flex;gap:0.5rem;">
                    <form method="POST" action="{{ route('guru.schedules.toggle', $s) }}" style="flex:1;">
                        @csrf
                        @if($s->is_active)
                            <button type="submit" style="width:100%;padding:0.65rem;border:1px solid #fca5a5;background:#fef2f2;color:#dc2626;border-radius:10px;font-size:0.88rem;font-weight:700;cursor:pointer;">
                                ⏸️ Nonaktifkan Ujian
                            </button>
                        @else
                            <button type="submit" style="width:100%;padding:0.65rem;border:none;background:linear-gradient(90deg,#16a34a,#15803d);color:white;border-radius:10px;font-size:0.88rem;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(22,163,74,0.3);">
                                ▶️ Aktifkan Ujian
                            </button>
                        @endif
                    </form>
                    {{-- Tombol Rekap Nilai --}}
                    <a href="{{ route('guru.exam-results', $s->id) }}"
                       style="padding:0.65rem 0.75rem;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;border-radius:10px;font-size:0.8rem;font-weight:700;text-decoration:none;display:flex;align-items:center;white-space:nowrap;"
                       title="Lihat Rekap Nilai">📊</a>
                    <form method="POST" action="{{ route('guru.schedules.destroy', $s) }}" onsubmit="return confirm('Hapus ujian ini dan semua nilainya?')">
                        @csrf @method('DELETE')
                        <button type="submit" style="padding:0.65rem 0.85rem;border:1px solid #e5e7eb;background:white;color:#9ca3af;border-radius:10px;font-size:0.85rem;cursor:pointer;" title="Hapus">🗑️</button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div style="text-align:center;padding:3rem 2rem;background:#f9fafb;border-radius:12px;color:#6b7280;border:2px dashed #e5e7eb;">
            <div style="font-size:3rem;margin-bottom:1rem;">🖥️</div>
            <h3 style="font-weight:700;margin-bottom:0.5rem;color:#374151;">Belum Ada Ujian</h3>
            <p style="font-size:0.9rem;">Gunakan form di bawah untuk mendaftarkan ujian baru.</p>
        </div>
    @endforelse
</div>

{{-- ======================== FORM DAFTARKAN UJIAN BARU ======================== --}}
<div class="card">
    <h3 style="font-weight:700;font-size:1rem;margin-bottom:0.4rem;">➕ Daftarkan Ujian Baru</h3>
    <p style="font-size:0.82rem;color:#6b7280;margin-bottom:1.25rem;">
        Ujian dibuat berstatus <strong>Tidak Aktif</strong>. Atur durasi pada kartu ujian, lalu tekan <strong>"▶️ Aktifkan Ujian"</strong>.
    </p>
    <form method="POST" action="{{ route('guru.schedules.store') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="input-group">
                <label class="input-label">Mata Pelajaran</label>
                <select name="subject_id" class="form-control" required>
                    <option value="">Pilih mata pelajaran...</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
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
                <label class="input-label">Durasi Awal (menit)</label>
                <input type="number" name="duration" class="form-control" value="90" min="10" max="600" required>
                <div style="font-size:0.75rem;color:#6b7280;margin-top:0.3rem;">⏱️ Dapat diubah kembali sebelum diaktifkan.</div>
            </div>
            <div class="input-group">
                <label class="input-label">KKM (Nilai Minimum Lulus)</label>
                <input type="number" name="kkm" class="form-control" value="75" min="0" max="100" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.25rem;padding:0.75rem 2rem;">📋 Daftarkan Ujian</button>
    </form>
</div>

<script>
// Adjust durasi pada form kartu (sebelum aktif)
function adjustDur(btn, delta) {
    const input = btn.closest('form').querySelector('input[name="duration"]');
    let val = Math.max(10, Math.min(600, parseInt(input.value) + delta));
    input.value = val;
}

// Adjust tambah waktu (saat aktif)
function adjustAdd(btn, delta) {
    const input = btn.closest('form').querySelector('input[name="minutes"]');
    let val = Math.max(1, Math.min(60, parseInt(input.value) + delta));
    input.value = val;
}

// ======= Live Countdown Timers =======
const activeTimers = {};

@foreach($schedules as $s)
@if($s->is_active && $s->remaining_seconds > 0)
activeTimers[{{ $s->id }}] = {{ (int) $s->remaining_seconds }};
@endif
@endforeach

function formatTime(secs) {
    const h = Math.floor(secs / 3600);
    const m = Math.floor((secs % 3600) / 60);
    const s = secs % 60;
    if (h > 0) return `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
    return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
}

/**
 * Nonaktifkan kartu ujian di DOM secara langsung tanpa reload halaman.
 */
function deactivateCardDOM(id) {
    const card = document.getElementById(`card-${id}`);
    if (!card) return;

    // 1. Ubah bar atas dari hijau ke abu-abu
    const topBar = card.querySelector('div[style*="height:4px"]');
    if (topBar) topBar.style.background = '#e5e7eb';

    // 2. Ganti semua badge status (bulat) menjadi tidak aktif
    card.querySelectorAll('span[style*="border-radius:999px"]').forEach(b => {
        b.style.background  = '#f3f4f6';
        b.style.color       = '#6b7280';
        b.style.borderColor = '#d1d5db';
        b.textContent = '⏸️ TIDAK AKTIF';
    });

    // 3. Hapus timer dari activeTimers agar tikTaknya berhenti
    delete activeTimers[id];
}

function tickAllTimers() {
    Object.keys(activeTimers).forEach(id => {
        activeTimers[id]--;
        const el = document.getElementById(`timer-${id}`);
        if (!el) return;
        if (activeTimers[id] <= 0) {
            el.textContent = '00:00';
            el.style.color = '#dc2626';
            expireSchedule(id);
        } else {
            el.textContent = formatTime(activeTimers[id]);
            if (activeTimers[id] < 300) {
                el.style.color = '#dc2626';
            } else if (activeTimers[id] < 600) {
                el.style.color = '#d97706';
            }
        }
    });
}

async function expireSchedule(id) {
    // Langsung update DOM — tidak perlu reload halaman
    deactivateCardDOM(id);
    try {
        await fetch(`/api/schedules/${id}/expire`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        });
        // Cascade event ke siswa sudah di-handle oleh backend (ExamStateChanged)
    } catch(e) { console.warn('Expire API error:', e); }
}

if (Object.keys(activeTimers).length > 0) {
    setInterval(tickAllTimers, 1000);
}
</script>
@endsection

