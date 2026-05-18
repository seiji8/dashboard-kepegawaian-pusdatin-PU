// ==========================================
// SURAT PENGAJUAN LOGIC
// ==========================================
let suratKategori = null;
let suratGroupsData = [];

function openSuratModal(kategori) {
    suratKategori = kategori;
    suratGroupsData = [];

    const modal = document.getElementById("suratModal");
    const loading = document.getElementById("suratLoading");
    const content = document.getElementById("suratContent");
    const footer = document.getElementById("suratFooter");

    if (modal) modal.style.display = "flex";
    if (loading) loading.style.display = "block";
    if (content) content.style.display = "none";
    if (footer) footer.style.display = "none";

    // Reset form fields
    document.getElementById("suratNomor").value = "";
    document.getElementById("suratTanggal").value = new Date()
        .toISOString()
        .split("T")[0];
    document.getElementById("suratTujuan").value =
        "Kepala Biro Kepegawaian, Organisasi, dan Tata Laksana, Sekretariat Jenderal, Kementerian Pekerjaan Umum";
    document.getElementById("suratNamaTTD").value = "Komang Sri Hartini";
    document.getElementById("suratNipTTD").value = "196811201994032001";
    document.getElementById("suratJabatanTTD").value =
        "Kepala Pusat Data dan Teknologi Informasi";
    document.getElementById("suratSelectAll").checked = false;

    // Show/hide KP-only fields (Masa Kerja & KPPN)
    const kpFields = document.getElementById("suratKPFields");
    const isKP = ["KP", "KP_Jafung", "KP_Struktural", "KP_Reguler"].includes(
        kategori,
    );
    kpFields.style.display = isKP ? "block" : "none";
    if (isKP) {
        document.getElementById("suratMasaKerja").value = "";
        document.getElementById("suratKPPN").value = "V Jakarta";
    }

    // Show/hide KGB-only fields
    const kgbFields = document.getElementById("suratKGBFields");
    const isKGB = kategori === "KGB";
    kgbFields.style.display = isKGB ? "block" : "none";

    // Hide "Pilih Semua" for KGB
    const labelSelectAll = document.getElementById("labelSelectAllSurat");
    if (labelSelectAll) {
        labelSelectAll.style.display = isKGB ? "none" : "flex";
    }

    if (isKGB) {
        document.getElementById("kgbSkPejabat").value =
            "Kepala Biro Kepegawaian, Organisasi dan Tata Laksana";
        document.getElementById("kgbSkNomor").value = "318/KPTS/M/2026";
        document.getElementById("kgbSkTanggal").value = "20 Februari 2026";
        document.getElementById("kgbGajiLama").value = "";
        document.getElementById("kgbGajiBaru").value = "";
    }

    // Fetch data
    fetch(`/surat-pengajuan/preview/${kategori}`)
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                // Update modal title
                document.getElementById("suratModalTitle").textContent =
                    "Surat Pengajuan " + data.kategori_label;
                document.getElementById("suratModalSub").textContent =
                    data.total + " pegawai tersedia";

                suratGroupsData = data.groups;
                renderSuratGroups(data.groups);

                if (loading) loading.style.display = "none";
                if (content) content.style.display = "block";
                if (footer) footer.style.display = "flex";
                updateSuratCount();
            } else {
                if (loading)
                    loading.innerHTML =
                        '<p style="color:#dc2626;">Gagal memuat data: ' +
                        (data.message || "Unknown error") +
                        "</p>";
            }
        })
        .catch((err) => {
            console.error("Error fetching surat data:", err);
            if (loading)
                loading.innerHTML =
                    '<p style="color:#dc2626;">Gagal memuat data pegawai.</p>';
        });
}

function closeSuratModal() {
    const modal = document.getElementById("suratModal");
    if (modal) modal.style.display = "none";
    suratKategori = null;
    suratGroupsData = [];
}

