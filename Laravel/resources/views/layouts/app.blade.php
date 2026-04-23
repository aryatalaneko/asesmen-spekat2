<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('header_title', 'Dashboard') — SMP Katolik St. Johanis</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="font-family: 'Inter', sans-serif;">
@auth
<div class="app-container">
    <aside class="sidebar">
        <div class="sidebar-brand" style="display:flex;align-items:center;gap:0.75rem;">
            <img src="{{ asset('images/logo.png') }}" alt="Logo SMP Katolik St. Johanis" style="width:45px;height:45px;object-fit:contain;border-radius:50%;border:2px solid var(--color-secondary);background:white;padding:2px;box-shadow:0 2px 4px rgba(0,0,0,0.1);">
            <div style="line-height:1.2;">
                <span style="font-size:0.65rem;display:block;font-weight:700;color:var(--color-secondary);text-transform:uppercase;letter-spacing:0.5px;">SMP Katolik</span>
                <span style="font-size:0.9rem;font-weight:800;color:var(--color-primary);">St. Johanis Laikit</span>
            </div>
        </div>
        <ul class="sidebar-menu">
            @if(auth()->user()->role === 'admin')
                <li><a href="{{ route('admin.users.index') }}"    class="sidebar-link {{ request()->is('admin/users*') ? 'active' : '' }}">👥 Manajemen Sistem</a></li>
                <li><a href="{{ route('admin.print-cards') }}"    class="sidebar-link {{ request()->is('admin/cetak-kartu*') ? 'active' : '' }}">🖨️ Cetak Kartu</a></li>
            @elseif(auth()->user()->role === 'guru')
                <li><a href="{{ route('guru.questions.index') }}" class="sidebar-link {{ request()->is('guru/questions*') ? 'active' : '' }}">📝 Bank Soal</a></li>
                <li><a href="{{ route('guru.schedules.index') }}" class="sidebar-link {{ request()->is('guru/schedules*') ? 'active' : '' }}">🖥️ Monitoring Ujian</a></li>
                <li><a href="{{ route('guru.analysis.index') }}"  class="sidebar-link {{ request()->is('guru/analysis*') ? 'active' : '' }}">🤖 Analisis Hasil Ujian</a></li>
            @elseif(auth()->user()->role === 'siswa')
                <li><a href="{{ route('siswa.exams.index') }}"   class="sidebar-link {{ request()->is('siswa/exams*') ? 'active' : '' }}">📋 Ujian Aktif</a></li>
                <li><a href="{{ route('siswa.results.index') }}" class="sidebar-link {{ request()->is('siswa/results*') ? 'active' : '' }}">🎯 Riwayat Nilai</a></li>
            @endif
        </ul>
        <div style="padding:1rem 1.5rem;border-top:1px solid var(--color-border);margin-top:auto;">
            <div style="font-size:0.75rem;color:#888;font-weight:600;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.5rem;">Versi Sistem</div>
            <div style="font-size:0.75rem;color:#aaa;">Assessment v2.0 — K-Means</div>
        </div>
    </aside>

    <div class="main-content">
        <header class="top-header">
            <div>
                <h2 style="font-size:1.25rem;font-weight:800;color:var(--color-primary);margin:0;">@yield('header_title', 'Dashboard')</h2>
            </div>
            <div class="header-user-info">
                <span class="user-role-badge">{{ strtoupper(auth()->user()->role) }}</span>
                <span style="font-weight:500;color:#4b5563;">Welcome, <strong style="color:var(--color-primary);">{{ auth()->user()->name }}</strong></span>
                <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="padding:0.25rem 0.75rem;font-size:0.875rem;">Logout</button>
                </form>
            </div>
        </header>

        <main class="content-wrapper">
            @if(session('success'))
                <div style="background:#d1fae5;border-left:4px solid #10b981;padding:0.85rem 1.25rem;border-radius:8px;color:#065f46;font-weight:600;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div style="background:#fee2e2;border-left:4px solid #ef4444;padding:0.85rem 1.25rem;border-radius:8px;color:#991b1b;font-weight:600;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                    ❌ {{ session('error') }}
                </div>
            @endif
            @if(session('info'))
                <div style="background:#eff6ff;border-left:4px solid #3b82f6;padding:0.85rem 1.25rem;border-radius:8px;color:#1e40af;font-weight:600;margin-bottom:1rem;">
                    ℹ️ {{ session('info') }}
                </div>
            @endif
            @if($errors->any())
                <div style="background:#fee2e2;border-left:4px solid #ef4444;padding:0.85rem 1.25rem;border-radius:8px;color:#991b1b;margin-bottom:1rem;">
                    <strong>Periksa form Anda:</strong>
                    <ul style="margin:0.5rem 0 0 1.25rem;font-size:0.875rem;">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
@else
    @yield('content')
@endauth
</body>
</html>
