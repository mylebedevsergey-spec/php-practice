<?php
/**
 * Главная страница: показывает CAPTCHA, принимает ответ и проверяет его.
 * Код хранится в пользовательской сессии (как требует задание).
 */

session_start();

$result = null;   // 'ok' | 'fail'
$entered = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entered = trim((string) ($_POST['code'] ?? ''));
    $expected = (string) ($_SESSION['captcha_code'] ?? '');

    if ($expected !== '' && strtoupper($entered) === $expected) {
        $result = 'ok';
    } else {
        $result = 'fail';
    }

    // одноразовость: следующий запрос картинки создаст новый код
    unset($_SESSION['captcha_code']);
}

$attempts = (int) ($_SESSION['attempts'] ?? 0) + ($result === 'fail' ? 1 : 0);
$_SESSION['attempts'] = $attempts;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Проверка CAPTCHA</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<main class="card">
    <h1>Введите код с картинки</h1>
    <p class="hint">Пять символов, регистр не важен. Код хранится в сессии и одноразовый.</p>

    <?php if ($result === 'ok'): ?>
        <div class="alert alert--ok">Код верный, проверка пройдена.</div>
    <?php elseif ($result === 'fail'): ?>
        <div class="alert alert--fail">Код неверный. Попробуйте ещё раз.</div>
    <?php endif; ?>

    <img class="captcha" src="captcha.php?<?= htmlspecialchars(time()) ?>" alt="CAPTCHA" width="240" height="80">

    <form method="post" action="index.php">
        <label class="field">
            <span>Символы с картинки</span>
            <input type="text" name="code" maxlength="5" autocomplete="off" required
                   value="<?= htmlspecialchars($entered) ?>" placeholder="ABCDE">
        </label>
        <button type="submit">Проверить</button>
    </form>

    <p class="meta">Неудачных попыток: <?= (int) $attempts ?></p>
</main>
</body>
</html>
