<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Peserta - SMP Katolik St. Johanis Laikit</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $groupedStudents = $students->groupBy(fn ($student) => $student->classRoom->name ?? 'Tanpa Kelas');
        $paperModeText = 'Rekomendasi cetak: kertas A4/F4 landscape.';
    @endphp

    <style>
        :root {
            --page-bg: #edf2f7;
            --panel-bg: #ffffff;
            --panel-border: #dbe3f0;
            --ink: #0f172a;
            --muted: #64748b;
            --brand: #163c8c;
            --brand-soft: #eff4ff;
            --accent: #d4a017;
            --card-width: 8.56cm;
            --card-height: 5.398cm;
            --card-gap-screen: 18px;
            --card-gap-print: 0.35cm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: var(--page-bg);
            color: var(--ink);
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            padding: 16px 22px;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid var(--panel-border);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
        }

        .toolbar-title {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .toolbar-title h1 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--brand);
        }

        .toolbar-title p {
            margin: 0;
            font-size: 0.82rem;
            color: var(--muted);
        }

        .toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
        }

        .toolbar-select {
            min-width: 210px;
            padding: 0.62rem 0.8rem;
            border: 1px solid var(--panel-border);
            border-radius: 10px;
            background: #fff;
            font: inherit;
            color: var(--ink);
        }

        .toolbar-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            background: var(--brand);
            color: #fff;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .toolbar-badge.success {
            background: #15803d;
        }

        .btn-toolbar {
            padding: 0.62rem 0.95rem;
            border: 1px solid var(--panel-border);
            border-radius: 10px;
            background: #fff;
            color: var(--ink);
            font: inherit;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            transition: transform 0.15s ease, box-shadow 0.15s ease, opacity 0.15s ease;
        }

        .btn-toolbar:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
        }

        .btn-toolbar.primary {
            border-color: #163c8c;
            background: #163c8c;
            color: #fff;
        }

        .btn-toolbar.link {
            text-decoration: none;
        }

        .page-shell {
            max-width: 1440px;
            margin: 0 auto;
            padding: 24px;
        }

        .class-section {
            margin-bottom: 26px;
            padding: 18px;
            background: rgba(255, 255, 255, 0.58);
            border: 1px solid rgba(219, 227, 240, 0.8);
            border-radius: 18px;
        }

        .class-section.is-hidden {
            display: none;
        }

        .class-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 16px;
        }

        .class-title {
            margin: 0;
            font-size: 0.94rem;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: 0.02em;
        }

        .class-subtitle {
            margin: 4px 0 0;
            font-size: 0.78rem;
            color: var(--muted);
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(var(--card-width), var(--card-width)));
            gap: var(--card-gap-screen);
            justify-content: center;
        }

        .card-wrapper {
            position: relative;
            width: var(--card-width);
        }

        .card-wrapper.unchecked .kartu-ujian {
            opacity: 0.35;
            filter: grayscale(0.25);
        }

        .card-wrapper.filtered-out {
            display: none;
        }

        .checkbox-label {
            position: absolute;
            top: 8px;
            left: 8px;
            z-index: 2;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 8px;
            border: 1px solid rgba(22, 60, 140, 0.2);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.12);
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--brand);
            cursor: pointer;
            user-select: none;
        }

        .checkbox-label input[type="checkbox"] {
            width: 14px;
            height: 14px;
            margin: 0;
            accent-color: var(--brand);
        }

        .kartu-ujian {
            width: var(--card-width);
            height: var(--card-height);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            border: 1.4px solid #143572;
            border-radius: 13px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
        }

        .kartu-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 0.22cm 0.28cm 0.2cm;
            background: linear-gradient(135deg, #143572 0%, #1d4fa8 100%);
            color: #fff;
            border-bottom: 0.06cm solid #d9ab1d;
        }

        .kartu-brand {
            min-width: 0;
        }

        .kartu-brand .school {
            font-size: 6.6pt;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .kartu-brand .subtitle {
            margin-top: 0.03cm;
            font-size: 5.3pt;
            line-height: 1.15;
            opacity: 0.92;
        }

        .kartu-title-badge {
            flex-shrink: 0;
            padding: 0.08cm 0.16cm;
            border: 0.02cm solid rgba(255, 255, 255, 0.24);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
            font-size: 5.3pt;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .kartu-body {
            display: grid;
            grid-template-columns: 1fr 1.95cm;
            gap: 0.18cm;
            padding: 0.18cm 0.26cm 0.14cm;
            flex: 1;
            min-height: 0;
        }

        .identity-pane {
            min-width: 0;
        }

        .student-name {
            margin: 0 0 0.08cm;
            font-size: 8.6pt;
            font-weight: 800;
            line-height: 1.1;
            color: #0f172a;
            text-transform: uppercase;
        }

        .student-class-chip {
            display: inline-flex;
            align-items: center;
            padding: 0.05cm 0.16cm;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand);
            font-size: 5.8pt;
            font-weight: 700;
            margin-bottom: 0.1cm;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: 0.95cm 0.08cm 1fr;
            row-gap: 0.04cm;
            column-gap: 0.04cm;
            font-size: 6.1pt;
            line-height: 1.25;
        }

        .detail-grid .label {
            color: #475569;
            font-weight: 700;
        }

        .detail-grid .sep {
            color: #94a3b8;
            text-align: center;
        }

        .detail-grid .value {
            min-width: 0;
            font-weight: 600;
            color: #0f172a;
            word-break: break-word;
        }

        .detail-note {
            margin-top: 0.1cm;
            padding: 0.08cm 0.11cm;
            border: 0.02cm solid #f3d382;
            border-radius: 0.14cm;
            background: #fff7e0;
            font-size: 5.25pt;
            line-height: 1.25;
            color: #8a5a00;
        }

        .aside-pane {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: space-between;
            gap: 0.12cm;
        }

        .photo-box {
            flex: 0 0 auto;
            height: 2.28cm;
            border: 0.03cm dashed #90a4c5;
            border-radius: 0.16cm;
            background: linear-gradient(180deg, #f8fbff 0%, #eef4ff 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #6b7fa6;
            text-align: center;
            padding: 0.08cm;
        }

        .photo-box svg {
            width: 0.5cm;
            height: 0.5cm;
            margin-bottom: 0.03cm;
        }

        .photo-box span {
            font-size: 5.1pt;
            font-weight: 700;
            line-height: 1.15;
        }

        .signature-box {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            padding: 0.08cm 0.1cm;
            border-radius: 0.16cm;
            background: #f8fafc;
            border: 0.02cm solid #dbe3f0;
            text-align: center;
            color: #475569;
        }

        .signature-box .location {
            font-size: 4.9pt;
            line-height: 1.2;
        }

        .signature-box .role {
            margin-top: 0.03cm;
            font-size: 4.8pt;
            line-height: 1.2;
        }

        .signature-box .line {
            margin: 0.22cm auto 0.04cm;
            width: 1.35cm;
            border-top: 0.02cm solid #475569;
        }

        .signature-box .name {
            font-size: 5.1pt;
            font-weight: 800;
            line-height: 1.2;
            color: #0f172a;
        }

        .kartu-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.14cm;
            padding: 0.12cm 0.26cm 0.14cm;
            border-top: 0.02cm solid #dbe3f0;
            background: #fbfdff;
        }

        .kartu-footer .left {
            font-size: 5.1pt;
            font-weight: 700;
            color: #143572;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .kartu-footer .right {
            font-size: 4.95pt;
            text-align: right;
            line-height: 1.2;
            color: #475569;
        }

        .empty-state {
            padding: 48px 20px;
            text-align: center;
            color: var(--muted);
            font-size: 0.92rem;
        }

        @media print {
            @page {
                size: landscape;
                margin: 1cm;
            }

            body {
                background: #fff;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .page-shell {
                max-width: none;
                padding: 0;
            }

            .class-section {
                margin: 0 0 0.4cm;
                padding: 0;
                background: transparent;
                border: 0;
                border-radius: 0;
                break-inside: auto;
                page-break-inside: auto;
            }

            .class-section.is-hidden {
                display: none !important;
            }

            .class-header {
                margin-bottom: 0.2cm;
            }

            .class-title {
                font-size: 10pt;
            }

            .class-subtitle {
                font-size: 7pt;
            }

            .cards-grid {
                gap: var(--card-gap-print);
                justify-content: center;
            }

            .card-wrapper {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .card-wrapper.filtered-out,
            .card-wrapper.hidden-for-print {
                display: none !important;
            }

            .kartu-ujian {
                box-shadow: none;
            }
        }
    </style>
</head>
<body>

<div class="toolbar no-print">
    <div class="toolbar-title">
        <h1>Cetak Kartu Peserta Ujian</h1>
        <p>
            Ukuran tiap kartu tetap <strong>8.56 cm x 5.398 cm</strong>.
            {{ $paperModeText }}
        </p>
        <p>
            Total data <span id="totalCount" class="toolbar-badge">{{ $students->count() }}</span>
            &nbsp;|&nbsp;
            Akan dicetak <span id="selectedCount" class="toolbar-badge success">{{ $students->count() }}</span>
        </p>
    </div>

    <div class="toolbar-actions">
        <select id="classFilter" class="toolbar-select" onchange="applyClassFilter()">
            <option value="all">Semua kelas</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
            @if($students->contains(fn ($student) => !$student->class_id))
                <option value="unassigned">Tanpa kelas</option>
            @endif
        </select>

        <button type="button" onclick="selectVisible()" class="btn-toolbar">Pilih Yang Tampil</button>
        <button type="button" onclick="deselectVisible()" class="btn-toolbar">Batal Yang Tampil</button>
        <a href="{{ route('admin.users.index') }}" class="btn-toolbar link">Kembali</a>
        <button type="button" onclick="doPrint()" class="btn-toolbar primary">Cetak Kartu Terpilih</button>
    </div>
</div>

<div class="page-shell">
    @forelse($groupedStudents as $className => $classStudents)
        @php
            $classId = $classStudents->first()->class_id ?? 'unassigned';
        @endphp

        <section class="class-section" data-class-id="{{ $classId }}">
            <div class="class-header">
                <div>
                    <h2 class="class-title">Kelas {{ $className }}</h2>
                    <p class="class-subtitle">{{ $classStudents->count() }} peserta tersedia pada kelompok ini.</p>
                </div>
            </div>

            <div class="cards-grid">
                @foreach($classStudents as $siswa)
                    @php
                        $cardClassId = $siswa->class_id ?? 'unassigned';
                    @endphp

                    <div
                        class="card-wrapper"
                        id="wrapper-{{ $siswa->id }}"
                        data-card-class="{{ $cardClassId }}"
                    >
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

                        <article class="kartu-ujian">
                            <header class="kartu-header">
                                <div class="kartu-brand">
                                    <div class="school">SMP Katolik St. Johanis Laikit</div>
                                    <div class="subtitle">Kartu peserta ujian akhir semester</div>
                                </div>
                                <div class="kartu-title-badge">ID Card</div>
                            </header>

                            <div class="kartu-body">
                                <div class="identity-pane">
                                    <h3 class="student-name">{{ $siswa->name }}</h3>
                                    <div class="student-class-chip">{{ $siswa->classRoom->name ?? 'Tanpa Kelas' }}</div>

                                    <div class="detail-grid">
                                        <div class="label">Username</div>
                                        <div class="sep">:</div>
                                        <div class="value">{{ $siswa->exam_username ?? '-' }}</div>

                                        <div class="label">Password</div>
                                        <div class="sep">:</div>
                                        <div class="value">{{ $siswa->exam_password_plain ?? '-' }}</div>
                                    </div>

                                    <div class="detail-note">
                                        Kartu wajib dibawa saat ujian dan hanya berlaku untuk peserta yang namanya tercantum.
                                    </div>
                                </div>

                                <div class="aside-pane">
                                    <div class="photo-box">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                        <span>Foto 3x4</span>
                                    </div>

                                    <div class="signature-box">
                                        <div class="location">Minahasa Utara, 24 April 2026</div>
                                        <div class="role">Kepala Satuan Pendidikan</div>
                                        <div class="line"></div>
                                        <div class="name">Annastas Wantania, S.Pd</div>
                                    </div>
                                </div>
                            </div>

                            <footer class="kartu-footer">
                                <div class="left">TP 2025/2026</div>
                                <div class="right">{{ $siswa->classRoom->name ?? 'Tanpa Kelas' }}</div>
                            </footer>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>
    @empty
        <div class="empty-state">
            Belum ada data siswa yang dapat dicetak.
        </div>
    @endforelse
</div>

<script>
    function getCheckboxes() {
        return Array.from(document.querySelectorAll('.card-checkbox'));
    }

    function getVisibleWrappers() {
        return Array.from(document.querySelectorAll('.card-wrapper')).filter(function(wrapper) {
            return !wrapper.classList.contains('filtered-out');
        });
    }

    function getVisibleCheckedCount() {
        return getVisibleWrappers().filter(function(wrapper) {
            const checkbox = wrapper.querySelector('.card-checkbox');
            return checkbox && checkbox.checked;
        }).length;
    }

    function updateSectionVisibility() {
        document.querySelectorAll('.class-section').forEach(function(section) {
            const visibleCards = section.querySelectorAll('.card-wrapper:not(.filtered-out)');
            section.classList.toggle('is-hidden', visibleCards.length === 0);
        });
    }

    function updateSelection() {
        getCheckboxes().forEach(function(cb) {
            const wrapper = document.getElementById('wrapper-' + cb.dataset.id);
            if (!wrapper) {
                return;
            }

            wrapper.classList.toggle('unchecked', !cb.checked);
            wrapper.classList.toggle('hidden-for-print', !cb.checked);
        });

        document.getElementById('selectedCount').textContent = getVisibleCheckedCount();
        updateSectionVisibility();
    }

    function applyClassFilter() {
        const filterValue = document.getElementById('classFilter').value;

        document.querySelectorAll('.card-wrapper').forEach(function(wrapper) {
            const cardClass = wrapper.dataset.cardClass;
            const isMatch = filterValue === 'all' || filterValue === cardClass;
            wrapper.classList.toggle('filtered-out', !isMatch);
        });

        updateSectionVisibility();
        document.getElementById('selectedCount').textContent = getVisibleCheckedCount();
    }

    function selectVisible() {
        getVisibleWrappers().forEach(function(wrapper) {
            const checkbox = wrapper.querySelector('.card-checkbox');
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        updateSelection();
    }

    function deselectVisible() {
        getVisibleWrappers().forEach(function(wrapper) {
            const checkbox = wrapper.querySelector('.card-checkbox');
            if (checkbox) {
                checkbox.checked = false;
            }
        });

        updateSelection();
    }

    function doPrint() {
        if (getVisibleCheckedCount() === 0) {
            alert('Belum ada kartu yang dipilih pada kelas yang sedang ditampilkan.');
            return;
        }

        window.print();
    }

    updateSelection();
    applyClassFilter();
</script>

</body>
</html>
