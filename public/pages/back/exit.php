<?php

if (isset($_SESSION['login'])) {
	unset($_SESSION['login']);
	session_destroy();
	header('Location: '.DOMAIN);
}