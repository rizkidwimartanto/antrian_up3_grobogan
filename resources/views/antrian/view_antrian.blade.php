<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Display Antrian</title>
    <link rel="icon" href="{{ asset('public/img/Logo_PLN.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-900 to-gray-800 text-white">

    {{-- Header --}}
    <div class="text-center mt-8">
        <h1 class="text-6xl font-extrabold mb-2">Antrian ULP Tegowanu</h1>
        <p class="text-gray-300 text-2xl">Pantauan Nomor Antrian Saat Ini</p>
    </div>

    {{-- Konten --}}
    <div class="flex flex-row gap-8 mt-14 w-full px-10">

        {{-- KIRI: LAYANAN | COL-3 --}}
        <div id="antrianContainer" class="col-span-3 flex flex-col gap-6">
            @foreach ($layanans as $layanan)
                @php
                    $warna = match ($layanan['kode']) {
                        'A' => 'from-blue-500 to-blue-700',
                        'B' => 'from-green-500 to-green-700',
                        default => 'from-gray-500 to-gray-700',
                    };
                @endphp

                <div class="antrian-box bg-gradient-to-br {{ $warna }} rounded-3xl p-12 text-center shadow-2xl"
                    data-kode="{{ $layanan['kode'] }}">

                    <h2 class="text-5xl font-extrabold mb-6">
                        {{ $layanan['nama'] }}
                    </h2>

                    @if (!empty($antrianSekarang[$layanan['kode']]))
                        <p class="nomor-display text-9xl font-extrabold animate-pulse">
                            {{ $antrianSekarang[$layanan['kode']]->layanan }}
                            {{ $antrianSekarang[$layanan['kode']]->nomor }}
                        </p>
                        <p class="status text-3xl mt-6 text-gray-100">Sedang dipanggil...</p>
                    @else
                        <p class="nomor-display text-8xl">—</p>
                        <p class="status text-3xl text-gray-400">Belum ada antrian</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- KANAN: VIDEO | COL-9 --}}
        <div class="col-span-9 relative flex items-center">
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

            {{-- Overlay klik suara --}}
            <div id="videoOverlay"
                class="absolute inset-0 flex items-center justify-center bg-black/40 text-white text-xl cursor-pointer rounded-3xl">
                Klik untuk aktifkan suara 🔊
            </div>
        </div>

    </div>

    {{-- SCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const video = document.getElementById('promoVideo');
            const overlay = document.getElementById('videoOverlay');

            // ===============================
            // VIDEO AUTOPLAY
            // ===============================
            video.muted = true;
            video.play();

            overlay.addEventListener('click', () => {
                video.muted = false;
                video.volume = 1.0;
                video.play();
                overlay.style.display = 'none';
            });

            // ===============================
            // AUTO UPDATE ANTRIAN
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
