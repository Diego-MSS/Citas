<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'App de Citas' ?></title>
  <link rel="stylesheet" href="/assets/css/bootstrap.min.css">
</head>
<body>
  <?php include __DIR__ . '/navbar.php'; ?>

  <main class="container py-4">
    <?= $content ?? '' ?> 
  </main>

  <?php include __DIR__ . '/footer.php'; ?>
</body>
</html>