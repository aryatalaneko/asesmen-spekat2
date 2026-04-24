@extends('layouts.app')
@section('header_title','Manajemen Sistem')
@section('content')
{{-- ===== ALERTS ===== --}}
@if(session('success'))
    <div style="background:#dcfce7;color:#166534;border:1px solid #86efac;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
        ✅ {{ session('success') }}
    </div>
@endif
@if(session('warning'))
        ⚠️ {{ session('warning') }}
    </div>
@endif
@if(session('error'))
    <div style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;">
        ❌ {{ session('error') }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;margin-bottom:1.5rem;">
    <div class="card" style="border:1px solid #bfdbfe;background:#eff6ff;">
        <h3 style="font-weight:800;font-size:1rem;color:#1e3a8a;margin-bottom:0.5rem;">Backup dan Restore Data</h3>
        <p style="font-size:0.85rem;color:#334155;line-height:1.6;margin-bottom:1rem;">
            Backup menyimpan data dinamis sistem: guru, siswa, kelas, mapel, penugasan, soal, jadwal, hasil, jawaban, izin ujian, dan clustering.
            Akun admin dan tabel sistem Laravel tidak ikut diubah.
        </p>

        <div style="display:flex;gap:0.75rem;flex-wrap:wrap;margin-bottom:1rem;">
            <a href="{{ route('admin.data.backup') }}" class="btn btn-primary" style="text-decoration:none;">Unduh Backup JSON</a>
        </div>

        <form method="POST" action="{{ route('admin.data.restore') }}" enctype="multipart/form-data" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-start;">
            @csrf
            <div style="flex:1;min-width:260px;">
                <input type="file" name="backup_file" accept=".json,application/json" class="form-control" required>
                @error('backup_file')
                    <p style="color:#b91c1c;font-size:0.8rem;margin-top:0.4rem;">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn" style="background:#0f766e;color:white;">Restore Data</button>
        </form>
    </div>

    <div class="card" style="border:1px solid #fecaca;background:#fff1f2;">
        <h3 style="font-weight:800;font-size:1rem;color:#991b1b;margin-bottom:0.5rem;">Kosongkan Semua Data Dinamis</h3>
        <p style="font-size:0.85rem;color:#4b5563;line-height:1.6;margin-bottom:1rem;">
            Aksi ini akan menghapus seluruh data operasional yang diinput pengguna.
            Akun admin akan tetap dipertahankan agar Anda tidak kehilangan akses ke sistem.
        </p>

        <form method="POST" action="{{ route('admin.data.clear') }}" onsubmit="return confirmClearData()">
            @csrf
            <div class="input-group" style="margin-bottom:0.75rem;">
                <label class="input-label">Ketik <strong>KOSONGKAN DATA</strong> untuk konfirmasi</label>
                <input type="text" name="confirmation_text" id="confirmation_text" class="form-control" placeholder="KOSONGKAN DATA" required>
                @error('confirmation_text')
                    <p style="color:#b91c1c;font-size:0.8rem;margin-top:0.4rem;">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="btn btn-danger">Kosongkan Data</button>
        </form>
    </div>
</div>

{{-- Tampilkan detail import dihapus (silent bypass pada back-end) --}}
{{-- ===== BULK IMPORT EXCEL ===== --}}
<div style="background:linear-gradient(135deg,#1e1b4b,#312e81);border-radius:14px;padding:1.5rem;margin-bottom:1.5rem;color:white;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:1rem;">
        {{-- Kiri: Judul & Deskripsi --}}
        <div>
            <h3 style="font-weight:800;font-size:1rem;margin:0 0 0.35rem;">📥 Import Siswa via Excel (Bulk)</h3>
            <p style="opacity:0.75;font-size:0.82rem;margin:0;max-width:480px;">Upload file Excel berformat <strong>xlsx/xls/csv</strong> dengan kolom: <code style="background:rgba(255,255,255,0.15);padding:0.1rem 0.4rem;border-radius:4px;">Nama</code>, <code style="background:rgba(255,255,255,0.15);padding:0.1rem 0.4rem;border-radius:4px;">NIS</code>, <code style="background:rgba(255,255,255,0.15);padding:0.1rem 0.4rem;border-radius:4px;">Kelas</code>. Username ujian dan password siswa akan dibuat otomatis oleh sistem.</p>
        </div>
        {{-- Kanan: Download Template --}}
        <a href="{{ route('admin.users.template') }}"
            style="display:inline-flex;align-items:center;gap:0.5rem;background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.3);padding:0.5rem 1rem;border-radius:8px;font-size:0.82rem;font-weight:700;text-decoration:none;white-space:nowrap;transition:background 0.2s;"
            onmouseover="this.style.background='rgba(255,255,255,0.25)'"
            onmouseout="this.style.background='rgba(255,255,255,0.15)'">
            📄 Download Template Excel
        </a>
    </div>

    {{-- Form Upload --}}
    <form method="POST" action="{{ route('admin.users.import') }}" enctype="multipart/form-data"
        style="display:flex;align-items:center;gap:0.75rem;flex-wrap:wrap;margin-top:1.25rem;">
        @csrf
        <div style="flex:1;min-width:220px;">
            <label style="position:relative;display:block;">
                <input type="file" name="excel_file" accept=".xlsx,.xls,.csv" required
                    id="excel_file_input"
                    onchange="document.getElementById('file_label').textContent = this.files[0]?.name || 'Pilih file...'"
                    style="position:absolute;width:0;height:0;opacity:0;">
                <span id="file_label"
                    style="display:flex;align-items:center;gap:0.5rem;background:rgba(255,255,255,0.1);border:1.5px dashed rgba(255,255,255,0.4);border-radius:8px;padding:0.6rem 1rem;font-size:0.82rem;color:rgba(255,255,255,0.8);cursor:pointer;transition:all 0.2s;"
                    onclick="document.getElementById('excel_file_input').click()">
                    📂 Pilih file Excel (.xlsx / .xls / .csv) — maks 5MB
                </span>
            </label>
            @error('excel_file')
                <p style="color:#fca5a5;font-size:0.78rem;margin-top:0.3rem;">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit"
            style="padding:0.6rem 1.5rem;background:#22c55e;color:white;border:none;border-radius:8px;font-weight:700;font-size:0.85rem;cursor:pointer;white-space:nowrap;"
            onmouseover="this.style.background='#16a34a'"
            onmouseout="this.style.background='#22c55e'">
            🚀 Upload & Import
        </button>
    </form>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;">


    {{-- ===== GURU ===== --}}
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
            <h3 style="font-weight:700;font-size:1rem;">👨‍🏫 Data Guru &amp; Penugasan ({{ $gurus->count() }})</h3>
        </div>

        {{-- Toolbar bulk-delete Guru (muncul saat ada yang dicentang) --}}
        <div id="guruBulkBar"
             style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:0.6rem 1rem;margin-bottom:0.75rem;display:none;align-items:center;justify-content:space-between;gap:0.75rem;">
            <span style="font-size:0.85rem;color:#991b1b;font-weight:600;">
                🗑️ <span id="guruSelectedCount">0</span> Guru dipilih
            </span>
            <div style="display:flex;gap:0.5rem;">
                <button onclick="selectAllGuru()" style="padding:0.3rem 0.75rem;border-radius:6px;border:1px solid #fca5a5;background:white;color:#dc2626;font-size:0.8rem;font-weight:600;cursor:pointer;">
                    ☑️ Pilih Semua
                </button>
                <button onclick="deselectAllGuru()" style="padding:0.3rem 0.75rem;border-radius:6px;border:1px solid #e5e7eb;background:white;color:#6b7280;font-size:0.8rem;font-weight:600;cursor:pointer;">
                    🔲 Batal
                </button>
                <button onclick="submitBulkGuru()" style="padding:0.3rem 0.85rem;border-radius:6px;background:#dc2626;color:white;border:none;font-size:0.8rem;font-weight:700;cursor:pointer;">
                    🗑️ Hapus yang Dipilih
                </button>
            </div>
        </div>

        {{-- Form hidden untuk bulk delete guru --}}
        <form id="guruBulkForm" method="POST" action="{{ route('admin.users.bulk-delete') }}" style="display:none;">
            @csrf
        </form>

        <table>
            <thead><tr>
                <th style="width:36px;">
                    <input type="checkbox" id="guruCheckAll" onchange="toggleAllGuru(this.checked)"
                           title="Pilih Semua Guru"
                           style="width:15px;height:15px;accent-color:#dc2626;cursor:pointer;">
                </th>
                <th>#</th><th>Nama</th><th>Penugasan Kelas/Mapel</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            @forelse($gurus as $i => $g)
                <tr id="guruRow{{ $g->id }}">
                    <td>
                        <input type="checkbox" class="guru-checkbox"
                               value="{{ $g->id }}"
                               onchange="updateGuruCount()"
                               style="width:15px;height:15px;accent-color:#dc2626;cursor:pointer;">
                    </td>
                    <td>{{ $i+1 }}</td>
                    <td style="font-weight:600;">{{ $g->name }}</td>
                    <td>
                        @forelse($g->teacherClasses as $tc)
                           <div style="font-size:0.8rem;background:#eff6ff;color:#1d4ed8;padding:2px 6px;border-radius:4px;margin-bottom:2px;">
                               {{ $tc->subject->name ?? '-' }} ({{ $tc->classRoom->name ?? '-' }})
                           </div>
                        @empty
                           <span style="color:#aaa;font-size:0.8rem;">Belum ditugaskan</span>
                        @endforelse
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.destroy', $g) }}" onsubmit="return confirm('Hapus guru ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger" style="padding:0.2rem 0.6rem;font-size:0.8rem;">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;color:#aaa;">Belum ada guru.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== SISWA ===== --}}
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.75rem;">
            <h3 style="font-weight:700;font-size:1rem;">👨‍🎓 Data Siswa ({{ $siswas->count() }})</h3>
        </div>

        {{-- Toolbar bulk-delete Siswa --}}
        <div id="siswaBulkBar"
             style="display:none;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:0.6rem 1rem;margin-bottom:0.75rem;align-items:center;justify-content:space-between;gap:0.75rem;">
            <span style="font-size:0.85rem;color:#991b1b;font-weight:600;">
                🗑️ <span id="siswaSelectedCount">0</span> Siswa dipilih
            </span>
            <div style="display:flex;gap:0.5rem;">
                <button onclick="selectAllSiswa()" style="padding:0.3rem 0.75rem;border-radius:6px;border:1px solid #fca5a5;background:white;color:#dc2626;font-size:0.8rem;font-weight:600;cursor:pointer;">
                    ☑️ Pilih Semua
                </button>
                <button onclick="deselectAllSiswa()" style="padding:0.3rem 0.75rem;border-radius:6px;border:1px solid #e5e7eb;background:white;color:#6b7280;font-size:0.8rem;font-weight:600;cursor:pointer;">
                    🔲 Batal
                </button>
                <button onclick="submitBulkSiswa()" style="padding:0.3rem 0.85rem;border-radius:6px;background:#dc2626;color:white;border:none;font-size:0.8rem;font-weight:700;cursor:pointer;">
                    🗑️ Hapus yang Dipilih
                </button>
            </div>
        </div>

        {{-- Form hidden untuk bulk delete siswa --}}
        <form id="siswaBulkForm" method="POST" action="{{ route('admin.users.bulk-delete') }}" style="display:none;">
            @csrf
        </form>

        <table>
            <thead><tr>
                <th style="width:36px;">
                    <input type="checkbox" id="siswaCheckAll" onchange="toggleAllSiswa(this.checked)"
                           title="Pilih Semua Siswa"
                           style="width:15px;height:15px;accent-color:#dc2626;cursor:pointer;">
                </th>
                <th>#</th><th>Nama</th><th>NIS</th><th>Kelas</th><th>Aksi</th>
            </tr></thead>
            <tbody>
            @forelse($siswas as $i => $s)
                <tr id="siswaRow{{ $s->id }}">
                    <td>
                        <input type="checkbox" class="siswa-checkbox"
                               value="{{ $s->id }}"
                               onchange="updateSiswaCount()"
                               style="width:15px;height:15px;accent-color:#dc2626;cursor:pointer;">
                    </td>
                    <td>{{ $i+1 }}</td>
                    <td style="font-weight:600;">{{ $s->name }}</td>
                    <td style="font-size:0.82rem;color:#6b7280;">{{ $s->nis ?? '—' }}</td>
                    <td>{{ $s->classRoom->name ?? '-' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.users.destroy', $s) }}" onsubmit="return confirm('Hapus siswa ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger" style="padding:0.2rem 0.6rem;font-size:0.8rem;">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#aaa;">Belum ada siswa.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ===== FORM TAMBAH PENGGUNA ===== --}}
