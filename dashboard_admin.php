<?php
session_start();
require_once 'koneksi.php';

// =========================================================
// PROTEKSI HALAMAN: HANYA ADMIN YANG BOLEH MASUK
// =========================================================
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

$menu    = isset($_GET['menu']) ? $_GET['menu'] : 'prodi';
$pesan   = "";
$errorMsg = "";

// =========================================================
// PROSES CRUD: PROGRAM STUDI
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form']) && $_POST['form'] === 'prodi') {
    $kode_prodi = trim($_POST['kode_prodi']);
    $nama_prodi = trim($_POST['nama_prodi']);
    $fakultas   = trim($_POST['fakultas']);

    if (isset($_POST['mode_edit']) && $_POST['mode_edit'] === '1') {
        $stmt = mysqli_prepare($koneksi, "UPDATE program_studi SET nama_prodi=?, fakultas=? WHERE kode_prodi=?");
        mysqli_stmt_bind_param($stmt, "sss", $nama_prodi, $fakultas, $kode_prodi);
        mysqli_stmt_execute($stmt);
        $pesan = "Data program studi berhasil diperbarui.";
    } else {
        $stmt = mysqli_prepare($koneksi, "INSERT INTO program_studi (kode_prodi, nama_prodi, fakultas) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $kode_prodi, $nama_prodi, $fakultas);
        if (mysqli_stmt_execute($stmt)) {
            $pesan = "Program studi baru berhasil ditambahkan.";
        } else {
            $errorMsg = "Gagal menambah data. Kode prodi mungkin sudah dipakai.";
        }
    }
    $menu = 'prodi';
}

if ($menu === 'prodi' && isset($_GET['hapus'])) {
    $kode = $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM program_studi WHERE kode_prodi=?");
    mysqli_stmt_bind_param($stmt, "s", $kode);
    if (mysqli_stmt_execute($stmt)) {
        $pesan = "Program studi berhasil dihapus.";
    } else {
        $errorMsg = "Gagal menghapus. Pastikan tidak ada mahasiswa/pengguna yang masih terhubung ke prodi ini.";
    }
}

// =========================================================
// PROSES CRUD: MAHASISWA
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form']) && $_POST['form'] === 'mahasiswa') {
    $npm            = trim($_POST['npm']);
    $nama            = trim($_POST['nama_mahasiswa']);
    $jenis_kelamin   = $_POST['jenis_kelamin'];
    $tempat_lahir    = trim($_POST['tempat_lahir']);
    $tanggal_lahir   = $_POST['tanggal_lahir'];
    $tanggal_masuk   = $_POST['tanggal_masuk'];
    $alamat          = trim($_POST['alamat']);
    $kode_prodi      = $_POST['kode_prodi'];
    $password_input  = $_POST['password'];

    if (isset($_POST['mode_edit']) && $_POST['mode_edit'] === '1') {
        if ($password_input !== "") {
            $passHash = password_hash($password_input, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi,
                "UPDATE mahasiswa SET nama_mahasiswa=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?, tanggal_masuk=?, alamat=?, kode_prodi=?, password=? WHERE npm=?"
            );
            mysqli_stmt_bind_param($stmt, "sssssssss", $nama, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $tanggal_masuk, $alamat, $kode_prodi, $passHash, $npm);
        } else {
            $stmt = mysqli_prepare($koneksi,
                "UPDATE mahasiswa SET nama_mahasiswa=?, jenis_kelamin=?, tempat_lahir=?, tanggal_lahir=?, tanggal_masuk=?, alamat=?, kode_prodi=? WHERE npm=?"
            );
            mysqli_stmt_bind_param($stmt, "ssssssss", $nama, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $tanggal_masuk, $alamat, $kode_prodi, $npm);
        }
        mysqli_stmt_execute($stmt);
        $pesan = "Data mahasiswa berhasil diperbarui.";
    } else {
        $passHash = password_hash($password_input, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO mahasiswa (npm, nama_mahasiswa, jenis_kelamin, tempat_lahir, tanggal_lahir, tanggal_masuk, alamat, password, kode_prodi) VALUES (?,?,?,?,?,?,?,?,?)"
        );
        mysqli_stmt_bind_param($stmt, "sssssssss", $npm, $nama, $jenis_kelamin, $tempat_lahir, $tanggal_lahir, $tanggal_masuk, $alamat, $passHash, $kode_prodi);
        if (mysqli_stmt_execute($stmt)) {
            $pesan = "Data mahasiswa baru berhasil ditambahkan.";
        } else {
            $errorMsg = "Gagal menambah data. NPM mungkin sudah terdaftar.";
        }
    }
    $menu = 'mahasiswa';
}

