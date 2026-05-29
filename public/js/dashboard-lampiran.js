// =====================================================
// LAMPIRAN MODAL JAVASCRIPT
// =====================================================
let currentTrackerId = null;
let currentLampiransData = [];

function closeLampiranModal() {
    document.getElementById('lampiranModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('lampiranJudul').value = '';
    document.getElementById('lampiranFile').value = '';
    document.getElementById('dropZoneText').textContent = 'Klik untuk pilih file';
    document.getElementById('uploadProgress').style.display = 'none';
}

function handleFileSelected(input) {
    const file = input.files[0];
    if (file) {
        document.getElementById('dropZoneText').textContent = '✅ ' + file.name + ' (' + (file.size / 1024).toFixed(0) + ' KB)';
    }
}

function fetchLampiran(trackerId) {
    fetch('/lampiran/' + trackerId, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        currentLampiransData = data.lampiran || [];
        renderLampiranList(currentLampiransData);
        closeLampiranDetailPreview(); // Sembunyikan detail lama saat pegawai berganti
    })
    .catch(() => {
        document.getElementById('lampiranList').innerHTML = '<p style="color:#dc2626; font-size:13px;">Gagal memuat lampiran.</p>';
    });
}

function renderLampiranList(items) {
    const list = document.getElementById('lampiranList');
    const count = document.getElementById('lampiranCount');
    const btnClearAll = document.getElementById('btnClearAllLampiran');

    if (count) count.textContent = items.length;
    
    if (btnClearAll) {
        btnClearAll.style.display = items.length > 0 ? 'flex' : 'none';
    }

    if (items.length === 0) {
        list.innerHTML = '<p style="font-size:13px; color:#94a3b8; text-align:center; padding:20px 0;">Belum ada lampiran diupload.</p>';
        return;
    }

    list.innerHTML = items.map((item, idx) => {
        const isImage = item.mime_type && item.mime_type.includes('image');
        const icon = isImage ? 'ph-image' : 'ph-file-pdf';
        const iconColor = isImage ? '#0ea5e9' : '#ef4444';
        const sizeKb = item.ukuran_bytes ? (item.ukuran_bytes / 1024).toFixed(0) + ' KB' : '';
        return `<div class="lampiran-item-card">
            <div style="width:38px; height:38px; border-radius:10px; background:#f1f5f9; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                <i class="ph-bold ${icon}" style="font-size:18px; color:${iconColor};"></i>
            </div>
            <div style="flex:1; min-width:0;">
                <p style="margin:0; font-size:13px; font-weight:700; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; letter-spacing:-0.1px;">${item.judul_lampiran}</p>
                <p style="margin:2.5px 0 0; font-size:11px; color:#64748b; font-weight:500;">Urutan ${item.urutan} &nbsp;•&nbsp; ${sizeKb}</p>
            </div>
            <button type="button" onclick="viewLampiranDetail(${item.id})" style="color:#2563eb; background:#eff6ff; border:none; width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'" title="Preview">
                <i class="ph-bold ph-eye" style="font-size:15px;"></i>
            </button>
            <button type="button" onclick="deleteLampiran(${item.id})" style="color:#ef4444; background:#fee2e2; border:none; width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#fecaca'" onmouseout="this.style.background='#fee2e2'" title="Hapus">
                <i class="ph-bold ph-trash" style="font-size:15px;"></i>
            </button>
        </div>`;
    }).join('');
}

function viewLampiranDetail(id) {
    const item = currentLampiransData.find(x => x.id === id);
    if (!item) return;

    const container = document.getElementById("lampiranDetailPreview");
    const input = document.getElementById("editLampiranJudul");
    const saveBtn = document.getElementById("btnSaveLampiranJudul");
    const mediaContainer = document.getElementById("lampiranPreviewFrameContainer");

    if (container && input && saveBtn && mediaContainer) {
        container.style.display = "block";
        input.value = item.judul_lampiran || "";
        
        // Render media preview
        const isImage = item.mime_type && item.mime_type.includes('image');
        if (isImage) {
            mediaContainer.innerHTML = `<img src="${item.url_preview}" style="max-width:100%; max-height:250px; border-radius:6px; border:1px solid #cbd5e1; object-fit:contain;">`;
        } else {
            mediaContainer.innerHTML = `<iframe src="${item.url_preview}" style="width:100%; height:300px; border:1px solid #cbd5e1; border-radius:6px; background:#fff;"></iframe>`;
        }

        // Set save button action
        saveBtn.onclick = function() {
            saveLampiranJudul(id);
        };
    }
}