<div class="card" style="margin-top:1.5rem;">
    <h3 style="font-weight:700;font-size:1rem;margin-bottom:1rem;">➕ Tambah Pengguna Baru</h3>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1.5rem;align-items:start;">
            
            {{-- Kolom Kiri: Data Dasar --}}
            <div>
                <div class="input-group">
                    <label class="input-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Nama pengguna..." required>
                </div>
                <div class="input-group">
                    <label class="input-label" id="passwordLabel">Password (Khusus Guru)</label>
                    <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Min. 6 karakter">
                    <p id="passwordHint" style="font-size:0.78rem;color:#6b7280;margin-top:0.4rem;">
                        Untuk siswa, password ujian akan digenerate otomatis oleh sistem.
                    </p>
                </div>
                <div class="input-group">
                    <label class="input-label">Peran</label>
                    <select name="role" id="roleSelect" class="form-control" onchange="toggleRoleFields(this.value)" required>
                        <option value="guru">Guru</option>
                        <option value="siswa">Siswa</option>
                    </select>
                </div>
            </div>

            {{-- Kolom Tengah/Kanan dinamis --}}
            <div id="dynamicFieldsSiswa" style="display:none;grid-column: span 2;">
                <div class="input-group">
                    <label class="input-label">NIS Siswa</label>
                    <input type="text" name="nis" id="nisInput" class="form-control" placeholder="Masukkan NIS siswa">
                </div>
                <div class="input-group">
                    <label class="input-label">Pilih Kelas (Khusus Siswa)</label>
                    <select name="class_id" id="classIdInput" class="form-control">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="font-size:0.8rem;color:#475569;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:0.75rem 0.9rem;">
                    Username ujian akan dibentuk dari periode ujian dan 4 digit terakhir NIS. Password login siswa akan digenerate otomatis dan ditampilkan pada kartu peserta.
                </div>
            </div>

            <div id="dynamicFieldsGuru" style="grid-column: span 2;">
                <div id="mappingContainer">
                    <div style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; background: #f9fafb; position: relative;">
                        <h4 style="font-weight:700; font-size:0.85rem; margin-bottom: 0.75rem; color:#4b5563;">Penugasan 1</h4>
                        <div class="input-group">
                            <label class="input-label">Mata Pelajaran</label>
                            <select name="mappings[0][subject_id]" class="form-control">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group">
                            <label class="input-label">Tugaskan ke Kelas (Khusus Guru - Tahan CTRL untuk memilih >1)</label>
                            <select name="mappings[0][classes][]" class="form-control" multiple size="4">
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="addMappingRow()" class="btn" style="margin-top:0.75rem; background:#eff6ff; color:#1d4ed8; border: 1px dashed #bfdbfe; width:100%;">+ Tambah Mapel/Penugasan Lain</button>
            </div>

        </div>
        <button type="submit" class="btn btn-primary" style="margin-top:1.5rem;padding:0.75rem 2rem;width:100%;">💾 Simpan Akun </button>
    </form>
