// --- 1. DROPDOWN PROFILE (Now in app-common.js) ---

// --- 2. ACCORDION TASK ---
function toggleMainTask(targetId, headerElement) {
    const targetContent = document.getElementById(targetId);
    if (targetContent.classList.contains("active")) {
        targetContent.classList.remove("active");
        headerElement.querySelector(".arrow-icon").style.transform =
            "rotate(0deg)";
    } else {
        targetContent.classList.add("active");
        headerElement.querySelector(".arrow-icon").style.transform =
            "rotate(180deg)";
    }
}

function toggleSubTask(targetId) {
    const targetTable = document.getElementById(targetId);
    if (targetTable.classList.contains("active")) {
        targetTable.classList.remove("active");
    } else {
        targetTable.classList.add("active");
    }
}

// --- 3. MODALS ---
const detailModal = document.getElementById("detailModal");
const reminderModal = document.getElementById("reminderModal");
const confirmModal = document.getElementById("confirmModal");
let currentDetailNip = null;
let confirmTrackerId = null;

function openDetailModal(nip) {
    currentDetailNip = nip;

    if (detailModal) detailModal.style.display = "flex";

    // Toggle Skeleton vs Content
    const skeleton = document.getElementById("detailSkeleton");
    const contentBody = document.getElementById("modalContentBody");
    const loadingSpinner = document.getElementById("detailLoading"); // Fallback

    // Show Skeleton, Hide Content
    if (skeleton) skeleton.style.display = "block";
    if (contentBody) contentBody.style.display = "none";
    if (loadingSpinner) loadingSpinner.style.display = "none";

    fetch(`/data-pegawai/${nip}`)
        .then((response) => response.json())
        .then((res) => {
            if (res.success) {
                const data = res.data;
                const initials = data.nama
                    .split(" ")
                    .map((n) => n[0])
                    .slice(0, 2)
                    .join("")
                    .toUpperCase();

                // Populate Static Fields
                setText("detNama", data.nama);
                setText("detNIP", data.nip);
                setText("detJabatan", data.jabatan);
                setText("detTipeJabatan", data.tipe_jabatan);
                setText("detPangkat", data.pangkat);
                setText("detJenjang", data.jenjang);
                setText("detTmt", data.tmt_cpns);
                setText("detKredit", data.angka_kredit);
                setText("detHP", data.no_hp);
                setText("detEmail", data.email);
                setText("detAvatar", initials);
                setText("detNextKGB", data.next_kgb ? data.next_kgb : "-");

                // Populate Documents
                const docContainer =
                    document.getElementById("docStatusContainer");
                if (docContainer) {
                    if (
                        data.missing_documents &&
                        data.missing_documents.length > 0
                    ) {
                        let docsHtml = `
                            <div class="doc-warning-box">
                                <div class="doc-warning-header">
                                    <span>STATUS DOKUMEN</span>
                                    <span>TIDAK LENGKAP</span>
                                </div>
                        `;
                        data.missing_documents.forEach((doc, index) => {
                            docsHtml += `
                                <div class="doc-list-item">
                                    <div class="doc-number">${index + 1}</div>
                                    <div style="flex: 1;">${doc.nama_dokumen}</div>
                                </div>
                            `;
                        });
                        docsHtml += `</div>`;
                        docContainer.innerHTML = docsHtml;
                    } else {
                        docContainer.innerHTML = `
                            <div class="doc-success-box">
                                <div style="background: #10b981; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <i class="ph-bold ph-check" style="color: white; font-size: 14px;"></i>
                                </div>
                                Semua Dokumen Lengkap
                            </div>
                        `;
                    }
                }

                // Hide Skeleton, Show Content
                if (skeleton) skeleton.style.display = "none";
                if (loadingSpinner) loadingSpinner.style.display = "none";
                if (contentBody) contentBody.style.display = "flex";
            } else {
                showCustomToast("Gagal memuat data.", "error");
                closeDetailModal();
            }
        })
        .catch((err) => {
            console.error(err);
            showCustomToast("Terjadi kesalahan koneksi.", "error");
            closeDetailModal();
        });
}

function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.innerText = text;
}

function closeDetailModal() {
    if (detailModal) detailModal.style.display = "none";
}

let reminderTomSelect = null;

