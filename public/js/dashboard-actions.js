/**
 * Dashboard Actions
 * Handles: Admin actions that submit data to the server
 * (Confirm, UKOM, Reminder Email)
 */

// --- SUBMIT UKOM ---
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

function moveToUkomFromKJ(trackerId) {
    showPremiumConfirmModal({
        title: 'Daftarkan UKOM?',
        message: 'Kirim pegawai ini ke antrean Uji Kompetensi (UKOM) untuk diproses lebih lanjut?',
        confirmText: 'Ya, Kirim',
        cancelText: 'Batal',
        type: 'info',
        onConfirm: () => {
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
    });
}

// --- SET KELULUSAN UKOM ---
function setKelulusanUkom(trackerId, statusLulus, nama = "") {
    const existing = document.getElementById("popupKonfirmasiInline");
    if (existing) existing.remove();

    const title = statusLulus ? "Kelulusan Uji Kompetensi" : "Tidak Lulus Uji Kompetensi";
    const actionText = statusLulus ? "Ya, Set Lulus UKOM" : "Tetap di antrean UKOM";
    const color = statusLulus ? "#10b981" : "#ef4444";
    const icon = statusLulus ? "ph-check-circle" : "ph-x-circle";
    const message = statusLulus 
        ? "Apakah Anda yakin ingin menetapkan Lulus UKOM dan mengembalikan ke antrean Kenaikan Jenjang untuk:"
        : "Apakah Anda yakin menetapkan Tidak Lulus UKOM untuk:";

    const popup = document.createElement("div");
    popup.id = "popupKonfirmasiInline";
    popup.classList.add("modal-overlay");
    popup.style.cssText = "position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:99999;display:flex;align-items:center;justify-content:center;";
    popup.innerHTML = `
        <div class="confirm-modal-content">
            <div class="confirm-modal-icon">
                <i class="ph-fill ${icon}" style="font-size: 48px; color: ${color};"></i>
            </div>
            <h3 class="confirm-modal-title">${title}</h3>
            <p class="confirm-modal-text">${message}</p>
            <p class="confirm-modal-name" style="color: #0f172a;">${nama}</p>
            <div class="confirm-modal-actions">
                <button class="confirm-btn-cancel" onclick="document.getElementById('popupKonfirmasiInline').remove()">Batal</button>
                <button class="confirm-btn-yes" id="btnKonfirmasiSubmit" style="background:${color};" onclick="submitSetKelulusanUkom(${trackerId}, ${statusLulus})">${actionText}</button>
            </div>
        </div>`;
    document.body.appendChild(popup);
    popup.addEventListener("click", (e) => {
        if (e.target === popup) popup.remove();
    });
}

function submitSetKelulusanUkom(trackerId, statusLulus) {
    const btn = document.getElementById("btnKonfirmasiSubmit");
    btn.disabled = true;
    btn.innerHTML = '<i class="ph-bold ph-spinner"></i> Proses...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

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
            const popup = document.getElementById("popupKonfirmasiInline");
            if (popup) popup.remove();

            if (data.success) {
                showCustomToast(data.message, "success");
                setTimeout(() => window.location.reload(), 1000);
            } else {
                showCustomToast(data.message || "Gagal", "error");
            }
        });
}

// --- SEND REMINDER EMAIL ---
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

// --- SUBMIT CONFIRM ---
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