function renderSuratGroups(groups) {
    const container = document.getElementById("suratGroupsContainer");
    if (!container) return;

    // Flatten all pegawai and separate by status
    let belumDicetak = []; // status Usulan
    let sudahDicetak = []; // status Proses (reprint)
    const isTubelFlow = suratKategori === "TUBEL";
    let globalIdx = 0;

    groups.forEach((group) => {
        group.pegawai.forEach((p) => {
            const item = {
                ...p,
                periode_label: group.periode_label,
                gIdx: globalIdx,
            };
            if (
                (isTubelFlow && p.status === "Sedang Tubel") ||
                (!isTubelFlow &&
                    (p.status === "Usulan" || p.status === "Mendekati"))
            ) {
                belumDicetak.push(item);
            } else if (
                (isTubelFlow &&
                    (p.status === "Proses Pengembalian" ||
                        p.status === "Proses Pengaktifan Kembali" ||
                        p.status === "Proses Pengaktifan" ||
                        p.status === "Proses")) ||
                (!isTubelFlow && p.status === "Proses")
            ) {
                sudahDicetak.push(item);
            }
        });
        globalIdx++;
    });

    if (belumDicetak.length === 0 && sudahDicetak.length === 0) {
        container.innerHTML = `
            <div style="text-align:center; padding:30px; background:#f8fafc; border:1px dashed #cbd5e1; border-radius:8px; color:#64748b;">
                <i class="ph-bold ph-info" style="font-size:24px; display:block; margin-bottom:8px;"></i>
                <p style="margin:0; font-weight:600;">Tidak ada pegawai yang tersedia untuk surat pengajuan.</p>
            </div>`;
        return;
    }

    let html = "";

    // SECTION: Belum Dicetak
    if (belumDicetak.length > 0) {
        html += `
        <div style="margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                <span style="background:#dc2626; width:8px; height:8px; border-radius:50; display:inline-block;"></span>
                <span style="font-weight:700; font-size:13px; color:#dc2626;">Belum Dicetak</span>
                <span style="background:#fee2e2; color:#dc2626; padding:2px 10px; border-radius:10px; font-size:11px; font-weight:700;">${belumDicetak.length} Orang</span>
            </div>
            <div style="border:1px solid #fecaca; border-radius:10px; overflow:hidden;">`;

        belumDicetak.forEach((p) => {
            html += renderPegawaiRow(
                p,
                isTubelFlow ? "Sedang Tubel" : "Usulan Pengajuan",
                "#dc2626",
                "#fee2e2",
            );
        });

        html += `</div></div>`;
    }

    // SECTION: Sudah Dicetak (Cetak Ulang)
    if (sudahDicetak.length > 0) {
        html += `
        <div style="margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                <span style="background:#d97706; width:8px; height:8px; border-radius:50; display:inline-block;"></span>
                <span style="font-weight:700; font-size:13px; color:#d97706;">Sudah Dicetak &mdash; Cetak Ulang</span>
                <span style="background:#fef3c7; color:#d97706; padding:2px 10px; border-radius:10px; font-size:11px; font-weight:700;">${sudahDicetak.length} Orang</span>
            </div>
            <div style="border:1px solid #fde68a; border-radius:10px; overflow:hidden; opacity:0.85;">`;

        sudahDicetak.forEach((p) => {
            html += renderPegawaiRow(
                p,
                isTubelFlow ? "Proses Pengaktifan Kembali" : "Proses TTE",
                "#d97706",
                "#fef3c7",
            );
        });

        html += `</div></div>`;
    }

    container.innerHTML = html;
}