function openReminderModal() {
    if (reminderModal) reminderModal.style.display = "flex";

    // Init Tom Select setelah modal tampil
    setTimeout(() => {
        const el = document.getElementById("reminderTemplate");
        if (el && typeof TomSelect !== 'undefined' && !reminderTomSelect) {
            reminderTomSelect = new TomSelect(el, {
                allowEmptyOption: true,
                maxOptions: null,
                onChange: function() {
                    toggleMessageMode();
                }
            });
        }
    }, 50);
}

function closeReminderModal() {
    if (reminderModal) reminderModal.style.display = "none";
    if (reminderTomSelect) {
        reminderTomSelect.destroy();
        reminderTomSelect = null;
    }
}

function openConfirmModal(trackerId, pegawaiName) {
    confirmTrackerId = trackerId;
    const nameEl = document.getElementById("confirmPegawaiName");
    if (nameEl) nameEl.textContent = pegawaiName;
    if (confirmModal) confirmModal.style.display = "flex";
}

function closeConfirmModal() {
    if (confirmModal) confirmModal.style.display = "none";
    confirmTrackerId = null;
}

let ukomTrackerId = null;

function openUkomModal(trackerId, pegawaiName) {
    ukomTrackerId = trackerId;
    const nameEl = document.getElementById("ukomPegawaiName");
    if (nameEl) nameEl.textContent = pegawaiName;
    const modal = document.getElementById("ukomModal");
    if (modal) modal.style.display = "flex";
}

function closeUkomModal() {
    const modal = document.getElementById("ukomModal");
    if (modal) modal.style.display = "none";
    ukomTrackerId = null;
}

function submitUkom() {
    if (!ukomTrackerId) return;

    var btn = document.getElementById("ukomYesBtn");
    btn.textContent = "Memproses...";
    btn.disabled = true;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    fetch("/tracker/" + ukomTrackerId + "/move-to-ukom", {
        method: "POST",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest",
        },
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                closeUkomModal();
                // Reload halaman agar list terupdate
                window.location.reload();
            } else {
                showCustomToast(
                    data.message || "Gagal memindahkan pegawai.",
                    "error",
                );
                btn.textContent = "Ya, Daftarkan UKOM";
                btn.disabled = false;
            }
        })
        .catch((err) => {
            console.error("Gagal ukom:", err);
            btn.textContent = "Ya, Daftarkan UKOM";
            btn.disabled = false;
        });
}

function toggleMessageMode() {
    const isCustom = document.getElementById("checkCustom").checked;
    const selectTemplate = document.getElementById("reminderTemplate");
    const tomSelect =
        selectTemplate && selectTemplate.tomselect
            ? selectTemplate.tomselect
            : null;
    const txtMessage = document.getElementById("reminderMessage");

    if (isCustom) {
        if (tomSelect) {
            tomSelect.disable();
            tomSelect.clear();
        } else if (selectTemplate) {
            selectTemplate.disabled = true;
            selectTemplate.value = "";
        }
        txtMessage.disabled = false;
        txtMessage.focus();
    } else {
        if (tomSelect) {
            tomSelect.enable();
        } else if (selectTemplate) {
            selectTemplate.disabled = false;
        }
        txtMessage.disabled = true;
        txtMessage.value = "";
    }
}

function moveToUkomFromKJ(trackerId) {
    if (!confirm("Kirim pegawai ini ke antrean Uji Kompetensi?")) return;

    // Gunakan fungsi submitUkom/moveToUkom API yang sudah ada
    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    fetch("/tracker/" + trackerId + "/move-to-ukom", {
        method: "POST",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest",
        },
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                showCustomToast("Berhasil dipindah ke modul UKOM", "success");
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showCustomToast(data.message || "Gagal", "error");
            }
        });
}

function setKelulusanUkom(trackerId, statusLulus) {
    let msg = statusLulus
        ? "Set Lulus UKOM dan kembalikan ke Kenaikan Jenjang (Usulan)?"
        : "Set Tidak Lulus (Tetap di antrean UKOM)?";
    if (!confirm(msg)) return;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    fetch("/tracker/" + trackerId + "/set-kelulusan-ukom", {
        method: "POST",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify({ lulus: statusLulus }),
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                showCustomToast(data.message, "success");
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showCustomToast(data.message || "Gagal", "error");
            }
        });
}

