// --- 1. DROPDOWN PROFILE (Now in app-common.js) ---

// --- 2. ACCORDION TASK ---
function toggleMainTask(targetId, headerElement) {
    const targetContent = document.getElementById(targetId);
    if (targetContent.classList.contains('active')) {
        targetContent.classList.remove('active');
        headerElement.querySelector('.arrow-icon').style.transform = 'rotate(0deg)';
    } else {
        targetContent.classList.add('active');
        headerElement.querySelector('.arrow-icon').style.transform = 'rotate(180deg)';
    }
}

function toggleSubTask(targetId) {
    const targetTable = document.getElementById(targetId);
    if (targetTable.classList.contains('active')) {
        targetTable.classList.remove('active');
    } else {
        targetTable.classList.add('active');
    }
}

// --- 3. MODALS ---
const detailModal = document.getElementById('detailModal');
const reminderModal = document.getElementById('reminderModal');
const confirmModal = document.getElementById('confirmModal');
let currentDetailNip = null;
let confirmTrackerId = null;

function openDetailModal(nip) {
    currentDetailNip = nip;

    if (detailModal) detailModal.style.display = 'flex';

    // Toggle Skeleton vs Content
    const skeleton = document.getElementById('detailSkeleton');
    const contentBody = document.getElementById('modalContentBody');
    const loadingSpinner = document.getElementById('detailLoading'); // Fallback

    // Show Skeleton, Hide Content
    if (skeleton) skeleton.style.display = 'block';
    if (contentBody) contentBody.style.display = 'none';
    if (loadingSpinner) loadingSpinner.style.display = 'none';

    fetch(`/data-pegawai/${nip}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const data = res.data;
                const initials = data.nama.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();

                // Populate Static Fields
                setText('detNama', data.nama);
                setText('detNIP', data.nip);
                setText('detJabatan', data.jabatan);
                setText('detTipeJabatan', data.tipe_jabatan);
                setText('detPangkat', data.pangkat);
                setText('detJenjang', data.jenjang);
                setText('detTmt', data.tmt_cpns);
                setText('detKredit', data.angka_kredit);
                setText('detHP', data.no_hp);
                setText('detEmail', data.email);
                setText('detAvatar', initials);
                setText('detNextKGB', data.next_kgb ? data.next_kgb : '-');

                // Populate Documents
                const docContainer = document.getElementById('docStatusContainer');
                if (docContainer) {
                    if (data.missing_documents && data.missing_documents.length > 0) {
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
                if (skeleton) skeleton.style.display = 'none';
                if (loadingSpinner) loadingSpinner.style.display = 'none';
                if (contentBody) contentBody.style.display = 'flex';

            } else {
                showCustomToast('Gagal memuat data.', 'error');
                closeDetailModal();
            }
        })
        .catch(err => {
            console.error(err);
            showCustomToast('Terjadi kesalahan koneksi.', 'error');
            closeDetailModal();
        });
}

function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.innerText = text;
}

function closeDetailModal() {
    if (detailModal) detailModal.style.display = 'none';
}

function openReminderModal() {
    if (reminderModal) reminderModal.style.display = 'flex';
}

function closeReminderModal() {
    if (reminderModal) reminderModal.style.display = 'none';
}

function openConfirmModal(trackerId, pegawaiName) {
    confirmTrackerId = trackerId;
    const nameEl = document.getElementById('confirmPegawaiName');
    if (nameEl) nameEl.textContent = pegawaiName;
    if (confirmModal) confirmModal.style.display = 'flex';
}

function closeConfirmModal() {
    if (confirmModal) confirmModal.style.display = 'none';
    confirmTrackerId = null;
}

let ukomTrackerId = null;

function openUkomModal(trackerId, pegawaiName) {
    ukomTrackerId = trackerId;
    const nameEl = document.getElementById('ukomPegawaiName');
    if (nameEl) nameEl.textContent = pegawaiName;
    const modal = document.getElementById('ukomModal');
    if (modal) modal.style.display = 'flex';
}

function closeUkomModal() {
    const modal = document.getElementById('ukomModal');
    if (modal) modal.style.display = 'none';
    ukomTrackerId = null;
}

function submitUkom() {
    if (!ukomTrackerId) return;

    var btn = document.getElementById('ukomYesBtn');
    btn.textContent = 'Memproses...';
    btn.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/tracker/' + ukomTrackerId + '/move-to-ukom', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeUkomModal();
                // Reload halaman agar list terupdate
                window.location.reload();
            } else {
                showCustomToast(data.message || 'Gagal memindahkan pegawai.', 'error');
                btn.textContent = 'Ya, Daftarkan UKOM';
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error('Gagal ukom:', err);
            btn.textContent = 'Ya, Daftarkan UKOM';
            btn.disabled = false;
        });
}

function toggleMessageMode() {
    const isCustom = document.getElementById('checkCustom').checked;
    const selectTemplate = document.getElementById('reminderTemplate');
    const txtMessage = document.getElementById('reminderMessage');

    if (isCustom) {
        selectTemplate.disabled = true;
        selectTemplate.value = "";
        txtMessage.disabled = false;
        txtMessage.focus();
    } else {
        selectTemplate.disabled = false;
        txtMessage.disabled = true;
        txtMessage.value = "";
    }
}

function sendReminder() {
    if (!currentDetailNip) return;

    const isCustom = document.getElementById('checkCustom').checked;
    const templateId = document.getElementById('reminderTemplate').value;
    const customMessage = document.getElementById('reminderMessage').value;

    let payload = {};

    if (isCustom) {
        if (!customMessage) {
            showCustomToast('Harap isi pesan custom!', 'error');
            return;
        }
        payload = { custom_message: customMessage };
    } else {
        if (!templateId) {
            showCustomToast('Harap pilih template!', 'error');
            return;
        }
        payload = { template_id: templateId };
    }

    const btnSend = document.getElementById('btnSendManual');
    const originalText = btnSend.innerText;
    btnSend.innerText = 'Mengirim...';
    btnSend.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/data-pegawai/${currentDetailNip}/send-manual`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(payload)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeReminderModal();
                showCustomToast('Email berhasil dikirim!', 'success');
            } else {
                showCustomToast(data.message || 'Gagal mengirim email.', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showCustomToast('Terjadi kesalahan saat mengirim email.', 'error');
        })
        .finally(() => {
            btnSend.innerText = originalText;
            btnSend.disabled = false;
        });
}

