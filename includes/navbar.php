<?php
$username = $_SESSION['username'] ?? 'Guest';
$fotoProfil = $_SESSION['foto_profil'] ?? null;
?>
<nav class="navbar navbar-custom fixed-top">
  <div class="d-flex align-items-center flex-grow-1 flex-nowrap">
    <button id="sidebarToggle" aria-label="Toggle sidebar" aria-expanded="true" aria-controls="sidebar" title="Toggle sidebar"
      class="btn btn-link text-white p-0 mr-3">
      <i class="fas fa-bars fa-lg"></i>
    </button>
    <a class="navbar-brand font-weight-bold text-truncate" href="/index.php" style="white-space: nowrap;">
      PERPUSTAKAAN POLIWANGI
    </a>
  </div>

  <ul class="navbar-nav ml-auto d-flex align-items-center flex-nowrap">
    <li class="nav-item dropdown">
      <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true"
        aria-expanded="false" tabindex="0">
        <?php if ($fotoProfil && file_exists(__DIR__ . '/../' . $fotoProfil)): ?>
          <img src="/<?= htmlspecialchars($fotoProfil) ?>" alt="Foto Profil" style="width:32px; height:32px; border-radius:50%; object-fit:cover; margin-right:8px;">
        <?php else: ?>
          <i class="fas fa-user-circle fa-lg mr-2"></i>
        <?php endif; ?>
        <?= htmlspecialchars($username) ?>
      </a>
      <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
        <a class="dropdown-item" href="/profile/profile.php"><i class="fas fa-user"></i> Profil</a>
        <div class="dropdown-divider"></div>
        <a class="dropdown-item text-danger" href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </li>
  </ul>
</nav>