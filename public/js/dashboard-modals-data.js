/**
 * Dashboard Modals Data
 * Handles: Fetching data from server and populating modals
 * Depends on: dashboard-ui.js (shared variables), dashboard-tracker-builder.js (builder functions)
 */

// --- DETAIL MODAL (Data Pegawai Page) ---
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

// --- DASHBOARD DETAIL MODAL ---
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
    if (document.getElementById("dashModalKgbInfoWrapper")) document.getElementById("dashModalKgbInfoWrapper").style.display = "none";
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

                // Build Progress Tracker (from dashboard-tracker-builder.js)
                const trackerContainer = document.getElementById("dashModalTrackerContainer");
                const trackerEl = document.getElementById("dashModalTracker");
                renderDashboardTracker(kategori, data, trackerEl, trackerContainer, modalFooter);

                // Populate Info Grid (from dashboard-tracker-builder.js)
                populateDashInfoGrid(data, kategori);

                // Populate Documents (from dashboard-tracker-builder.js)
                populateDashDocuments(data, kategori, docsContainer, modalFooter);

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
