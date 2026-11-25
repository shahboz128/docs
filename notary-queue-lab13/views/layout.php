<?php /** @var View $this */ $active = $active ?? ''; ?>
<!doctype html>
<html lang="tg">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $this->e($title ?? 'Нотариат · Навбат') ?> — Нотариат · Навбат</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
  <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body>
<a class="visually-hidden-focusable" href="#content">Ба мундариҷа гузаштан</a>
<header>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar" aria-label="Менюи асосӣ">
  <div class="container">
    <a class="navbar-brand" href="/">Нотариат · Навбат</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
            aria-controls="mainNav" aria-expanded="false" aria-label="Кушодани меню">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
<?php foreach (['home' => ['/', 'Асосӣ'], 'tickets' => ['/tickets', 'Талонҳо'], 'services' => ['/services', 'Хизматҳо'], 'reports' => ['/reports/queue', 'Ҳисобот']] as $key => [$href, $label]): ?>
        <li class="nav-item"><a class="nav-link<?= $active === $key ? ' active' : '' ?>"<?= $active === $key ? ' aria-current="page"' : '' ?> href="<?= $href ?>"><?= $label ?></a></li>
<?php endforeach; ?>
      </ul>
      <div class="d-flex align-items-center gap-2 text-white">
        <span>Маъмур</span><span class="badge role-badge">admin</span>
      </div>
    </div>
  </div>
</nav>
</header>
<main class="container py-4" id="content">
<?= $this->partial('notice', ['msg' => $msg ?? '']) ?>
<?= $content ?>
</main>
<?= $this->partial('confirm-modal') ?>
<footer class="app-footer container py-4 text-center">
  <p class="mb-0">© 2026 Навбати электронии идораи нотариалӣ</p>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script type="module" src="/assets/js/app.js"></script>
</body>
</html>
