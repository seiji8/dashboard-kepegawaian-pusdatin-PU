<div class="empty-state-wrapper" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 20px; text-align: center; border-radius: 0; margin: 0; background-color: transparent;">
    <div style="background: #e0e7ff; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
        <i class="ph-duotone {{ $icon ?? 'ph-coffee' }}" style="font-size: 32px; color: #4338ca;"></i>
    </div>
    <h4 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 8px; letter-spacing: -0.3px;">{{ $title ?? 'Hore! Semua Beres 🎉' }}</h4>
    <p style="font-size: 13.5px; color: #64748b; max-width: 320px; margin: 0 auto; line-height: 1.6;">{{ $message ?? 'Antrean ini sudah diselesaikan dengan bersih. Silakan tarik napas atau seruput kopi Anda dulu!' }}</p>
</div>
