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

// Ambil data produk yang akan diedit dengan prepared statement
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header(DASHBOARD_URL);
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Pesan error dan sukses
$error = '';
$success = '';

// Proses form update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validasi input
    $nama_produk = trim($_POST['nama_produk']);
    $harga = filter_var($_POST['harga'], FILTER_VALIDATE_FLOAT);
    $stok = filter_var($_POST['stok'], FILTER_VALIDATE_INT);

    if (empty($nama_produk)) {
        $error = "Nama produk tidak boleh kosong";
    } elseif ($harga === false || $harga < 0) {
        $error = "Harga harus berupa angka positif";
    } elseif ($stok === false || $stok < 0) {
        $error = "Stok harus berupa angka positif";
    } else {
        // Update data produk dengan prepared statement
        $stmt = $conn->prepare("UPDATE products SET nama_produk = ?, harga = ?, stok = ? WHERE id = ?");
        $stmt->bind_param("sdii", $nama_produk, $harga, $stok, $id);

        if ($stmt->execute()) {
            $success = "Produk berhasil diperbarui!";
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
    <title>Edit Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow">
                    <div class="card-header bg-warning text-dark">
                        <h2 class="mb-0">Edit Produk</h2>
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
                                <label for="nama_produk" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" id="nama_produk" name="nama_produk"
                                    value="<?= htmlspecialchars($product['nama_produk'] ?? '') ?>" required>
                                <div class="form-text">Masukkan nama produk</div>
                            </div>

                            <div class="mb-3">
                                <label for="harga" class="form-label">Harga</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" step="0.01" min="0" class="form-control" id="harga" name="harga"
                                        value="<?= htmlspecialchars($product['harga'] ?? '') ?>" required>
                                </div>
                                <div class="form-text">Masukkan harga dalam format angka</div>
                            </div>

                            <div class="mb-3">
                                <label for="stok" class="form-label">Stok</label>
                                <input type="number" min="0" class="form-control" id="stok" name="stok"
                                    value="<?= htmlspecialchars($product['stok'] ?? '') ?>" required>
                                <div class="form-text">Masukkan jumlah stok produk</div>
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
</body>

</html>