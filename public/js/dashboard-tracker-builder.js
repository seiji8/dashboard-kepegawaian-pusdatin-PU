/**
 * Dashboard Tracker Builder
 * Builds tracker step HTML and populates info grid + documents
 * for the dashboard detail modal.
 */

// --- BUILD TRACKER SECTION ---
function renderDashboardTracker(kategori, data, trackerEl, trackerContainer, modalFooter) {
    if (!trackerContainer || !trackerEl) return;

    if (data.tracker_status) {
        trackerContainer.style.display = "block";
        let s = data.tracker_status;

        if (kategori === "UKOM" || kategori === "KJ_Jafung") {
            // 5-Step Tracker for KJ and UKOM
            let isStep1 =
                kategori === "KJ_Jafung" &&
                (s === "Mendekati" || s === "Menunggu UKOM");
            let isStep2 = kategori === "UKOM";
            let isStep3 =
                kategori === "KJ_Jafung" && s === "Usulan";
            let isStep4 =
                kategori === "KJ_Jafung" && s === "Proses";
            let isStep5 =
                kategori === "KJ_Jafung" &&
                (s === "Upload E-HRM" || s === "Selesai");

            let pass1 =
                isStep2 || isStep3 || isStep4 || isStep5;
            let pass2 = isStep3 || isStep4 || isStep5;
            let pass3 = isStep4 || isStep5;
            let pass4 = isStep5;

            let html = `
                <div class="tracker-step ${pass1 ? "done" : isStep1 ? "active active-inner" : ""}">
                    <div class="circle"></div>
                    <div class="label" style="font-size:12px;">Pengajuan UKOM</div>
                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4; font-size:10px;">Sedang proses pengajuan UKOM</div>
                </div>
                <div class="tracker-line ${pass1 ? "done" : "dashed"}"></div>
                
                <div class="tracker-step ${pass2 ? "done" : isStep2 ? "active active-inner" : ""}">
                    <div class="circle"></div>
                    <div class="label" style="font-size:12px;">Proses UKOM</div>
                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4; font-size:10px;">Sedang Melakukan UKOM</div>
                </div>
                <div class="tracker-line ${pass2 ? "done" : "dashed"}"></div>
                
                <div class="tracker-step ${pass3 ? "done" : isStep3 ? "active active-inner" : ""}">
                    <div class="circle"></div>
                    <div class="label" style="font-size:12px;">Usulan Pengajuan</div>
                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4; font-size:10px;">Segera cetak surat Pengajuan</div>
                </div>
                <div class="tracker-line ${pass3 ? "done" : "dashed"}"></div>
                
                <div class="tracker-step ${pass4 ? "done" : isStep4 ? "active active-inner" : ""}">
                    <div class="circle"></div>
                    <div class="label" style="font-size:12px;">Proses TTE</div>
                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4; font-size:10px;">Sedang dalam proses TTE</div>
                </div>
                <div class="tracker-line ${pass4 ? "done" : "dashed"}"></div>
                
                <div class="tracker-step ${isStep5 ? "active active-inner" : ""}">
                    <div class="circle"></div>
                    <div class="label" style="font-size:12px;">Upload E-HRM</div>
                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4; font-size:10px;">Ingatkan pegawai untuk segera upload berkas</div>
                </div>
            `;
            trackerEl.innerHTML = html;

            // Inject Custom Buttons
            if (modalFooter) {
                let actionButtons = "";
                if (isStep2) {
                    actionButtons = `
                        <button onclick="setKelulusanUkom(${data.tracker_id}, false, document.getElementById('dashModalNama').innerText)" style="padding:8px 20px; background:white; color:#ef4444; border:1px solid #ef4444; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px;">
                            Tidak Lulus
                        </button>
                        <button onclick="setKelulusanUkom(${data.tracker_id}, true, document.getElementById('dashModalNama').innerText)" style="padding:8px 20px; background:#3b82f6; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px; margin-left:10px;">
                            <i class="ph-bold ph-check"></i> Set Lulus UKOM
                        </button>
                    `;
                }

                // Append to modalFooter (keeping reminder button)
                modalFooter.innerHTML = `
                    <div style="display:flex; justify-content:flex-end; width:100%; align-items:center; gap:10px;">
                        ${actionButtons}
                        <button class="btn-reminder-yellow" onclick="openReminderModal()" style="width:auto; padding:8px 20px; margin:0; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-bell-ringing"></i> Kirim Pengingat
                        </button>
                    </div>
                `;
            }
        } else if (kategori === "TUBEL") {
            // TUBEL uses 2-step flow: Sedang Tubel -> Proses Pengaktifan Kembali
            let isStep1 = s === "Sedang Tubel";
            let isStep2 =
                s === "Proses Pengembalian" ||
                s === "Proses Pengaktifan Kembali" ||
                s === "Proses Pengaktifan" ||
                s === "Proses";
            let pass1 = isStep2;

            let html = `
                <div class="tracker-step ${pass1 ? "done" : isStep1 ? "active active-inner" : ""}">
                    <div class="circle"></div>
                    <div class="label">Sedang Tubel</div>
                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4;">Pegawai masih menjalani tugas belajar</div>
                </div>
                <div class="tracker-line ${pass1 ? "done" : "dashed"}"></div>

                <div class="tracker-step ${isStep2 ? "active active-inner" : ""}">
                    <div class="circle"></div>
                    <div class="label">Proses Pengaktifan Kembali</div>
                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4;">Surat pengajuan sudah dicetak, menunggu selesai pengaktifan kembali</div>
                </div>
            `;
            trackerEl.innerHTML = html;

            // Populate TUBEL extra info
            const tubelWrapper = document.getElementById("dashModalTubelWrapper");
            if (tubelWrapper && data.tubel_data) {
                tubelWrapper.style.display = "block";
                document.getElementById("dashModalTubelMulai").innerHTML =
                    `<i class="ph-fill ph-calendar-check" style="color:#16a34a; font-size:14px;"></i> ${data.tubel_data.tanggal_mulai}`;
                document.getElementById("dashModalTubelSelesai").innerHTML =
                    `<i class="ph-fill ph-calendar-x" style="color:#dc2626; font-size:14px;"></i> ${data.tubel_data.tanggal_selesai}`;
                document.getElementById("dashModalTubelPendidikan").innerHTML =
                    `<i class="ph-fill ph-book-open" style="color:#7c3aed; font-size:14px;"></i> ${data.tubel_data.pendidikan}`;
            }

            if (modalFooter) {
                modalFooter.innerHTML = `
                    <div style="display:flex; justify-content:flex-end; width:100%; align-items:center; gap:10px;">
                        <button class="btn-reminder-yellow" onclick="openReminderModal()" style="width:auto; padding:8px 20px; margin:0; display:flex; align-items:center; gap:8px;">
                            <i class="ph-bold ph-bell-ringing"></i> Kirim Pengingat
                        </button>
                    </div>
                `;
                modalFooter.style.display = "flex"; // Force show for TUBEL
            }
        } else {
            // DEFAULT 3-STEP TRACKER
            let step1 =
                s === "Usulan" ||
                s === "Proses" ||
                s === "Upload E-HRM" ||
                s === "Selesai";
            let step2 =
                s === "Proses" ||
                s === "Upload E-HRM" ||
                s === "Selesai";
            let step3 = s === "Upload E-HRM" || s === "Selesai";

            let act1 = s === "Usulan";
            let act2 = s === "Proses";
            let act3 = s === "Upload E-HRM" || s === "Selesai";

            let html = `
                <div class="tracker-step ${step1 ? (act1 ? "active active-inner" : "done") : ""}">
                    <div class="circle"></div>
                    <div class="label">Usulan Pengajuan</div>
                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4;">Segera cetak surat pengajuan</div>
                </div>
                <div class="tracker-line ${step2 ? "done" : "dashed"}"></div>
                
                <div class="tracker-step ${step2 ? (act2 ? "active active-inner" : "done") : ""}">
                    <div class="circle"></div>
                    <div class="label">Proses TTE</div>
                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4;">Sedang dalam proses TTE</div>
                </div>
                <div class="tracker-line ${step3 ? "done" : "dashed"}"></div>
                
                <div class="tracker-step ${step3 ? (act3 ? "active active-inner" : "done") : ""}">
                    <div class="circle"></div>
                    <div class="label">Upload E-HRM</div>
                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4;">Ingatkan pegawai untuk segera upload berkas</div>
                </div>
            `;
            trackerEl.innerHTML = html;

            if (modalFooter) {
                modalFooter.innerHTML = `
                    <button class="btn-reminder-yellow" onclick="openReminderModal()" style="width:auto; padding:8px 20px; margin:0; display:flex; align-items:center; gap:8px; margin-left:auto;">
                        <i class="ph-bold ph-bell-ringing"></i> Kirim Pengingat
                    </button>
                `;
            }
        }
    } else {
        trackerContainer.style.display = "none";
        trackerEl.innerHTML = "";
    }
}

