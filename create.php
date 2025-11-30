<?php
// create.php
require 'includes/db.php';

$errors = [];
$prodi_options = ['Teknik Informatika', 'Sistem Informasi', 'Manajemen', 'Akuntansi'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama'] ?? '');
    $nim = trim($_POST['nim'] ?? '');
    $prodi = $_POST['prodi'] ?? '';
    $angkatan = trim($_POST['angkatan'] ?? '');
    $status = $_POST['status'] ?? 'active';


    if (empty($nama))
        $errors[] = "Nama wajib diisi.";
    if (empty($nim))
        $errors[] = "NIM wajib diisi.";
    if (empty($prodi))
        $errors[] = "Prodi wajib diisi.";
    if (empty($angkatan))
        $errors[] = "Angkatan wajib diisi.";


    if (!is_numeric($angkatan) || (int) $angkatan <= 0) {
        $errors[] = "Angkatan harus berupa angka positif.";
    }

    if (!in_array($prodi, $prodi_options)) {
        $errors[] = "Pilihan Prodi tidak valid.";
    }

    $foto_path = NULL;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['foto'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];

        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png'];
        $maxSize = 2 * 1024 * 1024;

        if (!in_array($fileExt, $allowed)) {
            $errors[] = "Format file tidak diizinkan. Hanya JPG, JPEG, atau PNG.";
        }
        if ($fileSize > $maxSize) {
            $errors[] = "Ukuran file terlalu besar. Maksimal 2MB.";
        }


        if (empty($errors)) {
            $fileNameNew = uniqid('', true) . "." . $fileExt;
            $fileDestination = 'uploads/' . $fileNameNew;

            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                $foto_path = $fileDestination;
            } else {
                $errors[] = "Gagal mengunggah file foto.";
            }
        }
    }

    // === Jika semua validasi berhasil, simpan data ===
    if (empty($errors)) {
        try {
            // Data disimpan ke tabel basis data menggunakan prepared statement PDO
            $sql = "INSERT INTO students (nama, nim, prodi, angkatan, foto_path, status) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nama, $nim, $prodi, $angkatan, $foto_path, $status]);

            // Redirect ke halaman utama setelah berhasil
            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            // Tangani error, misal NIM sudah ada (UNIQUE)
            if ($e->getCode() == 23000) {
                $errors[] = "NIM sudah terdaftar. Gunakan NIM lain.";
            } else {
                $errors[] = "Error database: " . $e->getMessage();
            }
            // Hapus file yang terlanjur diupload jika terjadi error DB
            if ($foto_path && file_exists($foto_path)) {
                unlink($foto_path);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>CRUD Mahasiswa - Tambah</title>
</head>

<body>

    <h2>Tambah Data Mahasiswa</h2>
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

    <form action="create.php" method="POST" enctype="multipart/form-data">
        <label for="nama">Nama:</label><br>
        <input type="text" id="nama" name="nama" required value="<?= htmlspecialchars($nama ?? '') ?>"><br><br>

        <label for="nim">NIM:</label><br>
        <input type="text" id="nim" name="nim" required value="<?= htmlspecialchars($nim ?? '') ?>"><br><br>

        <label for="prodi">Prodi:</label><br>
        <select id="prodi" name="prodi" required>
            <option value="">-- Pilih Prodi --</option>
            <?php foreach ($prodi_options as $option): ?>
                <option value="<?= htmlspecialchars($option) ?>" <?= (isset($prodi) && $prodi === $option) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($option) ?>
                </option>
            <?php endforeach; ?>
        </select><br><br>

        <label for="angkatan">Angkatan:</label><br>
        <input type="number" id="angkatan" name="angkatan" required
            value="<?= htmlspecialchars($angkatan ?? '') ?>"><br><br>

        <label for="foto">Foto (Max 2MB, JPG/PNG):</label><br>
        <input type="file" id="foto" name="foto" accept=".jpg,.jpeg,.png"><br><br>

        <label for="status">Status:</label><br>
        <select id="status" name="status" required>
            <option value="active" <?= (isset($status) && $status === 'active') ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= (isset($status) && $status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
        </select><br><br>

        <button type="submit">Tambah Data</button>
    </form>

</body>

</html>