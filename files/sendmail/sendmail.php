<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';

$mail = new PHPMailer(true);
$mail->CharSet = 'UTF-8';
$mail->setLanguage('ru', 'phpmailer/language/');
$mail->IsHTML(false);  // Отключаем HTML

// От кого письмо
$mail->setFrom('chasmastersi@chas-master.site', 'Работка'); // Указать нужный E-mail
// Кому отправить
$mail->addAddress('master_chats@mail.ru'); // Указать нужный E-mail
// Тема письма
$mail->Subject = 'Привет! Это тебе прилетела работка';

// Тело письма
$body = "Новая работка\n\n";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	if (!empty($_POST['form'])) {
		$body .= "Имя: " . htmlspecialchars($_POST['form'][0] ?? '') . "\n";
		$body .= "Email: " . htmlspecialchars($_POST['form'][1] ?? '') . "\n";
		$body .= "Телефон: " . htmlspecialchars($_POST['form'][2] ?? '') . "\n";
		$body .= "Тема вопроса: " . htmlspecialchars($_POST['form'][3] ?? '') . "\n";
		$body .= "Сообщение: " . htmlspecialchars($_POST['form'][4] ?? '') . "\n";
	}

	$fields_to_check = [
		"kuhni",
		"krovat",
		"tumba",
		"stellazh",
		"cabinet",
		"dresser",
		"table",
		"mirror",
		"oborudovanie",
		"demontazh"
	];

	$field_names = [
		"kuhni" => "Кухня",
		"krovat" => "Кровать",
		"tumba" => "Тумба",
		"stellazh" => "Стеллаж",
		"cabinet" => "Шкаф",
		"dresser" => "Комод",
		"table" => "Стол",
		"mirror" => "Зеркало",
		"oborudovanie" => "Оборудование",
		"demontazh" => "Демонтаж"
	];

	foreach ($fields_to_check as $field) {
		if (!empty($_POST[$field])) {
			// Добавляем название раздела
			$field_name = $field_names[$field];
			$body .= "\n$field_name:\n";

			// Проверяем, является ли значение ассоциативным массивом
			foreach ($_POST[$field] as $value) {
				// Если значение является строкой, добавляем его в письмо
				if (is_string($value)) {
					$data = json_decode($value, true); // Декодируем JSON строку в ассоциативный массив
					if (is_array($data)) {
						foreach ($data as $name => $val) {
							// Добавляем данные в письмо
							$body .= htmlspecialchars($name) . ": " . htmlspecialchars($val) . "\n";
						}
					} else {
						$body .= htmlspecialchars($value) . "\n";
					}
				} elseif (is_bool($value) && $value) {
					// Если значение логическое true, добавляем только ключ
					$body .= htmlspecialchars($value) . "\n";
				} elseif (is_string($value) && empty($value)) {
					// Если значение строки пусто, пропускаем
					continue;
				}
			}
		}
	}

	// Добавляем информацию о цене, если она есть
	if (!empty($_POST['price'])) {
		$body .= "\nИтоговая цена:\n";
		$body .= htmlspecialchars($_POST['price']) . "\n";
	}
}

$mail->Body = $body;

// Отправляем
if (!$mail->send()) {
	$message = 'Ошибка';
} else {
	$message = 'Данные отправлены!';
}

$response = ['message' => $message];

header('Content-type: application/json');
echo json_encode($response);
