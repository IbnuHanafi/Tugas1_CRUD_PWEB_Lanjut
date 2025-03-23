<?php
//Nama : Ibnu Hanafi Assalam
//NIM  : A12.2023.06994
require_once 'config.php';

// Ambil ID dari URL dengan validasi
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : 0;

define('DASHBOARD_URL', 'Location: dashboard.php');
if (!$id) {
    header(DASHBOARD_URL);
    exit;
}

// Ambil data user yang akan diedit dengan prepared statement
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header(DASHBOARD_URL);
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// Pesan error dan sukses
$error = '';
$success = '';

// Proses form update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    $name = trim($_POST['name']);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $passw = $_POST['passw'];
    $confirm_passw = $_POST['confirm_passw'];

    if (empty($name)) {
        $error = "Nama tidak boleh kosong";
    } elseif ($email === false) {
        $error = "Format email tidak valid";
    } elseif (!empty($passw) && $passw !== $confirm_passw) {
        $error = "Password dan konfirmasi password tidak cocok";
    } else {
        // Prepared statement untuk update
        if (!empty($passw)) {
            // Hash password baru jika diisi
            $hashed_password = password_hash($passw, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, passw = ? WHERE id = ?");
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $id);
        } else {
            // Jika password kosong, jangan update password
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $stmt->bind_param("ssi", $name, $email, $id);
        }

        if ($stmt->execute()) {
            $success = "Data user berhasil diperbarui!";
            // Redirect setelah jeda sebentar untuk menampilkan pesan sukses
            header("refresh:1;url=dashboard.php");
        } else {
            $error = "Error: " . $stmt->error;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h2 class="mb-0">Edit User</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                        <?php endif; ?>

                        <form method="POST" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                                <div class="form-text">Masukkan nama lengkap</div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                <div class="form-text">Masukkan alamat email yang valid</div>
                            </div>

                            <div class="mb-3">
                                <label for="passw" class="form-label">Password</label>
                                <input type="password" class="form-control" id="passw" name="passw">
                                <div class="form-text">Kosongkan jika tidak ingin mengubah password</div>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_passw" class="form-label">Konfirmasi Password</label>
                                <input type="password" class="form-control" id="confirm_passw" name="confirm_passw">
                                <div class="form-text">Konfirmasi password baru</div>
                            </div>

                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-warning">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validasi password hanya jika diisi
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('passw').value;
            const confirmPassword = document.getElementById('confirm_passw').value;

            // Jika password tidak diubah, skip validasi
            if (password === '' && confirmPassword === '') {
                return true;
            }

            // Jika password diisi, pastikan keduanya sama
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Password dan konfirmasi password tidak cocok!');
                return false;
            }
        });
    </script>
</body>

</html>