function renderPegawaiRow(p, statusLabel, statusColor, statusBg) {
    return `
        <label style="display:flex; align-items:center; gap:12px; padding:10px 15px; border-bottom:1px solid #f1f5f9; cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
            <input type="checkbox" class="surat-pegawai-cb" data-tracker-id="${p.tracker_id}" onchange="handleSuratCbChange(this)" style="width:16px; height:16px; accent-color:#1e3a8a; cursor:pointer; flex-shrink:0;">
            <div style="flex:1; min-width:0;">
                <div style="font-weight:600; font-size:13px; color:#1e293b;">${p.nama}</div>
                <div style="font-size:11px; color:#64748b; margin-top:2px;">NIP: ${p.nip} &middot; ${p.pangkat_golongan} &middot; ${p.jabatan}</div>
            </div>
            <span style="background:${statusBg}; color:${statusColor}; padding:3px 10px; border-radius:12px; font-size:10px; font-weight:700; white-space:nowrap;">${statusLabel}</span>
        </label>`;
}

function suratToggleAll() {
    const isChecked = document.getElementById("suratSelectAll").checked;
    document
        .querySelectorAll(".surat-pegawai-cb")
        .forEach((cb) => (cb.checked = isChecked));
    updateSuratCount();
}

function handleSuratCbChange(cb) {
    // KGB: Enforce single selection
    if (suratKategori === "KGB" && cb.checked) {
        document.querySelectorAll(".surat-pegawai-cb").forEach((other) => {
            if (other !== cb) other.checked = false;
        });
    }
    updateSuratCount();
}

function suratToggleGroup(groupIdx) {
    // Legacy - no longer used but kept for safety
    updateSuratCount();
}

function updateSuratCount() {
    const checked = document.querySelectorAll(".surat-pegawai-cb:checked");
    const countEl = document.getElementById("suratSelectedCount");
    const btn = document.getElementById("btnGenerateSurat");
    if (countEl) countEl.textContent = checked.length + " pegawai terpilih";
    if (btn) btn.disabled = checked.length === 0;

    // Sync "select all" checkbox
    const allCbs = document.querySelectorAll(".surat-pegawai-cb");
    const selectAll = document.getElementById("suratSelectAll");
    if (selectAll)
        selectAll.checked =
            allCbs.length > 0 && checked.length === allCbs.length;
}

