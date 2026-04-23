@extends('layouts.exam')

@section('exam_title', $title)

@section('content')

{{-- ============================================================
     EXAM PAGE - Full Screen Isolated Layout
     Design: Modern SaaS Assessment UI (Tailwind CSS)
     ============================================================ --}}

<div class="flex flex-col min-h-screen font-sans bg-slate-50">

    {{-- ====================== STICKY TOP HEADER ====================== --}}
    <header class="sticky top-0 z-50 bg-white/80 backdrop-blur-xl border-b border-slate-100 shadow-sm">
        {{-- Neon Progress Bar --}}
        <div class="absolute top-0 left-0 w-full h-[3px] bg-slate-100">
            <div id="progressBar"
                 class="h-full bg-[#c6ff00] transition-all duration-700 ease-in-out progress-glow"
                 style="width: 0%;">
            </div>
        </div>

        <div class="max-w-[1400px] mx-auto px-8 h-20 flex justify-between items-center">
            {{-- Left: Logo & Assessment Info --}}
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-indigo-950 flex items-center justify-center shadow-lg shadow-indigo-900/20">
                        <div class="w-3.5 h-3.5 rounded-full bg-[#c6ff00]"></div>
                    </div>
                    <span class="text-xl font-black text-indigo-950 tracking-tight">St. Johanis.</span>
                </div>
                <div class="w-px h-8 bg-slate-200"></div>
                <div>
                    <h1 class="text-sm font-bold text-indigo-900 leading-tight">{{ $schedule->subject->name ?? 'Ujian' }}</h1>
                    <p class="text-xs font-medium text-slate-500">{{ auth()->user()->name }}</p>
                </div>
            </div>

            {{-- Right: Timer --}}
            <div class="flex items-center gap-5">
                <div class="flex items-center gap-3 bg-white border border-slate-200 pl-4 pr-5 py-2.5 rounded-full shadow-sm">
                    {{-- Clock Icon SVG --}}
                    <svg class="text-indigo-500 timer-pulse w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                    </svg>
                    <span id="timerDisplay" class="font-mono font-bold text-indigo-950 text-lg tracking-tight">{{ gmdate('H:i:s', $remainingSeconds) }}</span>
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-widest ml-1">Sisa</span>
                </div>
            </div>
        </div>
    </header>

    {{-- ====================== MAIN CONTENT ====================== --}}
    <main class="flex-1 max-w-[1400px] w-full mx-auto px-8 py-10 flex gap-10">

        {{-- ===== LEFT: Question Area ===== --}}
        <div class="flex-1 flex flex-col">

            {{-- Question header rendered per-question via JS --}}
            <div class="mb-8">
                <span id="questionBadge" class="inline-block px-3 py-1 bg-indigo-50 text-indigo-700 text-xs font-bold rounded-full mb-4 uppercase tracking-wider">
                    Soal 1 dari {{ $questions->count() }}
                </span>
                <h2 id="questionText" class="text-2xl font-extrabold text-indigo-950 leading-tight"></h2>
            </div>

            {{-- PG Option Cards (hidden for essay) --}}
            <div class="flex-1 flex flex-col gap-4" id="optionContainer">
                @foreach(['a','b','c','d','e'] as $letter)
                <button type="button"
                    onclick="selectOption('{{ $letter }}')"
                    id="option-{{ $letter }}"
                    data-value="{{ $letter }}"
                    class="option-card relative flex items-center p-5 rounded-[22px] text-left transition-all duration-200 border-2 border-white bg-white shadow-sm hover:border-slate-200 hover:shadow-md"
                    style="{{ $letter === 'e' ? 'display:none;' : '' }}">

                    <div id="check-{{ $letter }}" class="option-check w-6 h-6 rounded-full flex items-center justify-center mr-5 shrink-0 transition-all duration-200 bg-slate-100 border border-slate-200">
                        <svg class="check-icon w-3.5 h-3.5 text-transparent" fill="none" stroke="currentColor" stroke-width="3.5" viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    <span id="letter-{{ $letter }}" class="option-letter w-7 h-7 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-xs font-bold text-slate-400 mr-4 shrink-0 transition-colors duration-200">
                        {{ strtoupper($letter) }}
                    </span>
                    <span id="text-{{ $letter }}" class="option-text text-base font-medium text-slate-600 leading-snug transition-colors duration-200"></span>
                </button>
                @endforeach
            </div>

            {{-- Essay Textarea (hidden for PG) --}}
            <div id="essayContainer" class="hidden flex-1 flex flex-col">
                <div class="bg-white rounded-[22px] border-2 border-slate-100 p-6 shadow-sm flex flex-col gap-3">
                    <div class="flex items-center gap-2 text-sm font-semibold text-indigo-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Soal Uraian — Tulis Jawaban Anda
                    </div>
                    <textarea
                        id="essayTextarea"
                        oninput="saveEssayAnswer(this.value)"
                        rows="8"
                        placeholder="Tulis jawaban Anda di sini..."
                        style="border:1px solid #e5e7eb;border-radius:12px;padding:1rem;font-size:0.95rem;line-height:1.6;resize:vertical;outline:none;width:100%;font-family:inherit;"
                        onfocus="this.style.borderColor='#6366f1'"
                        onblur="this.style.borderColor='#e5e7eb'"
                    ></textarea>
                    <div class="text-xs text-slate-400">Jawaban akan tersimpan otomatis saat Anda mengetik.</div>
                </div>
            </div>

            {{-- Navigation Footer --}}
            <div class="mt-10 flex justify-between items-center pt-8 border-t border-slate-200">
                <button onclick="prevQuestion()"
                    class="flex items-center gap-2 text-slate-500 hover:text-indigo-900 font-semibold transition-colors px-4 py-2 rounded-xl hover:bg-slate-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <polyline points="15 18 9 12 15 6"/>
                    </svg>
                    Sebelumnya
                </button>

                <div class="flex items-center gap-4">
                    {{-- Flag toggle --}}
                    <button onclick="toggleFlag()" id="flagBtn"
                        class="flex items-center gap-2 text-sm font-semibold text-slate-400 hover:text-amber-500 transition-colors px-3 py-2 rounded-xl hover:bg-amber-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/><line x1="4" y1="22" x2="4" y2="15"/>
                        </svg>
                        <span id="flagBtnText">Tandai</span>
                    </button>

                    <button onclick="nextQuestion()"
                        id="nextBtn"
                        class="bg-indigo-950 text-white px-7 py-3.5 rounded-full font-bold text-sm tracking-wide hover:bg-indigo-800 transition-all shadow-xl shadow-indigo-950/20 flex items-center gap-2 group">
                        Selanjutnya
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <polyline points="9 18 15 12 9 6"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- ===== RIGHT: Navigator Sidebar ===== --}}
        <aside class="w-[320px] shrink-0">
            <div class="bg-white rounded-[28px] p-7 shadow-sm border border-slate-100 sticky top-[100px]">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="font-bold text-indigo-950 text-base">Navigasi Soal</h3>
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ $questions->count() }} Soal</span>
                </div>

                {{-- Question Grid --}}
                <div class="grid grid-cols-5 gap-2.5" id="questionGrid">
                    @foreach($questions as $idx => $q)
                    <button type="button"
                        onclick="jumpToQuestion({{ $idx }})"
                        id="grid-{{ $idx }}"
                        class="relative w-full aspect-square rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-500 text-sm font-semibold flex items-center justify-center transition-all duration-200 hover:border-indigo-300 hover:text-indigo-700">
                        {{ $idx + 1 }}
                        {{-- Status dot --}}
                        <span id="dot-{{ $idx }}" class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full border-2 border-white hidden"></span>
                    </button>
                    @endforeach
                </div>

                {{-- Legend --}}
                <div class="mt-6 pt-5 border-t border-slate-100 flex flex-col gap-2.5">
                    <div class="flex items-center gap-3 text-xs font-semibold text-slate-400">
                        <div class="relative w-4 h-4 rounded bg-indigo-50 border-2 border-indigo-200 shrink-0">
                            <div class="absolute -top-1 -right-1 w-2 h-2 bg-[#c6ff00] rounded-full border border-white"></div>
                        </div>
                        Sudah dijawab
                    </div>
                    <div class="flex items-center gap-3 text-xs font-semibold text-slate-400">
                        <div class="relative w-4 h-4 rounded bg-amber-50 border-2 border-amber-200 shrink-0">
                            <div class="absolute -top-1 -right-1 w-2 h-2 bg-amber-400 rounded-full border border-white"></div>
                        </div>
                        Ditandai/Ragu
                    </div>
                    <div class="flex items-center gap-3 text-xs font-semibold text-slate-400">
                        <div class="w-4 h-4 rounded border-2 border-slate-100 bg-slate-50 shrink-0"></div>
                        Belum dijawab
                    </div>
                </div>

                {{-- Submit Button --}}
                <div class="mt-7">
                    <p class="text-center text-[11px] text-slate-400 font-medium mb-3">
                        <span id="answeredCount">0</span> dari {{ $questions->count() }} soal terjawab
                    </p>
                    <button onclick="confirmSubmit()"
                        class="w-full bg-[#c6ff00] text-indigo-950 font-bold py-3.5 rounded-full hover:bg-[#b3e600] transition-all progress-glow-sm flex items-center justify-center gap-2 shadow-lg">
                        {{-- Checkmark SVG --}}
                        <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        Selesai & Submit
                    </button>
                </div>
            </div>
        </aside>
    </main>
