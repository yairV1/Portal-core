<?php
require ROOT_PATH . '/app/Views/layouts/header.php';
$imagenesError = [
  400 => 'errores.400.jpeg',
  403 => 'error.403.jpeg',
  500 => 'error.500.jpeg',
];
$imagenFondo = BASE_URL . '/uploads/mascota/' . ($imagenesError[$codigo] ?? 'errores.400.jpeg');
?>
<main class="error-400-page error-<?= e((string)$codigo) ?>-page" style="--error-400-image:url('<?= e($imagenFondo) ?>');">
  <section class="error-400-content">
    <div class="error-400-code"><?= e((string)$codigo) ?></div>
    <p class="error-400-label"><?= e($etiqueta) ?></p>
    <h1><?= e($titulo) ?></h1>
    <p class="error-400-message"><?= e($mensaje) ?></p>
    <a class="error-400-link" id="errorBackLink" href="<?= e($enlace) ?>"><?= e($textoEnlace) ?></a>
  </section>
</main>
<style>
  .error-400-page {
    min-height: 100vh;
    display: flex;
    align-items: center;
    padding: 3rem clamp(1.5rem, 6vw, 6rem);
    background-color: #d8d4d1;
    background-image: var(--error-400-image);
    background-position: center;
    background-size: cover;
    font-family: Poppins, sans-serif;
  }

  .error-400-content {
    width: min(100%, 34rem);
    color: #111;
  }

  .error-400-code {
    background: linear-gradient(90deg, #a21d68, #e8694e);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    font-size: clamp(6rem, 16vw, 9rem);
    font-weight: 700;
    letter-spacing: 0;
    line-height: .82;
  }

  .error-400-label {
    display: inline-block;
    margin: 1.4rem 0 1.1rem;
    padding: .45rem .9rem;
    border-radius: 999px;
    background: rgba(255, 255, 255, .28);
    color: #fff;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
  }

  .error-400-content h1 {
    margin: 0 0 1rem;
    font-size: clamp(2rem, 4vw, 3rem);
    font-weight: 700;
    line-height: 1.08;
  }

  .error-400-message {
    max-width: 27rem;
    margin-bottom: 2rem;
    color: #3f3b39;
    font-size: 1.05rem;
    line-height: 1.6;
  }

  .error-500-page .error-400-message {
    color: #fff;
  }

  .error-403-page .error-400-message {
    color: #fff;
  }

  .error-400-link {
    display: inline-block;
    padding: .85rem 1.6rem;
    border-radius: 999px;
    background: linear-gradient(90deg, #a21d68, #e8694e);
    color: #fff;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 10px 22px rgba(162, 29, 104, .2);
  }

  @media (max-width: 700px) {
    .error-400-page {
      align-items: flex-start;
      padding-top: 12vh;
      background-position: 63% center;
    }

    .error-400-content {
      max-width: 22rem;
    }
  }
</style>
<script>
  (function () {
    var errorBackLink = document.getElementById('errorBackLink');
    if (!errorBackLink) return;

    errorBackLink.addEventListener('click', function (evento) {
      if (window.history.length > 1) {
        evento.preventDefault();
        window.history.back();
      }
    });
  })();
</script>
<?php require ROOT_PATH . '/app/Views/layouts/footer.php'; ?>