if ($menu === 'mahasiswa' && isset($_GET['hapus'])) {
    $npm = $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM mahasiswa WHERE npm=?");
    mysqli_stmt_bind_param($stmt, "s", $npm);
    mysqli_stmt_execute($stmt);
    $pesan = "Data mahasiswa berhasil dihapus.";
}

// =========================================================
// PROSES CRUD: PENGGUNA
// =========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form']) && $_POST['form'] === 'pengguna') {
    $username   = trim($_POST['username']);
    $kode_role  = $_POST['kode_role'];
    $kode_prodi = ($_POST['kode_prodi'] === "") ? null : $_POST['kode_prodi'];
    $password_input = $_POST['password'];

    if (isset($_POST['mode_edit']) && $_POST['mode_edit'] === '1') {
        $id = $_POST['id_pengguna'];
        if ($password_input !== "") {
            $passHash = password_hash($password_input, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($koneksi, "UPDATE pengguna SET username=?, password=?, kode_role=?, kode_prodi=? WHERE id_pengguna=?");
            mysqli_stmt_bind_param($stmt, "ssisi", $username, $passHash, $kode_role, $kode_prodi, $id);
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE pengguna SET username=?, kode_role=?, kode_prodi=? WHERE id_pengguna=?");
            mysqli_stmt_bind_param($stmt, "sisi", $username, $kode_role, $kode_prodi, $id);
        }
        mysqli_stmt_execute($stmt);
        $pesan = "Data pengguna berhasil diperbarui.";
    } else {
        $passHash = password_hash($password_input, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($koneksi, "INSERT INTO pengguna (username, password, kode_role, kode_prodi) VALUES (?,?,?,?)");
        mysqli_stmt_bind_param($stmt, "ssis", $username, $passHash, $kode_role, $kode_prodi);
        if (mysqli_stmt_execute($stmt)) {
            $pesan = "Pengguna baru berhasil ditambahkan.";
        } else {
            $errorMsg = "Gagal menambah pengguna. Username mungkin sudah dipakai.";
        }
    }
    $menu = 'pengguna';
}

if ($menu === 'pengguna' && isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    $stmt = mysqli_prepare($koneksi, "DELETE FROM pengguna WHERE id_pengguna=?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $pesan = "Pengguna berhasil dihapus.";
}

// =========================================================
// AMBIL DATA UNTUK DITAMPILKAN
// =========================================================
$daftarProdi = mysqli_query($koneksi, "SELECT * FROM program_studi ORDER BY kode_prodi");

$dataEditProdi = null;
if ($menu === 'prodi' && isset($_GET['edit'])) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM program_studi WHERE kode_prodi=?");
    mysqli_stmt_bind_param($stmt, "s", $_GET['edit']);
    mysqli_stmt_execute($stmt);
    $dataEditProdi = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$daftarMahasiswa = mysqli_query($koneksi,
    "SELECT m.*, p.nama_prodi FROM mahasiswa m JOIN program_studi p ON m.kode_prodi = p.kode_prodi ORDER BY m.nama_mahasiswa"
);

$dataEditMahasiswa = null;
if ($menu === 'mahasiswa' && isset($_GET['edit'])) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM mahasiswa WHERE npm=?");
    mysqli_stmt_bind_param($stmt, "s", $_GET['edit']);
    mysqli_stmt_execute($stmt);
    $dataEditMahasiswa = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$daftarPengguna = mysqli_query($koneksi,
    "SELECT u.*, r.nama_role, p.nama_prodi FROM pengguna u
     JOIN role r ON u.kode_role = r.kode_role
     LEFT JOIN program_studi p ON u.kode_prodi = p.kode_prodi
     ORDER BY u.id_pengguna"
);

$dataEditPengguna = null;
if ($menu === 'pengguna' && isset($_GET['edit'])) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM pengguna WHERE id_pengguna=?");
    mysqli_stmt_bind_param($stmt, "i", $_GET['edit']);
    mysqli_stmt_execute($stmt);
    $dataEditPengguna = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$daftarRole = mysqli_query($koneksi, "SELECT * FROM role");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - SIM Mahasiswa UMB</title>
<style>
  :root {
    --primary: #1d61e7;
    --primary-hover: #154ec2;
    --primary-light: #eff5ff;
    --text-dark: #1e293b;
    --text-muted: #64748b;
    --bg-light: #f8fafc;
    --border-color: #e2e8f0;
    --danger: #dc2626;
  }
  * { margin:0; padding:0; box-sizing:border-box; font-family: Arial, Helvetica, sans-serif; }
  body { background: var(--bg-light); color: var(--text-dark); }

  .layout { display:flex; min-height:100vh; }

  /* SIDEBAR */
  .sidebar {
    width: 240px;
    background: #0f172a;
    color: #e2e8f0;
    padding: 1.5rem 1rem;
    flex-shrink: 0;
  }
  .sidebar h2 { font-size: 1rem; margin-bottom: 0.2rem; }
  .sidebar .sub { font-size: 0.72rem; color: #94a3b8; margin-bottom: 2rem; }
  .sidebar a {
    display: block;
    color: #cbd5e1;
    text-decoration: none;
    padding: 0.7rem 0.8rem;
    border-radius: 8px;
    margin-bottom: 0.3rem;
    font-size: 0.9rem;
    font-weight: 600;
  }
  .sidebar a:hover, .sidebar a.active { background: var(--primary); color: #fff; }
  .sidebar .logout { color: #fca5a5; margin-top: 2rem; }

  /* MAIN CONTENT */
  .content { flex:1; padding: 2rem; overflow-x:auto; }

  .topbar { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; }
  .topbar h1 { font-size:1.4rem; }
  .topbar .user-info { font-size:0.85rem; color: var(--text-muted); }

  .alert {
    padding: 0.8rem 1rem; border-radius:8px; font-size:0.88rem; margin-bottom:1.2rem;
  }
  .alert-success { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
  .alert-error { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }

  .card {
    background:#fff; border:1px solid var(--border-color); border-radius:12px;
    padding:1.5rem; margin-bottom:1.5rem;
  }
  .card h3 { margin-bottom:1rem; font-size:1.05rem; }

  form.inline-form { display:grid; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); gap:0.9rem; align-items:end; }
  .form-group { display:flex; flex-direction:column; }
  .form-group label { font-size:0.78rem; font-weight:600; margin-bottom:0.3rem; }
  .form-group input, .form-group select, .form-group textarea {
    padding:0.6rem 0.7rem; border:1.5px solid var(--border-color); border-radius:8px; font-size:0.88rem;
  }
  .btn {
    padding:0.65rem 1.2rem; border:none; border-radius:8px; font-weight:700; font-size:0.85rem; cursor:pointer;
  }
  .btn-primary { background:var(--primary); color:#fff; }
  .btn-primary:hover { background:var(--primary-hover); }
  .btn-secondary { background:#e2e8f0; color:var(--text-dark); text-decoration:none; display:inline-block; text-align:center; }

  table { width:100%; border-collapse:collapse; font-size:0.85rem; }
  th, td { text-align:left; padding:0.6rem 0.7rem; border-bottom:1px solid var(--border-color); white-space:nowrap; }
  th { background:#f1f5f9; font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); }

  .action-link { font-size:0.8rem; font-weight:700; margin-right:0.6rem; }
  .action-edit { color: var(--primary); }
  .action-hapus { color: var(--danger); }
</style>
</head>
<body>

<div class="layout">
  <aside class="sidebar">
    <h2>SIM Mahasiswa</h2>
    <div class="sub">Panel Admin</div>

    <a href="dashboard_admin.php?menu=prodi" class="<?php echo $menu === 'prodi' ? 'active' : ''; ?>">Program Studi</a>
    <a href="dashboard_admin.php?menu=mahasiswa" class="<?php echo $menu === 'mahasiswa' ? 'active' : ''; ?>">Mahasiswa</a>
    <a href="dashboard_admin.php?menu=pengguna" class="<?php echo $menu === 'pengguna' ? 'active' : ''; ?>">Pengguna</a>
    <a href="logout.php" class="logout">Keluar</a>
  </aside>

  <main class="content">
    <div class="topbar">
      <h1>
        <?php
          if ($menu === 'prodi') echo "Kelola Program Studi";
          elseif ($menu === 'mahasiswa') echo "Kelola Mahasiswa";
          elseif ($menu === 'pengguna') echo "Kelola Pengguna";
        ?>
      </h1>
      <div class="user-info">Masuk sebagai <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (Admin)</div>
    </div>

    <?php if ($pesan !== "") { ?><div class="alert alert-success"><?php echo htmlspecialchars($pesan); ?></div><?php } ?>
    <?php if ($errorMsg !== "") { ?><div class="alert alert-error"><?php echo htmlspecialchars($errorMsg); ?></div><?php } ?>

    <?php if ($menu === 'prodi') { ?>
      <div class="card">
        <h3><?php echo $dataEditProdi ? "Edit Program Studi" : "Tambah Program Studi"; ?></h3>
        <form method="POST" action="dashboard_admin.php?menu=prodi" class="inline-form">
          <input type="hidden" name="form" value="prodi">
          <?php if ($dataEditProdi) { ?><input type="hidden" name="mode_edit" value="1"><?php } ?>

          <div class="form-group">
            <label>Kode Prodi</label>
            <input type="text" name="kode_prodi" required maxlength="10"
                   value="<?php echo $dataEditProdi ? htmlspecialchars($dataEditProdi['kode_prodi']) : ''; ?>"
                   <?php echo $dataEditProdi ? 'readonly' : ''; ?>>
          </div>
          <div class="form-group">
            <label>Nama Program Studi</label>
            <input type="text" name="nama_prodi" required value="<?php echo $dataEditProdi ? htmlspecialchars($dataEditProdi['nama_prodi']) : ''; ?>">
          </div>
          <div class="form-group">
            <label>Fakultas</label>
            <input type="text" name="fakultas" required value="<?php echo $dataEditProdi ? htmlspecialchars($dataEditProdi['fakultas']) : ''; ?>">
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-primary"><?php echo $dataEditProdi ? 'Simpan Perubahan' : 'Tambah'; ?></button>
          </div>
        </form>
      </div>

      <div class="card">
        <h3>Daftar Program Studi</h3>
        <table>
          <tr><th>Kode</th><th>Nama Prodi</th><th>Fakultas</th><th>Aksi</th></tr>
          <?php while ($row = mysqli_fetch_assoc($daftarProdi)) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['kode_prodi']); ?></td>
            <td><?php echo htmlspecialchars($row['nama_prodi']); ?></td>
            <td><?php echo htmlspecialchars($row['fakultas']); ?></td>
            <td>
              <a class="action-link action-edit" href="dashboard_admin.php?menu=prodi&edit=<?php echo urlencode($row['kode_prodi']); ?>">Edit</a>
              <a class="action-link action-hapus" href="dashboard_admin.php?menu=prodi&hapus=<?php echo urlencode($row['kode_prodi']); ?>" onclick="return confirm('Yakin hapus prodi ini?');">Hapus</a>
            </td>
          </tr>
          <?php } ?>
        </table>
      </div>
    <?php } ?>

    <?php if ($menu === 'mahasiswa') {
      mysqli_data_seek($daftarProdi, 0);
    ?>
      <div class="card">
        <h3><?php echo $dataEditMahasiswa ? "Edit Mahasiswa" : "Tambah Mahasiswa"; ?></h3>
        <form method="POST" action="dashboard_admin.php?menu=mahasiswa" class="inline-form">
          <input type="hidden" name="form" value="mahasiswa">
          <?php if ($dataEditMahasiswa) { ?><input type="hidden" name="mode_edit" value="1"><?php } ?>

          <div class="form-group">
            <label>NPM</label>
            <input type="text" name="npm" required
                   value="<?php echo $dataEditMahasiswa ? htmlspecialchars($dataEditMahasiswa['npm']) : ''; ?>"
                   <?php echo $dataEditMahasiswa ? 'readonly' : ''; ?>>
          </div>
          <div class="form-group">
            <label>Nama Mahasiswa</label>
            <input type="text" name="nama_mahasiswa" required value="<?php echo $dataEditMahasiswa ? htmlspecialchars($dataEditMahasiswa['nama_mahasiswa']) : ''; ?>">
          </div>
          <div class="form-group">
            <label>Jenis Kelamin</label>
            <select name="jenis_kelamin">
              <?php $jk = $dataEditMahasiswa ? $dataEditMahasiswa['jenis_kelamin'] : ''; ?>
              <option value="Laki-laki" <?php echo $jk === 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
              <option value="Perempuan" <?php echo $jk === 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tempat Lahir</label>
            <input type="text" name="tempat_lahir" value="<?php echo $dataEditMahasiswa ? htmlspecialchars($dataEditMahasiswa['tempat_lahir']) : ''; ?>">
          </div>
          <div class="form-group">
            <label>Tanggal Lahir</label>
            <input type="date" name="tanggal_lahir" value="<?php echo $dataEditMahasiswa ? htmlspecialchars($dataEditMahasiswa['tanggal_lahir']) : ''; ?>">
          </div>
          <div class="form-group">
            <label>Tanggal Masuk</label>
            <input type="date" name="tanggal_masuk" value="<?php echo $dataEditMahasiswa ? htmlspecialchars($dataEditMahasiswa['tanggal_masuk']) : ''; ?>">
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <input type="text" name="alamat" value="<?php echo $dataEditMahasiswa ? htmlspecialchars($dataEditMahasiswa['alamat']) : ''; ?>">
          </div>
          <div class="form-group">
            <label>Program Studi</label>
            <select name="kode_prodi" required>
              <?php while ($p = mysqli_fetch_assoc($daftarProdi)) {
                $selected = ($dataEditMahasiswa && $dataEditMahasiswa['kode_prodi'] === $p['kode_prodi']) ? 'selected' : '';
              ?>
              <option value="<?php echo htmlspecialchars($p['kode_prodi']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($p['nama_prodi']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <label>Password <?php echo $dataEditMahasiswa ? '(kosongkan jika tidak diubah)' : ''; ?></label>
            <input type="password" name="password" <?php echo $dataEditMahasiswa ? '' : 'required'; ?>>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-primary"><?php echo $dataEditMahasiswa ? 'Simpan Perubahan' : 'Tambah'; ?></button>
          </div>
        </form>
      </div>

      <div class="card">
        <h3>Daftar Mahasiswa</h3>
        <table>
          <tr><th>NPM</th><th>Nama</th><th>JK</th><th>Prodi</th><th>Tgl Masuk</th><th>Aksi</th></tr>
          <?php while ($row = mysqli_fetch_assoc($daftarMahasiswa)) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['npm']); ?></td>
            <td><?php echo htmlspecialchars($row['nama_mahasiswa']); ?></td>
            <td><?php echo htmlspecialchars($row['jenis_kelamin']); ?></td>
            <td><?php echo htmlspecialchars($row['nama_prodi']); ?></td>
            <td><?php echo htmlspecialchars($row['tanggal_masuk']); ?></td>
            <td>
              <a class="action-link action-edit" href="dashboard_admin.php?menu=mahasiswa&edit=<?php echo urlencode($row['npm']); ?>">Edit</a>
              <a class="action-link action-hapus" href="dashboard_admin.php?menu=mahasiswa&hapus=<?php echo urlencode($row['npm']); ?>" onclick="return confirm('Yakin hapus data mahasiswa ini?');">Hapus</a>
            </td>
          </tr>
          <?php } ?>
        </table>
      </div>
    <?php } ?>

    <?php if ($menu === 'pengguna') {
      mysqli_data_seek($daftarProdi, 0);
    ?>
      <div class="card">
        <h3><?php echo $dataEditPengguna ? "Edit Pengguna" : "Tambah Pengguna"; ?></h3>
        <form method="POST" action="dashboard_admin.php?menu=pengguna" class="inline-form">
          <input type="hidden" name="form" value="pengguna">
          <?php if ($dataEditPengguna) { ?>
            <input type="hidden" name="mode_edit" value="1">
            <input type="hidden" name="id_pengguna" value="<?php echo $dataEditPengguna['id_pengguna']; ?>">
          <?php } ?>

          <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required value="<?php echo $dataEditPengguna ? htmlspecialchars($dataEditPengguna['username']) : ''; ?>">
          </div>
          <div class="form-group">
            <label>Password <?php echo $dataEditPengguna ? '(kosongkan jika tidak diubah)' : ''; ?></label>
            <input type="password" name="password" <?php echo $dataEditPengguna ? '' : 'required'; ?>>
          </div>
          <div class="form-group">
            <label>Role</label>
            <select name="kode_role" id="kode_role" onchange="toggleProdiField()">
              <?php
                mysqli_data_seek($daftarRole, 0);
                while ($r = mysqli_fetch_assoc($daftarRole)) {
                  $selected = ($dataEditPengguna && $dataEditPengguna['kode_role'] == $r['kode_role']) ? 'selected' : '';
              ?>
              <option value="<?php echo $r['kode_role']; ?>" data-nama="<?php echo htmlspecialchars($r['nama_role']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($r['nama_role']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group" id="prodiField">
            <label>Program Studi (khusus role Prodi)</label>
            <select name="kode_prodi">
              <option value="">- Tidak terikat prodi -</option>
              <?php while ($p = mysqli_fetch_assoc($daftarProdi)) {
                $selected = ($dataEditPengguna && $dataEditPengguna['kode_prodi'] === $p['kode_prodi']) ? 'selected' : '';
              ?>
              <option value="<?php echo htmlspecialchars($p['kode_prodi']); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($p['nama_prodi']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-primary"><?php echo $dataEditPengguna ? 'Simpan Perubahan' : 'Tambah'; ?></button>
          </div>
        </form>
      </div>

      <div class="card">
        <h3>Daftar Pengguna</h3>
        <table>
          <tr><th>Username</th><th>Role</th><th>Prodi</th><th>Aksi</th></tr>
          <?php while ($row = mysqli_fetch_assoc($daftarPengguna)) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['nama_role']); ?></td>
            <td><?php echo $row['nama_prodi'] ? htmlspecialchars($row['nama_prodi']) : '-'; ?></td>
            <td>
              <a class="action-link action-edit" href="dashboard_admin.php?menu=pengguna&edit=<?php echo $row['id_pengguna']; ?>">Edit</a>
              <a class="action-link action-hapus" href="dashboard_admin.php?menu=pengguna&hapus=<?php echo $row['id_pengguna']; ?>" onclick="return confirm('Yakin hapus pengguna ini?');">Hapus</a>
            </td>
          </tr>
          <?php } ?>
        </table>
      </div>
    <?php } ?>

  </main>
</div>

<script>
  function toggleProdiField() {
    const select = document.getElementById('kode_role');
    const prodiField = document.getElementById('prodiField');
    const roleName = select.options[select.selectedIndex].getAttribute('data-nama');
    prodiField.style.display = (roleName === 'Prodi') ? 'flex' : 'none';
  }
  window.addEventListener('DOMContentLoaded', toggleProdiField);
</script>

</body>
</html>
