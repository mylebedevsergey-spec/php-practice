<?php
/** Каталог книжного магазина: фильтр по жанру, поиск, добавление в корзину. */
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    $id = (int) $_POST['add'];
    if (book_by_id($id)) {
        $_SESSION['cart'][$id] = (int) ($_SESSION['cart'][$id] ?? 0) + 1;
    }
    header('Location: cart.php');
    exit;
}

$genre = trim((string) ($_GET['genre'] ?? ''));
$query = trim((string) ($_GET['q'] ?? ''));
$books = catalog($genre, $query);
$cartCount = array_sum(cart());
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Книжный магазин</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="logo" href="index.php">📚 Книжный</a>
    <nav class="filters">
        <a class="<?= $genre === '' ? 'is-active' : '' ?>" href="index.php">Все</a>
        <?php foreach (genres() as $item): ?>
            <a class="<?= $genre === $item ? 'is-active' : '' ?>"
               href="index.php?genre=<?= urlencode($item) ?>"><?= e($item) ?></a>
        <?php endforeach; ?>
    </nav>
    <a class="cart-link" href="cart.php">Корзина (<?= (int) $cartCount ?>)</a>
</header>

<main class="wrap">
    <form class="search" method="get" action="index.php">
        <?php if ($genre !== ''): ?><input type="hidden" name="genre" value="<?= e($genre) ?>"><?php endif; ?>
        <input type="search" name="q" value="<?= e($query) ?>" placeholder="Поиск по названию или автору">
        <button type="submit">Найти</button>
    </form>

    <p class="meta">Найдено книг: <?= count($books) ?></p>

    <section class="grid">
        <?php foreach ($books as $book): ?>
            <article class="card">
                <span class="genre"><?= e($book['genre']) ?></span>
                <h2><a href="book.php?id=<?= (int) $book['id'] ?>"><?= e($book['title']) ?></a></h2>
                <p class="author"><?= e($book['author']) ?>, <?= (int) $book['year'] ?></p>
                <p class="desc"><?= e($book['description']) ?></p>
                <div class="row">
                    <strong><?= money((int) $book['price']) ?></strong>
                    <form method="post" action="index.php">
                        <input type="hidden" name="add" value="<?= (int) $book['id'] ?>">
                        <button type="submit">В корзину</button>
                    </form>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$books): ?>
            <p class="empty">Ничего не нашлось. Попробуйте другой запрос.</p>
        <?php endif; ?>
    </section>
</main>
</body>
</html>
