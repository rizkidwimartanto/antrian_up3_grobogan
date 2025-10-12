<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Display Antrian</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 text-white flex flex-col items-center">

    {{-- Header --}}
    <div class="text-center mt-6">
        <h1 class="text-5xl font-extrabold tracking-wide mb-2">📺 DISPLAY ANTRIAN</h1>
        <p class="text-gray-300 text-lg">Pantauan Nomor Antrian Saat Ini</p>
    </div>

    {{-- Konten utama: Layanan + Video --}}
    <div class="flex flex-col lg:flex-row justify-center items-start gap-10 mt-10 w-full max-w-7xl px-8">

        {{-- Kiri: Daftar Layanan --}}
        <div id="antrianContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-8 flex-1">
            @foreach ($layanans as $kode)
                @php
                    $warna = match($kode) {
                        'A' => 'from-blue-500 to-blue-700',
                        'B' => 'from-green-500 to-green-700',
                        'C' => 'from-yellow-500 to-yellow-700',
                        'D' => 'from-red-500 to-red-700',
                        default => 'from-gray-500 to-gray-700'
                    };
                @endphp

                <div class="antrian-box rounded-3xl shadow-lg bg-gradient-to-br {{ $warna }} p-8 text-center transition duration-500 hover:scale-105" data-kode="{{ $kode }}">
                    <h2 class="text-3xl font-bold mb-4">Layanan {{ $kode }}</h2>

                    @if ($antrianSekarang[$kode])
                        <p class="nomor-display text-7xl font-extrabold tracking-wider animate-pulse">
                            {{ $antrianSekarang[$kode]->layanan }}{{ $antrianSekarang[$kode]->nomor }}
                        </p>
                        <p class="status text-lg text-gray-100 mt-4">Sedang dipanggil...</p>
                    @else
                        <p class="nomor-display text-6xl font-bold text-gray-300 mb-2">—</p>
                        <p class="status text-lg text-gray-400">Belum ada antrian</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Kanan: Video --}}
        <div class="flex-1 w-full max-w-xl mt-8 lg:mt-0">
            <video 
                id="promoVideo"
                class="w-full rounded-3xl shadow-2xl border-4 border-white/20"
                autoplay 
                loop 
                playsinline
                controls
            >
                <source src="{{ asset('public/video/icon.mp4') }}" type="video/mp4">
                Browser Anda tidak mendukung video tag.
            </video>
            <p class="text-center text-gray-400 text-sm mt-3">🎬 Video Informasi / Promosi</p>
        </div>
    </div>

    {{-- 🎥 Script Video + Auto Update --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('promoVideo');
            const savedTime = localStorage.getItem('videoTime');

            // ⏱️ Lanjutkan posisi video sebelumnya
            if (savedTime) video.currentTime = parseFloat(savedTime);

            // Simpan waktu video agar tidak reset
            video.addEventListener('timeupdate', () => {
                localStorage.setItem('videoTime', video.currentTime);
            });

            // ✅ Pastikan video autoplay dengan suara aktif
            video.muted = false;
            video.volume = 1.0;
            video.play().catch(() => {
                console.warn("Browser memblokir autoplay dengan suara. Klik sekali video untuk memulai audio.");
            });

            // 🔁 Update data antrian tiap 5 detik tanpa suara
            async function updateAntrian() {
                try {
                    const response = await fetch('{{ route("antrian.data") }}');
                    const data = await response.json();

                    for (const kode in data) {
                        const box = document.querySelector(`.antrian-box[data-kode="${kode}"]`);
                        if (!box) continue;

                        const nomorEl = box.querySelector('.nomor-display');
                        const statusEl = box.querySelector('.status');

                        if (data[kode]) {
                            nomorEl.textContent = data[kode].layanan + data[kode].nomor;
                            nomorEl.classList.add('animate-pulse');
                            statusEl.textContent = 'Sedang dipanggil...';
                            statusEl.classList.remove('text-gray-400');
                            statusEl.classList.add('text-gray-100');
                        } else {
                            nomorEl.textContent = '—';
                            nomorEl.classList.remove('animate-pulse');
                            statusEl.textContent = 'Belum ada antrian';
                            statusEl.classList.remove('text-gray-100');
                            statusEl.classList.add('text-gray-400');
                        }
                    }
                } catch (err) {
                    console.error('Gagal memuat data antrian:', err);
                }
            }

            updateAntrian();
            setInterval(updateAntrian, 5000);
        });
    </script>

</body>
</html>