// --- POPULATE INFO GRID ---
function populateDashInfoGrid(data, kategori) {
    document.getElementById("dashModalNip").innerText = data.nip
        ? "NIP: " + data.nip
        : "-";
    document.getElementById("dashModalEmail").innerText =
        data.email || "-";

    // Dynamic fields logic
    if (kategori && kategori.includes("Jafung")) {
        document.getElementById(
            "dashModalAKWrapper",
        ).style.display = "block";
        document.getElementById("dashModalAK").innerText =
            data.angka_kredit || "0";
    }
    if (kategori === "KGB") {
        // Hide the small KGB wrapper from the generic header
        document.getElementById("dashModalKGBWrapper").style.display = "none";
        
        // Show the dedicated KGB Info Box
        const kgbInfo = document.getElementById("dashModalKgbInfoWrapper");
        if (kgbInfo) {
            kgbInfo.style.display = "block";
            document.getElementById("dashModalKgbTmtLama").innerHTML = `<i class="ph-fill ph-calendar-check" style="color:#10b981; font-size:14px;"></i> ${data.tmt_kgb_terakhir || '-'}`;
            document.getElementById("dashModalKgbTmtBaru").innerHTML = `<i class="ph-fill ph-calendar-plus" style="color:#3b82f6; font-size:14px;"></i> ${data.next_kgb || '-'}`;
            document.getElementById("dashModalKgbGolongan").innerHTML = `<i class="ph-fill ph-medal" style="color:#f59e0b; font-size:14px;"></i> ${data.pangkat || '-'}`;
        }
    }
    if (kategori && kategori.includes("KP_")) {
        document.getElementById(
            "dashModalPangkatWrapper",
        ).style.display = "block";
        document.getElementById("dashModalPangkat").innerText =
            data.pangkat || "-";
    }

    if (data.tracker_keterangan) {
        if (document.getElementById("dashModalKeteranganWrapper")) {
            document.getElementById("dashModalKeteranganWrapper").style.display = "block";
            document.getElementById("dashModalKeterangan").innerText = data.tracker_keterangan;
        }
    }
}