function generateSurat(isPreview = false) {
    const selectedIds = [];
    document.querySelectorAll(".surat-pegawai-cb:checked").forEach((cb) => {
        selectedIds.push(parseInt(cb.dataset.trackerId));
    });

    if (selectedIds.length === 0) {
        showCustomToast("Pilih minimal 1 pegawai!", "error");
        return;
    }

    const btn = document.getElementById(isPreview ? "btnPreviewSurat" : "btnGenerateSurat");
    const originalHTML = btn.innerHTML;
    btn.innerHTML = `<i class="ph-bold ph-spinner ph-spin"></i> ${isPreview ? 'Previewing...' : 'Generating...'}`;
    btn.disabled = true;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    const payload = new FormData();
    payload.append("_token", csrfToken);
    payload.append("kategori", suratKategori);
    selectedIds.forEach((id) => payload.append("tracker_ids[]", id));

    const fields = {
        nomor_surat: document.getElementById("suratNomor").value,
        tanggal_surat: document.getElementById("suratTanggal").value,
        tujuan_surat: document.getElementById("suratTujuan").value,
        nama_ttd: document.getElementById("suratNamaTTD").value,
        nip_ttd: document.getElementById("suratNipTTD").value,
        jabatan_ttd: document.getElementById("suratJabatanTTD").value,
    };

    // KP-only: kirim KPPN & Masa Kerja
    if (
        ["KP", "KP_Jafung", "KP_Struktural", "KP_Reguler"].includes(
            suratKategori,
        )
    ) {
        fields["kppn"] = document.getElementById("suratKPPN").value;
        const masaKerjaVal = document.getElementById("suratMasaKerja").value;
        if (masaKerjaVal) {
            selectedIds.forEach((id) =>
                payload.append(`masa_kerja[${id}]`, masaKerjaVal),
            );
        }
    }

    // KGB-only: kirim field manual KGB
    if (suratKategori === "KGB") {
        fields["sk_lama_pejabat"] =
            document.getElementById("kgbSkPejabat").value;
        fields["sk_lama_nomor"] = document.getElementById("kgbSkNomor").value;
        fields["sk_lama_tanggal"] =
            document.getElementById("kgbSkTanggal").value;
        fields["gaji_lama"] = document.getElementById("kgbGajiLama").value;
        fields["gaji_baru"] = document.getElementById("kgbGajiBaru").value;
    }

    Object.keys(fields).forEach((key) => {
        payload.append(key, fields[key]);
    });

    if (suratKategori === "KJ_Jafung") {
        const queryParams = new URLSearchParams({
            nomor_surat: fields["nomor_surat"],
            tanggal: fields["tanggal_surat"],
        }).toString();

        btn.innerHTML = originalHTML;
        btn.disabled = false;
        
        if (isPreview) {
            const firstId = selectedIds[0];
            const previewUrl = `/dashboard/cetak-surat-kj/${firstId}?${queryParams}&preview=1`;
            document.getElementById("suratPreviewFrame").src = previewUrl;
            document.getElementById("suratPreviewContainer").style.display = "block";
            
            setTimeout(() => {
                document.getElementById("suratPreviewContainer").scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);

            if (selectedIds.length > 1) {
                showCustomToast("Preview hanya menampilkan surat pegawai pertama.", "info");
            }
        } else {
            selectedIds.forEach((id, index) => {
                // Jeda 500ms per file untuk mencegah browser memblokir terlalu agresif
                setTimeout(() => {
                    window.open(
                        `/dashboard/cetak-surat-kj/${id}?${queryParams}`,
                        "_blank",
                    );
                }, index * 500);
            });

            closeSuratModal();
            showCustomToast(
                `Mencetak ${selectedIds.length} surat usulan KJ...`,
                "success",
            );
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        }
        return;
    }

    fetch("/surat-pengajuan/generate", {
        method: "POST",
        body: payload,
    })
        .then((response) => {
            if (response.ok) {
                let filename = `Surat_Pengajuan_${suratKategori}_${new Date().getTime()}.pdf`;
                const disposition = response.headers.get("Content-Disposition");
                if (disposition && disposition.indexOf("attachment") !== -1) {
                    const filenameRegex =
                        /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(disposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, "");
                    }
                }
                return response.blob().then((blob) => ({ blob, filename }));
            }
            throw new Error("Terjadi kesalahan saat mencetak surat.");
        })
        .then(({ blob, filename }) => {
            const url = window.URL.createObjectURL(blob);
            
            if (isPreview) {
                // Tampilkan di iframe
                document.getElementById("suratPreviewFrame").src = url;
                document.getElementById("suratPreviewContainer").style.display = "block";
                
                setTimeout(() => {
                    document.getElementById("suratPreviewContainer").scrollIntoView({ behavior: 'smooth', block: 'start' });
                }, 100);

                btn.innerHTML = originalHTML;
                btn.disabled = false;
            } else {
                // Trigger manual file download
                const a = document.createElement("a");
                a.href = url;
                a.download = filename;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);

                closeSuratModal();
                showCustomToast("Surat berhasil dicetak!", "success");

                // Auto refresh halaman setelah jeda sebentar agar download sempat dimulai
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        })
        .catch((error) => {
            console.error("Error generating surat:", error);
            showCustomToast(error.message, "error");
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        });
}

// ============================================================
// KONFIRMASI USULAN KP & KGB (tanpa cetak surat)
// ============================================================

let _konfirmasiKategori = null;
const _konfirmasiLabels = {
    KGB: "Kenaikan Gaji Berkala",
    KP: "Kenaikan Pangkat",
    KP_Jafung: "Kenaikan Pangkat Fungsional",
    KP_Struktural: "Kenaikan Pangkat Struktural",
    KP_Reguler: "Kenaikan Pangkat Reguler",
};

