@extends('layouts.app')
@section('header_title','Daftar Ujian')
@section('content')

<div style="background:linear-gradient(135deg,#1e40af,#3b82f6);padding:1.5rem;border-radius:12px;color:white;margin-bottom:1.5rem;">
    <h2 style="font-weight:800;font-size:1.25rem;margin:0;">📋 Ujian Kelas {{ auth()->user()->classRoom->name ?? '-' }}</h2>
    <p style="opacity:0.8;font-size:0.875rem;margin:0.25rem 0 0;">Ujian yang tersedia untuk kelas Anda akan muncul di bawah ini.</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(300px, 1fr));gap:1.5rem;">
    @forelse($schedules as $s)
        @php
            $isDone = in_array($s->id, $doneScheduleIds);
        @endphp
        <div class="card" id="card_schedule_{{ $s->id }}" style="border-top:4px solid {{ $isDone ? '#10b981' : ($s->is_active ? '#3b82f6' : '#9ca3af') }};">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1rem;">
                <h3 style="font-weight:700;font-size:1.1rem;margin:0;">{{ $s->subject->name ?? '-' }}</h3>
                <div id="badge_schedule_{{ $s->id }}">
                    @if($isDone)
                        <span style="background:#d1fae5;color:#065f46;padding:0.2rem 0.5rem;border-radius:6px;font-size:0.75rem;font-weight:700;">✅ SELESAI</span>
                    @elseif($s->is_active)
                        <span style="background:#dbeafe;color:#1e40af;padding:0.2rem 0.5rem;border-radius:6px;font-size:0.75rem;font-weight:700;">🔥 AKTIF</span>
                    @else
                        <span style="background:#f3f4f6;color:#4b5563;padding:0.2rem 0.5rem;border-radius:6px;font-size:0.75rem;font-weight:700;">⏳ BELUM AKTIF</span>
                    @endif
                </div>
            </div>

            <div style="font-size:0.85rem;color:#4b5563;margin-bottom:1.5rem;line-height:1.5;">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.25rem;">
                    <span>⏱️</span> Durasi: <strong>{{ $s->duration }} Menit</strong>
                </div>
                <div style="display:flex;align-items:center;gap:0.5rem;">
                    <span>🎯</span> KKM: <strong>{{ $s->kkm }}</strong>
                </div>
            </div>

            <div id="action_schedule_{{ $s->id }}">
                @if($isDone)
                    <button class="btn" style="width:100%;background:#f3f4f6;color:#9ca3af;cursor:not-allowed;" disabled>Telah Dikerjakan</button>
                @elseif($s->is_active)
                    <a href="{{ route('siswa.exams.take', $s->id) }}" class="btn btn-primary" style="display:block;text-align:center;width:100%;">Mulai Kerjakan</a>
                @else
                    <button class="btn" style="width:100%;background:#f3f4f6;color:#9ca3af;cursor:not-allowed;" disabled>Belum Tersedia</button>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column:1/-1;text-align:center;padding:3rem;background:#f9fafb;border-radius:12px;color:#6b7280;">
            <div style="font-size:3rem;margin-bottom:1rem;">☕</div>
            <h3 style="font-weight:700;">Belum ada jadwa ujian</h3>
            <p style="font-size:0.9rem;">Guru belum menambahkan jadwal ujian untuk kelas Anda.</p>
        </div>
    @endforelse
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Collect all schedules IDs rendered
        const schedules = @json($schedules->pluck('id'));
        const doneSchedules = @json($doneScheduleIds);

        if (window.Echo) {
            schedules.forEach(scheduleId => {
                // Ignore finished exams
                if (doneSchedules.includes(scheduleId)) return;

                window.Echo.channel('exam.' + scheduleId)
                    .listen('.ExamStateChanged', (e) => {
                        console.log('Real-time exam update:', e);
                        
                        const actionDiv = document.getElementById('action_schedule_' + scheduleId);
                        const badgeDiv = document.getElementById('badge_schedule_' + scheduleId);
                        const cardDiv = document.getElementById('card_schedule_' + scheduleId);

                        if (!actionDiv || !badgeDiv) return;

                        if (e.action === 'activated') {
                            cardDiv.style.borderTopColor = '#3b82f6';
                            badgeDiv.innerHTML = '<span style="background:#dbeafe;color:#1e40af;padding:0.2rem 0.5rem;border-radius:6px;font-size:0.75rem;font-weight:700;">🔥 AKTIF</span>';
                            actionDiv.innerHTML = '<a href="/siswa/exams/' + scheduleId + '/take" class="btn btn-primary" style="display:block;text-align:center;width:100%;">Mulai Kerjakan</a>';
                        } else if (e.action === 'deactivated' || e.action === 'expired') {
                            cardDiv.style.borderTopColor = '#9ca3af';
                            badgeDiv.innerHTML = '<span style="background:#f3f4f6;color:#4b5563;padding:0.2rem 0.5rem;border-radius:6px;font-size:0.75rem;font-weight:700;">⏳ BELUM AKTIF</span>';
                            actionDiv.innerHTML = '<button class="btn" style="width:100%;background:#f3f4f6;color:#9ca3af;cursor:not-allowed;" disabled>Belum Tersedia</button>';
                        }
                    });
            });
        }
    });
</script>
@endsection