function saveLampiranJudul(id) {
    const newJudul = document.getElementById("editLampiranJudul").value.trim();
    const btn = document.getElementById("btnSaveLampiranJudul");
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner ph-spin"></i>';

    fetch(`/lampiran/${id}/update-judul`, {
        method: "PUT",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify({ judul_lampiran: newJudul })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        if (data.success) {
            showCustomToast("Judul lampiran berhasil disimpan!", "success");
            // Update locally
            const item = currentLampiransData.find(x => x.id === id);
            if (item) item.judul_lampiran = data.judul;
            // Re-render list
            renderLampiranList(currentLampiransData);
            // Re-trigger bundle preview to show the new title instantly!
            if (typeof generateSurat === "function" && document.getElementById("suratPreviewContainer").style.display !== "none") {
                generateSurat(true);
            }
        } else {
            showCustomToast(data.message || "Gagal memperbarui judul.", "error");
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
        showCustomToast("Gagal terhubung ke server.", "error");
    });
}

function deleteLampiran(id) {
    showPremiumConfirmModal({
        title: 'Hapus Lampiran?',
        message: 'Apakah Anda yakin ingin menghapus lampiran ini? Berkas fisik akan dihapus permanen dan tidak dapat dikembalikan.',
        confirmText: 'Ya, Hapus',
        cancelText: 'Batal',
        type: 'danger',
        onConfirm: () => {
            fetch(`/lampiran/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    showCustomToast('Lampiran berhasil dihapus!', 'success');
                    currentLampiransData = currentLampiransData.filter(x => x.id !== id);
                    renderLampiranList(currentLampiransData);
                    closeLampiranDetailPreview();
                    
                    // Re-trigger bundle preview to refresh automatically!
                    if (typeof generateSurat === "function" && document.getElementById("suratPreviewContainer").style.display !== "none") {
                        generateSurat(true);
                    }
                } else {
                    showCustomToast(data.message || 'Gagal menghapus lampiran.', 'error');
                }
            })
            .catch(() => {
                showCustomToast('Gagal terhubung ke server.', 'error');
            });
        }
    });
}

function clearAllLampiran() {
    if (!currentTrackerId) return;
    
    showPremiumConfirmModal({
        title: 'Bersihkan Semua Lampiran?',
        message: 'Semua lampiran yang Anda upload untuk pegawai ini akan dihapus permanen. Lanjutkan?',
        confirmText: 'Ya, Bersihkan',
        cancelText: 'Batal',
        type: 'danger',
        onConfirm: () => {
            executeClearAllLampiran();
        }
    });
}

function executeClearAllLampiran(isSilent = false) {
    if (!currentTrackerId) return;
    
    fetch(`/lampiran/clear/${currentTrackerId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            currentLampiransData = [];
            renderLampiranList(currentLampiransData);
            closeLampiranDetailPreview();
            
            if (!isSilent) {
                showCustomToast('Semua lampiran berhasil dibersihkan!', 'success');
                // Re-trigger bundle preview to refresh automatically!
                if (typeof generateSurat === "function" && document.getElementById("suratPreviewContainer").style.display !== "none") {
                    generateSurat(true);
                }
            }
        }
    })
    .catch(() => {
        if (!isSilent) showCustomToast('Gagal terhubung ke server.', 'error');
    });
}

function uploadLampiran() {
    if (!currentTrackerId) {
        showCustomToast('Pilih pegawai terlebih dahulu!', 'error');
        return;
    }
    
    const judul = document.getElementById('lampiranJudul').value.trim();
    const fileInput = document.getElementById('lampiranFile');
    const file = fileInput.files[0];
    
    if (!judul || !file) {
        showCustomToast('Judul dokumen dan file wajib diisi!', 'warning');
        return;
    }
    
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    formData.append('judul_lampiran', judul);
    formData.append('file', file); // Controller expects 'file', not 'file_lampiran'
    formData.append('tracker_id', currentTrackerId);
    
    const progressDiv = document.getElementById('uploadProgress');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    const btn = document.getElementById('btnUploadLampiran');
    
    btn.disabled = true;
    progressDiv.style.display = 'block';
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/lampiran', true); // Route is /lampiran, not /lampiran/upload
    
    xhr.upload.onprogress = function(e) {
        if (e.lengthComputable) {
            const percent = Math.round((e.loaded / e.total) * 100);
            progressBar.style.width = percent + '%';
            progressText.textContent = 'Mengupload... ' + percent + '%';
        }
    };
    
    xhr.onload = function() {
        btn.disabled = false;
        progressDiv.style.display = 'none';
        progressBar.style.width = '0%';
        
        if (xhr.status === 200) {
            const res = JSON.parse(xhr.responseText);
            if (res.success) {
                showCustomToast('Lampiran berhasil diupload!', 'success');
                document.getElementById('lampiranJudul').value = '';
                fileInput.value = '';
                document.getElementById('dropZoneText').textContent = 'Klik untuk pilih file';
                
                // Fetch updated list
                fetchLampiran(currentTrackerId);
                
                // Re-trigger bundle preview to show the new document instantly!
                if (typeof generateSurat === "function" && document.getElementById("suratPreviewContainer").style.display !== "none") {
                    generateSurat(true);
                }
            } else {
                showCustomToast(res.message || 'Gagal mengupload lampiran.', 'error');
            }
        } else {
            showCustomToast('Terjadi kesalahan saat mengupload berkas.', 'error');
        }
    };
    
    xhr.onerror = function() {
        btn.disabled = false;
        progressDiv.style.display = 'none';
        showCustomToast('Gagal terhubung ke server.', 'error');
    };
    
    xhr.send(formData);
}

function closeLampiranDetailPreview() {
    const container = document.getElementById("lampiranDetailPreview");
    if (container) {
        container.style.display = "none";
        document.getElementById("editLampiranJudul").value = "";
        document.getElementById("lampiranPreviewFrameContainer").innerHTML = "";
    }
}
