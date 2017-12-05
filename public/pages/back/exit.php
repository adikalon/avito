<?php

if (isset($_SESSION['user'])) {
	unset($_SESSION['user']);
	session_destroy();
	header('Location: '.DOMAIN);
}