# 🎯 Visi Utama & Identitas Proyek

**Judul Skripsi:** *Rancang Bangun Sistem Assesment Berbasis Web dengan Analisis Hasil Ujian Siswa Menggunakan Algoritma K-Means Clustering di SMP Katolik St. Johanis Laikit.*

**Tujuan Sistem:**
Membangun Sistem Akademik Level Enterprise yang mampu mengotomatisasi ujian, menilai jawaban teks (*Essay*) dengan Kecerdasan Buatan (NLP), dan mendeteksi secara dini siswa yang tertinggal menggunakan algoritma pemelajaran mesin (*Machine Learning*) K-Means Clustering.

---

## 👑 1. Hak Akses: ADMIN (Pusat Kendali Utama)
Admin bertindak sebagai pengelola sistem inti. Tugas administratif dipisahkan sepenuhnya dari Guru.
* **Master Data Controller:** Satu-satunya pihak yang berhak membuat data Master (Kelas, Mata Pelajaran) serta mendaftarkan akun Siswa dan Guru.
* **Sistem Penugasan (Mapping):** Mengatur relasi penjodohan antara Guru dan Kelas. (Contoh: Admin menugaskan Pak Budi secara eksklusif untuk mengajar dan mengelola ujian hanya di Kelas IX-A dan IX-B).

---

## 👨‍🏫 2. Hak Akses: GURU (Teacher Dashboard)
Dengan beban administratif yang diambil alih Admin, Guru fokus pada kegiatan akademik, penyusunan soal, dan evaluasi nilai.
* **Manajemen Kelas Otomatis:** *Dropdown* pilihan kelas di *dashboard* Guru hanya akan menampilkan kelas-kelas yang telah ditugaskan oleh Admin. Data ujian, soal, dan nilai dijamin tidak akan lintas-kelas.
* **Bank Soal Dinamis (Bobot Fleksibel):** Mampu meracik soal Pilihan Ganda (PG) dan Essay dalam satu paket ujian, dilengkapi dengan pengaturan **Bobot Poin** yang spesifik untuk setiap soal.
* **Analisis K-Means (Deteksi Dini):** Fitur analisis sekali klik yang memerintahkan mesin Python untuk mengelompokkan siswa di kelas aktif ke dalam klaster: *Aman*, *Bimbingan*, atau *Risiko Tinggi*.
* **Riwayat Analisis Permanen:** Hasil klastering disimpan ke dalam tabel historis (`hasil_clustering`) sebagai rekam jejak perkembangan akademik siswa dari waktu ke waktu.

---

## 👨‍🎓 3. Hak Akses: SISWA (Student Dashboard)
Dirancang untuk memberikan pengalaman ujian yang mulus tanpa mengorbankan integritas dan keamanan soal.
* **Ujian Campuran Berkelanjutan:** Transisi pengerjaan otomatis dari sesi soal PG menuju sesi soal Essay tanpa terputus.
* **Penilaian Essay Cerdas (Real-Time NLP):** Jawaban paragraf dianalisis seketika oleh algoritma Python, dicocokkan kemiripan semantiknya dengan kunci jawaban, dan langsung dikonversi menjadi persentase nilai.
* **Ringkasan Nilai Anti-Nyontek:** Segera setelah ujian selesai, sistem menampilkan *feedback* komprehensif tanpa membocorkan detail butir soal, meliputi:
  1. **Nilai Akhir Total:** Gabungan kalkulasi poin PG dan bobot nilai Essay.
  2. **Statistik PG:** Total soal PG yang dijawab Benar dan Salah.
  3. **Statistik Essay:** Total soal Essay yang berhasil dijawab dengan benar (memenuhi ambang batas nilai) dan yang dijawab salah.

---

## ⚙️ 4. Arsitektur Teknologi (Tech Stack)
Sistem ini menggunakan arsitektur hibrida (Dua Otak) yang efisien:
* **Backend Utama & Database:** PHP Native & MySQL (XAMPP).
* **Frontend (Antarmuka):** HTML5, CSS3, Vanilla JavaScript.
* **Mesin Machine Learning (Microservice):** Python, Flask API, Scikit-Learn (untuk K-Means), dan NLTK/Sastrawi (untuk NLP Text Processing).
* **Komunikasi Sistem:** Bridging menggunakan `cURL` PHP ke *endpoint* REST API Flask Python.