function openKonfirmasiModal(kategori) {
    _konfirmasiKategori = kategori;
    const modal = document.getElementById("modalKonfirmasiUsulan");
    const subtitle = document.getElementById("konfirmasiSubtitle");
    const listEl = document.getElementById("konfirmasiPegawaiList");
    const catatanEl = document.getElementById("konfirmasiCatatan");
    catatanEl.value = "";
    listEl.innerHTML =
        '<div style="text-align:center; padding:20px; color:#9ca3af;">Memuat data...</div>';
    subtitle.textContent = _konfirmasiLabels[kategori] || kategori;
    modal.style.display = "flex";
    fetch(`/surat-pengajuan/preview/${kategori}`)
        .then((res) => res.json())
        .then((data) => {
            if (!data.success || data.total === 0) {
                listEl.innerHTML =
                    '<div style="text-align:center; padding:20px; color:#9ca3af;">Tidak ada pegawai yang perlu dikonfirmasi.</div>';
                return;
            }
            let html = "";
            data.groups.forEach((group) => {
                html += `<div style="font-size:11px; font-weight:700; color:#6b7280; text-transform:uppercase; letter-spacing:0.05em; margin:8px 0 4px;">${group.periode_label}</div>`;
                group.pegawai.forEach((p) => {
                    html += `<label style="display:flex; align-items:center; gap:12px; padding:10px 12px; border:1.5px solid #e5e7eb; border-radius:8px; cursor:pointer; background:#fff; margin-bottom:4px;" onmouseover="this.style.borderColor='#16a34a'; this.style.background='#f0fdf4';" onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='#fff';">
                        <input type="checkbox" value="${p.tracker_id}" class="konfirmasi-checkbox" style="width:16px; height:16px; accent-color:#16a34a; cursor:pointer;" checked>
                        <div style="flex:1;">
                            <div style="font-weight:600; font-size:13px; color:#111827;">${p.nama}</div>
                            <div style="font-size:11px; color:#6b7280;">${p.nip} &bull; ${p.pangkat_golongan} &bull; TMT: ${p.tmt_target}</div>
                        </div>
                        <span style="font-size:11px; padding:3px 8px; border-radius:4px; background:#fef9c3; color:#854d0e; font-weight:600;">${p.status}</span>
                    </label>`;
                });
            });
            listEl.innerHTML = html;
        })
        .catch(() => {
            listEl.innerHTML =
                '<div style="text-align:center; padding:20px; color:#dc2626;">Gagal memuat data.</div>';
        });
}

function closeKonfirmasiModal() {
    document.getElementById("modalKonfirmasiUsulan").style.display = "none";
    _konfirmasiKategori = null;
}

function toggleSelectAllKonfirmasi(check) {
    document
        .querySelectorAll(".konfirmasi-checkbox")
        .forEach((cb) => (cb.checked = check));
}

function submitKonfirmasi() {
    const checkboxes = document.querySelectorAll(
        ".konfirmasi-checkbox:checked",
    );
    if (checkboxes.length === 0) {
        showCustomToast(
            "Pilih minimal 1 pegawai untuk dikonfirmasi.",
            "warning",
        );
        return;
    }
    const trackerIds = Array.from(checkboxes).map((cb) => cb.value);
    const catatan = document.getElementById("konfirmasiCatatan").value.trim();
    const btn = document.getElementById("btnSubmitKonfirmasi");
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner"></i> Memproses...';
    const formData = new FormData();
    formData.append("kategori", _konfirmasiKategori);
    formData.append(
        "_token",
        document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    );
    if (catatan) formData.append("catatan", catatan);
    trackerIds.forEach((id) => formData.append("tracker_ids[]", id));
    fetch("/surat-pengajuan/konfirmasi", { method: "POST", body: formData })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                closeKonfirmasiModal();
                showCustomToast(data.message, "success");
                setTimeout(() => window.location.reload(), 1200);
            } else {
                showCustomToast(data.message || "Terjadi kesalahan.", "error");
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }
        })
        .catch(() => {
            showCustomToast("Gagal terhubung ke server.", "error");
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        });
}

