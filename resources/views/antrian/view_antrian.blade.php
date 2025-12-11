<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Display Antrian</title>
    <link rel="icon" href="{{ asset('public/img/Logo_PLN.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 text-white flex flex-col items-center">

    {{-- Header --}}
    <div class="text-center mt-8">
        <h1 class="text-6xl font-extrabold tracking-wide mb-2">📺 DISPLAY ANTRIAN</h1>
        <p class="text-gray-300 text-2xl">Pantauan Nomor Antrian Saat Ini</p>
    </div>

    {{-- Konten utama: Layanan + Video --}}
    <div class="flex flex-col lg:flex-row justify-center items-start gap-12 mt-14 w-full max-w-[1600px] px-10">

        {{-- Kiri: Daftar Layanan --}}
        <div id="antrianContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-10 flex-1">
            @foreach ($layanans as $layanan)
                @php
                    $kode = $layanan['kode'];
                    $nama = $layanan['nama'];

                    $warna = match ($kode) {
                        'A' => 'from-blue-500 to-blue-700',
                        'B' => 'from-green-500 to-green-700',
                        default => 'from-gray-500 to-gray-700',
                    };
                @endphp

                <div class="antrian-box rounded-3xl shadow-2xl bg-gradient-to-br {{ $warna }} p-12 text-center transition duration-500 hover:scale-105"
                    data-kode="{{ $kode }}">

                    <h2 class="text-4xl font-bold text-white mb-6">{{ $nama }}</h2>

                    @if (!empty($antrianSekarang[$kode]))
                        <p class="nomor-display text-8xl font-extrabold tracking-wider text-white animate-pulse">
                            {{ $antrianSekarang[$kode]->layanan }}{{ $antrianSekarang[$kode]->nomor }}
                        </p>
                        <p class="status text-2xl text-gray-100 mt-6">Sedang dipanggil...</p>
                    @else
                        <p class="nomor-display text-7xl font-bold text-gray-200 mb-4">—</p>
                        <p class="status text-2xl text-gray-300">Belum ada antrian</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Kanan: Video --}}
        <div class="flex-1 w-full max-w-2xl mt-10 lg:mt-0">
            @php
                $path = public_path('video/current_video.txt');
                $videoFile = 'public/video/icon.mp4';
                if (file_exists($path)) {
                    $filename = trim(file_get_contents($path));
                    if (file_exists(public_path('video/' . $filename))) {
                        $videoFile = 'public/video/' . $filename;
                    }
                }
            @endphp

            <video id="promoVideo" class="w-full rounded-3xl shadow-2xl border-4 border-white/20 scale-105" autoplay
                loop muted playsinline>
                <source src="{{ asset($videoFile) }}" type="video/mp4">
                Browser Anda tidak mendukung video tag.
            </video>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('promoVideo');

            // Restore posisi terakhir video
            const savedTime = localStorage.getItem('videoTime');
            if (savedTime) {
                video.currentTime = parseFloat(savedTime);
            }

            // Simpan posisi video setiap detik
            video.addEventListener('timeupdate', () => {
                localStorage.setItem('videoTime', video.currentTime);
            });

            // Pastikan selalu mute (wajib untuk autoplay)
            video.muted = true;

            // Jalankan autoplay
            video.play().catch(() => {
                console.warn("Autoplay gagal — mungkin butuh interaksi pertama.");
            });

            // ===============================
            //  Auto Update Antrian
            // ===============================
            async function updateAntrian() {
                try {
                    const response = await fetch('{{ route('antrian.data') }}');
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('promoVideo');
            const savedTime = localStorage.getItem('videoTime');
            if (savedTime) video.currentTime = parseFloat(savedTime);

            video.addEventListener('timeupdate', () => {
                localStorage.setItem('videoTime', video.currentTime);
            });
        });
    </script>
</body>

</html>