</div>

<script>
let mappingCount = 0;
function addMappingRow() {
    mappingCount++;
    const html = `
    <div id="mapRow${mappingCount}" style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 1rem; background: #f9fafb; position: relative; margin-top: 1rem;">
        <button type="button" onclick="document.getElementById('mapRow${mappingCount}').remove()" style="position: absolute; right: 10px; top: 10px; background:transparent; border:none; color: #ef4444; font-weight:bold; cursor:pointer;">X</button>
        <h4 style="font-weight:700; font-size:0.85rem; margin-bottom: 0.75rem; color:#4b5563;">Penugasan ${mappingCount + 1}</h4>
        <div class="input-group">
            <label class="input-label">Mata Pelajaran</label>
            <select name="mappings[${mappingCount}][subject_id]" class="form-control" required>
                <option value="">-- Pilih Mata Pelajaran --</option>
                @foreach($subjects as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="input-group">
            <label class="input-label">Tugaskan ke Kelas (Khusus Guru - Tahan CTRL untuk memilih >1)</label>
            <select name="mappings[${mappingCount}][classes][]" class="form-control" multiple size="4" required>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    `;
    document.getElementById('mappingContainer').insertAdjacentHTML('beforeend', html);
}
</script>

{{-- Form Cepat untuk menambah Class dan Subject Master --}}
<div style="display:flex;gap:1.5rem;margin-top:1.5rem;">
    <div class="card" style="flex:1;">
        <h3 style="font-size:0.9rem;font-weight:700;">Tambah Data Master Kelas</h3>
        <form action="{{ route('admin.classes.store') }}" method="POST" style="display:flex;gap:0.5rem;margin-top:0.5rem;">
            @csrf
            <input type="text" name="name" class="form-control" placeholder="Nama Kelas mis. IX-A" required>
            <button class="btn btn-primary">Add</button>
        </form>
    </div>
    <div class="card" style="flex:1;">
        <h3 style="font-size:0.9rem;font-weight:700;">Tambah Master Mata Pelajaran</h3>
        <form action="{{ route('admin.subjects.store') }}" method="POST" style="display:flex;gap:0.5rem;margin-top:0.5rem;">
            @csrf
            <input type="text" name="name" class="form-control" placeholder="Mata Pelajaran" required>
            <button class="btn btn-primary">Add</button>
        </form>
    </div>
