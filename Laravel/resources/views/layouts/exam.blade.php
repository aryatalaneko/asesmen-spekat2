<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('exam_title', 'Pengerjaan Ujian') - Sistem Ujian Online</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Inter', sans-serif; }
        /* Selected option highlight ring animation */
        @keyframes selected-glow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(99,102,241,0.15); }
            50% { box-shadow: 0 0 0 6px rgba(99,102,241,0.08); }
        }
        .option-card.selected { animation: selected-glow 2s ease-in-out infinite; }

        /* Timer pulse */
        @keyframes timer-pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }
        .timer-pulse { animation: timer-pulse 1.5s ease-in-out infinite; }

        /* Progress bar glow */
        .progress-glow {
            box-shadow: 0 0 12px 2px rgba(198,255,0,0.6);
        }
        .progress-glow-sm {
            box-shadow: 0 0 6px 1px rgba(198, 255, 0, 0.5);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-indigo-950 selection:bg-[#c6ff00] selection:text-indigo-900 overflow-x-hidden">
    @yield('content')
</body>
</html>
