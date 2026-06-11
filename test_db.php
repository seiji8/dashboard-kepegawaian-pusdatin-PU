<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=db_kepegawaian', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT * FROM pegawai WHERE nip = '198806052025211066'");
    $stmt->execute();
    $pegawai = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$pegawai) {
        echo "Pegawai dengan NIP 198806052025211066 tidak ditemukan.\n";
    } else {
        echo "Data Pegawai:\n";
        print_r($pegawai);
        
        echo "\nRiwayat SKP:\n";
        $stmtSkp = $pdo->prepare("SELECT * FROM riwayat_skps WHERE nip = '198806052025211066'");
        $stmtSkp->execute();
        print_r($stmtSkp->fetchAll(PDO::FETCH_ASSOC));
        
        echo "\nTracker:\n";
        $stmtTracker = $pdo->prepare("SELECT * FROM dashboard_trackers WHERE pegawai_id = :id");
        $stmtTracker->execute(['id' => $pegawai['id_pegawai_api'] ?? '']);
        print_r($stmtTracker->fetchAll(PDO::FETCH_ASSOC));
        
        echo "\nNotifikasi:\n";
        $stmtNotif = $pdo->prepare("SELECT * FROM notifications WHERE notifiable_id = (SELECT id FROM users WHERE email = :email LIMIT 1) OR data LIKE '%198806052025211066%'");
        $stmtNotif->execute(['email' => $pegawai['email'] ?? '']);
        print_r($stmtNotif->fetchAll(PDO::FETCH_ASSOC));
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
