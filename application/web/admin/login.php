<?php
define('LOCATION', 'admin');
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params'])) {
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array(
	'header' => array(
		'functions',
		'vendor/jquery_' . JQUERYVERSION,
		'vendor/growl',
		'admin'
	),
	'footer' => array(
		'md5'
	)
);
$sheel->template->meta['cssinclude'] = array(
	'login',
	'vendor' => array(
		'font-awesome',
		'glyphicons',
		'growl'
	)
);
$sheel->template->meta['navcrumb'] = array($sheel->ilpage['login'] => $sheel->ilcrumbs[$sheel->ilpage['login']]);
$cmd = $sidenav = '';

if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'signout') {
	$sheel->template->meta['areatitle'] = '{_logging_out}';
	$sheel->template->meta['pagetitle'] = '{_logging_out}';

	set_cookie('lastvisit', DATETIME24H);
	set_cookie('lastactivity', DATETIME24H);
	set_cookie('userid', '', 0, false);
	set_cookie('password', '', 0, false);
	set_cookie('inlineproduct', '', 0, false);
	set_cookie('inlineservice', '', 0, false);
	set_cookie('inlineprovider', '', 0, false);
	$sheel->sessions->session_destroy(session_id());
	session_destroy();
	refresh(HTTPS_SERVER_ADMIN);
	exit();
} 
else if (isset($sheel->GPC['cmd']) and $sheel->GPC['cmd'] == 'renew-password') {
	$sheel->template->meta['areatitle'] = '{_admin_password_renewal_menu}';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_admin_password_renewal_menu}';
	$admin_cookie = '';
	if (!empty($_COOKIE[COOKIE_PREFIX . 'username'])) {
		$admin_cookie = $sheel->crypt->decrypt($_COOKIE[COOKIE_PREFIX . 'username']);
	}
	if (isset($sheel->GPC['subcmd']) and $sheel->GPC['subcmd'] == 'process') {
		$sql = $sheel->db->query("
                        SELECT user_id, email, username
                        FROM " . DB_PREFIX . "users
                        WHERE (username = '" . $sheel->db->escape_string($sheel->GPC['username']) . "' OR email = '" . $sheel->db->escape_string($sheel->GPC['username']) . "')
                                AND isadmin = '1'
                        LIMIT 1
                ");
		if ($sheel->db->num_rows($sql) > 0) {
			$sheel->show['error_login'] = false;
			$res = $sheel->db->fetch_array($sql, DB_ASSOC);
			$salt = $sheel->construct_password_salt(5);
			$newp = $sheel->construct_password(8);
			$pass = md5(md5($newp) . $salt);
			$sheel->db->query("
                                UPDATE " . DB_PREFIX . "users
                                SET password = '" . $sheel->db->escape_string($pass) . "',
				salt = '" . $sheel->db->escape_string($salt) . "',
				password_lastchanged = '" . DATETIME24H . "'
                                WHERE user_id = '" . intval($res['user_id']) . "'
                                LIMIT 1
                        ");
			$subject = "New password generated for " . SITE_NAME . " Admin CP";
			$message = "Administrator,\n\nYou or someone else has requested to renew the administration password for your account on " . DATETIME24H . ".  Please find the following information below to sign-in:
						Username: " . stripslashes($res['username']) . "
						New Password: " . $newp . "
						------------------------------------------
						Password change request from IP: " . IPADDRESS . "
						Device/Agent: " . USERAGENT . "
						** If you did not request the password to be changed please consider adding the above IP address to your marketplace blacklist and/or at the firewall level.
						" . SITE_NAME . "
						" . HTTPS_SERVER;
			$sheel->email->mail = $res['email'];
			$sheel->email->subject = $subject;
			$sheel->email->message = $message;
			$sheel->email->send();
			$sheel->show['password_renewed'] = true;
		} else {
			$sheel->show['error_login'] = true;
			$sheel->show['password_renewed'] = false;
		}
	}
	$vars = array('admin_cookie' => $admin_cookie, 'sidenav' => '');

	$sheel->template->fetch('main', 'login_pwrenew.html', 1);
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
	$sheel->template->pprint('main', $vars);
	exit();
} else { // sign-in
	$sheel->template->meta['areatitle'] = '{_login_area_menu}';
	$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_login_area_menu}';
	$username = isset($sheel->GPC['username']) ? o($sheel->GPC['username']) : '';
	$password = isset($sheel->GPC['password']) ? o($sheel->GPC['password']) : '';
	$redirect = HTTPS_SERVER_ADMIN;
	if (!empty($sheel->GPC['redirect'])) {
		$redirect = trim($sheel->GPC['redirect']);
	}
	$sheel->template->meta['onload'] .= (!empty($_COOKIE[COOKIE_PREFIX . 'username'])) ? 'document.login.password.focus();' : 'document.login.username.focus();';
	$admin_cookie = '';
	if (!empty($_COOKIE[COOKIE_PREFIX . 'username']) and empty($sheel->GPC['username'])) {
		$admin_cookie = $sheel->crypt->decrypt($_COOKIE[COOKIE_PREFIX . 'username']);
		$username = $admin_cookie;
	}
	if (!empty($_SESSION['sheeldata']['user']['userid']) and isset($_SESSION['sheeldata']['user']['isadmin']) and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
		header("Location: " . HTTPS_SERVER_ADMIN);
		exit();
	}
	$vars = array('username' => $username, 'password' => $password, 'redirect' => $redirect, 'sidenav' => '');
	$sheel->template->fetch('main', 'login.html', 1);
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage));
	$sheel->template->pprint('main', $vars);
	exit();
}
?>