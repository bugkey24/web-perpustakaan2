<?php
$role = $_SESSION['role'] ?? 'anggota';

// Mendapatkan path relatif dari URL setelah domain, misal "/user/index.php"
$currentPath = $_SERVER['REQUEST_URI'];

// Fungsi sederhana untuk cek apakah path aktif
function isActive($path, $currentPath)
{
  // Jika path sama persis, atau path adalah prefix dari currentPath (untuk folder)
  if ($path === $currentPath) return true;
  // Contoh: jika path adalah "/user/" dan currentPath "/user/index.php"
  if (str_starts_with($currentPath, rtrim($path, '/') . '/')) return true;
  return false;
}
?>
<aside class="sidebar" id="sidebar" role="navigation" aria-label="Sidebar navigation">
  <nav class="nav flex-column mt-3">
    <a href="/index.php" class="nav-link <?= isActive('/index.php', $currentPath) ? 'active' : '' ?>">
      <i class="fas fa-home"></i> Home
    </a>
    <?php if ($role === 'pustakawan'): ?>
      <a href="/bibliografi_kategori/index.php" class="nav-link <?= isActive('/bibliografi_kategori/index.php', $currentPath) ? 'active' : '' ?>">
        <i class="fas fa-th-list"></i> Kategori Bibliografi
      </a>
      <a href="/bibliografi/index.php" class="nav-link <?= isActive('/bibliografi/index.php', $currentPath) ? 'active' : '' ?>">
        <i class="fas fa-book"></i> Bibliografi
      </a>
      <a href="/koleksi/index.php" class="nav-link <?= isActive('/koleksi/index.php', $currentPath) ? 'active' : '' ?>">
        <i class="fas fa-archive"></i> Koleksi
      </a>
      <a href="/user/index.php" class="nav-link <?= isActive('/user/index.php', $currentPath) ? 'active' : '' ?>">
        <i class="fas fa-users"></i> Anggota
      </a>
      <a href="/peminjaman/index.php" class="nav-link <?= isActive('/peminjaman/index.php', $currentPath) ? 'active' : '' ?>">
        <i class="fas fa-book-reader"></i> Peminjaman
      </a>
    <?php else: ?>
      <a href="/koleksi/index.php" class="nav-link <?= isActive('/koleksi/index.php', $currentPath) ? 'active' : '' ?>">
        <i class="fas fa-archive"></i> Koleksi
      </a>
      <a href="/peminjaman/anggota.php" class="nav-link <?= isActive('/peminjaman/anggota.php', $currentPath) ? 'active' : '' ?>">
        <i class="fas fa-book-reader"></i> Peminjaman
      </a>
    <?php endif; ?>
  </nav>
</aside>