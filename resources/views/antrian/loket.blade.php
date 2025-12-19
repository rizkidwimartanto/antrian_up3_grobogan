<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Panel Loket</title>

    {{-- Tailwind --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="{{ asset('public/img/Logo_PLN.png') }}" type="image/png">

    {{-- DataTables Tailwind CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.tailwindcss.min.css">

    {{-- Custom kecil (optional) --}}
    <style>
        /* Pagination aktif */
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background-color: #2563eb;
            color: white;
            border-radius: 6px;
        }


        /* Hover pagination */
        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            @apply bg-blue-100 text-blue-700;
        }
    </style>
</head>


<body class="min-h-screen bg-gray-100 flex flex-col items-center p-8">

    <h1 class="text-3xl font-bold mb-6">Panel Loket Pemanggil Antrian</h1>

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

            <form id="resetForm" action="{{ route('antrian.reset') }}" method="POST">
                @csrf
                <button type="button" id="resetBtn"
                    class="mt-6 bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transition duration-200 w-full">
                    🔄 Reset Antrian
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
                            <th class="py-3 px-4 text-left">Aksi</th>
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
                                <td class="py-3 px-4">
                                    @if ($antrian->status === 'menunggu')
                                        <form action="{{ route('antrian.cancel', $antrian->id) }}" method="POST"
                                            onsubmit="return confirm('Batalkan antrian {{ $antrian->layanan }}{{ $antrian->nomor }}?')">
                                            @csrf
                                            <button type="button"
                                                class="btn-cancel bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"
                                                data-id="${item.id}" data-nomor="${item.layanan}${item.nomor}">
                                                ❌ Cancel
                                            </button>
                                        </form>
                                    @endif
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
    <div class="w-full bg-white shadow-lg rounded-xl overflow-hidden mt-6">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-700">
                📊 Total Antrian Per Hari
            </h2>

            <span id="total-hari-ini" class="bg-blue-100 text-blue-700 text-sm font-semibold px-3 py-1 rounded-full">
                Hari ini: {{ $totalHariIni }}
            </span>

        </div>

        <div class="overflow-x-auto p-4">
            <table id="datatable-total-perhari" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-blue-600 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Total Antrian</th>
                    </tr>
                </thead>

                <tbody id="total-perhari-body" class="divide-y divide-gray-100 bg-white">
                    @forelse ($totalPerHari as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-gray-700 font-medium">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}
                            </td>

                            <td class="px-4 py-3 font-bold text-blue-600">
                                {{ $item->total }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-6 text-gray-500 italic">
                                Belum ada data antrian
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const BASE_URL = "{{ url('/') }}";
    </script>

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    {{-- DataTables JS --}}
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

    {{-- ✅ SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            $('#datatable-total-perhari').DataTable({
                pageLength: 10,
                order: [
                    [0, 'desc']
                ], // tanggal
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    zeroRecords: "Data tidak ditemukan",
                    paginate: {
                        next: "›",
                        previous: "‹"
                    }
                }
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            function renderTotalPerHari(data) {
                const tbody = document.getElementById('total-perhari-body');
                const badge = document.getElementById('total-hari-ini');

                tbody.innerHTML = '';

                if (!data || data.length === 0) {
                    tbody.innerHTML = `
                <tr>
                    <td colspan="2" class="text-center py-6 text-gray-500 italic">
                        Belum ada data antrian
                    </td>
                </tr>
            `;
                    return;
                }

                data.forEach(item => {
                    const tanggal = new Date(item.tanggal).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'long',
                        year: 'numeric'
                    });

                    const row = `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-700 font-medium">${tanggal}</td>
                    <td class="px-4 py-3 font-bold text-blue-600">${item.total}</td>
                </tr>
            `;

                    tbody.insertAdjacentHTML('beforeend', row);
                });
            }

            function fetchTotalPerHari() {
                fetch(`{{ route('antrian.total_perhari') }}`)
                    .then(res => res.json())
                    .then(res => {
                        // 🔹 Update badge
                        document.getElementById('total-hari-ini').innerText =
                            `Hari ini: ${res.totalHariIni}`;

                        // 🔹 Update tabel
                        renderTotalPerHari(res.data);
                    })
                    .catch(err => console.error('Gagal load total per hari:', err));
            }

            // Load awal
            fetchTotalPerHari();

            // 🔄 Update tiap 5 detik
            setInterval(fetchTotalPerHari, 5000);
        });
    </script>


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

                    const layananNama =
                        item.layanan === 'A' ? 'Pelayanan Pelanggan' :
                        item.layanan === 'B' ? 'Pengaduan' : 'Tidak Dikenal';

                    const statusLabel = {
                        menunggu: `<span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm">Menunggu</span>`,
                        dipanggil: `<span class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm">Dipanggil</span>`,
                        selesai: `<span class="px-3 py-1 rounded-full bg-gray-200 text-gray-600 text-sm">Selesai</span>`,
                        reset_antrian: `<span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-sm">Reset</span>`
                    } [item.status] ?? '';

                    // ⏱️ WAKTU SELALU TAMPIL
                    const waktuCetak = new Date(item.created_at).toLocaleString('id-ID');

                    // ❌ AKSI HANYA UNTUK MENUNGGU
                    const aksi = item.status === 'menunggu' ?
                        `
        <form action="${BASE_URL}/antrian/${item.id}/cancel" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <button 
                type="button"
                class="btn-cancel bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm"
                data-id="${item.id}"
                data-nomor="${item.layanan}${item.nomor}">
                ❌ Cancel
            </button>
        </form>
        ` :
                        `<span class="text-gray-400 italic text-sm">-</span>`;

                    const row = `
        <tr class="hover:bg-gray-50">
            <td class="py-3 px-4 font-semibold text-blue-600">${layananNama}</td>
            <td class="py-3 px-4 font-semibold text-blue-600">${item.layanan}${item.nomor}</td>
            <td class="py-3 px-4">${statusLabel}</td>
            <td class="py-3 px-4 text-sm text-gray-600">${waktuCetak}</td>
            <td class="py-3 px-4 text-center">${aksi}</td>
        </tr>
    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            }

            function fetchAntrian() {
                fetch(`{{ route('antrian.data_loket') }}`)
                    .then(res => res.json())
                    .then(response => {
                        // response.data tetap, tapi pagination tidak dipakai
                        renderAntrian(response.data);
                    })
                    .catch(err => console.error('Gagal mengambil data antrian:', err));
            }

            // Pertama kali load
            fetchAntrian();

            // Update setiap 3 detik
            setInterval(fetchAntrian, 3000);

        });
    </script>

    <script>
        document.getElementById('resetBtn').addEventListener('click', function() {
            Swal.fire({
                title: 'Konfirmasi Reset Antrian',
                text: 'Apakah Anda yakin ingin menghapus semua antrian hari ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Reset Sekarang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('resetForm').submit();
                }
            });
        });
    </script>
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                showConfirmButton: false,
                timer: 2000
            }).then(() => location.reload());
        </script>
    @endif
    <script>
        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('btn-cancel')) return;

            const id = e.target.dataset.id;
            const nomor = e.target.dataset.nomor;

            Swal.fire({
                title: 'Batalkan Antrian?',
                text: `Yakin ingin membatalkan antrian ${nomor}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Batalkan',
                cancelButtonText: 'Batal'
            }).then(async (result) => {

                if (!result.isConfirmed) return;

                const response = await fetch(`${BASE_URL}/antrian/${id}/cancel`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                            .content,
                        'Accept': 'application/json'
                    }
                });

                // ❌ JIKA HTTP BUKAN 200
                if (!response.ok) {
                    Swal.fire('Error', 'Gagal membatalkan antrian', 'error');
                    return;
                }

                // 🔥 AMAN
                const res = await response.json();

                if (res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    });

                    fetchAntrian();
                    updateAntrian();
                }
            });
        });
    </script>

</body>

</html>
