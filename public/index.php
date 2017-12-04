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
		<link rel="stylesheet" href="style.css">
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	</head>
	<body>
		<?php if (isset($_SESSION['login'])): ?>
		<div class="ui labeled icon small menu">
			<a class="<?php if ($page == 'accounts') echo 'active '; ?>item" href="<?php echo DOMEN; ?>/?page=accounts">
				<i class="users icon"></i>
				<span class="menu-text">Аккаунты</span>
			</a>
			<a class="<?php if ($page == 'categories') echo 'active '; ?>item" href="<?php echo DOMEN; ?>/?page=categories">
				<i class="browser icon"></i>
				<span class="menu-text">Категории</span>
			</a>
			<a class="<?php if ($page == 'texts') echo 'active '; ?>item" href="<?php echo DOMEN; ?>/?page=texts">
				<i class="write icon"></i>
				<span class="menu-text">Тексты</span>
			</a>
			<a class="<?php if ($page == 'settings') echo 'active '; ?>item" href="<?php echo DOMEN; ?>/?page=settings">
				<i class="settings icon"></i>
				<span class="menu-text">Настройки</span>
			</a>
			<a class="<?php if ($page == 'logging') echo 'active '; ?>item" href="<?php echo DOMEN; ?>/?page=logging">
				<i class="database icon"></i>
				<span class="menu-text">Логи</span>
			</a>
			<a class="<?php if ($page == 'info') echo 'active '; ?>item" href="<?php echo DOMEN; ?>/?page=info">
				<i class="info circle icon"></i>
				<span class="menu-text">Справка</span>
			</a>
			<div class="right menu">
				<a class="item" href="<?php echo DOMEN; ?>/?page=exit"><i class="sign out icon"></i><span class="menu-text">Выход</span></a>
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