<?php
// Get current language from URL
$currentLang = isset($_GET['lang']) ? $_GET['lang'] : 'th';
$langParam = $currentLang !== 'th' ? '?lang=' . $currentLang : '';
// Get current page directory name
$currentPage = basename(dirname($_SERVER['PHP_SELF']));
?>

<header class="header header-layout1">
  <nav class="navbar navbar-expand-lg sticky-navbar">
    <div class="container-fluid">
      <a class="navbar-brand" href="<?= BASE_PATH ?>/home/<?= $langParam ?>">
        <?php $imgSrc = BASE_PATH . '/assets/images/logo/logo.svg'; ?>
        <img src="<?= $imgSrc ?>" class="logo-light" alt="logo" style="height: 50px;">
        <img src="<?= $imgSrc ?>" class="logo-dark" alt="logo" style="height: 50px;">
      </a>
      <button class="navbar-toggler" type="button">
        <span class="menu-lines"><span></span></span>
      </button>
      <div class="collapse navbar-collapse" id="mainNavigation">
        <ul class="navbar-nav ml-auto">
          <li class="nav__item">
            <a href="../home/<?= $langParam ?>"
              class="nav__item-link <?= $currentPage == 'home' ? 'active' : '' ?>">หน้าหลัก</a>
          </li>
          <li class="nav__item">
            <a href="../projects/<?= $langParam ?>"
              class="nav__item-link <?= $currentPage == 'projects' ? 'active' : '' ?>">โครงการ</a>
          </li>
          <li class="nav__item">
            <a href="../news/<?= $langParam ?>"
              class="nav__item-link <?= $currentPage == 'news' ? 'active' : '' ?>">ข่าวสาร</a>
          </li>
          <li class="nav__item">
            <a href="../step/<?= $langParam ?>"
              class="nav__item-link <?= $currentPage == 'step' ? 'active' : '' ?>">ขั้นตอนการบริการ</a>
          </li>
          <li class="nav__item">
            <a href="../benefits/<?= $langParam ?>"
              class="nav__item-link <?= $currentPage == 'benefits' ? 'active' : '' ?>">สิทธิประโยชน์</a>
          </li>
          <li class="nav__item">
            <a href="../receipts/<?= $langParam ?>"
              class="nav__item-link <?= $currentPage == 'receipts' ? 'active' : '' ?>">ค้นหาใบเสร็จ</a>
          </li>
          <li class="nav__item">
            <a href="../contact/<?= $langParam ?>"
              class="nav__item-link <?= $currentPage == 'contact' ? 'active' : '' ?>">ติดต่อเรา</a>
          </li>
          <li class="nav__item d-flex align-items-center">
            <div class="lang-switch">
              <a href="?lang=th" class="lang-switch__btn active">TH</a>
              <!-- <span class="lang-switch__divider">|</span> -->
              <!-- <a href="?lang=en" class="lang-switch__btn">EN</a> -->
            </div>
          </li>
        </ul>
        <button class="close-mobile-menu d-block d-lg-none"><i class="fas fa-times"></i></button>
      </div>
    </div>
  </nav>
</header>