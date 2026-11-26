<?php /** @var \App\Core\View $this */ ?>
<div class="text-center py-5">
  <p class="display-3 fw-bold mb-2"><?= $this->e($code) ?></p>
  <h1 class="h3"><?= $this->e($heading ?? $title) ?></h1>
  <p class="text-muted"><?= $this->e($message) ?></p>
  <a class="btn btn-primary" href="<?= $this->url('/') ?>">Ба саҳифаи асосӣ</a>
</div>