</div>

{{-- Hidden Confirmation Modal --}}
<div id="confirmModal" class="hidden fixed inset-0 bg-indigo-950/60 backdrop-blur-sm z-[999] flex items-center justify-center">
    <div class="bg-white rounded-[28px] p-8 max-w-md w-full mx-4 shadow-2xl">
        <div class="w-14 h-14 rounded-2xl bg-[#c6ff00] flex items-center justify-center mb-5">
            <svg class="w-7 h-7 text-indigo-950" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <h3 class="text-xl font-extrabold text-indigo-950 mb-2">Submit Jawaban?</h3>
        <p class="text-slate-500 text-sm mb-6">Anda telah menjawab <strong id="modalAnswered" class="text-indigo-700">0</strong> dari <strong>{{ $questions->count() }}</strong> soal. Pastikan semua jawaban sudah benar sebelum submit.</p>
        <div class="flex gap-3">
            <button onclick="closeModal()" class="flex-1 py-3 rounded-full border-2 border-slate-200 font-bold text-slate-600 hover:bg-slate-50 transition-colors">Kembali</button>
            <button onclick="submitExam()" class="flex-1 py-3 rounded-full bg-indigo-950 font-bold text-white hover:bg-indigo-800 transition-colors">Submit Sekarang</button>
        </div>
    </div>
