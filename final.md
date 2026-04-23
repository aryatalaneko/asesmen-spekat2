# 🚀 Kebutuhan Update Final H-1 (Enterprise Assessment System)

**Konteks untuk AI Assistant:**
Sistem ujian berbasis Laravel 11 ini akan diimplementasikan BESOK (Jumat). Kita memiliki 4 Task kritikal yang harus diselesaikan. Tolong tuliskan kode dengan sangat hati-hati, pastikan error handling yang baik, dan jangan merombak arsitektur database yang sudah ada. Terapkan Tailwind CSS untuk semua UI.

---

## TASK 1: Fitur Cetak ID Card Peserta (Role Admin)
Admin membutuhkan fitur untuk mencetak Kartu Ujian Siswa (kertas A4). 
Package `simplesoftwareio/simple-qrcode` sudah terinstal.

**Kebutuhan:**
1. **Route:** Buat `Route::get('/admin/cetak-kartu', [AdminController::class, 'printCards'])`.
2. **Controller:** Method `printCards` mengambil semua data user dengan `role == 'siswa'`.
3. **View (`admin/print-cards.blade.php`):**
   - **CSS:** Gunakan Tailwind. Tambahkan `<style> @media print { .card-container { break-inside: avoid; page-break-inside: avoid; } body { background: white; } } </style>`.
   - **Layout Utama:** Grid 2 kolom (`grid-cols-2`).
   - **Desain Kartu (Border hitam tipis, padding rapi):**
     - **Header:** Teks rata tengah, font tebal: "KARTU PESERTA UJIAN AKHIR SEMESTER"<br>"SMP KATOLIK ST. JOHANIS LAIKIT TAHUN 2026". Beri border bottom tebal.
     - **Body (Tabel/Grid):**
       - Nama Peserta : `{{ $siswa->name }}`
       - Username : `{{ $siswa->username }}`
       - Password : `{{ $siswa->nis }}` *(Panggil data NIS di sini, bukan kolom password)*.
       - Asal Sekolah : SMP KATOLIK ST. JOHANIS LAIKIT
     - **Footer (Flex justify-between):**
       - Kiri: Kotak 3x4 cm bergaris tipis, berisi ikon SVG user sederhana dan teks "FOTO 3x4".
       - Tengah: Teks rata tengah (ukuran kecil/text-sm): "Minahasa Utara, 24 April 2026"<br>"Kepala Satuan Pendidikan,"<br><br><br>"(Nama Kepala Sekolah, S.Pd)"<br>"NIP. -".
       - Kanan: QR Code `Qrcode::size(60)->generate($siswa->nis)`.

---

## TASK 2: Fix Bug Toggle Status Ujian & Auto-Deactivate (Role Guru)
Ada error saat Guru mengaktifkan/menonaktifkan ujian di Dashboard Monitoring. Sekaligus kita perlu logika agar ujian otomatis tertutup saat waktu habis.

**Tindakan yang Dibutuhkan:**
1. **Perbaikan Controller:** Cek method `toggleStatus` atau `activateExam` di Controller Guru. Pastikan tipe data yang dikirim dan diterima benar. Pastikan event Reverb (jika ada) ter-*dispatch* dengan aman menggunakan `try-catch`.
2. **Logika Auto-Deactivate:** - Karena H-1, gunakan validasi *On-Demand*. Saat Siswa akan submit jawaban, Controller harus mengecek apakah `now()` sudah melewati `activated_at + durasi`. 
   - Jika sudah lewat, ubah status ujian di database menjadi `selesai`/`nonaktif` secara otomatis, dan terima jawaban siswa tersebut sebagai *submission* terakhir.

---

## TASK 3: Fitur Auto-Submit pada Ujian Siswa (Role Siswa)
Siswa harus dipaksa submit (otomatis mengirim jawaban) jika waktu (timer) di UI sudah habis.

**Tindakan yang Dibutuhkan (di dalam `take_exam.blade.php` atau JS terkait):**
1. Di dalam script *Countdown Timer* (Vanilla JS), tambahkan logika pengecekan jika sisa waktu `(timeRemaining <= 0)`.
2. Jika <= 0:
   - Hentikan interval timer (`clearInterval`).
   - Tampilkan Alert/Modal (misal: "Waktu Habis! Jawaban Anda sedang dikirim otomatis...").
   - Kunci/Disable semua input jawaban (Radio button & Textarea).
   - Eksekusi `.submit()` pada Form ujian, atau jalankan fungsi AJAX *Final Submit* jika menggunakan SPA/AJAX.

---

## TASK 4: Halaman Hasil Ujian & Analisis (Role Guru)
Saat ini Guru belum bisa melihat nilai ujian (hanya bisa lihat analisis K-Means/NLP). Guru butuh tabel rekap nilai siswa.

**Tindakan yang Dibutuhkan:**
1. **Route & Controller:** Buatkan method `examResults($exam_id)` di Controller Guru.
2. **View (`guru/exam-results.blade.php`):**
   - Tampilkan informasi Ujian (Nama Mapel, Kelas).
   - Tampilkan **Tabel Rekap Nilai**: Nomor, Nama Siswa, Nilai Akhir (Skala 100), Status Kelulusan (Lulus KKM / Tidak).
   - Pastikan Controller mengambil relasi data yang tepat (antara `Exams`, `Results`, dan `Users`).
   - Tambahkan tombol "Export Excel" (opsional, jika memungkinkan waktu H-1, gunakan library Laravel-Excel yang sudah ada).