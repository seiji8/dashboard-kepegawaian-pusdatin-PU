/**
 * Dashboard UI Components
 * Handles: Accordion, Modal open/close, shared variables, helpers
 */

// --- SHARED VARIABLES ---
const detailModal = document.getElementById("detailModal");
const reminderModal = document.getElementById("reminderModal");
const confirmModal = document.getElementById("confirmModal");
let currentDetailNip = null;
let confirmTrackerId = null;
let reminderTomSelect = null;
let ukomTrackerId = null;

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

// --- HELPER ---
function setText(id, text) {
    const el = document.getElementById(id);
    if (el) el.innerText = text;
}

// --- MODAL CLOSE FUNCTIONS ---
function closeDetailModal() {
    if (detailModal) detailModal.style.display = "none";
}

function closeDashboardDetail() {
    const modal = document.getElementById("dashboardDetailModal");
    if (modal) modal.classList.remove("open");
}

// --- REMINDER MODAL ---
function openReminderModal() {
    if (reminderModal) reminderModal.classList.add("open");

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
    if (reminderModal) reminderModal.classList.remove("open");
    if (reminderTomSelect) {
        reminderTomSelect.destroy();
        reminderTomSelect = null;
    }
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

// --- CONFIRM MODAL ---
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

// --- UKOM MODAL ---
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

// --- WINDOW CLICK LISTENER (Close modals on overlay click) ---
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

// --- INIT ---
document.addEventListener("DOMContentLoaded", function () {
    fetchNotifications();
});

/**
 * Premium Awwwards-class Confirmation Modal
 * @param {Object} options Configuration options
 */
function showPremiumConfirmModal(options) {
    const overlay = document.createElement("div");
    overlay.id = "premiumConfirmModal";
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.4);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    `;

    let iconHTML = `<i class="ph-bold ph-warning" style="font-size: 28px; color: #f59e0b;"></i>`;
    let iconBg = `linear-gradient(135deg, #fef3c7, #fde68a)`;
    let iconShadow = `0 8px 24px rgba(245, 158, 11, 0.15)`;
    let actionBtnBg = `linear-gradient(135deg, #3b82f6, #1d4ed8)`;
    let actionBtnShadow = `0 8px 20px rgba(37, 99, 235, 0.25)`;

    if (options.type === 'danger') {
        iconHTML = `<i class="ph-bold ph-trash" style="font-size: 28px; color: #ef4444;"></i>`;
        iconBg = `linear-gradient(135deg, #fee2e2, #fecaca)`;
        iconShadow = `0 8px 24px rgba(239, 68, 68, 0.15)`;
        actionBtnBg = `linear-gradient(135deg, #ef4444, #dc2626)`;
        actionBtnShadow = `0 8px 20px rgba(239, 68, 68, 0.25)`;
    }

    const card = document.createElement("div");
    card.style.cssText = `
        background: #ffffff;
        border-radius: 24px;
        width: 420px;
        max-width: 90%;
        padding: 36px 30px;
        box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.18), 0 0 0 1px rgba(0, 0, 0, 0.05);
        transform: scale(0.92);
        transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
        text-align: center;
        position: relative;
        overflow: hidden;
    `;

    card.innerHTML = `
        <div style="background: ${iconBg}; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px auto; box-shadow: ${iconShadow};">
            ${iconHTML}
        </div>
        <h3 style="margin: 0 0 10px 0; font-size: 20px; font-weight: 850; color: #0f172a; letter-spacing: -0.4px; font-family: 'Poppins', sans-serif;">${options.title || 'Konfirmasi'}</h3>
        <p style="margin: 0 0 28px 0; font-size: 13.5px; color: #475569; font-weight: 500; line-height: 1.6; padding: 0 10px; font-family: 'Poppins', sans-serif;">${options.message || 'Apakah Anda yakin?'}</p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button id="premiumConfirmCancelBtn" style="
                flex: 1;
                padding: 12px 24px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                color: #475569;
                border-radius: 14px;
                font-size: 13.5px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.2s ease;
                font-family: 'Poppins', sans-serif;
            " onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1'" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'">
                ${options.cancelText || 'Batal'}
            </button>
            <button id="premiumConfirmYesBtn" style="
                flex: 1;
                padding: 12px 24px;
                background: ${actionBtnBg};
                border: none;
                color: #ffffff;
                border-radius: 14px;
                font-size: 13.5px;
                font-weight: 700;
                cursor: pointer;
                transition: all 0.2s ease;
                box-shadow: ${actionBtnShadow};
                font-family: 'Poppins', sans-serif;
            " onmouseover="this.style.transform='translateY(-1px)';" onmouseout="this.style.transform='translateY(0)';">
                ${options.confirmText || 'Ya'}
            </button>
        </div>
    `;

    overlay.appendChild(card);
    document.body.appendChild(overlay);

    // Animation in
    setTimeout(() => {
        overlay.style.opacity = "1";
        card.style.transform = "scale(1)";
    }, 10);

    const closeConfirm = (confirmed) => {
        overlay.style.opacity = "0";
        card.style.transform = "scale(0.92)";
        setTimeout(() => {
            overlay.remove();
            if (confirmed && typeof options.onConfirm === 'function') {
                options.onConfirm();
            }
        }, 250);
    };

    overlay.addEventListener("click", (e) => {
        if (e.target === overlay) {
            closeConfirm(false);
        }
    });

    document.getElementById("premiumConfirmCancelBtn").addEventListener("click", () => closeConfirm(false));
    document.getElementById("premiumConfirmYesBtn").addEventListener("click", () => closeConfirm(true));
}