</div>

<script>
// ===== ROLE FIELD TOGGLE =====
function toggleRoleFields(role) {
    document.getElementById('dynamicFieldsSiswa').style.display = role === 'siswa' ? 'block' : 'none';
    document.getElementById('dynamicFieldsGuru').style.display = role === 'guru' ? 'block' : 'none';

    const passwordInput = document.getElementById('passwordInput');
    const passwordLabel = document.getElementById('passwordLabel');
    const passwordHint = document.getElementById('passwordHint');
    const nisInput = document.getElementById('nisInput');
    const classIdInput = document.getElementById('classIdInput');

    if (role === 'guru') {
        passwordInput.required = true;
        passwordLabel.textContent = 'Password (Khusus Guru)';
        passwordInput.placeholder = 'Min. 6 karakter';
        passwordHint.textContent = 'Password guru ditentukan manual oleh admin.';
        nisInput.required = false;
        classIdInput.required = false;
    } else {
        passwordInput.required = false;
        passwordInput.value = '';
        passwordLabel.textContent = 'Password (Otomatis untuk Siswa)';
        passwordInput.placeholder = 'Akan dibuat otomatis';
        passwordHint.textContent = 'Sistem akan membuat password login siswa secara otomatis saat akun disimpan.';
        nisInput.required = true;
        classIdInput.required = true;
    }
}
toggleRoleFields('guru');

