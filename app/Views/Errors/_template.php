<?php
require ROOT_PATH . '/app/Views/layouts/header.php';
?>
<main class="d-flex align-items-center justify-content-center min-vh-100 px-3 py-5" style="background:linear-gradient(135deg,#f8f3f6 0%,#fff 55%,#f2e9ef 100%);">
  <section class="text-center" style="max-width:620px;">
    <div class="mb-4" style="font-size:clamp(5rem,18vw,9rem);font-weight:700;line-height:.9;color:#9e1f63;letter-spacing:0;">
      <?= e((string)$codigo) ?>
    </div>
    <p class="text-uppercase fw-semibold mb-2" style="letter-spacing:.08em;color:#6f1746;">
      <?= e($etiqueta) ?>
    </p>
    <h1 class="h2 fw-bold mb-3"><?= e($titulo) ?></h1>
    <p class="text-secondary mb-4"><?= e($mensaje) ?></p>
    <a class="btn btn-primary px-4" href="<?= e($enlace) ?>">
      <?= e($textoEnlace) ?>
    </a>
  </section>
</main>
<?php require ROOT_PATH . '/app/Views/layouts/footer.php'; ?>
