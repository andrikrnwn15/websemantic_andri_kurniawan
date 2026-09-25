<?php
session_start();
require_once 'koneksi.php';

// Kalau sudah login sebagai mahasiswa, langsung arahkan ke dashboard
if (isset($_SESSION['mhs_npm'])) {
    header("Location: dashboard_mahasiswa.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['npm'], $_POST['password'])) {
    $npm      = trim($_POST['npm']);
    $password = $_POST['password'];

    if ($npm === "" || $password === "") {
        $error = "NPM dan password wajib diisi.";
    } else {
        $stmt = mysqli_prepare($koneksi,
            "SELECT m.npm, m.nama_mahasiswa, m.password, m.kode_prodi, p.nama_prodi
             FROM mahasiswa m
             JOIN program_studi p ON m.kode_prodi = p.kode_prodi
             WHERE m.npm = ?"
        );
        mysqli_stmt_bind_param($stmt, "s", $npm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $mhs    = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        $passwordValid = false;
        if ($mhs) {
            if (password_verify($password, $mhs['password'])) {
                $passwordValid = true;
            } elseif ($password === $mhs['password']) {
                $passwordValid = true;
            }
        }

        if ($mhs && $passwordValid) {
            $_SESSION['mhs_npm']    = $mhs['npm'];
            $_SESSION['mhs_nama']   = $mhs['nama_mahasiswa'];
            $_SESSION['mhs_prodi']  = $mhs['nama_prodi'];
            header("Location: dashboard_mahasiswa.php");
            exit;
        } else {
            $error = "NPM atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Mahasiswa - SIM Mahasiswa UMB</title>
<style>
  :root {
    --primary: #1d61e7;
    --primary-hover: #154ec2;
    --border-color: #e2e8f0;
    --text-dark: #1e293b;
    --text-muted: #64748b;
  }
  * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, Helvetica, sans-serif; }
  body {
    background: linear-gradient(180deg, #f0f5ff 0%, #ffffff 100%);
    color: var(--text-dark);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
  }
  .login-card {
    background: #fff;
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 2.2rem;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
  }
  .logo-emblem {
    width: 46px; height: 46px;
    background: linear-gradient(135deg, #1d61e7, #0b3899);
    border-radius: 50%;
    display:flex; align-items:center; justify-content:center;
    color:#ffd700; font-weight:800; margin-bottom:1rem;
  }
  .login-card h3 { font-size:1.25rem; font-weight:800; margin-bottom:0.2rem; }
  .login-card p.sub { color: var(--text-muted); font-size:0.85rem; margin-bottom:1.5rem; }
  .form-group { margin-bottom:1.1rem; }
  .form-group label { display:block; font-size:0.82rem; font-weight:600; margin-bottom:0.4rem; }
  .form-group input {
    width:100%; padding:0.75rem 0.9rem; border:1.5px solid var(--border-color);
    border-radius:8px; font-size:0.92rem; outline:none;
  }
  .form-group input:focus { border-color: var(--primary); }
  .btn-submit {
    width:100%; background:var(--primary); color:#fff; padding:0.85rem;
    border:none; border-radius:8px; font-weight:700; font-size:0.95rem; cursor:pointer;
  }
  .btn-submit:hover { background: var(--primary-hover); }
  .alert-error {
    background:#fef2f2; color:#b91c1c; border:1px solid #fecaca;
    padding:0.7rem 1rem; border-radius:8px; font-size:0.85rem; margin-bottom:1.1rem;
  }
  .back-link { display:block; text-align:center; margin-top:1.2rem; font-size:0.85rem; color:var(--text-muted); text-decoration:none; }
  .back-link:hover { color: var(--primary); }
</style>
</head>
<body>

<div class="login-card">
  <div class="logo-emblem">UMB</div>
  <h3>Login Mahasiswa</h3>
  <p class="sub">Masuk menggunakan NPM dan password kamu.</p>

  <?php if ($error !== "") { ?>
    <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
  <?php } ?>

  <form method="POST" action="login_mahasiswa.php">
    <div class="form-group">
      <label for="npm">NPM</label>
      <input type="text" name="npm" id="npm" placeholder="Masukkan NPM" required>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" name="password" id="password" placeholder="Masukkan password" required>
    </div>
    <button type="submit" class="btn-submit">MASUK</button>
  </form>

  <a href="index.php" class="back-link">&larr; Kembali ke Beranda</a>
</div>

</body>
</html>