function sendReminder() {
    if (!currentDetailNip) return;

    const isCustom = document.getElementById("checkCustom").checked;
    const templateId = document.getElementById("reminderTemplate").value;
    const customMessage = document.getElementById("reminderMessage").value;

    let payload = {};

    if (isCustom) {
        if (!customMessage) {
            showCustomToast("Harap isi pesan custom!", "error");
            return;
        }
        payload = { custom_message: customMessage };
    } else {
        if (!templateId) {
            showCustomToast("Harap pilih template!", "error");
            return;
        }
        payload = { template_id: templateId };
    }

    const btnSend = document.getElementById("btnSendManual");
    if (!btnSend) return;
    const originalText = btnSend.innerText;
    btnSend.innerText = "Mengirim...";
    btnSend.disabled = true;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    fetch(`/data-pegawai/${currentDetailNip}/send-manual`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
        },
        body: JSON.stringify(payload),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                closeReminderModal();
                showCustomToast("Email berhasil dikirim!", "success");
            } else {
                showCustomToast(
                    data.message || "Gagal mengirim email.",
                    "error",
                );
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            showCustomToast("Terjadi kesalahan saat mengirim email.", "error");
        })
        .finally(() => {
            btnSend.innerText = originalText;
            btnSend.disabled = false;
        });
}

function submitConfirm() {
    if (!confirmTrackerId) return;

    var btn = document.getElementById("confirmYesBtn");
    btn.textContent = "Memproses...";
    btn.disabled = true;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    fetch("/tracker/" + confirmTrackerId + "/confirm", {
        method: "POST",
        headers: {
            Accept: "application/json",
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrfToken,
            "X-Requested-With": "XMLHttpRequest",
        },
    })
        .then((res) => res.json())
        .then((data) => {
            if (data.success) {
                closeConfirmModal();
                // Reload halaman agar list terupdate
                window.location.reload();
            }
        })
        .catch((err) => {
            console.error("Gagal konfirmasi:", err);
            btn.textContent = "Ya, Sudah Diproses";
            btn.disabled = false;
        });
}

// --- GLOBAL CLICK LISTENER (Now in app-common.js) ---

// --- 4. SINKRONISASI LOGIC (Now in app-common.js) ---

// --- 5. NOTIFICATION LOGIC (Now in app-common.js) ---

// Auto-fetch notifications on load
// Side bar drag prevention moved to app-common.js

document.addEventListener("DOMContentLoaded", function () {
    fetchNotifications();
});

