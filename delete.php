<?php
require 'includes/db.php';

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    die("ID Mahasiswa tidak valid.");
}

try {
    // Opsional: Ambil path foto jika ingin dihapus dari folder server juga
    $stmt_select = $pdo->prepare('SELECT foto_path FROM students WHERE id = ?');
    $stmt_select->execute([$id]);
    $student = $stmt_select->fetch();

    // Menghapus record dari basis data menggunakan PDO
    $sql = "DELETE FROM students WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    // Jika ada file terkait, penghapusan file dari folder boleh dilakukan
    if ($student && $student['foto_path'] && file_exists($student['foto_path'])) {
        unlink($student['foto_path']);
    }

    // Redirect kembali ke halaman utama
    header('Location: index.php?delete=success');
    exit;

} catch (PDOException $e) {
    die("Error menghapus data: " . $e->getMessage());
}
?>