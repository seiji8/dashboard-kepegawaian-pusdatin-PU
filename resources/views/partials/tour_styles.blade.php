<!-- Driver.js CSS untuk Panduan Tour -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css"/>
<style>
    /* Kustomisasi Mewah Driver.js (Panduan Tour) */
    .driver-popover {
        font-family: 'Poppins', sans-serif !important;
        border-radius: 14px !important;
        padding: 24px !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.05) !important;
        border: none !important;
        max-width: 350px !important;
    }
    .driver-popover-title {
        font-size: 17.5px !important;
        font-weight: 700 !important;
        color: #1e3a8a !important; /* Biru Logo PU */
        margin-bottom: 12px !important;
        letter-spacing: -0.3px;
    }
    .driver-popover-description {
        font-size: 14px !important;
        color: #475569 !important;
        line-height: 1.6 !important;
    }
    .driver-popover-footer {
        margin-top: 24px !important;
        display: flex !important;
        align-items: center !important;
    }
    .driver-popover-progress-text {
        font-size: 13px !important;
        color: #64748b !important;
        font-weight: 600 !important;
    }
    .driver-popover-next-btn, .driver-popover-prev-btn {
        font-family: 'Poppins', sans-serif !important;
        font-weight: 600 !important;
        border-radius: 8px !important;
        padding: 8px 18px !important;
        font-size: 13.5px !important;
        transition: all 0.2s ease !important;
        text-shadow: none !important;
    }
    .driver-popover-next-btn {
        background-color: #1e3a8a !important;
        color: #ffffff !important;
        border: none !important;
        box-shadow: 0 4px 6px -1px rgba(30, 58, 138, 0.2) !important;
    }
    .driver-popover-next-btn:hover {
        background-color: #1e40af !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 8px -1px rgba(30, 58, 138, 0.3) !important;
    }
    .driver-popover-prev-btn {
        background-color: #f8fafc !important;
        color: #475569 !important;
        border: 1px solid #cbd5e1 !important;
        margin-right: 12px !important;
    }
    .driver-popover-prev-btn:hover {
        background-color: #f1f5f9 !important;
        color: #0f172a !important;
    }
    .driver-popover-close-btn {
        color: #94a3b8 !important;
        transition: color 0.2s !important;
    }
    .driver-popover-close-btn:hover {
        color: #ef4444 !important;
    }

    /* Tooltip Bantuan */
    .tooltip {
        position: relative;
        display: inline-flex;
        align-items: center;
        margin-left: 8px;
        cursor: help;
    }
    .tooltip .tooltiptext {
        visibility: hidden;
        width: 250px;
        background-color: #1e293b;
        color: #f8fafc;
        text-align: left;
        border-radius: 8px;
        padding: 10px 14px;
        position: absolute;
        z-index: 100;
        bottom: 150%;
        left: 50%;
        transform: translateX(-50%);
        opacity: 0;
        transition: all 0.3s ease;
        font-size: 12px;
        font-weight: 400;
        line-height: 1.5;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        pointer-events: none;
        white-space: normal;
    }
    .tooltip .tooltiptext::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -6px;
        border-width: 6px;
        border-style: solid;
        border-color: #1e293b transparent transparent transparent;
    }
    .tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
        bottom: 135%;
    }
    .icon-help {
        background: #e2e8f0;
        color: #64748b;
        border-radius: 50%;
        width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        font-weight: 700;
        transition: all 0.2s;
    }
    .tooltip:hover .icon-help {
        background: #3b82f6;
        color: white;
        transform: scale(1.1);
    }
</style>
