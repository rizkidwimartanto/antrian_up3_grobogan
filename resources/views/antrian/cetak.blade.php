<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cetak Tiket Antrian</title>
    <link rel="icon" href="{{ asset('public/img/Logo_PLN.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* ===============================
           CETAK PAS UNTUK PRINTER 58mm
        =============================== */
        @media print {
            @page {
                size: 58mm auto; /* lebar 58mm, tinggi otomatis */
                margin: 0; /* tanpa margin */
            }

            html,
            body {
                width: 58mm;
                margin: 0;
                padding: 0;
                background: white;
            }

            .ticket {
                width: 100%;
                margin: 0;
                padding: 6mm 0; /* ruang vertikal kecil agar teks tidak menempel */
                border: 1px solid black;
                border-radius: 0;
                box-shadow: none;
                text-align: center;
                page-break-after: avoid;
            }
        }

        /* Tampilan di layar (sebelum cetak) */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: white;
        }

        .ticket {
            border: 2px solid #000;
            border-radius: 0.75rem;
            padding: 2rem;
            width: 72mm;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
        }
    </style>

    <script>
        window.onload = function() {
            window.print();
            window.onafterprint = function() {
                window.location.href = "{{ route('antrian.index') }}";
            };
        };
    </script>
</head>

<body>
    <div class="ticket">
        <h2 class="text-xl font-bold mb-2">NOMOR ANTRIAN</h2>
        <h1 class="text-6xl font-bold mb-2">
            {{ $antrian->layanan }}{{ $antrian->nomor }}
        </h1>
        <p class="text-sm font-bold mb-1">Layanan: <strong>{{ $namaLayanan }}</strong></p>
        <p class="text-sm font-bold">Silakan tunggu panggilan Anda</p>
        <p class="text-xs mt-3 font-bold">
            {{ now()->format('d-m-Y H:i') }}
        </p>
    </div>
</body>

</html>
