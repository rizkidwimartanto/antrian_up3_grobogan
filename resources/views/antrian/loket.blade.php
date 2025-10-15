<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Panel Loket</title>
    <link rel="icon" href="{{ asset('public/img/Logo_PLN.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen bg-gray-100 flex flex-col items-center p-8">

    <h1 class="text-3xl font-bold mb-6">Panel Loket Pemanggil Antrian</h1>

    @if (session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- 🔹 Layout dua kolom: kiri (layanan) dan kanan (tabel) --}}
    <div class="flex flex-col lg:flex-row w-full max-w-7xl gap-6">

        {{-- 🔸 Kolom KIRI: Panel Layanan --}}
        <div class="flex-1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($layanans as $layanan)
                    @php
                        $kode = $layanan['kode'];
                        $nama = $layanan['nama'];
                    @endphp

                    <div
                        class="bg-white shadow-md rounded-xl p-6 text-center border-t-4 
                        @if ($kode == 'A') border-blue-600 
                        @elseif($kode == 'B') border-green-600 @endif">

                        <h2 class="text-2xl font-semibold mb-2 flex items-center justify-center gap-2">
                            {{ $nama }} {{-- 🔹 tampilkan nama layanan --}}
                            <span id="berikutnya_{{ $kode }}"
                                class="text-sm bg-gray-200 text-gray-700 px-3 py-1 rounded-full">
                                @if ($antrianBerikutnya[$kode])
                                    Berikutnya:
                                    {{ $antrianBerikutnya[$kode]->layanan }}{{ $antrianBerikutnya[$kode]->nomor }}
                                @else
                                    (Tidak ada)
                                @endif
                            </span>
                        </h2>

                        <p class="text-gray-500 mb-3">Nomor Sekarang:</p>

                        <p id="sekarang_{{ $kode }}" class="text-5xl font-extrabold text-blue-600 mb-3">
                            @if ($antrianSekarang[$kode])
                                {{ $antrianSekarang[$kode]->layanan }}{{ $antrianSekarang[$kode]->nomor }}
                            @else
                                —
                            @endif
                        </p>

                        <form action="{{ route('antrian.panggil') }}" method="POST" class="space-y-2">
                            @csrf
                            <input type="hidden" name="layanan" value="{{ $kode }}">
                            <button type="submit" name="aksi" value="berikutnya"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg font-semibold w-full">
                                🔊 Panggil Berikutnya
                            </button>

                            @if ($antrianSekarang[$kode])
                                <button type="submit" name="aksi" value="ulang"
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-3 rounded-lg font-semibold w-full">
                                    🔁 Panggil Lagi
                                    ({{ $antrianSekarang[$kode]->layanan }}{{ $antrianSekarang[$kode]->nomor }})
                                </button>
                            @endif
                        </form>
                    </div>
                @endforeach
            </div>

            {{-- 🔴 Tombol Reset Manual --}}
            <form action="{{ route('antrian.reset') }}" method="POST"
                onsubmit="return confirm('Yakin ingin mereset semua antrian hari ini?')">
                @csrf
                <button type="submit"
                    class="mt-6 bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transition duration-200 w-full">
                    🔄 Reset Antrian Hari Ini
                </button>
            </form>

            <a href="{{ route('antrian.index') }}"
                class="mt-6 inline-block text-blue-600 hover:underline text-center w-full">
                ← Kembali ke Halaman Utama
            </a>

            <div class="mt-6 bg-white p-4 rounded-lg shadow-md border border-gray-200">
                <h3 class="text-lg font-semibold mb-2 text-gray-700">🎥 Update Video Tampilan Antrian</h3>
                <form action="{{ route('antrian.upload_video') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="flex items-center gap-4">
                        <input type="file" name="video" accept="video/mp4" required
                            class="border border-gray-300 rounded p-2 w-full text-sm">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium">
                            🔄 Update Video
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Hanya format .mp4 (maks 100MB).</p>
                </form>
            </div>

        </div>

        {{-- 🔸 Kolom KANAN: Tabel Data Antrian --}}
        <div class="flex-1 bg-white shadow-md rounded-xl p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-semibold text-gray-700">Data Antrian</h2>

                <form method="GET" action="{{ route('antrian.loket') }}">
                    <label for="entries" class="text-gray-600 text-sm mr-2">Show entries:</label>
                    <select name="entries" id="entries" onchange="this.form.submit()"
                        class="border-gray-300 rounded-md text-sm p-1.5">
                        <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                        <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                        <option value="30" {{ $perPage == 30 ? 'selected' : '' }}>30</option>
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 rounded-lg overflow-hidden">
                    <thead class="bg-blue-600 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left">Layanan</th>
                            <th class="py-3 px-4 text-left">Nomor</th>
                            <th class="py-3 px-4 text-left">Status</th>
                            <th class="py-3 px-4 text-left">Waktu Cetak</th>
                        </tr>
                    </thead>
                    <tbody id="antrian-body" class="divide-y divide-gray-200">
                        @forelse($antrians as $antrian)
                            <tr class="hover:bg-gray-50">
                                @if ($antrian->layanan === 'A')
                                    <td class="py-3 px-4 font-semibold text-blue-600">Pelayanan Pelanggan</td>
                                @elseif ($antrian->layanan === 'B')
                                    <td class="py-3 px-4 font-semibold text-blue-600">Pengaduan</td>
                                @endif

                                <td class="py-3 px-4 font-semibold text-blue-600">
                                    {{ $antrian->layanan }}{{ $antrian->nomor }}
                                </td>

                                <td class="py-3 px-4">
                                    @if ($antrian->status === 'dipanggil')
                                        <span
                                            class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm font-medium">Dipanggil</span>
                                    @elseif($antrian->status === 'menunggu')
                                        <span
                                            class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium">Menunggu</span>
                                    @elseif($antrian->status === 'selesai')
                                        <span
                                            class="px-3 py-1 rounded-full bg-gray-200 text-gray-600 text-sm font-medium">Selesai</span>
                                    @elseif($antrian->status === 'reset_antrian')
                                        <span
                                            class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-sm font-medium">Reset
                                            Antrian</span>
                                    @endif
                                </td>

                                <td class="py-3 px-4 text-sm text-gray-600">
                                    {{ $antrian->created_at->format('d M Y H:i') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-gray-500 italic">Belum ada data antrian
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 🔹 Pagination --}}
            <div id="pagination" class="mt-4 flex justify-center space-x-2"></div>

        </div>
    </div>

    {{-- 🔊 Suara --}}
    @if (session('speak'))
        <script>
            const text = "{{ session('speak') }}";

            function speak(text) {
                const utter = new SpeechSynthesisUtterance(text);
                utter.lang = 'id-ID';
                utter.rate = 0.9;
                speechSynthesis.speak(utter);
            }
            setTimeout(() => speak(text), 800);
        </script>
    @endif

    <script>
        function updateAntrian() {
            fetch("{{ route('antrian.refresh') }}")
                .then(res => res.json())
                .then(data => {
                    for (const kode in data) {
                        document.getElementById(`sekarang_${kode}`).innerText = data[kode].sekarang;
                        document.getElementById(`berikutnya_${kode}`).innerText = "Berikutnya: " + data[kode]
                            .berikutnya;
                    }
                })
                .catch(err => console.error('Gagal memuat data antrian:', err));
        }

        updateAntrian();
        setInterval(updateAntrian, 3000);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let currentPage = 1; // halaman aktif
            const entries = 5; // jumlah data per halaman

            function renderAntrian(data) {
                const tbody = document.getElementById('antrian-body');
                tbody.innerHTML = '';

                if (!data || data.length === 0) {
                    tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4 text-gray-500 italic">
                        Belum ada data antrian
                    </td>
                </tr>`;
                    return;
                }

                data.forEach(item => {
                    const layananNama = item.layanan === 'A' ?
                        'Pelayanan Pelanggan' :
                        item.layanan === 'B' ?
                        'Pengaduan' :
                        'Tidak Dikenal';

                    let statusLabel = '';
                    switch (item.status) {
                        case 'dipanggil':
                            statusLabel =
                                `<span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm font-medium">Dipanggil</span>`;
                            break;
                        case 'menunggu':
                            statusLabel =
                                `<span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium">Menunggu</span>`;
                            break;
                        case 'selesai':
                            statusLabel =
                                `<span class="px-3 py-1 rounded-full bg-gray-200 text-gray-600 text-sm font-medium">Selesai</span>`;
                            break;
                        case 'reset_antrian':
                            statusLabel =
                                `<span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-sm font-medium">Reset Antrian</span>`;
                            break;
                    }

                    const row = `
                <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4 font-semibold text-blue-600">${layananNama}</td>
                    <td class="py-3 px-4 font-semibold text-blue-600">${item.layanan}${item.nomor}</td>
                    <td class="py-3 px-4">${statusLabel}</td>
                    <td class="py-3 px-4 text-sm text-gray-600">
                        ${new Date(item.created_at).toLocaleString('id-ID')}
                    </td>
                </tr>`;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            }

            function renderPagination(response) {
                const paginationDiv = document.getElementById('pagination');
                paginationDiv.innerHTML = '';

                if (!response || response.last_page <= 1) return;

                let buttons = '';

                // Tombol Previous
                if (response.current_page > 1) {
                    buttons += `
                <button data-page="${response.current_page - 1}" 
                    class="px-3 py-1 border rounded hover:bg-gray-100">Prev</button>`;
                }

                // Tombol halaman
                for (let i = 1; i <= response.last_page; i++) {
                    buttons += `
                <button data-page="${i}" 
                    class="px-3 py-1 border rounded 
                        ${i === response.current_page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100'}">
                    ${i}
                </button>`;
                }

                // Tombol Next
                if (response.current_page < response.last_page) {
                    buttons += `
                <button data-page="${response.current_page + 1}" 
                    class="px-3 py-1 border rounded hover:bg-gray-100">Next</button>`;
                }

                paginationDiv.innerHTML = buttons;

                // Tambahkan event listener untuk klik halaman
                document.querySelectorAll('#pagination button').forEach(btn => {
                    btn.addEventListener('click', function() {
                        currentPage = parseInt(this.getAttribute('data-page'));
                        fetchAntrian();
                    });
                });
            }

            function fetchAntrian() {
                fetch(`{{ route('antrian.data_loket') }}?page=${currentPage}&entries=${entries}`)
                    .then(res => res.json())
                    .then(response => {
                        renderAntrian(response.data);
                        renderPagination(response);
                    })
                    .catch(err => console.error('Gagal mengambil data antrian:', err));
            }

            // Jalankan saat halaman pertama kali dibuka
            fetchAntrian();

            // Update otomatis setiap 3 detik (tetap di halaman aktif)
            setInterval(fetchAntrian, 3000);
        });
    </script>
</body>

</html>
