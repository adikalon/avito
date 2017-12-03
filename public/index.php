<?php
require_once __DIR__ . '/../core_front.php';
require_once PUBL . '/router.php';
require_once PAGES . '/back/' . $page . '.php';
?>
<!doctype html>
<html lang="ru">
	<head>
		<meta charset="UTF-8">
		<title><?php echo $pages[$page]; ?></title>
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.9/semantic.min.css">
		<link rel="stylesheet" href="<?php echo CSS; ?>">
		<link rel="shortcut icon" href="<?php echo ICO; ?>" type="image/x-icon">
	</head>
	<body>
		<?php if (isset($_SESSION['login'])): ?>
		<div class="ui labeled icon small menu">
			<a class="<?php if ($page == 'accounts') echo 'active '; ?>item" href="accounts">
				<i class="users icon"></i>
				<span class="menu-text">Аккаунты</span>
			</a>
			<a class="<?php if ($page == 'categories') echo 'active '; ?>item" href="categories">
				<i class="browser icon"></i>
				<span class="menu-text">Категории</span>
			</a>
			<a class="<?php if ($page == 'texts') echo 'active '; ?>item" href="texts">
				<i class="write icon"></i>
				<span class="menu-text">Тексты</span>
			</a>
			<a class="<?php if ($page == 'settings') echo 'active '; ?>item" href="settings">
				<i class="settings icon"></i>
				<span class="menu-text">Настройки</span>
			</a>
			<a class="<?php if ($page == 'logging') echo 'active '; ?>item" href="logging">
				<i class="database icon"></i>
				<span class="menu-text">Логи</span>
			</a>
			<a class="<?php if ($page == 'info') echo 'active '; ?>item" href="info">
				<i class="info circle icon"></i>
				<span class="menu-text">Информация</span>
			</a>
			<div class="right menu">
				<a class="item" href="exit"><i class="sign out icon"></i><span class="menu-text">Выход</span></a>
			</div>
		</div>
		<?php endif; ?>
		<?php require_once PAGES . '/front/' . $page . '.php'; ?>
		<div class="bottom"></div>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.2.9/semantic.min.js"></script>
		<script>
			$('.message .close').on('click', function() {
				$(this).closest('.message').transition('fade');
			});
			$('.ui.checkbox').checkbox();
		</script>
	</body>
</html>