// --- POPULATE DOCUMENTS ---
function populateDashDocuments(data, kategori, docsContainer, modalFooter) {
    if (!docsContainer) return;

    if (
        data.all_documents &&
        data.all_documents.length > 0
    ) {
        const filteredDocs = kategori
            ? data.all_documents.filter(
                  (doc) => doc.kategori == kategori,
              )
            : data.all_documents;

        if (filteredDocs.length > 0) {
            let docsHtml = "";
            let isAllUploaded = true;

            filteredDocs.forEach((doc, index) => {
                let isUploaded = doc.is_uploaded || false;


                if (!isUploaded) isAllUploaded = false;

                const badgeClass = isUploaded
                    ? "ph-check-circle"
                    : "ph-warning";
                const badgeText = isUploaded
                    ? "Lengkap"
                    : "Tidak Lengkap";
                const badgeWrapStyle = isUploaded
                    ? "background:#d1fae5; color:#059669; border:1px solid #6ee7b7;"
                    : "background:#fee2e2; color:#dc2626; border:1px solid #fca5a5;";
                const itemBorderStyle = isUploaded
                    ? "border:1px solid #6ee7b7; background:#f0fdf4;"
                    : "border:1px solid #e2e8f0; background:#fff;";

                docsHtml += `
                    <div style="display:flex; align-items:center; justify-content:space-between; ${itemBorderStyle} padding:12px 15px; border-radius:8px; margin-bottom:8px;">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:28px; height:28px; background:#f1f5f9; color:#64748b; font-weight:700; font-size:12px; display:flex; align-items:center; justify-content:center; border-radius:6px;">
                                ${index + 1}
                            </div>
                            <div style="font-weight:600; color:#1e293b; font-size:14px;">
                                ${doc.nama_dokumen}
                            </div>
                        </div>
                        <span style="${badgeWrapStyle} padding:4px 10px; border-radius:20px; font-size:12px; font-weight:700; display:flex; align-items:center; gap:4px;">
                            <i class="ph-bold ${badgeClass}"></i> ${badgeText}
                        </span>
                    </div>
                `;
            });
            docsContainer.innerHTML = docsHtml;

            // Tampilkan footer (tombol-tombol) selama masih ada dokumen yang blm upload
            if (modalFooter) {
                modalFooter.style.display = isAllUploaded
                    ? "none"
                    : "flex";
            }
        }
    } else {
        docsContainer.innerHTML = `
            <div style="text-align:center; padding:30px 20px; background:#f1f5f9; border:1px dashed #cbd5e1; border-radius:8px;">
                <p style="margin:0; font-weight:600; color:#64748b; font-size:14px;">Tidak ada syarat dokumen terdeteksi</p>
            </div>
        `;
        // Untuk TUBEL, footer tetap tampil karena sudah diset di tracker section
        if (modalFooter && kategori !== 'TUBEL') modalFooter.style.display = "none";
    }
}
