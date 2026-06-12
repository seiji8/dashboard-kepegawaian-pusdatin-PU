<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f0f4f8;padding:40px 20px;">
    <tr>
        <td align="center">

            <!-- ===== EMAIL CARD ===== -->
            <table width="620" cellpadding="0" cellspacing="0" border="0"
                   style="max-width:620px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 10px 30px rgba(20,43,111,0.12);">

                <!-- ===== HEADER: Gradient Brand Bar ===== -->
                <tr>
                    <td style="background:linear-gradient(135deg,#142B6F 0%,#1e3a8a 60%,#1d4ed8 100%);padding:32px 40px;">
                        <table cellpadding="0" cellspacing="0" border="0" width="100%">
                            <tr>
                                <td style="vertical-align:middle;width:60px;">
                                    <img src="{{ $message->embed(public_path('assets/Logo_PU.png')) }}"
                                         alt="Logo PU"
                                         width="52"
                                         style="display:block;height:auto;border:0;">
                                </td>
                                <td style="vertical-align:middle;padding-left:16px;">
                                    <p style="margin:0;font-size:22px;font-weight:800;color:#ffffff;letter-spacing:0.3px;line-height:1.2;">
                                        <span style="color:#FFC928;">Dashboard</span>Alert
                                    </p>
                                    <p style="margin:4px 0 0;font-size:12px;color:rgba(255,255,255,0.75);letter-spacing:0.5px;">
                                        PUSDATIN Kementerian PU &mdash; Notifikasi Kepegawaian
                                    </p>
                                </td>
                                <td style="vertical-align:middle;text-align:right;">
                                    <!-- Badge -->
                                    <span style="display:inline-block;background:rgba(255,201,40,0.15);border:1px solid rgba(255,201,40,0.4);color:#FFC928;font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;padding:5px 12px;border-radius:20px;">
                                        📋 Rekap Usulan
                                    </span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <!-- ===== Accent Divider ===== -->
                <tr>
                    <td style="padding:0;line-height:0;">
                        <div style="height:4px;background:linear-gradient(90deg,#FFC928 0%,#142B6F 50%,#FFC928 100%);"></div>
                    </td>
                </tr>

                <!-- ===== BODY ===== -->
                <tr>
                    <td style="padding:36px 40px 0 40px;">

                        <!-- Greeting -->
                        <p style="margin:0 0 4px;font-size:13px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:1px;">
                            Kepada Yth.
                        </p>
                        <h2 style="margin:0 0 24px;font-size:24px;font-weight:800;color:#0f172a;line-height:1.2;">
                            Halo, {{ $pegawai->nama }} 👋
                        </h2>

                        <!-- Intro text -->
                        <p style="margin:0 0 24px;font-size:14px;color:#475569;line-height:1.8;">
                            Berikut adalah informasi terkait notifikasi kepegawaian dari sistem <strong style="color:#142B6F;">Dashboard Alert PUSDATIN PU</strong>:
                        </p>

                        <!-- ===== Content Card ===== -->
                        @php
                            $lines     = explode("\n", str_replace("\r", "", $content));
                            $intro     = [];
                            $bullets   = [];
                            $outro     = [];
                            $inBullets = false;

                            $blocks = [];
                            $currentList = [];

                            foreach ($lines as $line) {
                                $trimmed = trim($line);
                                if ($trimmed === '') {
                                    if (!empty($currentList)) {
                                        $blocks[] = ['type' => 'list', 'items' => $currentList];
                                        $currentList = [];
                                    }
                                    continue;
                                }

                                // Deteksi bullet point tabel (•) untuk summary admin
                                if (mb_substr($trimmed, 0, 1) === '•') {
                                    $inBullets = true;
                                    $bullets[] = ltrim(ltrim($trimmed, '•'), ' ');
                                    continue;
                                }

                                // Deteksi bullet point reguler (- atau *)
                                if (mb_substr($trimmed, 0, 2) === '- ' || mb_substr($trimmed, 0, 2) === '* ') {
                                    // Tutup block paragraf sebelumnya jika ada yang menumpuk
                                    $currentList[] = ltrim(mb_substr($trimmed, 2));
                                    continue;
                                }

                                // Jika ada list yang sedang berjalan, flush ke blocks
                                if (!empty($currentList)) {
                                    $blocks[] = ['type' => 'list', 'items' => $currentList];
                                    $currentList = [];
                                }

                                if ($inBullets) {
                                    $outro[] = $trimmed;
                                } else {
                                    $intro[] = $trimmed;
                                    $blocks[] = ['type' => 'p', 'content' => $trimmed];
                                }
                            }

                            if (!empty($currentList)) {
                                $blocks[] = ['type' => 'list', 'items' => $currentList];
                            }
                        @endphp

                        <table cellpadding="0" cellspacing="0" border="0" width="100%"
                               style="background:#ffffff;border-radius:12px;border:1px solid #e2e8f0;margin-bottom:28px;overflow:hidden;">
                            <!-- Card Top Gradient Border -->
                            <tr>
                                <td style="height:4px;background:linear-gradient(90deg,#FFC928,#142B6F);padding:0;line-height:0;"></td>
                            </tr>
                            <tr>
                                <td style="padding:24px 28px;">

                                    @if(count($bullets) > 0)
                                        {{-- Legacy / Summary Alert Layout --}}
                                        @foreach($intro as $line)
                                        <p style="margin:0 0 20px;font-size:14px;color:#111827;line-height:1.7;font-weight:500;">
                                            {{ $line }}
                                        </p>
                                        @endforeach

                                        <table cellpadding="0" cellspacing="0" border="0" width="100%"
                                               style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:20px;">

                                            {{-- Table Header --}}
                                            <tr style="background:#142B6F;">
                                                <td style="padding:10px 16px;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.8px;width:60%;">
                                                    Jenis Usulan
                                                </td>
                                                <td style="padding:10px 16px;font-size:11px;font-weight:700;color:#ffffff;text-transform:uppercase;letter-spacing:0.8px;text-align:right;">
                                                    Jumlah
                                                </td>
                                            </tr>

                                            @foreach($bullets as $i => $bullet)
                                            @php
                                                $parts    = explode(':', $bullet, 2);
                                                $kategori = trim($parts[0]);
                                                $detail   = isset($parts[1]) ? trim($parts[1]) : '';
                                                preg_match('/(\d+)/', $detail, $numMatch);
                                                $angka  = $numMatch[1] ?? '';
                                                $satuan = trim(preg_replace('/\d+/', '', $detail));
                                                $isOdd  = ($i % 2 === 0);
                                            @endphp
                                            <tr style="background:{{ $isOdd ? '#f9fafb' : '#ffffff' }};border-top:1px solid #e5e7eb;">
                                                <td style="padding:12px 16px;font-size:14px;color:#111827;font-weight:600;">
                                                    {{ $kategori }}
                                                </td>
                                                <td style="padding:12px 16px;text-align:right;white-space:nowrap;vertical-align:middle;">
                                                    @if($angka)
                                                    <span style="display:inline-block;background:#142B6F;color:#ffffff;font-size:13px;font-weight:800;padding:3px 14px;border-radius:20px;">
                                                        {{ $angka }}
                                                    </span>
                                                    <span style="font-size:12px;color:#374151;margin-left:6px;font-weight:500;">{{ $satuan }}</span>
                                                    @else
                                                    <span style="font-size:13px;color:#111827;font-weight:600;">{{ $detail }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach

                                        </table>

                                        @foreach($outro as $line)
                                        <p style="margin:0 0 8px;font-size:13px;color:#111827;line-height:1.7;font-weight:500;">
                                            {{ $line }}
                                        </p>
                                        @endforeach
                                    @else
                                        {{-- Custom / Bullet List Layout (Untuk Diklat dsb) --}}
                                        @foreach($blocks as $block)
                                            @if($block['type'] === 'p')
                                                <p style="margin:0 0 16px;font-size:14px;color:#111827;line-height:1.7;font-weight:500;">
                                                    {{ $block['content'] }}
                                                </p>
                                            @elseif($block['type'] === 'list')
                                                <ul style="margin:0 0 16px;padding-left:20px;font-size:14px;color:#111827;line-height:1.7;font-weight:500;list-style-type:disc;">
                                                    @foreach($block['items'] as $item)
                                                        <li style="margin-bottom:6px;">{{ $item }}</li>
                                                    @endforeach
                                                </ul>
                                            @endif
                                        @endforeach
                                    @endif

                                    @if(preg_match('/(upload|unggah|dokumen|berkas|sertifikat)/i', $content))
                                    <!-- E-HRM Upload Button -->
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:20px;">
                                        <tr>
                                            <td align="center">
                                                <a href="https://ehrm.pu.go.id/data-saya" target="_blank" style="display:inline-block;background-color:#1e3a8a;color:#ffffff;text-decoration:none;padding:12px 24px;border-radius:6px;font-weight:bold;font-size:14px;">
                                                    Upload Dokumen di E-HRM
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                    @endif

                                    @if(isset($pdfData))
                                    <!-- PDF Attachment Notice -->
                                    <table cellpadding="0" cellspacing="0" border="0" width="100%"
                                           style="margin-top:20px;padding-top:16px;border-top:1px dashed #cbd5e1;">
                                        <tr>
                                            <td style="vertical-align:middle;width:36px;">
                                                <span style="font-size:22px;">📎</span>
                                            </td>
                                            <td style="vertical-align:middle;padding-left:10px;">
                                                <p style="margin:0;font-size:13px;color:#142B6F;font-weight:600;line-height:1.5;">
                                                    Rekap detail tersedia di lampiran PDF
                                                </p>
                                                <p style="margin:2px 0 0;font-size:12px;color:#64748b;">
                                                    Silakan unduh file <em>Rekap_Usulan_Kepegawaian.pdf</em> pada attachment email ini.
                                                </p>
                                            </td>
                                        </tr>
                                    </table>
                                    @endif

                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                <!-- ===== FOOTER SIGNATURE ===== -->
                <tr>
                    <td style="padding:0 40px 36px 40px;">

                        <!-- Signature -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-bottom:24px;">
                            <tr>
                                <td style="vertical-align:top;padding-right:20px;">
                                    <p style="margin:0 0 2px;font-size:13px;color:#64748b;">Salam hangat,</p>
                                    <p style="margin:0 0 2px;font-size:15px;font-weight:700;color:#142B6F;">Admin Kepegawaian</p>
                                    <p style="margin:0;font-size:12px;color:#94a3b8;">Pusat Data dan Teknologi Informasi &mdash; Kementerian PU</p>
                                </td>
                            </tr>
                        </table>

                        <!-- Zona Integritas Banner -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%"
                               style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;margin-bottom:12px;">
                            <tr>
                                <td style="padding:14px 18px;">
                                    <p style="margin:0;font-size:11px;line-height:1.7;color:#1d4ed8;font-style:italic;text-align:justify;">
                                        &#8220;Dalam menunjang pembangunan zona integritas menuju wilayah birokrasi bersih dan melayani,
                                        <strong>PUSDATIN</strong> berkomitmen meningkatkan kualitas pelayanan publik yang bebas dari
                                        korupsi dan memberikan pelayanan prima serta <strong>tidak dipungut biaya apapun</strong>.&#8221;
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <!-- Hubungi Tim Kepegawaian Banner -->
                        <table cellpadding="0" cellspacing="0" border="0" width="100%"
                               style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;margin-bottom:28px;">
                            <tr>
                                <td style="padding:14px 18px;">
                                    <p style="margin:0;font-size:11px;line-height:1.7;color:#1d4ed8;text-align:center;">
                                        Apabila terdapat pertanyaan atau membutuhkan informasi lebih lanjut terkait notifikasi ini, silakan menghubungi <strong>Tim Kepegawaian PUSDATIN</strong>.
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

                <!-- ===== FOOTER COPYRIGHT ===== -->
                <tr>
                    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:18px 40px;text-align:center;">
                        <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.6;">
                            &copy; {{ date('Y') }} Pusat Data dan Teknologi Informasi &mdash; Kementerian PU.<br>
                            Email ini dikirim otomatis oleh sistem. Mohon tidak membalas email ini.
                        </p>
                    </td>
                </tr>

            </table>
            <!-- End Email Card -->

        </td>
    </tr>
</table>

</body>
</html>
