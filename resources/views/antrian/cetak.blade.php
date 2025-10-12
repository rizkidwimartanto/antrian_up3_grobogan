<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Tiket Antrian</title>
    <link rel="icon" href="{{ asset('public/img/Logo_PLN.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.onload = function() {
            // Jalankan print
            window.print();

            // Setelah print selesai, kembali ke halaman index
            window.onafterprint = function() {
                window.location.href = "{{ route('antrian.index') }}";
            };
        };
    </script>
</head>

<body class="flex flex-col items-center justify-center h-screen bg-white">

    <div class="border-2 border-gray-800 rounded-xl p-8 w-72 text-center shadow-lg">
        <h2 class="text-xl font-bold mb-2">NOMOR ANTRIAN</h2>
        <h1 class="text-6xl font-extrabold text-blue-600 mb-2">
            {{ $antrian->layanan }}{{ $antrian->nomor }}
        </h1>
        <p class="text-sm mb-1">Layanan: <strong>{{ $antrian->layanan }}</strong></p>
        <p class="text-sm">Silakan tunggu panggilan Anda</p>
        <p class="text-xs text-gray-500 mt-3">
            {{ now()->format('d-m-Y H:i') }}
        </p>
    </div>

</body>

</html>
