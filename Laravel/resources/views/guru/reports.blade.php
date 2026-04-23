@extends('layouts.app')

@section('header_title', $title)

@section('content')
<style>
    .widget-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .widget-card {
        background-color: var(--color-surface);
        padding: 1.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        border-left: 5px solid var(--color-primary);
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .widget-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-hover);
    }

    .widget-title {
        color: var(--color-text-main);
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .widget-value {
        color: var(--color-primary);
        font-size: 2rem;
        font-weight: 700;
    }

    .table-container {
        background-color: var(--color-surface);
        padding: 1.5rem;
        border-radius: var(--radius);
        box-shadow: var(--shadow);
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .status-lulus {
        background-color: #dcfce7;
        color: #166534;
    }

    .status-tidak-lulus {
        background-color: #fee2e2;
        color: #991b1b;
    }

    .progress-bar-container {
        width: 100%;
        background-color: var(--color-border);
        border-radius: 9999px;
        height: 8px;
        margin-top: 0.5rem;
        overflow: hidden;
    }

    .progress-bar-fill {
        background-color: var(--color-secondary);
        height: 100%;
        border-radius: 9999px;
    }
</style>

<!-- Bagian Papan Statistik Deskriptif (Dashboard Widget) -->
<div class="widget-grid">
    <div class="widget-card">
        <div class="widget-title">Nilai Rata-Rata (AVG)</div>
        <div class="widget-value">{{ round($avgScore) }}</div>
    </div>
    
    <div class="widget-card">
        <div class="widget-title">Nilai Tertinggi (MAX)</div>
        <div class="widget-value">{{ round($maxScore) }}</div>
    </div>

    <div class="widget-card">
        <div class="widget-title">Nilai Terendah (MIN)</div>
        <div class="widget-value">{{ round($minScore) }}</div>
    </div>

    <div class="widget-card">
        <div class="widget-title">Ketuntasan Belajar</div>
        <div class="widget-value">{{ number_format($passingPercentage, 1) }}%</div>
        <div class="progress-bar-container">
            <div class="progress-bar-fill" style="width: {{ $passingPercentage }}%;"></div>
        </div>
    </div>
</div>

<!-- Tabel Rekapitulasi Nilai -->
<div class="table-container">
    <h3 style="margin-bottom: 1rem; color: var(--color-text-main);">Tabel Rekapitulasi Nilai</h3>
    
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Siswa</th>
                    <th>Jumlah Benar</th>
                    <th>Jumlah Salah</th>
                    <th>Nilai Akhir</th>
                    <th>Status Kelulusan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $index => $result)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $result->student->name ?? 'User Tidak Ditemukan' }}</td>
                    <td>{{ $result->correct_count }}</td>
                    <td>{{ $result->wrong_count }}</td>
                    <td style="font-weight: 600; color: var(--color-primary);">{{ round($result->final_score) }}</td>
                    <td>
                        <span class="status-badge {{ $result->status === 'lulus' ? 'status-lulus' : 'status-tidak-lulus' }}">
                            {{ strtoupper(str_replace('_', ' ', $result->status)) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem;">Belum ada data rekapitulasi ujian.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