// ============================================================
// KONFIRMASI PER-BARIS KP & KGB
// ============================================================
function konfirmasiPerBaris(btnElement, trackerId, nama, kategori) {
    window._currentBtnElement = btnElement;
    // Buat popup kecil inline
    const existing = document.getElementById("popupKonfirmasiInline");
    if (existing) existing.remove();

    const labels = {
        KGB: "KGB",
        KP: "Kenaikan Pangkat",
        KP_Jafung: "KP Fungsional",
        KP_Struktural: "KP Struktural",
        KP_Reguler: "KP Reguler",
    };

    const popup = document.createElement("div");
    popup.id = "popupKonfirmasiInline";
    popup.classList.add("modal-overlay");
    popup.style.cssText =
        "position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;display:flex;align-items:center;justify-content:center;";
    popup.innerHTML = `
        <div class="confirm-modal-content">
            <div class="confirm-modal-icon">
                <i class="ph-fill ph-check-circle" style="font-size: 48px; color: #10b981;"></i>
            </div>
            <h3 class="confirm-modal-title">Konfirmasi Usulan</h3>
            <p class="confirm-modal-text">Apakah Anda yakin sudah memproses ${labels[kategori] || kategori} untuk:</p>
            <p class="confirm-modal-name" style="color: #0f172a;">${nama}</p>
            
            <div style="display:none;">
                <textarea id="catatanInline" placeholder="Catatan"></textarea>
            </div>
            
            <div class="confirm-modal-actions">
                <button class="confirm-btn-cancel" onclick="document.getElementById('popupKonfirmasiInline').remove()">Batal</button>
                <button class="confirm-btn-yes" id="btnKonfirmasiSubmit" onclick="submitKonfirmasiPerBaris(${trackerId},'${kategori}')">Ya, Sudah Diproses</button>
            </div>
        </div>`;
    document.body.appendChild(popup);
    popup.addEventListener("click", (e) => {
        if (e.target === popup) popup.remove();
    });
}

function submitKonfirmasiPerBaris(trackerId, kategori) {
    const btn = document.getElementById("btnKonfirmasiSubmit");
    const catatan = (
        document.getElementById("catatanInline").value || ""
    ).trim();
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner"></i> Proses...';

    const formData = new FormData();
    formData.append("kategori", kategori);
    formData.append("tracker_ids[]", trackerId);
    formData.append(
        "_token",
        document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    );
    if (catatan) formData.append("catatan", catatan);

    fetch("/surat-pengajuan/konfirmasi", { method: "POST", body: formData })
        .then((r) => r.json())
        .then((data) => {
            const popup = document.getElementById("popupKonfirmasiInline");
            if (popup) popup.remove();
            if (data.success) {
                showCustomToast(data.message, "success");
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showCustomToast(data.message || "Terjadi kesalahan.", "error");
            }
        })
        .catch(() => {
            showCustomToast("Gagal terhubung ke server.", "error");
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        });
}

// ============================================================
// TUBEL: KONFIRMASI SELESAI PENGEMBALIAN
// ============================================================

function cetakSuratPengaktifan(trackerId, nama) {
    if (!confirm(`Cetak surat pengaktifan kembali untuk pegawai ${nama}?`))
        return;

    // Untuk sementara, kita ganti statusnya menjadi 'Proses' menggunakan endpoint konfirmasi
    // Jika ada template surat khusus Tubel, bisa diarahkan ke generate surat
    const formData = new FormData();
    formData.append("kategori", "TUBEL");
    formData.append("tracker_ids[]", trackerId);
    formData.append(
        "_token",
        document
            .querySelector('meta[name="csrf-token"]')
            .getAttribute("content"),
    );
    formData.append("catatan", "Surat Pengaktifan Dicetak");

    fetch("/surat-pengajuan/konfirmasi", { method: "POST", body: formData })
        .then((r) => r.json())
        .then((data) => {
            if (data.success) {
                showCustomToast(
                    "Surat pengaktifan diproses (Status: Surat Dicetak)",
                    "success",
                );
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showCustomToast(data.message || "Terjadi kesalahan.", "error");
            }
        })
        .catch(() => {
            showCustomToast("Gagal terhubung ke server.", "error");
        });
}

