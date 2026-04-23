@extends('layouts.app')
@section('header_title','Pilih Konteks Bank Soal')
@section('content')

<div style="background:linear-gradient(135deg,#1e1b4b,#312e81);padding:1.5rem;border-radius:12px;color:white;margin-bottom:1.5rem;">
    <h2 style="font-weight:800;font-size:1.25rem;margin:0;">📝 Manajemen Bank Soal</h2>
    <p style="opacity:0.7;font-size:0.875rem;margin:0.25rem 0 0;">Anda memiliki lebih dari satu penugasan kelas/mata pelajaran. Pilih konteks bank soal yang ingin dikelola.</p>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(280px, 1fr));gap:1rem;">
    @forelse($teacherClasses as $tc)
        <a href="{{ route('guru.questions.index', ['class_id' => $tc->class_id, 'subject_id' => $tc->subject_id]) }}" 
           style="display:block;border:1px solid #e5e7eb;border-radius:12px;padding:1.5rem;text-decoration:none;color:inherit;transition:transform 0.2s, box-shadow 0.2s;"
           onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 15px -3px rgba(0, 0, 0, 0.1)'"
           onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
            <div style="font-size:1.25rem;margin-bottom:0.5rem;">📚</div>
            <h3 style="font-weight:800;font-size:1.1rem;margin:0 0 0.25rem 0;">{{ $tc->subject->name ?? '-' }}</h3>
            <span style="background:#eff6ff;color:#1d4ed8;padding:0.2rem 0.6rem;border-radius:6px;font-size:0.85rem;font-weight:600;">Kelas {{ $tc->classRoom->name ?? '-' }}</span>
        </a>
    @empty
        <div style="grid-column: 1 / -1; background: #fee2e2; border: 1px solid #fca5a5; padding: 2rem; border-radius: 12px; text-align: center; color: #991b1b;">
            <svg style="width: 3rem; height: 3rem; margin: 0 auto 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <h3 style="font-weight: 700; font-size: 1.25rem; margin-bottom: 0.5rem;">Belum Ada Penugasan</h3>
            <p>Anda belum ditugaskan ke kelas dan mata pelajaran apapun. Silahkan hubungi Admin untuk mengatur penugasan Anda terlebih dahulu.</p>
        </div>
    @endforelse
</div>
@endsection