</div>

{{-- Hidden real form for submission --}}
<form method="POST" action="{{ route('siswa.exams.submit', $schedule->id) }}" id="examForm">
    @csrf
    <div id="hiddenAnswers"></div>
</form>

<script>
// ============================================================
// EXAM ENGINE JS - Single Page Question Navigator
// ============================================================

// Data from PHP/Blade
const questions = @json($questions->values());
const totalQuestions = questions.length;

// State
let currentIndex = 0;
let answers = {};    // { questionId: 'a' | 'b' | 'c' | 'd' }
let flagged = {};    // { index: true/false }
// Sisa waktu tersinkronisasi dengan waktu aktivasi guru
let timerSeconds = {{ (int) $remainingSeconds }};
const scheduleId = {{ $schedule->id }};

// ---- RENDER QUESTION ----
function renderQuestion(index) {
    const q = questions[index];
    if (!q) return;

    const isEssay = q.type === 'essay';

    // Update badge
    document.getElementById('questionBadge').textContent = `Soal ${index + 1} dari ${totalQuestions}`;

    // Tipe label setelah badge
    const typeLabel = isEssay ? ' 📝 Uraian' : ' 📋 Pilihan Ganda';
    document.getElementById('questionBadge').textContent = `Soal ${index + 1} dari ${totalQuestions} ${typeLabel}`;

    // Update question text with animation
    const qTextEl = document.getElementById('questionText');
    qTextEl.style.opacity = '0';
    qTextEl.style.transform = 'translateY(8px)';
    setTimeout(() => {
        qTextEl.textContent = q.question_text;
        qTextEl.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        qTextEl.style.opacity = '1';
        qTextEl.style.transform = 'translateY(0)';
    }, 80);

    const optContainer   = document.getElementById('optionContainer');
    const essayContainer = document.getElementById('essayContainer');

    if (isEssay) {
        // Tampilkan textarea, sembunyikan PG
        optContainer.classList.add('hidden');
        optContainer.classList.remove('flex-1', 'flex', 'flex-col');
        essayContainer.classList.remove('hidden');
        essayContainer.classList.add('flex-1', 'flex', 'flex-col');

        // Restore essai jawaban
        const textarea = document.getElementById('essayTextarea');
        textarea.value = answers[q.id] || '';
    } else {
        // Tampilkan PG, sembunyikan textarea
        optContainer.classList.remove('hidden');
        optContainer.classList.add('flex-1', 'flex', 'flex-col');
        essayContainer.classList.add('hidden');
        essayContainer.classList.remove('flex-1', 'flex', 'flex-col');

        // Update options — termasuk opsi E (hanya tampil jika ada datanya)
        const opts = { a: q.option_a, b: q.option_b, c: q.option_c, d: q.option_d, e: q.option_e };
        ['a','b','c','d','e'].forEach(letter => {
            const el    = document.getElementById(`text-${letter}`);
            const card  = document.getElementById(`option-${letter}`);
            if (!el || !card) return;

            if (letter === 'e') {
                // Opsi E: tampilkan hanya jika datanya ada dan tidak kosong
                if (opts.e && opts.e.trim() !== '') {
                    card.style.display = '';
                    el.textContent = opts.e;
                } else {
                    card.style.display = 'none';
                    el.textContent = '';
                }
            } else {
                el.textContent = opts[letter] || '';
            }
        });

        // Restore selection state (termasuk 'e')
        const savedAnswer = answers[q.id] || null;
        ['a','b','c','d','e'].forEach(letter => {
            const card = document.getElementById(`option-${letter}`);
            // Hanya apply style jika card terlihat
            if (card && card.style.display !== 'none') {
                applyOptionStyle(letter, savedAnswer === letter);
            } else if (card) {
                applyOptionStyle(letter, false); // reset hidden card
            }
        });
    }

    // Update grid active state
    updateGrid(index);

    // Update flag button
    updateFlagBtn(index);

    // Update Next/Finish button
    const nextBtn = document.getElementById('nextBtn');
    if (index === totalQuestions - 1) {
        nextBtn.innerHTML = `Selesai
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
            </svg>`;
        nextBtn.onclick = confirmSubmit;
    } else {
        nextBtn.innerHTML = `Selanjutnya
            <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <polyline points="9 18 15 12 9 6"/>
            </svg>`;
        nextBtn.onclick = nextQuestion;
    }

    // Update progress bar
    const answered = Object.keys(answers).length;
    const progress = (answered / totalQuestions) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
    document.getElementById('answeredCount').textContent = answered;
}