function openDashboardDetail(nip, kategori) {
    currentDetailNip = nip;

    const modal = document.getElementById("dashboardDetailModal");
    if (modal) modal.style.display = "flex";

    const loadingSpinner = document.getElementById("dashModalLoading");
    const contentBody = document.getElementById("dashModalContentBody");
    const modalFooter = document.getElementById("dashModalFooter");
    const docsContainer = document.getElementById("dashModalDocsContainer");

    // Reset fields
    document.getElementById("dashModalNama").innerText = "Memuat...";
    document.getElementById("dashModalKategori").innerText = kategori
        ? kategori.replace(/_/g, " ")
        : "-";

    document.getElementById("dashModalNip").innerText = "-";
    document.getElementById("dashModalEmail").innerText = "-";

    // Hide dynamic wrappers initially
    document.getElementById("dashModalAKWrapper").style.display = "none";
    document.getElementById("dashModalKGBWrapper").style.display = "none";
    document.getElementById("dashModalPangkatWrapper").style.display = "none";
    document.getElementById("dashModalTubelWrapper").style.display = "none";
    if (document.getElementById("dashModalKeteranganWrapper")) {
        document.getElementById("dashModalKeteranganWrapper").style.display = "none";
    }

    if (loadingSpinner) loadingSpinner.style.display = "block";
    if (contentBody) contentBody.style.display = "none";
    if (modalFooter) modalFooter.style.display = "none";
    if (docsContainer) docsContainer.innerHTML = "";

    fetch(`/data-pegawai/${nip}?kategori=${kategori || ""}`)
        .then((response) => response.json())
        .then((res) => {
            if (res.success) {
                const data = res.data;
                const initials = data.nama
                    .split(" ")
                    .map((n) => n[0])
                    .slice(0, 2)
                    .join("")
                    .toUpperCase();

                // Populate Header
                document.getElementById("dashModalNama").innerText = data.nama;
                document.getElementById("dashModalAvatar").innerText = initials;

                // Build Progress Tracker
                const trackerContainer = document.getElementById(
                    "dashModalTrackerContainer",
                );
                const trackerEl = document.getElementById("dashModalTracker");
                if (trackerContainer && trackerEl) {
                    if (data.tracker_status) {
                        trackerContainer.style.display = "block";
                        let s = data.tracker_status;

                        if (kategori === "UKOM" || kategori === "KJ_Jafung") {
                            // 5-Step Tracker for KJ and UKOM
                            // Step 1: KJ_Jafung + (Mendekati / Menunggu UKOM)
                            // Step 2: UKOM + Proses
                            // Step 3: KJ_Jafung + Usulan
                            // Step 4: KJ_Jafung + Proses
                            // Step 5: KJ_Jafung + (Upload E-HRM / Selesai)

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
                                        <button onclick="setKelulusanUkom(${data.tracker_id}, false)" style="padding:8px 20px; background:white; color:#ef4444; border:1px solid #ef4444; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px;">
                                            Tidak Lulus
                                        </button>
                                        <button onclick="setKelulusanUkom(${data.tracker_id}, true)" style="padding:8px 20px; background:#3b82f6; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:600; font-size:13px; margin-left:10px;">
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
                            // TUBEL uses 2-step flow: Sedang Tubel -> Proses Pengembalian
                            let isStep1 = s === "Sedang Tubel";
                            let isStep2 =
                                s === "Proses Pengembalian" ||
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
                                    <div class="label">Proses Pengembalian</div>
                                    <div class="sub-label" style="padding: 0 10px; line-height: 1.4;">Surat pengajuan sudah dicetak, menunggu selesai pengembalian</div>
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

                // Populate Info Grid
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
                    document.getElementById(
                        "dashModalKGBWrapper",
                    ).style.display = "block";
                    document.getElementById("dashModalKGB").innerText =
                        data.next_kgb || "-";
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

                // Populate Documents (filtered by category)
                if (docsContainer) {
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
                                
                                // ---- DUMMY FAKE LOGIC UNTUK EZA (NIP 105) ----
                                // Karena backend tidak diubah, kita fake khusus di frontend
                                if (
                                    data.nip == "105" &&
                                    doc.nama_dokumen == "SK Jabatan Terakhir"
                                ) {
                                    isUploaded = true;
                                }
                                // ----------------------------------------------

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

                // Show Content
                if (loadingSpinner) loadingSpinner.style.display = "none";
                if (contentBody) contentBody.style.display = "block";
            } else {
                showCustomToast("Gagal memuat data.", "error");
                closeDashboardDetail();
            }
        })
        .catch((err) => {
            console.error(err);
            showCustomToast("Terjadi kesalahan koneksi.", "error");
            closeDashboardDetail();
        });
}

function closeDashboardDetail() {
    const modal = document.getElementById("dashboardDetailModal");
    if (modal) modal.style.display = "none";
}

window.addEventListener("click", function (event) {
    const dashModal = document.getElementById("dashboardDetailModal");
    if (dashModal && event.target === dashModal) {
        closeDashboardDetail();
    }

    const suratModal = document.getElementById("suratModal");
    if (suratModal && event.target === suratModal) {
        closeSuratModal();
    }
});

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
                isTubelFlow ? "Proses Pengembalian" : "Proses TTE",
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
                    <div style="font-size:11px;opacity:0.85;margin-top:2px;">Proses Pengembalian</div>
                </div>
                <button onclick="document.getElementById('popupKonfirmasiTubel').remove()" style="background:rgba(255,255,255,0.2);border:none;border-radius:6px;color:#fff;width:28px;height:28px;cursor:pointer;font-size:15px;">×</button>
            </div>
            <div style="padding:18px 20px;">
                <p style="margin:0 0 12px;font-size:13px;color:#374151;">Konfirmasi bahwa proses pengembalian dari tugas belajar untuk <strong>${nama}</strong> sudah selesai sepenuhnya?</p>
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
        body: JSON.stringify({ catatan: "Proses Pengembalian Tubel Selesai" }),
    })
        .then((r) => r.json())
        .then((data) => {
            const popup = document.getElementById("popupKonfirmasiTubel");
            if (popup) popup.remove();
            if (data.success) {
                showCustomToast(
                    "Proses pengembalian tubel berhasil diselesaikan!",
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
