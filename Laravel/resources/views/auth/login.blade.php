<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem Ujian Online SMP Katolik St. Johanis Laikit</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center selection:bg-yellow-400 selection:text-blue-900" style="font-family: 'Inter', sans-serif;">

    <div class="w-full max-w-md mx-auto px-4">

        {{-- Logo / Branding --}}
        <div class="text-center mb-8">
            <div class="inline-flex justify-center mb-4">
                <img src="{{ asset('images/logo.png') }}" alt="Logo SMP Katolik St. Johanis" class="w-24 h-24 object-contain shadow-lg rounded-full border-4 border-yellow-400 bg-white">
            </div>
            <h1 class="text-2xl font-extrabold text-blue-900 tracking-tight">Sistem Ujian Online</h1>
            <p class="text-slate-500 text-sm mt-1 font-medium">SMP Katolik St. Johanis Laikit</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-[28px] shadow-xl shadow-blue-900/10 border border-slate-100 p-8">

            <h2 class="text-lg font-bold text-blue-900 mb-1">Masuk ke Akun Anda</h2>
            <p class="text-sm text-slate-400 mb-7">Masukkan nama dan password yang diberikan oleh Admin.</p>

            {{-- Error Alert --}}
            @if ($errors->any())
                <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 rounded-2xl px-4 py-3.5 mb-6 text-sm font-medium">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <ul class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf

                {{-- Name Field --}}
                <div>
                    <label for="name" class="block text-sm font-semibold text-blue-900 mb-2">Nama Pengguna</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-4.5 h-4.5 text-slate-400 w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            placeholder="Masukkan nama Anda..."
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-blue-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all"
                        >
                    </div>
                </div>

                {{-- Password Field --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-blue-900 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-[18px] h-[18px] text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan password..."
                            class="w-full pl-11 pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-blue-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-600 focus:border-transparent transition-all"
                        >
                    </div>
                </div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    class="w-full bg-blue-700 text-yellow-400 font-bold py-3.5 rounded-xl hover:bg-blue-800 transition-all shadow-lg shadow-blue-900/20 flex items-center justify-center gap-2 mt-2">
                    Masuk Sekarang
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <polyline points="9 18 15 12 9 6"/>
                    </svg>
                </button>
            </form>
        </div>

        {{-- Footer Note --}}
        <p class="text-center text-xs text-slate-400 mt-6 font-medium">
            Hubungi Admin jika belum memiliki akun.
        </p>
    </div>

</body>
</html>