// ---- OPTION STYLING ----
function applyOptionStyle(letter, isSelected) {
    const card = document.getElementById(`option-${letter}`);
    const check = document.getElementById(`check-${letter}`);
    const checkIcon = check.querySelector('.check-icon');
    const letterBadge = document.getElementById(`letter-${letter}`);
    const textEl = document.getElementById(`text-${letter}`);

    if (isSelected) {
        card.className = card.className.replace(/border-white|border-slate-200/g, '') + ' border-indigo-500';
        card.style.backgroundColor = 'rgb(238 242 255)'; // indigo-50
        card.style.transform = 'translateX(6px)';
        card.style.boxShadow = '0 4px 20px 0 rgba(99,102,241,0.12)';
        check.className = check.className.replace(/bg-slate-100|border-slate-200/g, '') + ' bg-[#c6ff00] border-[#c6ff00]';
        checkIcon.classList.remove('text-transparent');
        checkIcon.classList.add('text-indigo-950');
        letterBadge.className = letterBadge.className.replace(/text-slate-400|bg-slate-50|border-slate-100/g, '') + ' text-indigo-700 bg-indigo-100 border-indigo-200';
        textEl.classList.remove('text-slate-600');
        textEl.classList.add('text-indigo-950', 'font-semibold');
    } else {
        card.className = 'option-card relative flex items-center p-5 rounded-[22px] text-left transition-all duration-200 border-2 border-white bg-white shadow-sm hover:border-slate-200 hover:shadow-md';
        card.style.backgroundColor = '';
        card.style.transform = '';
        card.style.boxShadow = '';
        check.className = 'option-check w-6 h-6 rounded-full flex items-center justify-center mr-5 shrink-0 transition-all duration-200 bg-slate-100 border border-slate-200';
        checkIcon.classList.add('text-transparent');
        checkIcon.classList.remove('text-indigo-950');
        letterBadge.className = 'option-letter w-7 h-7 rounded-lg bg-slate-50 border border-slate-100 flex items-center justify-center text-xs font-bold text-slate-400 mr-4 shrink-0 transition-colors duration-200';
        textEl.className = 'option-text text-base font-medium text-slate-600 leading-snug transition-colors duration-200';
    }
}