// ===== BULK DELETE — GURU =====
function updateGuruCount() {
    const checked = document.querySelectorAll('.guru-checkbox:checked');
    const count   = checked.length;
    const bar     = document.getElementById('guruBulkBar');
    document.getElementById('guruSelectedCount').textContent = count;
    // Tampilkan toolbar hanya jika ada yang dicentang
    bar.style.display = count > 0 ? 'flex' : 'none';
    // Sinkronkan checkbox header
    const all = document.querySelectorAll('.guru-checkbox');
    document.getElementById('guruCheckAll').indeterminate = (count > 0 && count < all.length);
    document.getElementById('guruCheckAll').checked = (count === all.length && all.length > 0);
}

function toggleAllGuru(checked) {
    document.querySelectorAll('.guru-checkbox').forEach(cb => { cb.checked = checked; });
    updateGuruCount();
}

function selectAllGuru()   { toggleAllGuru(true);  }
function deselectAllGuru() { toggleAllGuru(false); }

function submitBulkGuru() {
    const checked = document.querySelectorAll('.guru-checkbox:checked');
    if (checked.length === 0) {
        alert('Pilih minimal 1 guru untuk dihapus!');
        return;
    }
    if (!confirm(`⚠️ Yakin ingin menghapus ${checked.length} akun Guru? Tindakan ini tidak dapat dibatalkan.`)) return;

    const form = document.getElementById('guruBulkForm');
    // Bersihkan input lama
    form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
    // Tambahkan ID yang dicentang
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });
    form.submit();
}

// ===== BULK DELETE — SISWA =====
function updateSiswaCount() {
    const checked = document.querySelectorAll('.siswa-checkbox:checked');
    const count   = checked.length;
    const bar     = document.getElementById('siswaBulkBar');
    document.getElementById('siswaSelectedCount').textContent = count;
    bar.style.display = count > 0 ? 'flex' : 'none';
    const all = document.querySelectorAll('.siswa-checkbox');
    document.getElementById('siswaCheckAll').indeterminate = (count > 0 && count < all.length);
    document.getElementById('siswaCheckAll').checked = (count === all.length && all.length > 0);
}

function toggleAllSiswa(checked) {
    document.querySelectorAll('.siswa-checkbox').forEach(cb => { cb.checked = checked; });
    updateSiswaCount();
}

function selectAllSiswa()   { toggleAllSiswa(true);  }
function deselectAllSiswa() { toggleAllSiswa(false); }

function submitBulkSiswa() {
    const checked = document.querySelectorAll('.siswa-checkbox:checked');
    if (checked.length === 0) {
        alert('Pilih minimal 1 siswa untuk dihapus!');
        return;
    }
    if (!confirm(`⚠️ Yakin ingin menghapus ${checked.length} akun Siswa? Semua data nilai dan jawaban siswa ini juga akan ikut terhapus!`)) return;

    const form = document.getElementById('siswaBulkForm');
    form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
    checked.forEach(cb => {
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'ids[]';
        input.value = cb.value;
        form.appendChild(input);
    });
    form.submit();
}

function confirmClearData() {
    const value = document.getElementById('confirmation_text')?.value?.trim();

    if (value !== 'KOSONGKAN DATA') {
        alert('Konfirmasi tidak cocok. Ketik tepat: KOSONGKAN DATA');
        return false;
    }

    return confirm('Semua data dinamis akan dihapus. Backup data terlebih dahulu jika masih dibutuhkan. Lanjutkan?');
}
</script>
@endsection
