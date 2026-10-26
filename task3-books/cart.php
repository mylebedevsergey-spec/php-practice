<?php
/** Корзина: изменение количества, удаление, оформление заказа. */
declare(strict_types=1);
session_start();
require __DIR__ . '/db.php';

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remove'])) {
        unset($_SESSION['cart'][(int) $_POST['remove']]);
        header('Location: cart.php');
        exit;
    }
    if (isset($_POST['qty'], $_POST['id'])) {
        $id = (int) $_POST['id'];
        $qty = max(0, (int) $_POST['qty']);
        if ($qty === 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id] = $qty;
        }
        header('Location: cart.php');
        exit;
    }
    if (isset($_POST['checkout'])) {
        $customer = trim((string) ($_POST['customer'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $items = cart();

        if ($customer === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Укажите имя и корректный e-mail.';
        } elseif (!$items) {
            $message = 'Корзина пуста.';
        } else {
            $pdo = db();
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('INSERT INTO orders (customer, email, total, created_at) VALUES (?, ?, ?, ?)');
            $stmt->execute([$customer, $email, cart_total(), date('c')]);
            $orderId = (int) $pdo->lastInsertId();

            $itemStmt = $pdo->prepare('INSERT INTO order_items (order_id, book_id, quantity, price) VALUES (?, ?, ?, ?)');
            foreach ($items as $id => $qty) {
                $book = book_by_id((int) $id);
                if ($book) {
                    $itemStmt->execute([$orderId, $book['id'], (int) $qty, (int) $book['price']]);
                }
            }
            $pdo->commit();

            $_SESSION['cart'] = [];
            $message = 'Заказ №' . $orderId . ' оформлен. Спасибо!';
        }
    }
}

$items = [];
$total = 0;
foreach (cart() as $id => $qty) {
    $book = book_by_id((int) $id);
    if ($book) {
        $book['quantity'] = (int) $qty;
        $book['sum'] = (int) $book['price'] * (int) $qty;
        $total += $book['sum'];
        $items[] = $book;
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Корзина, Книжный</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="logo" href="index.php">📚 Книжный</a>
    <a class="cart-link" href="cart.php">Корзина (<?= (int) array_sum(cart()) ?>)</a>
</header>
<main class="wrap">
    <h1>Корзина</h1>
    <?php if ($message): ?><div class="alert"><?= e($message) ?></div><?php endif; ?>

    <?php if (!$items): ?>
        <p class="empty">Корзина пуста. <a href="index.php">Перейти в каталог</a></p>
    <?php else: ?>
        <table class="cart">
            <thead><tr><th>Книга</th><th>Цена</th><th>Кол-во</th><th>Сумма</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><a href="book.php?id=<?= (int) $item['id'] ?>"><?= e($item['title']) ?></a><br>
                        <small><?= e($item['author']) ?></small></td>
                    <td><?= money((int) $item['price']) ?></td>
                    <td>
                        <form method="post" class="qty">
                            <input type="hidden" name="id" value="<?= (int) $item['id'] ?>">
                            <input type="number" name="qty" min="0" max="99" value="<?= (int) $item['quantity'] ?>">
                            <button type="submit">OK</button>
                        </form>
                    </td>
                    <td><?= money((int) $item['sum']) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="remove" value="<?= (int) $item['id'] ?>">
                            <button class="link" type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr><td colspan="3">Итого</td><td colspan="2"><strong><?= money($total) ?></strong></td></tr></tfoot>
        </table>

        <form class="checkout" method="post">
            <h2>Оформление заказа</h2>
            <label>Имя <input type="text" name="customer" required value="<?= e((string) ($_POST['customer'] ?? '')) ?>"></label>
            <label>E-mail <input type="email" name="email" required value="<?= e((string) ($_POST['email'] ?? '')) ?>"></label>
            <button type="submit" name="checkout" value="1">Оформить заказ</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>
