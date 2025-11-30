<?php
// update.php
require 'includes/db.php';

$errors = [];
$prodi_options = ['Teknik Informatika', 'Sistem Informasi', 'Manajemen', 'Akuntansi'];
$id = $_GET['id'] ?? null;

// Validasi ID
if (!$id || !is_numeric($id)) {
    die("ID Mahasiswa tidak valid.");
}

// 1. Ambil data lama (pre-fill data lama)
try {
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$id]);
    $student = $stmt->fetch();

    if (!$student) {
        die("Data mahasiswa tidak ditemukan.");
    }

    // Set variabel untuk pre-fill form
    $nama = $student['nama'];
    $nim = $student['nim'];
    $prodi = $student['prodi'];
    $angkatan = $student['angkatan'];
    $current_foto_path = $student['foto_path'];
    $status = $student['status'];

} catch (PDOException $e) {
    die("Error mengambil data lama: " . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $new_nama = trim($_POST['nama'] ?? '');
    $new_nim = trim($_POST['nim'] ?? '');
    $new_prodi = $_POST['prodi'] ?? '';
    $new_angkatan = trim($_POST['angkatan'] ?? '');
    $new_status = $_POST['status'] ?? 'active';
    $preserve_file = $_POST['preserve_file'] ?? 'no';

    if (empty($new_nama))
        $errors[] = "Nama wajib diisi.";

    if (!is_numeric($new_angkatan) || (int) $new_angkatan <= 0)
        $errors[] = "Angkatan harus berupa angka positif.";
    if (!in_array($new_prodi, $prodi_options))
        $errors[] = "Pilihan Prodi tidak valid.";

    $final_foto_path = $current_foto_path;


    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        $fileSize = $file['size'];
        $fileTmpName = $file['tmp_name'];

        $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array($fileExt, $allowed)) {
            $errors[] = "Format file baru tidak diizinkan.";
        }
        if ($fileSize > $maxSize) {
            $errors[] = "Ukuran file baru terlalu besar.";
        }

        if (empty($errors)) {
            // Upload file baru
            $fileNameNew = uniqid('', true) . "." . $fileExt;
            $fileDestination = 'uploads/' . $fileNameNew;

            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                $final_foto_path = $fileDestination;

                // Hapus file lama jika ada dan berhasil upload file baru
                if ($current_foto_path && file_exists($current_foto_path)) {
                    unlink($current_foto_path);
                }
            } else {
                $errors[] = "Gagal mengunggah file foto baru.";
            }
        }
    } else if ($preserve_file === 'no' && $current_foto_path) {
        // Jika user memilih untuk HAPUS file lama, dan tidak ada file baru diupload
        if (file_exists($current_foto_path)) {
            unlink($current_foto_path);
        }
        $final_foto_path = NULL; // Kosongkan path di database
    }


    // === Proses Update menggunakan PDO prepared statement ===
    if (empty($errors)) {
        try {
            // Mendukung update semua field kecuali Primary Key (ID)
            $sql = "UPDATE students SET nama = ?, nim = ?, prodi = ?, angkatan = ?, foto_path = ?, status = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$new_nama, $new_nim, $new_prodi, $new_angkatan, $final_foto_path, $new_status, $id]);

            // Redirect ke halaman utama setelah berhasil
            header('Location: index.php?update=success');
            exit;
        } catch (PDOException $e) {
            // Tangani error, misal NIM sudah ada (UNIQUE)
            if ($e->getCode() == 23000) {
                $errors[] = "NIM sudah terdaftar. Gunakan NIM lain.";
            } else {
                $errors[] = "Error database: " . $e->getMessage();
            }
        }
    }

    // Jika ada error saat POST, gunakan nilai POST yang baru untuk pre-fill form
    $nama = $new_nama;
    $nim = $new_nim;
    $prodi = $new_prodi;
    $angkatan = $new_angkatan;
    $status = $new_status;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>CRUD Mahasiswa - Ubah</title>
</head>

<body>

    <h2>Ubah Data Mahasiswa (ID: <?= htmlspecialchars($id) ?>)</h2>
    <p><a href="index.php">Kembali ke Daftar</a></p>

    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="update.php?id=<?= htmlspecialchars($id) ?>" method="POST" enctype="multipart/form-data">

        <label for="nama">Nama:</label><br>
        <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($nama) ?>"><br><br>

        <label for="nim">NIM:</label><br>
        <input type="text" id="nim" name="nim" required value="<?= htmlspecialchars($nim) ?>"><br><br>

        <label for="prodi">Prodi:</label><br>
        <select id="prodi" name="prodi" required>
            <option value="">-- Pilih Prodi --</option>
            <?php foreach ($prodi_options as $option): ?>
                <option value="<?= htmlspecialchars($option) ?>" <?= ($prodi === $option) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="angkatan">Angkatan:</label><br>
        <input type="number" id="angkatan" name="angkatan" required value="<?= htmlspecialchars($angkatan) ?>"><br><br>

        <label>Foto Saat Ini:</label><br>
        <?php if ($current_foto_path): ?>
            <img src="<?= htmlspecialchars($current_foto_path) ?>" alt="Foto Mahasiswa"
                style="width:100px; height:auto;"><br>
            <label><input type="checkbox" name="preserve_file" value="yes" checked> Pertahankan File Lama</label><br><br>
        <?php else: ?>
            <p>Tidak ada foto saat ini.</p>
        <?php endif; ?>

        <label for="foto">Ganti Foto (Max 2MB, JPG/PNG):</label><br>
        <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png"><br><br>

        <label for="status">Status:</label><br>
        <select id="status" name="status" required>
            <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
        </select><br><br>

        <button type="submit">Simpan Perubahan</button>
    </form>

</body>

</html>