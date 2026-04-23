<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Peserta — SMP Katolik St. Johanis Laikit</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ===================================================
           BASE
        =================================================== */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; }

        /* ===================================================
           KARTU — UKURAN FISIK TERKUNCI (cm)
           Berlaku di layar DAN saat print
        =================================================== */
        .kartu-ujian {
            width: 10.5cm;
            height: 14.5cm;
            border: 1.5px solid #1e293b;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            background: white;
            border-radius: 6px;
            overflow: hidden;
            /* Drop shadow hanya di layar */
            box-shadow: 0 4px 16px rgba(0,0,0,0.10);
        }

        /* --- Header kartu --- */
        .kartu-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%);
            color: white;
            text-align: center;
            padding: 9px 10px;
            border-bottom: 3px solid #eab308;
            flex-shrink: 0;
        }
        .kartu-header .judul-sekolah {
            font-size: 7.5pt;
            font-weight: 800;
            letter-spacing: 0.3px;
            line-height: 1.5;
            text-transform: uppercase;
        }

        /* --- Body kartu --- */
        .kartu-body {
            padding: 10px 12px;
            flex: 1;
        }
        .kartu-body table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
        }
        .kartu-body table td {
            padding: 3px 2px;
            vertical-align: top;
            line-height: 1.4;
        }
        .kartu-body table td.label {
            font-weight: 600;
            color: #374151;
            width: 80px;
        }
        .kartu-body table td.sep {
            width: 8px;
            color: #9ca3af;
        }
        .kartu-body table td.value {
            color: #111827;
            font-weight: 500;
        }
        .kartu-body table td.value strong {
            font-weight: 800;
        }
        .kartu-divider {
            height: 1px;
            background: #e5e7eb;
            margin: 8px 0;
        }

        /* --- Footer kartu --- */
        .kartu-footer {
            padding: 8px 12px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            flex-shrink: 0;
            gap: 6px;
        }
        .foto-box {
            width: 2.2cm;
            height: 2.9cm;
            border: 1px solid #9ca3af;
            border-radius: 3px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: #9ca3af;
        }
        .foto-box span {
            font-size: 5.5pt;
            margin-top: 3px;
            text-align: center;
        }
        .ttd-area {
            text-align: center;
            font-size: 6.5pt;
            color: #374151;
            line-height: 1.6;
            flex: 1;
        }
        .ttd-area .ttd-name {
            display: inline-block;
            border-top: 1px solid #374151;
            padding-top: 2px;
            font-weight: 800;
            font-size: 6.5pt;
        }

        /* ===================================================
           WRAPPER KARTU (untuk checkbox di layar)
        =================================================== */
        .card-wrapper {
            position: relative;
            display: inline-block;
        }
        .card-wrapper.unchecked .kartu-ujian {
            opacity: 0.35;
        }

        /* Checkbox label (hanya di layar) */
        .checkbox-label {
            position: absolute;
            top: 6px;
            left: 6px;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 5px;
            background: white;
            border: 1.5px solid #1e3a8a;
            border-radius: 6px;
            padding: 3px 8px;
            cursor: pointer;
            font-size: 7pt;
            font-weight: 700;
            color: #1e3a8a;
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
            user-select: none;
        }
        .checkbox-label input[type="checkbox"] {
            width: 13px;
            height: 13px;
            accent-color: #1e3a8a;
            cursor: pointer;
        }

        /* Kontainer flex untuk semua kartu */
        .cards-flex {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.5rem;
            padding: 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* ===================================================
           TOOLBAR
        =================================================== */
        .toolbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.8rem 1.5rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
            gap: 0.75rem;
        }
        .badge {
            display: inline-block;
            background: #1e3a8a;
            color: white;
            padding: 2px 8px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            margin-left: 4px;
        }
        .badge.green { background: #16a34a; }
        .btn-toolbar {
            padding: 0.42rem 0.85rem;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            border: none;
            transition: opacity 0.15s;
        }
        .btn-toolbar:hover { opacity: 0.85; }

        /* ===================================================
           PRINT STYLES
        =================================================== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 1cm;
            }
            body {
                background: white !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .no-print { display: none !important; }

            /* Kartu yang tidak dicentang → tidak dicetak */
            .card-wrapper.hidden-for-print { display: none !important; }

            .card-container {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .cards-flex {
                padding: 0;
                gap: 0.5cm;
            }

            .kartu-ujian {
                box-shadow: none;
                border-radius: 4px;
            }
        }
    </style>
</head>
<body>

{{-- ===== TOOLBAR (tidak ikut cetak) ===== --}}
<div class="toolbar no-print">
    <div>
        <div style="font-weight:800;font-size:1rem;color:#1e3a8a;">🖨️ Cetak Kartu Peserta Ujian</div>
        <div style="font-size:0.78rem;color:#6b7280;margin-top:3px;">
            Total: <span id="totalCount" class="badge">{{ $students->count() }}</span>
            &nbsp;|&nbsp; Akan Dicetak: <span id="selectedCount" class="badge green">{{ $students->count() }}</span>
        </div>
    </div>
    <div style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
        <button onclick="selectAll()" class="btn-toolbar" style="background:#eff6ff;color:#1e3a8a;border:1.5px solid #bfdbfe;">
            ☑️ Pilih Semua
        </button>
        <button onclick="deselectAll()" class="btn-toolbar" style="background:#f9fafb;color:#6b7280;border:1.5px solid #e5e7eb;">
            🔲 Batal Semua
        </button>
        <div style="width:1px;height:28px;background:#e5e7eb;"></div>
        <a href="{{ route('admin.users.index') }}"
           style="padding:0.42rem 0.85rem;border-radius:8px;border:1.5px solid #e5e7eb;color:#374151;font-weight:700;font-size:0.8rem;text-decoration:none;">
            ← Kembali
        </a>
        <button onclick="doPrint()" class="btn-toolbar" style="background:#1e3a8a;color:#eab308;padding:0.5rem 1.4rem;box-shadow:0 2px 8px rgba(30,58,138,0.25);">
            🖨️ Cetak Kartu Terpilih
        </button>
    </div>
</div>

{{-- ===== FLEX CONTAINER KARTU ===== --}}
<div class="cards-flex" id="cardsContainer">

    @forelse($students as $siswa)
    <div class="card-wrapper" id="wrapper-{{ $siswa->id }}">

        {{-- Checkbox (tidak ikut cetak) --}}
        <label class="checkbox-label no-print" for="check-{{ $siswa->id }}">
            <input
                type="checkbox"
                id="check-{{ $siswa->id }}"
                class="card-checkbox"
                data-id="{{ $siswa->id }}"
                checked
                onchange="updateSelection()"
            >
            Cetak
        </label>

        {{-- KARTU UJIAN --}}
        <div class="card-container">
            <div class="kartu-ujian">

                {{-- HEADER --}}
                <div class="kartu-header">
                    <div class="judul-sekolah">
                        KARTU PESERTA UJIAN AKHIR SEMESTER<br>
                        SMP KATOLIK ST. JOHANIS LAIKIT<br>
                        TAHUN PELAJARAN 2025/2026
                    </div>
                </div>

                {{-- BODY --}}
                <div class="kartu-body">
                    <table>
                        <tr>
                            <td class="label">Nama Peserta</td>
                            <td class="sep">:</td>
                            <td class="value"><strong>{{ strtoupper($siswa->name) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Username</td>
                            <td class="sep">:</td>
                            <td class="value">{{ $siswa->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Password</td>
                            <td class="sep">:</td>
                            <td class="value"><strong>{{ $siswa->nis ?? '—' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="label">Kelas</td>
                            <td class="sep">:</td>
                            <td class="value">{{ $siswa->classRoom->name ?? '—' }}</td>
                        </tr>
                    </table>

                    <div class="kartu-divider"></div>

                    <table>
                        <tr>
                            <td class="label">Asal Sekolah</td>
                            <td class="sep">:</td>
                            <td class="value">SMP Katolik St. Johanis Laikit</td>
                        </tr>
                        <tr>
                            <td class="label">Tahun Ajaran</td>
                            <td class="sep">:</td>
                            <td class="value">2025 / 2026</td>
                        </tr>
                    </table>

                    {{-- Catatan kecil --}}
                    <div style="margin-top:10px;padding:6px 8px;background:#fffbeb;border:1px solid #fde68a;border-radius:4px;font-size:6.5pt;color:#92400e;line-height:1.5;">
                        ⚠️ Kartu ini wajib dibawa saat ujian berlangsung. Tidak berlaku jika rusak atau digandakan tanpa izin.
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="kartu-footer">
                    {{-- Kiri: Foto --}}
                    <div class="foto-box">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                        <span>FOTO 3×4</span>
                    </div>

                    {{-- Kanan: TTD Kepala Sekolah --}}
                    <div class="ttd-area">
                        Minahasa Utara, 24 April 2026<br>
                        Kepala Satuan Pendidikan,<br>
                        <br><br>
                        <span class="ttd-name">Annastas Wantania, S.Pd</span><br>
                        <span style="font-size:6pt;color:#6b7280;">NIP. —</span>
                    </div>
                </div>

            </div><!-- /.kartu-ujian -->
        </div><!-- /.card-container -->

    </div><!-- /.card-wrapper -->
    @empty
        <div style="text-align:center;padding:3rem;color:#9ca3af;width:100%;">
            Belum ada data siswa yang terdaftar.
        </div>
    @endforelse

</div>{{-- /.cards-flex --}}

{{-- ===== JAVASCRIPT ===== --}}
<script>
    function getCheckboxes() {
        return document.querySelectorAll('.card-checkbox');
    }

    function updateSelection() {
        const checkboxes = getCheckboxes();
        let selected = 0;

        checkboxes.forEach(function(cb) {
            const wrapper = document.getElementById('wrapper-' + cb.dataset.id);
            if (cb.checked) {
                selected++;
                wrapper.classList.remove('unchecked', 'hidden-for-print');
            } else {
                wrapper.classList.add('unchecked', 'hidden-for-print');
            }
        });

        document.getElementById('selectedCount').textContent = selected;
    }

    function selectAll() {
        getCheckboxes().forEach(function(cb) { cb.checked = true; });
        updateSelection();
    }

    function deselectAll() {
        getCheckboxes().forEach(function(cb) { cb.checked = false; });
        updateSelection();
    }

    function doPrint() {
        const selected = document.querySelectorAll('.card-checkbox:checked').length;
        if (selected === 0) {
            alert('⚠️ Belum ada kartu yang dipilih untuk dicetak!\nCentang minimal 1 kartu terlebih dahulu.');
            return;
        }
        window.print();
    }

    // Init
    updateSelection();
</script>

</body>
</html>
