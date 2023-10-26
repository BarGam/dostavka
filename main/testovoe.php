<?php
//Задание тестовое парсинг сайта
require __DIR__ . '/../vendor/autoload.php'; // Подключение библиотеки Goutte
//библиоткка для парсинга
use Goutte\Client;

$client = new Client();

// URL для парсинга
$url = 'https://www.bills.ru';

$crawler = $client->request('GET', $url);

// Находим все элементы <tr> с классом "bizon_api_news_row"
$newsRows = $crawler->filter('.bizon_api_news_row');

// Подключение к базе данных
$host = 'localhost';
$database = 'testoviy';
$username = 'root';
$password = 'mysql';

try {
    $db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

// Перебираем каждую новость
$newsRows->each(function ($newsRow) use ($db) {
    // извлекаем новость из тега <a>
    $newsLink = $newsRow->filter('a')->text();

    // извлекаем ссылку href из  тега <a>
    $newsUrl = $newsRow->filter('a')->attr('href');

    // извлекаем дату из тега <td> с классом "news_date"
    $dateText = $newsRow->filter('td.news_date')->text();
    $dateText = trim($dateText);

    // Преобразуем текстовое представление месяца в числовой формат
    $dateText = str_replace(
        ['янв', 'фев', 'мар', 'апр', 'май', 'июн', 'июл', 'авг', 'сен', 'окт', 'ноя', 'дек'],
        ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'],
        $dateText
    );

    // преобразуем текстовое представление даты в объект DateTime
    $date = DateTime::createFromFormat('d m Y', $dateText);

    if ($date !== false) {
        // Форматируем дату в нужный вид
        $formattedDate = $date->format('Y-m-d H:i:s');

        // SQL-запрос для вставки данных
        $sql = "INSERT INTO bills_ru_events (date, title, url) VALUES (:date, :title, :url)";

        // Подготовка запроса
        $stmt = $db->prepare($sql);

        // привязываем значений к параметрам
        $stmt->bindParam(':date', $formattedDate);
        $stmt->bindParam(':title', $newsLink);
        $stmt->bindParam(':url', $newsUrl);

        // здесь уже выполняется запрос
        if ($stmt->execute()) {
            echo "Данные успешно вставлены в базу данных.\n";
        } else {
            echo "Ошибка при вставке данных: " . $stmt->errorInfo()[2] . "\n";
        }
    } else {
        echo "Ошибка: Не удалось преобразовать дату.\n";
    }
// выводим для проверки ошибок
    echo $formattedDate;
    echo $newsLink;
    echo $newsUrl;
});
