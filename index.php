<?php
// index.php
require 'includes/db.php'; // Panggil file koneksi

// 1. Ambil data dari tabel students
try {
    $stmt = $pdo->query('SELECT id, nim, nama, prodi, angkatan, status FROM students');
    $students = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error mengambil data: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>CRUD Mahasiswa - Daftar</title>
</head>

<body>

    <h2>Daftar Mahasiswa</h2>
    <p><a href="create.php">Tambah Mahasiswa Baru</a></p>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>NIM</th>
                <th>Nama</th>
                <th>Prodi</th>
                <th>Angkatan</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($students) > 0): ?>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['id']) ?></td>
                        <td><?= htmlspecialchars($student['nim']) ?></td>
                        <td><?= htmlspecialchars($student['nama']) ?></td>
                        <td><?= htmlspecialchars($student['prodi']) ?></td>
                        <td><?= htmlspecialchars($student['angkatan']) ?></td>
                        <td><?= htmlspecialchars($student['status']) ?></td>
                        <td>
                            <a href="update.php?id=<?= htmlspecialchars($student['id']) ?>">Edit</a> |
                            <a href="delete.php?id=<?= htmlspecialchars($student['id']) ?>"
                                onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">Tidak ada data mahasiswa.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>

</html>