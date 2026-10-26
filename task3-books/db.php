<?php
/**
 * Подключение к SQLite и первичное наполнение каталога книг.
 */

declare(strict_types=1);

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    $pdo = new PDO('sqlite:' . $dir . '/shop.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec('
        CREATE TABLE IF NOT EXISTS books (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            author TEXT NOT NULL,
            genre TEXT NOT NULL,
            price INTEGER NOT NULL,
            year INTEGER NOT NULL,
            description TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS orders (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            customer TEXT NOT NULL,
            email TEXT NOT NULL,
            total INTEGER NOT NULL,
            created_at TEXT NOT NULL
        );
        CREATE TABLE IF NOT EXISTS order_items (
            order_id INTEGER NOT NULL,
            book_id INTEGER NOT NULL,
            quantity INTEGER NOT NULL,
            price INTEGER NOT NULL
        );
    ');

    if ((int) $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn() === 0) {
        seed($pdo);
    }

    return $pdo;
}

function seed(PDO $pdo): void
{
    $books = [
        ['Чистый код', 'Роберт Мартин', 'Программирование', 1490, 2008, 'Практики написания читаемого и поддерживаемого кода.'],
        ['Алгоритмы: построение и анализ', 'Кормен и др.', 'Алгоритмы', 2990, 2013, 'Классический учебник по алгоритмам и структурам данных.'],
        ['Грокаем алгоритмы', 'Адитья Бхаргава', 'Алгоритмы', 1290, 2016, 'Иллюстрированное введение в алгоритмы для начинающих.'],
        ['SQL за 10 минут', 'Бен Форта', 'Базы данных', 990, 2020, 'Быстрый старт в SQL на примерах.'],
        ['PostgreSQL. Основы', 'Лузанов и др.', 'Базы данных', 1790, 2019, 'Администрирование и разработка на PostgreSQL.'],
        ['JavaScript. Подробное руководство', 'Дэвид Флэнаган', 'Веб-разработка', 2490, 2020, 'Полный справочник по современному JavaScript.'],
        ['CSS. Справочник', 'Эрик Мейер', 'Веб-разработка', 1690, 2018, 'Глубокий разбор каскадных таблиц стилей.'],
        ['React в действии', 'Марк Тиленс', 'Веб-разработка', 1990, 2021, 'Практика разработки интерфейсов на React.'],
        ['Чистая архитектура', 'Роберт Мартин', 'Программирование', 1590, 2017, 'Принципы проектирования устойчивых систем.'],
        ['Рефакторинг', 'Мартин Фаулер', 'Программирование', 2190, 2019, 'Улучшение проектирования существующего кода.'],
        ['Тестирование Дот Ком', 'Роман Савин', 'Тестирование', 1190, 2007, 'Практическое руководство по тестированию ПО.'],
        ['Как тестируют в Google', 'Уиттакер и др.', 'Тестирование', 1390, 2014, 'Опыт организации тестирования в Google.'],
    ];

    $stmt = $pdo->prepare('INSERT INTO books (title, author, genre, price, year, description) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($books as $book) {
        $stmt->execute($book);
    }
}

function book_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM books WHERE id = ?');
    $stmt->execute([$id]);
    $book = $stmt->fetch();
    return $book ?: null;
}

function genres(): array
{
    return db()->query('SELECT DISTINCT genre FROM books ORDER BY genre')->fetchAll(PDO::FETCH_COLUMN);
}

function catalog(string $genre, string $query): array
{
    $sql = 'SELECT * FROM books WHERE 1 = 1';
    $params = [];

    if ($genre !== '') {
        $sql .= ' AND genre = ?';
        $params[] = $genre;
    }
    if ($query !== '') {
        $sql .= ' AND (title LIKE ? OR author LIKE ?)';
        $params[] = '%' . $query . '%';
        $params[] = '%' . $query . '%';
    }
    $sql .= ' ORDER BY title';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function cart(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_total(): int
{
    $total = 0;
    foreach (cart() as $id => $qty) {
        $book = book_by_id((int) $id);
        if ($book) {
            $total += (int) $book['price'] * (int) $qty;
        }
    }
    return $total;
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function money(int $value): string
{
    return number_format($value, 0, '', ' ') . ' руб.';
}