function submitConfirm() {
    if (!confirmTrackerId) return;

    var btn = document.getElementById('confirmYesBtn');
    btn.textContent = 'Memproses...';
    btn.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/tracker/' + confirmTrackerId + '/confirm', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                closeConfirmModal();
                // Reload halaman agar list terupdate
                window.location.reload();
            }
        })
        .catch(err => {
            console.error('Gagal konfirmasi:', err);
            btn.textContent = 'Ya, Sudah Diproses';
            btn.disabled = false;
        });
}

// --- GLOBAL CLICK LISTENER (Now in app-common.js) ---

// --- 4. SINKRONISASI LOGIC (Now in app-common.js) ---

// --- 5. NOTIFICATION LOGIC (Now in app-common.js) ---

// Auto-fetch notifications on load
// Side bar drag prevention moved to app-common.js

document.addEventListener('DOMContentLoaded', function () {
    fetchNotifications();
});

function openDashboardDetail(nip, kategori) {
    currentDetailNip = nip;

    const modal = document.getElementById('dashboardDetailModal');
    if (modal) modal.style.display = 'flex';

    const loadingSpinner = document.getElementById('dashModalLoading');
    const contentBody = document.getElementById('dashModalContentBody');
    const modalFooter = document.getElementById('dashModalFooter');
    const docsContainer = document.getElementById('dashModalDocsContainer');

    // Reset fields
    document.getElementById('dashModalNama').innerText = 'Memuat...';
    document.getElementById('dashModalKategori').innerText = kategori ? kategori.replace(/_/g, ' ') : '-';
    
    document.getElementById('dashModalNip').innerText = '-';
    document.getElementById('dashModalEmail').innerText = '-';
    
    // Hide dynamic wrappers initially
    document.getElementById('dashModalAKWrapper').style.display = 'none';
    document.getElementById('dashModalKGBWrapper').style.display = 'none';
    document.getElementById('dashModalPangkatWrapper').style.display = 'none';

    if (loadingSpinner) loadingSpinner.style.display = 'block';
    if (contentBody) contentBody.style.display = 'none';
    if (modalFooter) modalFooter.style.display = 'none';
    if (docsContainer) docsContainer.innerHTML = '';

    fetch(`/data-pegawai/${nip}?kategori=${kategori || ''}`)
        .then(response => response.json())
        .then(res => {
            if (res.success) {
                const data = res.data;
                const initials = data.nama.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase();
                
                // Populate Header
                document.getElementById('dashModalNama').innerText = data.nama;
                document.getElementById('dashModalAvatar').innerText = initials;

                // Build Progress Tracker
                const trackerContainer = document.getElementById('dashModalTrackerContainer');
                const trackerEl = document.getElementById('dashModalTracker');
                if (trackerContainer && trackerEl) {
                    if (data.tracker_status) {
                        trackerContainer.style.display = 'block';
                        
                        let s = data.tracker_status;
                        let step1 = (s === 'Usulan' || s === 'Proses' || s === 'Upload E-HRM' || s === 'Selesai');
                        let step2 = (s === 'Proses' || s === 'Upload E-HRM' || s === 'Selesai');
                        let step3 = (s === 'Upload E-HRM' || s === 'Selesai');
                        
                        // Active inner logic for the current running step
                        let act1 = (s === 'Usulan');
                        let act2 = (s === 'Proses');
                        let act3 = (s === 'Upload E-HRM' || s === 'Selesai');
                        
                        // Wait, looking at image: 3 steps
                        // Step 1: Langkah Pertama (Usulan Pengajuan)
                        // Step 2: Langkah Kedua (Proses TTE) -> The text in user image
                        // Step 3: Langkah Ketiga (Upload E-HRM) -> The text in user image
                        
                        let html = `
                            <div class="tracker-step ${step1 ? (act1 ? 'active active-inner' : 'done') : ''}">
                                <div class="circle"></div>
                                <div class="label">Usulan Pengajuan</div>
                                <div class="sub-label" style="padding: 0 10px; line-height: 1.4;">Segera cetak surat pengajuan</div>
                            </div>
                            <div class="tracker-line ${step2 ? 'done' : 'dashed'}"></div>
                            
                            <div class="tracker-step ${step2 ? (act2 ? 'active active-inner' : 'done') : ''}">
                                <div class="circle"></div>
                                <div class="label">Proses TTE</div>
                                <div class="sub-label" style="padding: 0 10px; line-height: 1.4;">Sedang dalam proses TTE</div>
                            </div>
                            <div class="tracker-line ${step3 ? 'done' : 'dashed'}"></div>
                            
                            <div class="tracker-step ${step3 ? (act3 ? 'active active-inner' : 'done') : ''}">
                                <div class="circle"></div>
                                <div class="label">Upload E-HRM</div>
                                <div class="sub-label" style="padding: 0 10px; line-height: 1.4;">Ingatkan pegawai untuk segera upload berkas</div>
                            </div>
                        `;
                        trackerEl.innerHTML = html;
                    } else {
                        trackerContainer.style.display = 'none';
                        trackerEl.innerHTML = '';
                    }
                }

                // Populate Info Grid
                document.getElementById('dashModalNip').innerText = data.nip ? 'NIP: ' + data.nip : '-';
                document.getElementById('dashModalEmail').innerText = data.email || '-';
                
                // Dynamic fields logic
                if (kategori && kategori.includes('Jafung')) {
                    document.getElementById('dashModalAKWrapper').style.display = 'block';
                    document.getElementById('dashModalAK').innerText = data.angka_kredit || '0';
                }
                if (kategori === 'KGB') {
                    document.getElementById('dashModalKGBWrapper').style.display = 'block';
                    document.getElementById('dashModalKGB').innerText = data.next_kgb || '-';
                }
                if (kategori && kategori.includes('KP_')) {
                    document.getElementById('dashModalPangkatWrapper').style.display = 'block';
                    document.getElementById('dashModalPangkat').innerText = data.pangkat || '-';
                }

                // Populate Documents (filtered by category)
                if (docsContainer) {
                    let missingCount = 0;
                    if (data.missing_documents && data.missing_documents.length > 0) {
                        const filteredDocs = kategori ? data.missing_documents.filter(doc => doc.kategori == kategori) : data.missing_documents;
                        missingCount = filteredDocs.length;
                        
                        if (missingCount > 0) {
                            let docsHtml = '';
                            filteredDocs.forEach((doc, index) => {
                                docsHtml += `
                                    <div style="display:flex; align-items:center; justify-content:space-between; background:#fff; padding:12px 15px; border-radius:8px; border:1px solid #e2e8f0;">
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <div style="width:28px; height:28px; background:#f1f5f9; color:#64748b; font-weight:700; font-size:12px; display:flex; align-items:center; justify-content:center; border-radius:6px;">
                                                ${index + 1}
                                            </div>
                                            <div style="font-weight:600; color:#1e293b; font-size:14px;">
                                                ${doc.nama_dokumen}
                                            </div>
                                        </div>
                                        <span style="background:#fee2e2; color:#dc2626; padding:4px 10px; border-radius:20px; font-size:12px; font-weight:700; display:flex; align-items:center; gap:4px;">
                                            <i class="ph-bold ph-warning"></i> Tidak Lengkap
                                        </span>
                                    </div>
                                `;
                            });
                            docsContainer.innerHTML = docsHtml;
                        }
                    }

                    if (missingCount === 0) {
                        docsContainer.innerHTML = `
                            <div style="text-align:center; padding:30px 20px; background:#f0fdf4; border:1px dashed #bbf7d0; border-radius:8px;">
                                <div style="background:#d1fae5; width:48px; height:48px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 10px;">
                                    <i class="ph-bold ph-check" style="font-size:24px; color:#059669;"></i>
                                </div>
                                <p style="margin:0; font-weight:700; color:#166534; font-size:14px;">Seluruh Syarat Dokumen Telah Lengkap</p>
                            </div>
                        `;
                    }
                    
                    if (modalFooter && missingCount > 0) modalFooter.style.display = 'flex';
                }

                // Show Content
                if (loadingSpinner) loadingSpinner.style.display = 'none';
                if (contentBody) contentBody.style.display = 'block';

            } else {
                showCustomToast('Gagal memuat data.', 'error');
                closeDashboardDetail();
            }
        })
        .catch(err => {
            console.error(err);
            showCustomToast('Terjadi kesalahan koneksi.', 'error');
            closeDashboardDetail();
        });
}

