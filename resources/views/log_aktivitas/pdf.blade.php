<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Log Aktivitas Sistem</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0;
            color: #1e3a8a;
            font-size: 18px;
            text-transform: uppercase;
        }
        .header p {
            margin: 5px 0 0;
            color: #666;
            font-size: 10px;
        }
        .filter-info {
            margin-bottom: 20px;
            font-size: 11px;
            background-color: #f8fafc;
            padding: 10px;
            border: 1px solid #e2e8f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
        }
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge {
            display: inline-block;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-super { background-color: #fef3c7; color: #d97706; }
        .badge-admin { background-color: #dbeafe; color: #1d4ed8; }
        .badge-sistem { background-color: #e2e8f0; color: #475569; }
        .footer {
            margin-top: 30px;
            text-align: right;
            font-size: 10px;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>Laporan Log Aktivitas Sistem</h2>
        <p>DashboardAlert Pusdatin Kementerian PUPR</p>
    </div>

    <div class="filter-info">
        <strong>Parameter Filter:</strong><br>
        Waktu Unduh: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}<br>
        @if($request->filled('dari_tanggal') || $request->filled('sampai_tanggal'))
            Periode: {{ $request->dari_tanggal ?? 'Awal' }} s/d {{ $request->sampai_tanggal ?? 'Akhir' }}<br>
        @endif
        @if($request->filled('jenis_pengguna'))
            Jenis Pengguna: {{ $request->jenis_pengguna == 'super_admin' ? 'Admin Super' : ($request->jenis_pengguna == 'admin_pegawai' ? 'Admin Kepegawaian' : 'Sistem') }}<br>
        @endif
        @if($request->filled('aksi'))
            Pencarian Aksi: "{{ $request->aksi }}"<br>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Waktu</th>
                <th style="width: 15%;">Jenis Pengguna</th>
                <th style="width: 15%;">Aksi</th>
                <th style="width: 55%;">Deskripsi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td>{{ \Carbon\Carbon::parse($log->waktu)->format('d/m/Y H:i') }}</td>
                <td>
                    @if($log->admin)
                        @if($log->admin->role === 'super_admin')
                            <span class="badge badge-super">Admin Super</span>
                        @else
                            <span class="badge badge-admin">Admin Kepegawaian</span>
                        @endif
                    @else
                        <span class="badge badge-sistem">Sistem</span>
                    @endif
                </td>
                <td>
                    @if($log->tipe == 'ADMIN_ACTION')
                        @if(str_contains($log->deskripsi, 'Login'))
                            Login
                        @elseif(str_contains($log->deskripsi, 'Mengkonfirmasi'))
                            Konfirmasi Tugas
                        @elseif(str_contains($log->deskripsi, 'Verifikasi'))
                            Verifikasi Data
                        @else
                            Aksi Admin
                        @endif
                    @elseif($log->tipe == 'NOTIF_SENT')
                        Kirim Notifikasi
                    @elseif($log->tipe == 'API_SYNC')
                        Sinkronisasi API
                    @else
                        Log Sistem
                    @endif
                </td>
                <td>{{ $log->deskripsi }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px;">Tidak ada data log yang sesuai dengan filter pencarian.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini di-generate secara otomatis oleh sistem pada {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
    </div>

</body>
</html>