// ---- SELECT OPTION (PG) ----
function selectOption(letter) {
    const q = questions[currentIndex];
    if (!q) return;
    answers[q.id] = letter;
    // Reset semua opsi yang visible, set yang dipilih
    ['a','b','c','d','e'].forEach(l => {
        const card = document.getElementById(`option-${l}`);
        if (card && card.style.display !== 'none') {
            applyOptionStyle(l, l === letter);
        }
    });

    // Update dot on grid
    const dot = document.getElementById(`dot-${currentIndex}`);
    if (dot) {
        dot.classList.remove('hidden', 'bg-amber-400');
        dot.classList.add('bg-[#c6ff00]');
    }

    // Update progress
    const answered = Object.keys(answers).length;
    const progress = (answered / totalQuestions) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
    document.getElementById('answeredCount').textContent = answered;
}

// ---- SAVE ESSAY ANSWER ----
function saveEssayAnswer(value) {
    const q = questions[currentIndex];
    if (!q) return;
    if (value.trim()) {
        answers[q.id] = value;
    } else {
        delete answers[q.id];
    }

    // Update dot on grid
    const dot = document.getElementById(`dot-${currentIndex}`);
    if (dot) {
        if (value.trim()) {
            dot.classList.remove('hidden', 'bg-amber-400');
            dot.classList.add('bg-[#c6ff00]');
        } else {
            dot.classList.add('hidden');
        }
    }

    // Update progress
    const answered = Object.keys(answers).length;
    const progress = (answered / totalQuestions) * 100;
    document.getElementById('progressBar').style.width = `${progress}%`;
    document.getElementById('answeredCount').textContent = answered;
}

