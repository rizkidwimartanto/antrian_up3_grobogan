<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pilih Jenis Layanan</title>
    <link rel="icon" href="{{ asset('public/img/Logo_PLN.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- ✅ Tambahkan SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-blue-100 to-blue-300">

    {{-- 🔹 Notifikasi Sukses atau Error --}}
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
    @elseif (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('error') }}',
                showConfirmButton: false,
                timer: 3000
            }).then(() => location.reload());
        </script>
    @endif

    {{-- 🔹 Judul --}}
    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-2">Sistem Antrian Pelayanan</h1>
        <p class="text-lg text-gray-600">Silakan pilih layanan yang ingin Anda ambil nomornya</p>
    </div>

    {{-- 🔹 Tombol Pilihan Layanan --}}
    <div class="grid grid-cols-2 gap-8 place-items-center">
        @foreach ($layanans as $layanan)
            @php
                $warna = match ($layanan['kode']) {
                    'A' => 'bg-blue-600 hover:bg-blue-700',
                    'B' => 'bg-green-600 hover:bg-green-700',
                    'C' => 'bg-yellow-500 hover:bg-yellow-600',
                    'D' => 'bg-red-600 hover:bg-red-700',
                    default => 'bg-gray-500 hover:bg-gray-600',
                };
            @endphp

            {{-- Tombol Ambil Nomor --}}
            <form action="{{ route('antrian.ambilNomor', $layanan['kode']) }}" method="GET"
                onsubmit="return konfirmasiAmbil(event, '{{ $layanan['nama'] }}')"
                class="{{ count($layanans) === 1 ? 'col-span-2 flex justify-center' : '' }}">

                <button type="submit"
                    class="{{ $warna }} w-48 h-40 text-white font-semibold py-8 px-6 rounded-2xl shadow-lg text-center transition duration-300 transform hover:scale-105">
                    <span class="block text-2xl mb-2">Layanan {{ $layanan['kode'] }}</span>
                    <span class="text-sm opacity-80">{{ $layanan['nama'] }}</span>
                </button>
            </form>
        @endforeach
    </div>

    {{-- 🔹 SweetAlert Konfirmasi --}}
    <script>
        function konfirmasiAmbil(event, namaLayanan) {
            event.preventDefault(); // Stop submit langsung

            Swal.fire({
                title: 'Ambil Nomor Antrian?',
                text: `Anda yakin ingin ambil nomor untuk layanan "${namaLayanan}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Ambil Nomor',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#6b7280',
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.submit(); // submit form kalau dikonfirmasi
                }
            });

            return false; // cegah submit default
        }
    </script>
</body>

</html>