function konfirmasiSelesaiTubel(trackerId, nama) {
    // Buat popup konfirmasi inline
    const existing = document.getElementById("popupKonfirmasiTubel");
    if (existing) existing.remove();

    const popup = document.createElement("div");
    popup.id = "popupKonfirmasiTubel";
    popup.style.cssText =
        "position:fixed;inset:0;background:rgba(0,0,0,0.45);z-index:99999;display:flex;align-items:center;justify-content:center;";
    popup.innerHTML = `
        <div style="background:#fff;border-radius:14px;width:420px;max-width:95vw;overflow:hidden;box-shadow:0 20px 60px rgba(0,0,0,0.25);">
            <div style="background:linear-gradient(135deg,#1e3a8a,#2563eb);padding:16px 20px;color:#fff;display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <div style="font-weight:700;font-size:15px;">✅ Konfirmasi Selesai Tubel</div>
                    <div style="font-size:11px;opacity:0.85;margin-top:2px;">Proses Pengaktifan Kembali</div>
                </div>
                <button onclick="document.getElementById('popupKonfirmasiTubel').remove()" style="background:rgba(255,255,255,0.2);border:none;border-radius:6px;color:#fff;width:28px;height:28px;cursor:pointer;font-size:15px;">×</button>
            </div>
            <div style="padding:18px 20px;">
                <p style="margin:0 0 12px;font-size:13px;color:#374151;">Konfirmasi bahwa proses pengaktifan kembali dari tugas belajar untuk <strong>${nama}</strong> sudah selesai sepenuhnya?</p>
            </div>
            <div style="padding:0 20px 18px;display:flex;justify-content:flex-end;gap:8px;">
                <button onclick="document.getElementById('popupKonfirmasiTubel').remove()" style="padding:8px 18px;background:#f1f5f9;color:#374151;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;">Batal</button>
                <button id="btnKonfirmasiTubel" onclick="submitSelesaiTubel(${trackerId})" style="padding:8px 18px;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600;font-size:12px;display:flex;align-items:center;gap:6px;">
                    <i class="ph-bold ph-check-circle"></i> Selesai
                </button>
            </div>
        </div>`;
    document.body.appendChild(popup);
    popup.addEventListener("click", (e) => {
        if (e.target === popup) popup.remove();
    });
}

function submitSelesaiTubel(trackerId) {
    const btn = document.getElementById("btnKonfirmasiTubel");
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner"></i> Proses...';

    // Menggunakan endpoint tracker confirm standar yang akan set dikonfirmasi_at = now()
    fetch(`/tracker/${trackerId}/confirm`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
            Accept: "application/json",
            "Content-Type": "application/json",
        },
        body: JSON.stringify({ catatan: "Proses Pengaktifan Kembali Tubel Selesai" }),
    })
        .then((r) => r.json())
        .then((data) => {
            const popup = document.getElementById("popupKonfirmasiTubel");
            if (popup) popup.remove();
            if (data.success) {
                showCustomToast(
                    "Proses pengaktifan kembali tubel berhasil diselesaikan!",
                    "success",
                );
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showCustomToast(data.message || "Terjadi kesalahan.", "error");
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }
        })
        .catch(() => {
            showCustomToast("Gagal terhubung ke server.", "error");
            btn.innerHTML = originalHTML;
            btn.disabled = false;
        });
}
