<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pilih Jenis Layanan</title>
    <link rel="icon" href="{{ asset('public/img/Logo_PLN.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="min-h-screen flex flex-col items-center justify-center bg-gradient-to-br from-blue-100 to-blue-300">

    <div class="text-center mb-10">
        <h1 class="text-4xl font-extrabold text-gray-800 mb-2">Sistem Antrian Pelayanan</h1>
        <p class="text-lg text-gray-600">Silakan pilih layanan yang ingin Anda ambil nomornya</p>
    </div>

    <div class="grid grid-cols-2 gap-8">
        @foreach ($layanans as $layanan)
            @php
                // Tentukan warna berdasarkan kode layanan
                $warna = match ($layanan['kode']) {
                    'A' => 'bg-blue-600 hover:bg-blue-700',
                    'B' => 'bg-green-600 hover:bg-green-700',
                    'C' => 'bg-yellow-500 hover:bg-yellow-600',
                    'D' => 'bg-red-600 hover:bg-red-700',
                    default => 'bg-gray-500 hover:bg-gray-600',
                };
            @endphp

            <a href="{{ route('antrian.ambilNomor', $layanan['kode']) }}"
                class="{{ $warna }} text-white font-semibold py-8 px-12 rounded-2xl shadow-lg text-center transition duration-300 transform hover:scale-105">
                <span class="block text-2xl mb-2">Layanan {{ $layanan['kode'] }}</span>
                <span class="text-sm opacity-80">{{ $layanan['nama'] }}</span>
            </a>
        @endforeach
    </div>
</body>

</html>
