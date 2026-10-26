<?php
/** Страница книги. */
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

$book = book_by_id((int) ($_GET['id'] ?? 0));
if (!$book) {
    http_response_code(404);
    echo 'Книга не найдена. <a href="index.php">В каталог</a>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['cart'][$book['id']] = (int) ($_SESSION['cart'][$book['id']] ?? 0) + 1;
    header('Location: cart.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title><?= e($book['title']) ?>, Книжный</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="logo" href="index.php">📚 Книжный</a>
    <a class="cart-link" href="cart.php">Корзина (<?= (int) array_sum(cart()) ?>)</a>
</header>
<main class="wrap">
    <a class="back" href="index.php">← В каталог</a>
    <article class="card card--wide">
        <span class="genre"><?= e($book['genre']) ?></span>
        <h1><?= e($book['title']) ?></h1>
        <p class="author"><?= e($book['author']) ?>, <?= (int) $book['year'] ?></p>
        <p><?= e($book['description']) ?></p>
        <div class="row">
            <strong class="price"><?= money((int) $book['price']) ?></strong>
            <form method="post">
                <button type="submit">Добавить в корзину</button>
            </form>
        </div>
    </article>
</main>
</body>
</html>
