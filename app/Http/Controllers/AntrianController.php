<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAntrianRequest;
use App\Http\Requests\UpdateAntrianRequest;

class AntrianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // Halaman utama untuk ambil nomor
    public function index()
    {
        $layanans = [
            ['kode' => 'A', 'nama' => 'Pelayanan Pelanggan'],
            ['kode' => 'B', 'nama' => 'Pengaduan'],
        ];

        return view('antrian.index', compact('layanans'));
    }

    // Ambil nomor per layanan
    public function ambilNomor($kode)
    {
        $today = now()->toDateString();

        $lastAntrian = \App\Models\Antrian::where('layanan', $kode)
            ->whereDate('tanggal', $today)
            ->orderBy('nomor', 'desc')
            ->first();

        $nomorBaru = $lastAntrian ? $lastAntrian->nomor + 1 : 1;

        $antrian = \App\Models\Antrian::create([
            'layanan' => $kode,
            'nomor' => $nomorBaru,
            'tanggal' => $today,
            'status' => 'menunggu',
        ]);

        // 🔹 Pemetaan kode ke nama layanan
        $namaLayanan = [
            'A' => 'Pelayanan Pelanggan',
            'B' => 'Pengaduan',
        ][$kode] ?? 'Layanan Tidak Dikenal';

        // Kirim nama layanan ke view juga
        return view('antrian.cetak', compact('antrian', 'namaLayanan'));
    }


    public function loket(Request $request)
    {
        $perPage = $request->input('entries', 5);

        $antrians = \App\Models\Antrian::orderBy('id', 'desc')
            ->paginate($perPage)
            ->appends(['entries' => $perPage]);

        // Daftar layanan dengan kode dan nama
        $layanans = [
            ['kode' => 'A', 'nama' => 'Pelayanan Pelanggan'],
            ['kode' => 'B', 'nama' => 'Pengaduan'],
        ];

        $antrianSekarang = [];
        $antrianBerikutnya = [];

        foreach ($layanans as $layanan) {
            $kode = $layanan['kode'];

            $antrianSekarang[$kode] = \App\Models\Antrian::where('layanan', $kode)
                ->where('status', 'dipanggil')
                ->latest('updated_at')
                ->first();

            $antrianBerikutnya[$kode] = \App\Models\Antrian::where('layanan', $kode)
                ->where('status', 'menunggu')
                ->orderBy('nomor', 'asc')
                ->first();
        }

        return view('antrian.loket', compact('layanans', 'antrianSekarang', 'antrianBerikutnya', 'antrians', 'perPage'));
    }

    public function panggil(Request $request)
    {
        $layanan = $request->layanan;
        $aksi = $request->aksi; // bisa "berikutnya" atau "ulang"

        if ($aksi === 'berikutnya') {
            $antrian = Antrian::where('layanan', $layanan)
                ->where('status', 'menunggu')
                ->orderBy('nomor', 'asc')
                ->first();

            if ($antrian) {
                // Tandai semua yang sedang dipanggil jadi selesai dulu
                Antrian::where('layanan', $layanan)
                    ->where('status', 'dipanggil')
                    ->update(['status' => 'selesai']);

                $antrian->update(['status' => 'dipanggil']);

                return redirect()->back()
                    ->with('success', "Memanggil antrian {$antrian->layanan}{$antrian->nomor}")
                    ->with('speak', "Nomor antrian {$antrian->layanan} {$antrian->nomor}, silakan ke loket {$layanan}");
            } else {
                // Tidak ada antrian menunggu → biarkan antrian terakhir tetap tampil
                $lastCalled = Antrian::where('layanan', $layanan)
                    ->whereIn('status', ['dipanggil', 'selesai'])
                    ->latest('updated_at')
                    ->first();

                if ($lastCalled) {
                    return redirect()->back()
                        ->with('success', "Tidak ada antrian baru untuk layanan {$layanan}. Menampilkan antrian terakhir {$lastCalled->layanan}{$lastCalled->nomor}.");
                }

                return redirect()->back()
                    ->with('success', "Tidak ada antrian menunggu untuk layanan {$layanan}");
            }
        }

        if ($aksi === 'ulang') {
            $antrian = Antrian::where('layanan', $layanan)
                ->where('status', 'dipanggil')
                ->latest()
                ->first();

            if ($antrian) {
                return redirect()->back()
                    ->with('success', "Memanggil ulang antrian {$antrian->layanan}{$antrian->nomor}")
                    ->with('speak', "Nomor antrian {$antrian->layanan} {$antrian->nomor}, silakan ke loket {$layanan}.");
            } else {
                return redirect()->back()
                    ->with('success', "Belum ada antrian yang sedang dipanggil untuk layanan {$layanan}");
            }
        }
    }

    public function reset()
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // 🔹 Tandai semua antrian hari ini sebagai "reset_antrian"
        \App\Models\Antrian::whereDate('tanggal', $today)
            ->update([
                'status' => 'reset_antrian',
                'tanggal' => $yesterday
            ]);

        return redirect()->back()->with('success', '🔁 Semua antrian hari ini telah direset dan diberi status reset_antrian. Nomor baru akan dimulai dari 1.');
    }

    public function refresh()
    {
        $layanans = ['A', 'B'];
        $data = [];

        foreach ($layanans as $kode) {
            $antrianSekarang = Antrian::where('layanan', $kode)
                ->where('status', 'dipanggil')
                ->latest()
                ->first();

            $antrianBerikutnya = Antrian::where('layanan', $kode)
                ->where('status', 'menunggu')
                ->orderBy('nomor', 'asc')
                ->first();

            $data[$kode] = [
                'sekarang' => $antrianSekarang ? $antrianSekarang->layanan . $antrianSekarang->nomor : '—',
                'berikutnya' => $antrianBerikutnya ? $antrianBerikutnya->layanan . $antrianBerikutnya->nomor : '(Tidak ada)',
            ];
        }

        return response()->json($data);
    }

    public function getdata_loket(Request $request)
    {
        $perPage = $request->input('entries', 5);
        $antrians = \App\Models\Antrian::orderBy('id', 'desc')->paginate($perPage);
        return response()->json($antrians);
    }

    public function viewAntrian()
    {
        // 🔹 Daftar layanan dengan kode dan nama
        $layanans = [
            ['kode' => 'A', 'nama' => 'Pelayanan Pelanggan'],
            ['kode' => 'B', 'nama' => 'Pengaduan'],
        ];

        $antrianSekarang = [];

        foreach ($layanans as $layanan) {
            $kode = $layanan['kode'];

            // 🔹 Ambil antrian yang sedang dipanggil
            $antrian = \App\Models\Antrian::where('layanan', $kode)
                ->where('status', 'dipanggil')
                ->latest('updated_at')
                ->first();

            // 🔸 Jika belum ada yang sedang dipanggil, ambil yang terakhir (selesai/dipanggil)
            if (!$antrian) {
                $antrian = \App\Models\Antrian::where('layanan', $kode)
                    ->whereIn('status', ['selesai', 'dipanggil'])
                    ->latest('updated_at')
                    ->first();
            }

            $antrianSekarang[$kode] = $antrian;
        }

        return view('antrian.view_antrian', compact('layanans', 'antrianSekarang'));
    }

    public function getData()
    {
        $layanans = ['A', 'B'];
        $antrianSekarang = [];

        foreach ($layanans as $kode) {
            $antrian = \App\Models\Antrian::where('layanan', $kode)
                ->where('status', 'dipanggil')
                ->latest('updated_at')
                ->first();

            if (!$antrian) {
                $antrian = \App\Models\Antrian::where('layanan', $kode)
                    ->whereIn('status', ['selesai', 'dipanggil'])
                    ->latest('updated_at')
                    ->first();
            }

            $antrianSekarang[$kode] = $antrian;
        }

        return response()->json($antrianSekarang);
    }

    public function uploadVideo(Request $request)
    {
        $request->validate([
            'video' => 'required|mimes:mp4|max:102400', 
        ]);

        $file = $request->file('video');
        $filename = 'display_' . time() . '.' . $file->getClientOriginalExtension();
        $path = public_path('video');

        // Hapus video lama jika ada
        if (file_exists($path . '/current_video.txt')) {
            $oldVideo = trim(file_get_contents($path . '/current_video.txt'));
            if ($oldVideo && file_exists($path . '/' . $oldVideo)) {
                @unlink($path . '/' . $oldVideo);
            }
        }

        // Simpan video baru
        $file->move($path, $filename);

        // Simpan nama video aktif ke file
        file_put_contents($path . '/current_video.txt', $filename);

        return back()->with('success', 'Video berhasil diperbarui! Tampilan display akan otomatis menyesuaikan.');
    }

    public function getVideo()
    {
        $path = public_path('video/current_video.txt');
        $default = asset('public/video/icon.mp4');

        if (file_exists($path)) {
            $filename = trim(file_get_contents($path));
            $filePath = public_path('video/' . $filename);
            if (file_exists($filePath)) {
                return response()->json(['video' => asset('public/video/' . $filename)]);
            }
        }

        return response()->json(['video' => $default]);
    }
}
