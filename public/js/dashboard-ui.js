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
    if (modal) modal.style.display = "none";
}

// --- REMINDER MODAL ---
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