function closeDashboardDetail() {
    const modal = document.getElementById('dashboardDetailModal');
    if (modal) modal.style.display = 'none';
}

window.addEventListener('click', function(event) {
    const dashModal = document.getElementById('dashboardDetailModal');
    if (dashModal && event.target === dashModal) {
        closeDashboardDetail();
    }

    const suratModal = document.getElementById('suratModal');
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

    const modal = document.getElementById('suratModal');
    const loading = document.getElementById('suratLoading');
    const content = document.getElementById('suratContent');
    const footer = document.getElementById('suratFooter');

    if (modal) modal.style.display = 'flex';
    if (loading) loading.style.display = 'block';
    if (content) content.style.display = 'none';
    if (footer) footer.style.display = 'none';

    // Reset form fields
    document.getElementById('suratNomor').value = '';
    document.getElementById('suratTanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('suratTujuan').value = '';
    document.getElementById('suratNamaTTD').value = '';
    document.getElementById('suratNipTTD').value = '';
    document.getElementById('suratJabatanTTD').value = 'Kepala Sub Bagian Kepegawaian';
    document.getElementById('suratSelectAll').checked = false;

    // Fetch data
    fetch(`/surat-pengajuan/preview/${kategori}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Update modal title
                document.getElementById('suratModalTitle').textContent = 'Surat Pengajuan ' + data.kategori_label;
                document.getElementById('suratModalSub').textContent = data.total + ' pegawai tersedia';

                suratGroupsData = data.groups;
                renderSuratGroups(data.groups);

                if (loading) loading.style.display = 'none';
                if (content) content.style.display = 'block';
                if (footer) footer.style.display = 'flex';
                updateSuratCount();
            } else {
                if (loading) loading.innerHTML = '<p style="color:#dc2626;">Gagal memuat data: ' + (data.message || 'Unknown error') + '</p>';
            }
        })
        .catch(err => {
            console.error('Error fetching surat data:', err);
            if (loading) loading.innerHTML = '<p style="color:#dc2626;">Gagal memuat data pegawai.</p>';
        });
}

function closeSuratModal() {
    const modal = document.getElementById('suratModal');
    if (modal) modal.style.display = 'none';
    suratKategori = null;
    suratGroupsData = [];
}

function renderSuratGroups(groups) {
    const container = document.getElementById('suratGroupsContainer');
    if (!container) return;

    // Flatten all pegawai and separate by status
    let belumDicetak = []; // status Usulan
    let sudahDicetak = []; // status Proses (reprint)
    let globalIdx = 0;

    groups.forEach(group => {
        group.pegawai.forEach(p => {
            const item = { ...p, periode_label: group.periode_label, gIdx: globalIdx };
            if (p.status === 'Usulan' || p.status === 'Mendekati') {
                belumDicetak.push(item);
            } else if (p.status === 'Proses') {
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

    let html = '';

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

        belumDicetak.forEach(p => {
            html += renderPegawaiRow(p, 'Usulan Pengajuan', '#dc2626', '#fee2e2');
        });

        html += `</div></div>`;
    }

    // SECTION: Sudah Dicetak (Cetak Ulang)
    if (sudahDicetak.length > 0) {
        html += `
        <div style="margin-bottom:16px;">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
                <span style="background:#d97706; width:8px; height:8px; border-radius:50; display:inline-block;"></span>
                <span style="font-weight:700; font-size:13px; color:#d97706;">Sudah Dicetak — Cetak Ulang</span>
                <span style="background:#fef3c7; color:#d97706; padding:2px 10px; border-radius:10px; font-size:11px; font-weight:700;">${sudahDicetak.length} Orang</span>
            </div>
            <div style="border:1px solid #fde68a; border-radius:10px; overflow:hidden; opacity:0.85;">`;

        sudahDicetak.forEach(p => {
            html += renderPegawaiRow(p, 'Proses TTE', '#d97706', '#fef3c7');
        });

        html += `</div></div>`;
    }

    container.innerHTML = html;
}

function renderPegawaiRow(p, statusLabel, statusColor, statusBg) {
    return `
        <label style="display:flex; align-items:center; gap:12px; padding:10px 15px; border-bottom:1px solid #f1f5f9; cursor:pointer; transition:background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
            <input type="checkbox" class="surat-pegawai-cb" data-tracker-id="${p.tracker_id}" onchange="updateSuratCount()" style="width:16px; height:16px; accent-color:#1e3a8a; cursor:pointer; flex-shrink:0;">
            <div style="flex:1; min-width:0;">
                <div style="font-weight:600; font-size:13px; color:#1e293b;">${p.nama}</div>
                <div style="font-size:11px; color:#64748b; margin-top:2px;">NIP: ${p.nip} · ${p.pangkat_golongan} · ${p.jabatan}</div>
            </div>
            <span style="background:${statusBg}; color:${statusColor}; padding:3px 10px; border-radius:12px; font-size:10px; font-weight:700; white-space:nowrap;">${statusLabel}</span>
        </label>`;
}

function suratToggleAll() {
    const isChecked = document.getElementById('suratSelectAll').checked;
    document.querySelectorAll('.surat-pegawai-cb').forEach(cb => cb.checked = isChecked);
    updateSuratCount();
}

function suratToggleGroup(groupIdx) {
    // Legacy - no longer used but kept for safety
    updateSuratCount();
}

function updateSuratCount() {
    const checked = document.querySelectorAll('.surat-pegawai-cb:checked');
    const countEl = document.getElementById('suratSelectedCount');
    const btn = document.getElementById('btnGenerateSurat');
    if (countEl) countEl.textContent = checked.length + ' pegawai terpilih';
    if (btn) btn.disabled = checked.length === 0;

    // Sync "select all" checkbox
    const allCbs = document.querySelectorAll('.surat-pegawai-cb');
    const selectAll = document.getElementById('suratSelectAll');
    if (selectAll) selectAll.checked = allCbs.length > 0 && checked.length === allCbs.length;
}

function generateSurat() {
    const selectedIds = [];
    document.querySelectorAll('.surat-pegawai-cb:checked').forEach(cb => {
        selectedIds.push(parseInt(cb.dataset.trackerId));
    });

    if (selectedIds.length === 0) {
        showCustomToast('Pilih minimal 1 pegawai!', 'error');
        return;
    }

    const btn = document.getElementById('btnGenerateSurat');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i> Generating...';
    btn.disabled = true;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const payload = new FormData();
    payload.append('_token', csrfToken);
    payload.append('kategori', suratKategori);
    selectedIds.forEach(id => payload.append('tracker_ids[]', id));

    const fields = {
        'nomor_surat': document.getElementById('suratNomor').value,
        'tanggal_surat': document.getElementById('suratTanggal').value,
        'tujuan_surat': document.getElementById('suratTujuan').value,
        'nama_ttd': document.getElementById('suratNamaTTD').value,
        'nip_ttd': document.getElementById('suratNipTTD').value,
        'jabatan_ttd': document.getElementById('suratJabatanTTD').value,
    };

    Object.keys(fields).forEach(key => {
        payload.append(key, fields[key]);
    });

    fetch('/surat-pengajuan/generate', {
        method: 'POST',
        body: payload
    })
    .then(response => {
        if (response.ok) {
            let filename = `Surat_Pengajuan_${suratKategori}_${new Date().getTime()}.pdf`;
            const disposition = response.headers.get('Content-Disposition');
            if (disposition && disposition.indexOf('attachment') !== -1) {
                const filenameRegex = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                const matches = filenameRegex.exec(disposition);
                if (matches != null && matches[1]) { 
                  filename = matches[1].replace(/['"]/g, '');
                }
            }
            return response.blob().then(blob => ({ blob, filename }));
        }
        throw new Error('Terjadi kesalahan saat mencetak surat.');
    })
    .then(({ blob, filename }) => {
        // Trigger manual file download
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);

        closeSuratModal();
        showCustomToast('Surat berhasil dicetak!', 'success');
        
        // Auto refresh halaman setelah jeda sebentar agar download sempat dimulai
        setTimeout(() => {
            window.location.reload();
        }, 1500);
    })
    .catch(error => {
        console.error('Error generating surat:', error);
        showCustomToast(error.message, 'error');
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    });
}

