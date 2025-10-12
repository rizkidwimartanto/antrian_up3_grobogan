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

    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 w-full max-w-5xl">
        @foreach ($layanans as $kode)
            <div
                class="bg-white shadow-md rounded-xl p-6 text-center border-t-4 
            @if ($kode == 'A') border-blue-600 
            @elseif($kode == 'B') border-green-600 
            @elseif($kode == 'C') border-yellow-500 
            @elseif($kode == 'D') border-red-600 @endif">

                <h2 class="text-2xl font-semibold mb-2 flex items-center justify-center gap-2">
                    Layanan {{ $kode }}
                    <span id="berikutnya_{{ $kode }}"
                        class="text-sm bg-gray-200 text-gray-700 px-3 py-1 rounded-full">
                        @if ($antrianBerikutnya[$kode])
                            Berikutnya: {{ $antrianBerikutnya[$kode]->layanan }}{{ $antrianBerikutnya[$kode]->nomor }}
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

    <a href="{{ route('antrian.index') }}" class="mt-10 text-blue-600 hover:underline">
        ← Kembali ke Halaman Utama
    </a>

    {{-- Tombol Reset Manual --}}
    <form action="{{ route('antrian.reset') }}" method="POST"
        onsubmit="return confirm('Yakin ingin mereset semua antrian hari ini?')">
        @csrf
        <button type="submit"
            class="mt-6 mb-6 bg-red-600 hover:bg-red-700 text-white font-semibold px-6 py-3 rounded-lg shadow-lg transition duration-200">
            🔄 Reset Antrian Hari Ini
        </button>
    </form>

    {{-- 🔹 Tabel Data Antrian --}}
    <div class="bg-white shadow-md rounded-xl p-6 w-full max-w-5xl">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-semibold text-gray-700">Data Antrian</h2>

            <form method="GET" action="{{ route('antrian.loket') }}">
                <label for="entries" class="text-gray-600 text-sm mr-2">Show entries:</label>
                <select name="entries" id="entries" onchange="this.form.submit()"
                    class="border-gray-300 rounded-md text-sm p-1.5">
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
                        <th class="py-3 px-4 text-left">ID</th>
                        <th class="py-3 px-4 text-left">Layanan</th>
                        <th class="py-3 px-4 text-left">Nomor</th>
                        <th class="py-3 px-4 text-left">Status</th>
                        <th class="py-3 px-4 text-left">Dibuat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($antrians as $antrian)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">{{ $antrian->id }}</td>
                            <td class="py-3 px-4 font-semibold text-blue-600">{{ $antrian->layanan }}</td>
                            <td class="py-3 px-4 font-semibold text-blue-600">
                                {{ $antrian->layanan }}{{ $antrian->nomor }}
                            </td>
                            <td class="py-3 px-4">
                                @if ($antrian->status === 'dipanggil')
                                    <span
                                        class="px-3 py-1 rounded-full bg-yellow-100 text-yellow-700 text-sm font-medium">
                                        Dipanggil
                                    </span>
                                @elseif($antrian->status === 'menunggu')
                                    <span
                                        class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-sm font-medium">
                                        Menunggu
                                    </span>
                                @elseif($antrian->status === 'selesai')
                                    <span class="px-3 py-1 rounded-full bg-gray-200 text-gray-600 text-sm font-medium">
                                        Selesai
                                    </span>
                                @elseif($antrian->status === 'reset_antrian')
                                    <span class="px-3 py-1 rounded-full bg-red-100 text-red-600 text-sm font-medium">
                                        Reset Antrian
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">
                                {{ $antrian->created_at->format('d M Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-gray-500 italic">Belum ada data antrian</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 🔹 Pagination --}}
        <div class="mt-4">
            {{ $antrians->links('pagination::tailwind') }}
        </div>
    </div>


    {{-- 🔊 SCRIPT SUARA --}}
    @if (session('speak'))
        <script>
            const text = "{{ session('speak') }}";
            const nomor = "{{ session('nomor') }}";

            // Fungsi untuk memutar suara
            function speak(text) {
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID'; // Bahasa Indonesia
                utterance.rate = 0.9; // Kecepatan bicara
                utterance.pitch = 1.0; // Nada suara
                speechSynthesis.speak(utterance);
            }

            // Tunggu sedikit agar tampilan update dulu
            setTimeout(() => speak(text), 800);
        </script>
    @endif

    <script>
        // Fungsi untuk memperbarui antrian tanpa refresh
        function updateAntrian() {
            fetch("{{ route('antrian.refresh') }}")
                .then(response => response.json())
                .then(data => {
                    for (const kode in data) {
                        document.getElementById(`sekarang_${kode}`).innerText = data[kode].sekarang;
                        document.getElementById(`berikutnya_${kode}`).innerText = "Berikutnya: " + data[kode]
                            .berikutnya;
                    }
                })
                .catch(err => console.error('Gagal memuat data antrian:', err));
        }

        // Jalankan pertama kali dan setiap 3 detik
        updateAntrian();
        setInterval(updateAntrian, 3000);
    </script>

</body>

</html>
