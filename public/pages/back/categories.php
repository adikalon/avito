<?php

// Добавление или обновление категории
if (isset($_POST['link'])) {
	$resultCategory = Category::setCategory($_POST['link']);
	if (is_array($resultCategory)) {
		$insertResponse = $resultCategory;
	} else {
		$error = 'Некорректная ссылка, либо категория отсутствует';
	}
}

// Удаление категории
if (isset($_POST['delete'])) {
	$id = $_POST['delete']['id'];
	$name = $_POST['delete']['name'];
	$delete = CategoryWriter::delete($id);
	if ($delete) {
		$success = "Категория <b>$name</b> удалена";
	} else {
		$error = 'Категория отсутствует в базе данных';
	}
}

$sql = "SELECT id, name, link FROM categories";
$categories = DB::connect()->query($sql)->fetchAll(PDO::FETCH_OBJ);