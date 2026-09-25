<?php
session_start();
require_once 'koneksi.php';

$error = "";

// =========================================================
// PROSES LOGIN
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === "" || $password === "") {
        $error = "Username dan password wajib diisi.";
    } else {
        // Ambil data pengguna beserta nama role-nya
        $stmt = mysqli_prepare($koneksi,
            "SELECT p.id_pengguna, p.username, p.password, p.kode_prodi, r.nama_role
             FROM pengguna p
             JOIN role r ON p.kode_role = r.kode_role
             WHERE p.username = ?"
        );
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user   = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // Catatan: password di database sebaiknya disimpan dengan password_hash().
        // Baris di bawah mendukung hash (password_verify) maupun fallback teks biasa
        // untuk mempermudah masa pengembangan awal.
        $passwordValid = false;
        if ($user) {
            if (password_verify($password, $user['password'])) {
                $passwordValid = true;
            } elseif ($password === $user['password']) {
                $passwordValid = true;
            }
        }

        if ($user && $passwordValid) {
            $_SESSION['id_pengguna'] = $user['id_pengguna'];
            $_SESSION['username']    = $user['username'];
            $_SESSION['role']        = $user['nama_role'];
            $_SESSION['kode_prodi']  = $user['kode_prodi'];

            if ($user['nama_role'] === 'Admin') {
                header("Location: dashboard_admin.php");
                exit;
            } elseif ($user['nama_role'] === 'Prodi') {
                header("Location: dashboard_prodi.php");
                exit;
            } else {
                header("Location: index.php");
                exit;
            }
        } else {
            $error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SIM Mahasiswa Per Program Studi - Universitas Muhammadiyah Bengkulu</title>
<style>
  :root {
    --primary: #1d61e7;
    --primary-hover: #154ec2;
    --primary-light: #eff5ff;
    --text-dark: #1e293b;
    --text-muted: #64748b;
    --bg-light: #f8fafc;
    --border-color: #e2e8f0;
  }

  * { margin: 0; padding: 0; box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; }

  body { color: var(--text-dark); background-color: #ffffff; line-height: 1.6; }

  a { text-decoration: none; color: inherit; }

  /* HEADER */
  header {
    background: #ffffff;
    border-bottom: 1px solid var(--border-color);
    position: sticky;
    top: 0;
    z-index: 100;
  }

  .nav-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .logo-group { display: flex; align-items: center; gap: 0.75rem; }

  .logo-emblem {
    width: 44px;
    height: 44px;
    background: linear-gradient(135deg, #1d61e7, #0b3899);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffd700;
    font-weight: 800;
    font-size: 1.1rem;
  }

  .logo-text h1 { font-size: 1.05rem; font-weight: 800; }
  .logo-text span { font-size: 0.72rem; color: var(--text-muted); font-weight: 600; }

  .btn-login-nav {
    background: var(--primary);
    color: #fff;
    padding: 0.6rem 1.4rem;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
  }

  .btn-login-nav:hover { background: var(--primary-hover); }

  .nav-actions { display: flex; align-items: center; gap: 0.7rem; }

  .btn-login-mhs {
    background: #fff;
    color: var(--primary);
    border: 1.5px solid var(--primary);
    padding: 0.55rem 1.2rem;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.85rem;
    cursor: pointer;
  }
  .btn-login-mhs:hover { background: var(--primary-light); }

  @media (max-width: 640px) {
    .nav-actions { flex-direction: column; align-items: stretch; gap: 0.5rem; }
    .btn-login-mhs, .btn-login-nav { width: 100%; }
  }

  /* HERO */
  .hero-section {
    background: linear-gradient(180deg, #f0f5ff 0%, #ffffff 100%);
    padding: 3.5rem 1.5rem 4rem;
  }

  .hero-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1.1fr 1fr;
    gap: 2.5rem;
    align-items: center;
  }

  .hero-content h2 {
    font-size: 2.4rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 1rem;
  }

  .hero-content h2 span { color: var(--primary); display: block; }

  .hero-description {
    font-size: 1rem;
    color: var(--text-muted);
    margin-bottom: 1.5rem;
    max-width: 520px;
  }

  .hero-tags { display: flex; flex-wrap: wrap; gap: 0.6rem; }

  .hero-tag {
    background: var(--primary-light);
    color: var(--primary);
    font-size: 0.78rem;
    font-weight: 600;
    padding: 0.4rem 0.85rem;
    border-radius: 999px;
  }

  /* LOGIN CARD */
  .login-card {
    background: #fff;
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 2rem;
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
  }

  .login-card h3 { font-size: 1.3rem; font-weight: 800; margin-bottom: 0.25rem; }
  .login-card p.sub { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1.5rem; }

  .form-field-group { margin-bottom: 1.1rem; }

  .form-field-group label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    margin-bottom: 0.4rem;
  }

  .form-field-group input {
    width: 100%;
    padding: 0.75rem 0.9rem;
    border: 1.5px solid var(--border-color);
    border-radius: 8px;
    font-size: 0.92rem;
    outline: none;
  }

  .form-field-group input:focus { border-color: var(--primary); }

  .password-wrapper { position: relative; }

  .password-toggle-btn {
    position: absolute;
    right: 0.8rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.8rem;
    color: var(--text-muted);
  }

  .btn-submit-login {
    width: 100%;
    background: var(--primary);
    color: #fff;
    padding: 0.85rem;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    margin-top: 0.3rem;
  }

  .btn-submit-login:hover { background: var(--primary-hover); }

  .alert-error {
    background: #fef2f2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    padding: 0.7rem 1rem;
    border-radius: 8px;
    font-size: 0.85rem;
    margin-bottom: 1.1rem;
  }

  /* STATS */
  .stats-section {
    background: var(--bg-light);
    padding: 2.5rem 1.5rem;
    border-top: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
  }

  .stats-container {
    max-width: 1200px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.5rem;
    text-align: center;
  }

  .stat-item h3 { font-size: 1.6rem; font-weight: 800; color: var(--primary); }
  .stat-item p { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; }

  /* ABOUT */
  .about-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 3.5rem 1.5rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2.5rem;
    align-items: center;
  }

  .about-section h2 { font-size: 1.8rem; font-weight: 800; margin-bottom: 1rem; }
  .about-section p { color: var(--text-muted); margin-bottom: 0.9rem; }

  .feature-list { list-style: none; margin-top: 1rem; }
  .feature-list li {
    padding: 0.6rem 0;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.92rem;
  }
  .feature-list li::before { content: "✓ "; color: var(--primary); font-weight: 800; }

  /* FOOTER */
  footer {
    background: #0f172a;
    color: #cbd5e1;
    padding: 2rem 1.5rem;
    text-align: center;
    font-size: 0.85rem;
  }

  @media (max-width: 860px) {
    .hero-container, .about-section { grid-template-columns: 1fr; }
    .stats-container { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

<header>
  <div class="nav-container">
    <div class="logo-group">
      <div class="logo-emblem">UMB</div>
      <div class="logo-text">
        <h1>SIM Mahasiswa</h1>
        <span>Universitas Muhammadiyah Bengkulu</span>
      </div>
    </div>
    <div class="nav-actions">
      <a href="login_mahasiswa.php"><button class="btn-login-mhs">Login Mahasiswa</button></a>
      <a href="#login"><button class="btn-login-nav">Login Admin / Prodi</button></a>
    </div>
  </div>
</header>

<section class="hero-section">
  <div class="hero-container">
    <div class="hero-content">
      <h2>Sistem Informasi Manajemen<span>Mahasiswa Per Program Studi</span></h2>
      <p class="hero-description">
        Platform pengelolaan data mahasiswa yang terintegrasi per program studi di lingkungan
        Universitas Muhammadiyah Bengkulu. Memudahkan pengelolaan data mahasiswa, program studi,
        serta hak akses pengguna secara terstruktur dan aman.
      </p>
      <div class="hero-tags">
        <span class="hero-tag">Data Mahasiswa</span>
        <span class="hero-tag">Program Studi</span>
        <span class="hero-tag">Multi Level Akses</span>
        <span class="hero-tag">PHP Native &amp; MySQLi</span>
      </div>
    </div>

    <div class="login-card" id="login">
      <h3>Masuk ke Sistem</h3>
      <p class="sub">Silakan login menggunakan akun yang telah terdaftar.</p>

      <?php if ($error !== "") { ?>
        <div class="alert-error"><?php echo htmlspecialchars($error); ?></div>
      <?php } ?>

      <form method="POST" action="index.php#login">
        <div class="form-field-group">
          <label for="username">Username</label>
          <input type="text" name="username" id="username" placeholder="Masukkan username" required>
        </div>

        <div class="form-field-group">
          <label for="password">Password</label>
          <div class="password-wrapper">
            <input type="password" name="password" id="password" placeholder="Masukkan password" required>
            <button type="button" class="password-toggle-btn" onclick="togglePassword()" id="toggleBtn">Lihat</button>
          </div>
        </div>

        <button type="submit" class="btn-submit-login">MASUK</button>
      </form>
    </div>
  </div>
</section>

<section class="stats-section">
  <div class="stats-container">
    <div class="stat-item">
      <h3 id="statMahasiswa">-</h3>
      <p>Total Mahasiswa</p>
    </div>
    <div class="stat-item">
      <h3 id="statProdi">-</h3>
      <p>Program Studi</p>
    </div>
    <div class="stat-item">
      <h3>2</h3>
      <p>Level Akses (Admin &amp; Prodi)</p>
    </div>
  </div>
</section>

<section class="about-section">
  <div>
    <h2>Tentang Sistem</h2>
    <p>
      Sistem ini dikembangkan untuk membantu pengelolaan data mahasiswa berdasarkan program studi
      masing-masing di Fakultas Teknik Universitas Muhammadiyah Bengkulu.
    </p>
    <p>
      Setiap program studi memiliki akses pengelolaan data mahasiswanya sendiri, sementara admin
      memiliki hak akses penuh terhadap seluruh data program studi dan mahasiswa.
    </p>
  </div>
  <div>
    <ul class="feature-list">
      <li>Pengelolaan data mahasiswa per program studi</li>
      <li>Manajemen data program studi dan fakultas</li>
      <li>Hak akses berjenjang: Admin dan Prodi</li>
      <li>Keamanan login dengan enkripsi password</li>
      <li>Dibangun dengan PHP Native dan MySQLi</li>
    </ul>
  </div>
</section>

<footer>
  &copy; <?php echo date("Y"); ?> Sistem Informasi Manajemen Mahasiswa - Universitas Muhammadiyah Bengkulu.
</footer>

<script>
  function togglePassword() {
    const pwd = document.getElementById('password');
    const btn = document.getElementById('toggleBtn');
    if (pwd.type === 'password') {
      pwd.type = 'text';
      btn.textContent = 'Sembunyikan';
    } else {
      pwd.type = 'password';
      btn.textContent = 'Lihat';
    }
  }
</script>

<script>
  // Ambil statistik jumlah mahasiswa & prodi secara sederhana lewat PHP inline (tanpa AJAX)
  document.getElementById('statMahasiswa').textContent = "<?php
    $r = mysqli_query($koneksi, 'SELECT COUNT(*) AS jml FROM mahasiswa');
    $row = mysqli_fetch_assoc($r);
    echo (int)$row['jml'];
  ?>";
  document.getElementById('statProdi').textContent = "<?php
    $r2 = mysqli_query($koneksi, 'SELECT COUNT(*) AS jml FROM program_studi');
    $row2 = mysqli_fetch_assoc($r2);
    echo (int)$row2['jml'];
  ?>";
</script>

</body>
</html>
