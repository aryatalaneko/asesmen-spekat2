@extends('layouts.app')
@section('header_title','Bank Soal')
@section('content')

@php
    $editing = isset($editQuestion) && $editQuestion;
    $selectedType = old('type', $editing ? $editQuestion->type : 'pg');
    $weightInputValue = old('weight', $editing ? rtrim(rtrim(number_format((float) $editQuestion->weight, 2, '.', ''), '0'), '.') : '1');
@endphp

@if(session('success'))
    <div style="background:#dcfce7;color:#166534;border:1px solid #86efac;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
        ✅ {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;">
        ❌ {{ session('error') }}
    </div>
@endif
@if($errors->any())
    <div style="background:#fee2e2;border-left:4px solid #ef4444;padding:0.85rem 1.25rem;border-radius:8px;color:#991b1b;margin-bottom:1rem;">
        <strong>Periksa form soal Anda:</strong>
        <ul style="margin:0.5rem 0 0 1.25rem;font-size:0.875rem;">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div style="background:linear-gradient(135deg,#1e3a8a,#1d4ed8);padding:1.25rem 1.5rem;border-radius:12px;color:white;margin-bottom:1.5rem;display:flex;flex-wrap:wrap;gap:0.75rem;justify-content:space-between;align-items:center;">
    <div>
        <h2 style="font-weight:800;font-size:1.1rem;margin:0;">📚 {{ $currentMap->subject->name ?? '-' }} - Kelas {{ $currentMap->classRoom->name ?? 'Semua' }}</h2>
        <p style="opacity:0.8;font-size:0.8rem;margin:0.25rem 0 0;">Soal yang dibuat di sini hanya berlaku untuk mata pelajaran dan kelas ini.</p>
    </div>
    <a href="{{ route('guru.questions.index') }}" class="btn" style="background:rgba(255,255,255,0.2);color:white;border:none;font-size:0.85rem;">Ganti Kelas/Mapel</a>
</div>

<div style="background:linear-gradient(135deg,#0f172a,#1e3a8a);border-radius:14px;padding:1.25rem 1.5rem;margin-bottom:1.5rem;color:white;">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:1rem;">
        <div>
            <div style="font-weight:800;font-size:1rem;margin-bottom:0.2rem;">📥 Import Soal via Excel / CSV</div>
            <div style="font-size:0.8rem;opacity:0.75;">Upload file Excel (.xlsx/.xls) atau CSV (.csv) sesuai format template. Soal PG dan essay otomatis terdeteksi.</div>
        </div>
        <a href="{{ route('guru.questions.template') }}" style="background:#eab308;color:#0f172a;padding:0.45rem 1rem;border-radius:8px;font-weight:700;font-size:0.8rem;text-decoration:none;white-space:nowrap;">Unduh Template (.xlsx)</a>
    </div>

    <form action="{{ route('guru.questions.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="subject_id" value="{{ $subjectId }}">
        <input type="hidden" name="class_id" value="{{ $classId }}">
        <div style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:flex-end;">
            <div style="flex:1;min-width:200px;">
                <label style="display:block;font-size:0.78rem;font-weight:600;margin-bottom:0.4rem;opacity:0.8;">Pilih File (.xlsx, .xls, atau .csv)</label>
                <input type="file" name="excel_soal" accept=".xlsx,.xls,.csv" required
                    style="width:100%;padding:0.5rem 0.75rem;border-radius:8px;border:2px solid rgba(255,255,255,0.3);background:rgba(255,255,255,0.1);color:white;font-size:0.85rem;cursor:pointer;">
            </div>
            <button type="submit" style="background:#22c55e;color:white;padding:0.6rem 1.5rem;border-radius:8px;border:none;font-weight:700;font-size:0.85rem;cursor:pointer;white-space:nowrap;">
                Import Sekarang
            </button>
        </div>
    </form>

    <div style="margin-top:0.85rem;padding:0.65rem 1rem;background:rgba(255,255,255,0.08);border-radius:8px;border-left:3px solid #eab308;font-size:0.78rem;opacity:0.9;">
        <strong>Format kolom:</strong> Tipe | Bobot | Pertanyaan | Opsi_A | Opsi_B | Opsi_C | Opsi_D | Opsi_E | Kunci<br>
        <strong>Tipe valid:</strong> <code>pg</code> atau <code>essay</code>. Untuk essay, kolom Opsi_A-E boleh dikosongkan.<br>
        <strong>Tips:</strong> Bobot boleh memakai angka desimal seperti <code>2.5</code>. Kolom Opsi_E kosongkan jika soal SMP (4 opsi).
    </div>
</div>

<div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:0.75rem;margin-bottom:1.25rem;">
    <span style="font-size:0.9rem;color:#555;">
        Total: <strong>{{ $questions->count() }}</strong> soal |
        PG: <strong>{{ $questions->where('type','pg')->count() }}</strong> |
        Essay: <strong>{{ $questions->where('type','essay')->count() }}</strong>
    </span>
    <button onclick="document.getElementById('addForm').scrollIntoView({behavior:'smooth'})"
        class="btn btn-primary" style="font-size:0.85rem;">{{ $editing ? 'Lihat Form Edit' : 'Tambah Soal Manual' }}</button>
</div>

<div id="copyToolbar"
     style="display:none;background:#fffbeb;border:1px solid #fde68a;border-radius:10px;padding:0.75rem 1rem;margin-bottom:1rem;align-items:center;justify-content:space-between;gap:0.75rem;flex-wrap:wrap;">
    <span style="font-size:0.85rem;color:#92400e;font-weight:600;">
        <span id="copyCount">0</span> soal dipilih
    </span>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
        <button onclick="selectAllQuestions()" style="padding:0.3rem 0.75rem;border-radius:6px;border:1px solid #fde68a;background:white;color:#92400e;font-size:0.8rem;font-weight:600;cursor:pointer;">
            Pilih Semua
        </button>
        <button onclick="deselectAllQuestions()" style="padding:0.3rem 0.75rem;border-radius:6px;border:1px solid #e5e7eb;background:white;color:#6b7280;font-size:0.8rem;font-weight:600;cursor:pointer;">
            Batal
        </button>
        <button onclick="submitBulkDelete()" style="padding:0.3rem 0.75rem;border-radius:6px;background:#ef4444;border:none;color:white;font-size:0.8rem;font-weight:600;cursor:pointer;" title="Hapus Soal Terpilih">
            Hapus
        </button>

        @if($otherClasses->isNotEmpty())
            <div style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                <select id="targetClassSelect" style="padding:0.35rem 0.6rem;border-radius:6px;border:1px solid #fde68a;font-size:0.82rem;font-weight:600;color:#374151;background:white;">
                    @foreach($otherClasses as $tc)
                        <option value="{{ $tc->class_id }}">
                            Salin ke: Kelas {{ $tc->classRoom->name ?? $tc->class_id }}
                        </option>
                    @endforeach
                </select>
                <button onclick="submitCopy()"
                    style="padding:0.35rem 1rem;border-radius:6px;background:#eab308;color:#0f172a;border:none;font-size:0.82rem;font-weight:700;cursor:pointer;">
                    Salin Soal ke Kelas Ini
                </button>
            </div>
        @else
            <span style="font-size:0.78rem;color:#9ca3af;font-style:italic;">
                Tidak ada kelas lain dengan mapel yang sama
            </span>
        @endif
    </div>
</div>

<form id="copyForm" method="POST" action="{{ route('guru.questions.copy') }}" style="display:none;">
    @csrf
    <input type="hidden" name="subject_id" value="{{ $subjectId }}">
    <input type="hidden" id="copyTargetClass" name="target_class_id" value="">
</form>

<form id="bulkDeleteForm" method="POST" action="{{ route('guru.questions.bulk-delete') }}" style="display:none;">
    @csrf
</form>

<div class="card" style="padding:0;overflow:hidden;">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="width:36px;">
                        <input type="checkbox" id="checkAll" onchange="toggleAllQuestions(this.checked)"
                               title="Pilih Semua Soal"
                               style="width:15px;height:15px;accent-color:#1d4ed8;cursor:pointer;">
                    </th>
                    <th style="width:36px;">#</th>
                    <th>Pertanyaan</th>
                    <th class="hide-mobile" style="width:80px;">Tipe</th>
                    <th class="hide-mobile" style="width:70px;">Bobot</th>
                    <th style="width:150px;"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($questions as $i => $q)
                @php
                    $formattedWeight = rtrim(rtrim(number_format((float) $q->weight, 2, '.', ''), '0'), '.');
                @endphp
                <tr id="qrow-{{ $q->id }}">
                    <td>
                        <input type="checkbox" class="q-checkbox"
                               value="{{ $q->id }}"
                               onchange="updateCopyCount()"
                               style="width:15px;height:15px;accent-color:#1d4ed8;cursor:pointer;">
                    </td>
                    <td style="color:#9ca3af;font-size:0.85rem;">{{ $i + 1 }}</td>
                    <td style="max-width:400px;">
                        <div style="font-weight:600;font-size:0.9rem;">{{ Str::limit($q->question_text, 100) }}</div>
                        <span class="show-mobile" style="padding:0.15rem 0.5rem;border-radius:999px;font-size:0.72rem;font-weight:700;background:{{ $q->type === 'pg' ? '#eff6ff' : '#fff7ed' }};color:{{ $q->type === 'pg' ? '#1d4ed8' : '#c2410c' }};">
                            {{ strtoupper($q->type) }} · Bobot {{ $formattedWeight }}
                        </span>
                        @if($q->type === 'pg')
                            <div style="font-size:0.78rem;color:#888;margin-top:0.2rem;">
                                A. {{ $q->option_a }} | B. {{ $q->option_b }} | C. {{ $q->option_c }} | D. {{ $q->option_d }}
                                @if($q->option_e)
                                    | E. {{ $q->option_e }}
                                    <span style="margin-left:0.4rem;background:#fef3c7;color:#92400e;padding:0.1rem 0.4rem;border-radius:4px;font-size:0.7rem;font-weight:700;">SMA</span>
                                @endif
                                | <strong>Kunci: {{ strtoupper($q->correct_option) }}</strong>
                            </div>
                        @else
                            <div style="font-size:0.78rem;color:#888;margin-top:0.2rem;font-style:italic;">
                                Kunci Essay: {{ Str::limit($q->essay_key, 60) }}
                            </div>
                        @endif
                    </td>
                    <td class="hide-mobile">
                        <span style="padding:0.2rem 0.6rem;border-radius:999px;font-size:0.78rem;font-weight:700;background:{{ $q->type === 'pg' ? '#eff6ff' : '#fff7ed' }};color:{{ $q->type === 'pg' ? '#1d4ed8' : '#c2410c' }};">
                            {{ strtoupper($q->type) }}
                        </span>
                    </td>
                    <td class="hide-mobile" style="text-align:center;font-weight:700;">{{ $formattedWeight }}</td>
                    <td style="text-align:right;padding-right:0.75rem;">
                        <div style="display:flex;justify-content:flex-end;gap:0.4rem;flex-wrap:wrap;">
                            <a href="{{ route('guru.questions.index', ['class_id' => $classId, 'subject_id' => $subjectId, 'edit' => $q->id]) }}#addForm"
                                style="background:#1d4ed8;color:white;border:none;border-radius:8px;padding:0.35rem 0.8rem;font-size:0.78rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:0.3rem;text-decoration:none;">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('guru.questions.destroy', $q) }}" onsubmit="return confirm('Yakin hapus soal ini? Tindakan tidak bisa dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    style="background:#ef4444;color:white;border:none;border-radius:8px;padding:0.35rem 0.8rem;font-size:0.78rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:0.3rem;transition:background 0.15s;"
                                    onmouseover="this.style.background='#b91c1c'"
                                    onmouseout="this.style.background='#ef4444'">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:2rem;color:#aaa;">Belum ada soal. Tambahkan manual atau import via Excel.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card" id="addForm" style="margin-top:2rem;">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-bottom:1.5rem;">
        <h3 style="font-weight:700;margin:0;">{{ $editing ? 'Edit Soal' : 'Tambah Soal Baru (Manual)' }}</h3>
        @if($editing)
            <a href="{{ route('guru.questions.index', ['class_id' => $classId, 'subject_id' => $subjectId]) }}#addForm"
                style="background:#f3f4f6;color:#374151;padding:0.45rem 0.9rem;border-radius:8px;font-weight:700;font-size:0.82rem;text-decoration:none;">
                Batal Edit
            </a>
        @endif
    </div>

    <form method="POST" action="{{ $editing ? route('guru.questions.update', $editQuestion) : route('guru.questions.store') }}">
        @csrf
        @if($editing)
            @method('PUT')
        @endif
        <input type="hidden" name="subject_id" value="{{ $subjectId }}">
        <input type="hidden" name="class_id" value="{{ $classId }}">

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1rem;">
            <div class="input-group" style="margin:0;">
                <label class="input-label">Tipe Soal</label>
                <select name="type" id="typeSelect" class="form-control" onchange="toggleType(this.value)" required>
                    <option value="pg" {{ $selectedType === 'pg' ? 'selected' : '' }}>Pilihan Ganda (PG)</option>
                    <option value="essay" {{ $selectedType === 'essay' ? 'selected' : '' }}>Essay</option>
                </select>
            </div>
            <div class="input-group" style="margin:0;">
                <label class="input-label">Bobot Poin</label>
                <input type="number" name="weight" class="form-control" value="{{ $weightInputValue }}" min="0.1" max="100" step="0.1" required>
            </div>
        </div>

        <div class="input-group">
            <label class="input-label">Teks Pertanyaan</label>
            <textarea name="question_text" class="form-control" rows="3" placeholder="Tulis pertanyaan di sini..." required style="resize:vertical;">{{ old('question_text', $editing ? $editQuestion->question_text : '') }}</textarea>
        </div>

        <div id="pgFields">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;">
                <div class="input-group"><label class="input-label">Opsi A</label><input type="text" name="option_a" class="form-control" placeholder="Opsi A" value="{{ old('option_a', $editing ? $editQuestion->option_a : '') }}"></div>
                <div class="input-group"><label class="input-label">Opsi B</label><input type="text" name="option_b" class="form-control" placeholder="Opsi B" value="{{ old('option_b', $editing ? $editQuestion->option_b : '') }}"></div>
                <div class="input-group"><label class="input-label">Opsi C</label><input type="text" name="option_c" class="form-control" placeholder="Opsi C" value="{{ old('option_c', $editing ? $editQuestion->option_c : '') }}"></div>
                <div class="input-group"><label class="input-label">Opsi D</label><input type="text" name="option_d" class="form-control" placeholder="Opsi D" value="{{ old('option_d', $editing ? $editQuestion->option_d : '') }}"></div>
                <div class="input-group">
                    <label class="input-label">Opsi E <span style="font-size:0.72rem;color:#9ca3af;font-weight:400;">(opsional - kosongkan jika SMP)</span></label>
                    <input type="text" name="option_e" class="form-control" placeholder="Opsi E (khusus SMA)" value="{{ old('option_e', $editing ? $editQuestion->option_e : '') }}">
                </div>
            </div>
            <div class="input-group">
                <label class="input-label">Kunci Jawaban</label>
                @php $correctOption = old('correct_option', $editing ? $editQuestion->correct_option : 'a'); @endphp
                <select name="correct_option" class="form-control">
                    <option value="a" {{ $correctOption === 'a' ? 'selected' : '' }}>A</option>
                    <option value="b" {{ $correctOption === 'b' ? 'selected' : '' }}>B</option>
                    <option value="c" {{ $correctOption === 'c' ? 'selected' : '' }}>C</option>
                    <option value="d" {{ $correctOption === 'd' ? 'selected' : '' }}>D</option>
                    <option value="e" {{ $correctOption === 'e' ? 'selected' : '' }}>E</option>
                </select>
            </div>
        </div>

        <div id="essayFields" style="display:none;">
            <div class="input-group">
                <label class="input-label">Kunci Jawaban Essay</label>
                <textarea name="essay_key" class="form-control" rows="4" placeholder="Tulis kunci jawaban/model jawaban yang akan digunakan sebagai acuan NLP..." style="resize:vertical;">{{ old('essay_key', $editing ? $editQuestion->essay_key : '') }}</textarea>
            </div>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top:0.5rem;padding:0.75rem 2rem;">{{ $editing ? 'Update Soal' : 'Simpan Soal' }}</button>
    </form>
</div>

<script>
function toggleType(type) {
    document.getElementById('pgFields').style.display = type === 'pg' ? 'block' : 'none';
    document.getElementById('essayFields').style.display = type === 'essay' ? 'block' : 'none';
}

function updateCopyCount() {
    const checked = document.querySelectorAll('.q-checkbox:checked');
    const count = checked.length;
    const toolbar = document.getElementById('copyToolbar');
    document.getElementById('copyCount').textContent = count;
    toolbar.style.display = count > 0 ? 'flex' : 'none';

    const all = document.querySelectorAll('.q-checkbox');
    const cb = document.getElementById('checkAll');
    cb.indeterminate = count > 0 && count < all.length;
    cb.checked = count === all.length && all.length > 0;
}

function toggleAllQuestions(checked) {
    document.querySelectorAll('.q-checkbox').forEach(cb => { cb.checked = checked; });
    updateCopyCount();
}

function selectAllQuestions() { toggleAllQuestions(true); }
function deselectAllQuestions() { toggleAllQuestions(false); }

function submitCopy() {
    const selected = Array.from(document.querySelectorAll('.q-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        alert('Pilih minimal 1 soal untuk disalin.');
        return;
    }

    const targetClass = document.getElementById('targetClassSelect').value;
    if (!confirm(`Yakin ingin menyalin ${selected.length} soal ke kelas tujuan?`)) {
        return;
    }

    const form = document.getElementById('copyForm');
    document.getElementById('copyTargetClass').value = targetClass;
    form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });

    form.submit();
}

function submitBulkDelete() {
    const selected = Array.from(document.querySelectorAll('.q-checkbox:checked')).map(cb => cb.value);
    if (selected.length === 0) {
        return;
    }

    if (!confirm(`Yakin ingin menghapus ${selected.length} soal terpilih? Tindakan ini tidak dapat dibatalkan.`)) {
        return;
    }

    const form = document.getElementById('bulkDeleteForm');
    form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());

    selected.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'ids[]';
        input.value = id;
        form.appendChild(input);
    });

    form.submit();
}

toggleType(document.getElementById('typeSelect').value);
</script>

<style>
@media (max-width: 640px) { .hide-mobile { display: none !important; } }
.show-mobile { display: none; }
@media (max-width: 640px) { .show-mobile { display: inline-block; } }
</style>
@endsection
