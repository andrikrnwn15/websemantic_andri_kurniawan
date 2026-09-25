<?php
session_start();
require_once 'koneksi.php';

// =========================================================
// PROTEKSI HALAMAN: HANYA MAHASISWA YANG SUDAH LOGIN
// =========================================================
if (!isset($_SESSION['mhs_npm'])) {
    header("Location: login_mahasiswa.php");
    exit;
}

$npm    = $_SESSION['mhs_npm'];
$pesan  = "";
$errorMsg = "";

// =========================================================
// PROSES UPDATE PROFIL (ALAMAT & PASSWORD)
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form']) && $_POST['form'] === 'update_profil') {
    $alamat          = trim($_POST['alamat']);
    $password_baru   = $_POST['password_baru'];
    $password_ulang  = $_POST['password_ulang'];

    if ($password_baru !== "") {
        if ($password_baru !== $password_ulang) {
            $errorMsg = "Konfirmasi password baru tidak sama.";
        } else {
            $passHash = password_hash($password_baru, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "UPDATE mahasiswa SET alamat=?, password=? WHERE npm=?");
            mysqli_stmt_bind_param($stmt, "sss", $alamat, $passHash, $npm);
            mysqli_stmt_execute($stmt);
            $pesan = "Profil dan password berhasil diperbarui.";
        }
    } else {
        $stmt = mysqli_prepare($koneksi, "UPDATE mahasiswa SET alamat=? WHERE npm=?");
        mysqli_stmt_bind_param($stmt, "ss", $alamat, $npm);
        mysqli_stmt_execute($stmt);
        $pesan = "Alamat berhasil diperbarui.";
    }
}

// =========================================================
// AMBIL DATA MAHASISWA
// =========================================================
$stmt = mysqli_prepare($koneksi,
    "SELECT m.*, p.nama_prodi, p.fakultas
     FROM mahasiswa m
     JOIN program_studi p ON m.kode_prodi = p.kode_prodi
     WHERE m.npm = ?"
);
mysqli_stmt_bind_param($stmt, "s", $npm);
mysqli_stmt_execute($stmt);
$mhs = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Mahasiswa - SIM Mahasiswa UMB</title>
<style>
  :root {
    --primary: #1d61e7;
    --primary-hover: #154ec2;
    --border-color: #e2e8f0;
    --text-dark: #1e293b;
    --text-muted: #64748b;
    --bg-light: #f8fafc;
  }
  * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, Helvetica, sans-serif; }
  body { background: var(--bg-light); color: var(--text-dark); }

  header {
    background:#fff; border-bottom:1px solid var(--border-color);
    padding:1rem 1.5rem; display:flex; justify-content:space-between; align-items:center;
  }
  header .brand { font-weight:800; font-size:1.05rem; }
  header .brand span { display:block; font-size:0.72rem; color:var(--text-muted); font-weight:600; }
  header .logout { color:#dc2626; font-weight:700; font-size:0.85rem; text-decoration:none; }

  .container { max-width: 800px; margin: 2rem auto; padding: 0 1.5rem; }

  .card {
    background:#fff; border:1px solid var(--border-color); border-radius:12px;
    padding:1.8rem; margin-bottom:1.5rem;
  }
  .card h3 { margin-bottom:1.2rem; font-size:1.1rem; }

  .biodata-grid { display:grid; grid-template-columns:1fr 1fr; gap:1rem 2rem; }
  .biodata-item label { display:block; font-size:0.75rem; color:var(--text-muted); font-weight:600; margin-bottom:0.2rem; }
  .biodata-item div.value { font-size:0.95rem; font-weight:600; }

  .form-group { margin-bottom:1.1rem; }
  .form-group label { display:block; font-size:0.82rem; font-weight:600; margin-bottom:0.4rem; }
  .form-group input {
    width:100%; padding:0.7rem 0.9rem; border:1.5px solid var(--border-color);
    border-radius:8px; font-size:0.9rem; outline:none;
  }
  .form-group input:focus { border-color: var(--primary); }
  .hint { font-size:0.75rem; color: var(--text-muted); margin-top:-0.6rem; margin-bottom:1rem; }

  .btn-submit {
    background:var(--primary); color:#fff; padding:0.75rem 1.6rem;
    border:none; border-radius:8px; font-weight:700; font-size:0.9rem; cursor:pointer;
  }
  .btn-submit:hover { background: var(--primary-hover); }

  .alert { padding:0.8rem 1rem; border-radius:8px; font-size:0.88rem; margin-bottom:1.2rem; }
  .alert-success { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
  .alert-error { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }

  @media (max-width:600px) { .biodata-grid { grid-template-columns:1fr; } }
</style>
</head>
<body>

<header>
  <div class="brand">SIM Mahasiswa <span>Universitas Muhammadiyah Bengkulu</span></div>
  <a href="logout.php" class="logout">Keluar</a>
</header>

<div class="container">

  <?php if ($pesan !== "") { ?><div class="alert alert-success"><?php echo htmlspecialchars($pesan); ?></div><?php } ?>
  <?php if ($errorMsg !== "") { ?><div class="alert alert-error"><?php echo htmlspecialchars($errorMsg); ?></div><?php } ?>

  <div class="card">
    <h3>Biodata Mahasiswa</h3>
    <div class="biodata-grid">
      <div class="biodata-item">
        <label>NPM</label>
        <div class="value"><?php echo htmlspecialchars($mhs['npm']); ?></div>
      </div>
      <div class="biodata-item">
        <label>Nama Mahasiswa</label>
        <div class="value"><?php echo htmlspecialchars($mhs['nama_mahasiswa']); ?></div>
      </div>
      <div class="biodata-item">
        <label>Jenis Kelamin</label>
        <div class="value"><?php echo htmlspecialchars($mhs['jenis_kelamin']); ?></div>
      </div>
      <div class="biodata-item">
        <label>Tempat, Tanggal Lahir</label>
        <div class="value"><?php echo htmlspecialchars($mhs['tempat_lahir']) . ', ' . htmlspecialchars($mhs['tanggal_lahir']); ?></div>
      </div>
      <div class="biodata-item">
        <label>Tanggal Masuk</label>
        <div class="value"><?php echo htmlspecialchars($mhs['tanggal_masuk']); ?></div>
      </div>
      <div class="biodata-item">
        <label>Program Studi</label>
        <div class="value"><?php echo htmlspecialchars($mhs['nama_prodi']); ?></div>
      </div>
      <div class="biodata-item">
        <label>Fakultas</label>
        <div class="value"><?php echo htmlspecialchars($mhs['fakultas']); ?></div>
      </div>
    </div>
  </div>

  <div class="card">
    <h3>Perbarui Alamat &amp; Password</h3>
    <form method="POST" action="dashboard_mahasiswa.php">
      <input type="hidden" name="form" value="update_profil">

      <div class="form-group">
        <label>Alamat</label>
        <input type="text" name="alamat" value="<?php echo htmlspecialchars($mhs['alamat']); ?>">
      </div>

      <div class="form-group">
        <label>Password Baru</label>
        <input type="password" name="password_baru" placeholder="Kosongkan jika tidak ingin mengubah password">
      </div>
      <div class="hint">Isi hanya jika ingin mengganti password.</div>

      <div class="form-group">
        <label>Ulangi Password Baru</label>
        <input type="password" name="password_ulang">
      </div>

      <button type="submit" class="btn-submit">Simpan Perubahan</button>
    </form>
  </div>

</div>

</body>
</html>