// ---- NAVIGATION ----
function nextQuestion() {
    if (currentIndex < totalQuestions - 1) {
        currentIndex++;
        renderQuestion(currentIndex);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function prevQuestion() {
    if (currentIndex > 0) {
        currentIndex--;
        renderQuestion(currentIndex);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

function jumpToQuestion(index) {
    currentIndex = index;
    renderQuestion(currentIndex);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ---- GRID STYLING ----
function updateGrid(activeIndex) {
    questions.forEach((q, idx) => {
        const btn = document.getElementById(`grid-${idx}`);
        if (!btn) return;
        if (idx === activeIndex) {
            btn.className = 'relative w-full aspect-square rounded-xl border-2 border-indigo-950 bg-indigo-950 text-[#c6ff00] text-sm font-bold flex items-center justify-center transition-all duration-200 scale-110 shadow-md z-10';
        } else if (flagged[idx]) {
            btn.className = 'relative w-full aspect-square rounded-xl border-2 border-amber-200 bg-amber-50 text-amber-700 text-sm font-semibold flex items-center justify-center transition-all duration-200 hover:border-amber-300';
        } else if (answers[q.id]) {
            btn.className = 'relative w-full aspect-square rounded-xl border-2 border-indigo-100 bg-indigo-50 text-indigo-700 text-sm font-semibold flex items-center justify-center transition-all duration-200 hover:border-indigo-300';
        } else {
            btn.className = 'relative w-full aspect-square rounded-xl border-2 border-slate-100 bg-slate-50 text-slate-500 text-sm font-semibold flex items-center justify-center transition-all duration-200 hover:border-slate-300 hover:text-indigo-700';
        }
    });
}

// ---- FLAG ----
function toggleFlag() {
    flagged[currentIndex] = !flagged[currentIndex];
    updateFlagBtn(currentIndex);
    updateGrid(currentIndex);
    const dot = document.getElementById(`dot-${currentIndex}`);
    if (dot && !answers[questions[currentIndex]?.id]) {
        if (flagged[currentIndex]) {
            dot.classList.remove('hidden', 'bg-[#c6ff00]');
            dot.classList.add('bg-amber-400');
        } else {
            dot.classList.add('hidden');
        }
    }
}

function updateFlagBtn(index) {
    const btn = document.getElementById('flagBtn');
    const text = document.getElementById('flagBtnText');
    if (flagged[index]) {
        btn.classList.add('text-amber-500');
        btn.classList.remove('text-slate-400');
        text.textContent = 'Ditandai';
    } else {
        btn.classList.remove('text-amber-500');
        btn.classList.add('text-slate-400');
        text.textContent = 'Tandai';
    }
}

// ---- SUBMIT ----
function confirmSubmit() {
    const answered = Object.keys(answers).length;
    document.getElementById('modalAnswered').textContent = answered;
    document.getElementById('confirmModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('confirmModal').classList.add('hidden');
}

function submitExam() {
    // Build hidden answer inputs
    const container = document.getElementById('hiddenAnswers');
    container.innerHTML = '';
    Object.entries(answers).forEach(([qId, val]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `answers[${qId}]`;
        input.value = val;
        container.appendChild(input);
    });
    document.getElementById('examForm').submit();
}

// ---- COUNTDOWN TIMER ----
function startTimer() {
    const timerEl = document.getElementById('timerDisplay');
    const interval = setInterval(() => {
        if (timerSeconds <= 0) {
            clearInterval(interval);
            // Waktu habis — auto submit
            timerEl.textContent = '00:00';
            timerEl.style.color = '#ef4444';
            setTimeout(() => submitExam(), 500);
            return;
        }
        timerSeconds--;

        // Format waktu
        const h = Math.floor(timerSeconds / 3600);
        const m = Math.floor((timerSeconds % 3600) / 60);
        const s = timerSeconds % 60;
        if (h > 0) {
            timerEl.textContent = `${String(h).padStart(2,'0')}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        } else {
            timerEl.textContent = `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
        }

        // Warna peringatan
        if (timerSeconds < 300) {
            timerEl.style.color = '#ef4444';
            timerEl.style.animation = 'pulse 1s infinite';
        } else if (timerSeconds < 600) {
            timerEl.style.color = '#f59e0b';
        }
    }, 1000);
}

// ---- INIT ----
renderQuestion(0);
startTimer();

// ---- WEBSOCKETS REAL-TIME LISTENER ----
document.addEventListener('DOMContentLoaded', function () {
    if (typeof window.Echo === 'undefined') {
        console.warn('Laravel Echo belum tersedia. Cek apakah Reverb berjalan dan npm run build sudah dijalankan.');
        return;
    }

    const currentUserId = {{ auth()->id() }};

    window.Echo.channel('exam.{{ $schedule->id }}')
        .listen('.ExamStateChanged', (e) => {
            console.log('[Reverb] Event diterima:', e);

            if (e.action === 'duration_changed' || e.action === 'duration_extended') {
                // Sinkronisasi ulang timer dari server
                fetch('/api/schedules/{{ $schedule->id }}/remaining', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(r => r.json())
                .then(data => {
                    if (data.is_active && data.remaining_seconds > 0) {
                        timerSeconds = data.remaining_seconds;
                        // Tampilkan notifikasi non-blocking
                        const banner = document.getElementById('realtimeBanner');
                        if (banner) {
                            banner.textContent = '⏱️ Guru telah menambah waktu ujian!';
                            banner.style.display = 'block';
                            setTimeout(() => banner.style.display = 'none', 4000);
                        }
                    }
                });
            } else if (e.action === 'permission_changed') {
                if ((e.payload?.user_id ?? null) !== currentUserId) return;

                const banner = document.getElementById('realtimeBanner');

                if (e.payload?.allowed) {
                    if (banner) {
                        banner.textContent = 'Akses ujian Anda diaktifkan kembali oleh guru.';
                        banner.style.display = 'block';
                        setTimeout(() => banner.style.display = 'none', 4000);
                    }
                    return;
                }

                if (banner) {
                    banner.textContent = 'Akses ujian dicabut oleh guru. Jawaban akan langsung dikumpulkan.';
                    banner.style.display = 'block';
                }

                timerSeconds = 0;
            } else if (e.action === 'deactivated' || e.action === 'expired') {
                // Waktu habis atau ujian dihentikan paksa → set timer ke 0
                // setInterval di startTimer() akan catch timerSeconds <= 0 dan auto-submit
                console.log('[Reverb] Ujian dihentikan, memaksa submit...');
                timerSeconds = 0;
            }
        });
});
</script>

{{-- Banner notifikasi real-time (tidak memblokir) --}}
<div id="realtimeBanner" style="display:none;position:fixed;bottom:1.5rem;right:1.5rem;background:#1e40af;color:white;padding:0.75rem 1.5rem;border-radius:12px;font-weight:700;font-size:0.875rem;z-index:9999;box-shadow:0 8px 24px rgba(0,0,0,0.2);">
    ⏱️ Waktu ujian telah diperbarui!
</div>

@endsection
