<?php
define('LOCATION', 'admin');
require_once(SITE_ROOT . 'application/config.php');
if (isset($match['params'])) {
	$sheel->GPC = array_merge($sheel->GPC, $match['params']);
}
$sheel->template->meta['jsinclude'] = array(
	'header' => array(
		'functions',
		'admin',
		'inline',
		'vendor/chartist',
		'vendor/growl'
	),
	'footer' => array(
	)
);
$sheel->template->meta['cssinclude'] = array(
	'common',
	'vendor' => array(
		'font-awesome',
		'glyphicons',
		'chartist',
		'growl'
	)
);
// #### setup default breadcrumb ###############################################
$sheel->template->meta['navcrumb'] = array($sheel->ilpage['dashboard'] => $sheel->ilcrumbs[$sheel->ilpage['dashboard']]);
$sheel->template->meta['areatitle'] = '{_admin_cp_dashboard}';
$sheel->template->meta['pagetitle'] = SITE_NAME . ' - {_admin_cp_dashboard}';


if (($sidenav = $sheel->cache->fetch("sidenav_dashboard")) === false) {
	$sidenav = $sheel->admincp_nav->print($sheel->ilpage['dashboard']);
	$sheel->cache->store("sidenav_dashboard", $sidenav);
}

if (!empty($_SESSION['sheeldata']['user']['userid']) and $_SESSION['sheeldata']['user']['userid'] > 0 and $_SESSION['sheeldata']['user']['isadmin'] == '1') {
	$sheel->GPC['period'] = ((isset($sheel->GPC['period']) and !empty($sheel->GPC['period'])) ? $sheel->GPC['period'] : 'last7days');
	$period = '{_last_7_days}';
	$periods = array(
		'today' => array(
			'title' => '{_today}',
		),
		'yesterday' => array(
			'title' => '{_yesterday}',
		),
		'last7days' => array(
			'title' => '{_last_7_days}',
		),
		'last30days' => array(
			'title' => '{_last_30_days}',
		),
		'last60days' => array(
			'title' => '{_last_60_days}',
		),
		'last90days' => array(
			'title' => '{_last_90_days}',
		),
		'last365days' => array(
			'title' => '{_last_365_days}',
		)
	);
	foreach ($periods as $key => $value) {
		$parr[$key] = $value['title'];
		$parrs[] = $key;
	}
	if (!in_array($sheel->GPC['period'], $parrs)) {
		$sheel->GPC['period'] = 'last7days';
	}
	$periodpulldown = $sheel->construct_pulldown('period', 'period', $parr, $sheel->GPC['period'], 'class="draw-select" onchange="this.form.submit()"');
	unset($parr);
	if (isset($sheel->GPC['period']) and isset($periods[$sheel->GPC['period']]['title'])) {
		$period = $periods[$sheel->GPC['period']]['title'];
	}
	$stats = $sheel->admincp->stats('dashboard', $sheel->GPC['period']);
	$revenue = $sales = array();
	if ($sheel->config['platform_type'] == 'multivendor') { // multi-vendor
		$revenue['revenue7days'] = $stats['revenue']['revenue7days'];
		$revenue['revenuecount7days'] = $stats['revenue']['revenuecount7days'];
		$revenue['revenuetoday'] = $stats['revenue']['revenuetoday'];
		$revenue['revenuecounttoday'] = $stats['revenue']['revenuecounttoday'];
		$revenue['revenueyesterday'] = $stats['revenue']['revenueyesterday'];
		$revenue['revenuecountyesterday'] = $stats['revenue']['revenuecountyesterday'];
		$revenue['revenue30days'] = $stats['revenue']['revenue30days'];
		$revenue['revenuecount30days'] = $stats['revenue']['revenuecount30days'];
		$revenue['revenue90days'] = $stats['revenue']['revenue90days'];
		$revenue['revenuecount90days'] = $stats['revenue']['revenuecount90days'];
		$revenue['label'] = $stats['revenue']['label'];
		$revenue['series'] = $stats['revenue']['series'];
	} else { // single seller
		$sales['sellersales7days'] = $stats['sales']['sellersales7days'];
		$sales['sellersalescount7days'] = $stats['sales']['sellersalescount7days'];
		$sales['sellersalestoday'] = $stats['sales']['sellersalestoday'];
		$sales['sellersalescounttoday'] = $stats['sales']['sellersalescounttoday'];
		$sales['sellersalesyesterday'] = $stats['sales']['sellersalesyesterday'];
		$sales['sellersalescountyesterday'] = $stats['sales']['sellersalescountyesterday'];
		$sales['sellersales30days'] = $stats['sales']['sellersales30days'];
		$sales['sellersalescount30days'] = $stats['sales']['sellersalescount30days'];
		$sales['sellersales90days'] = $stats['sales']['sellersales90days'];
		$sales['sellersalescount90days'] = $stats['sales']['sellersalescount90days'];
		$sales['label'] = $stats['sales']['label'];
		$sales['series'] = $stats['sales']['series'];
	}

	$visitors['visitors'] = $stats['visitors']['visitors'];
	$visitors['uniquevisitors'] = $stats['visitors']['uniquevisitors'];
	$visitors['label'] = $stats['visitors']['label'];
	$visitors['series'] = $stats['visitors']['series'];
	$visitors['pageviews'] = $stats['visitors']['pageviews'];

	$percent['buyerpercent'] = $stats['percent']['buyerpercent'];
	$percent['buyercount'] = $stats['percent']['buyercount'];
	$percent['sellerpercent'] = $stats['percent']['sellerpercent'];
	$percent['sellercount'] = $stats['percent']['sellercount'];
	$percent['bothpercent'] = $stats['percent']['bothpercent'];
	$percent['bothcount'] = $stats['percent']['bothcount'];

	$loops = array(
		'topcategories' => $stats['stats']['topcategories'],
		'topcountries' => $stats['stats']['topcountries'],
		'topdevices' => $stats['stats']['topdevices'],
		'topbrowsers' => $stats['stats']['topbrowsers'],
		'trafficsources' => $stats['stats']['trafficsources'],
		'socialreferrers' => $stats['stats']['socialreferrers'],
		'topreferrers' => $stats['stats']['topreferrers'],
		'topsearchterms' => $stats['stats']['topsearchterms'],
		'toplandingpages' => $stats['stats']['toplandingpages'],
		'utmcampaigns' => $stats['stats']['utmcampaigns'],
		'mostactive' => $stats['visitors']['mostactive']
	);
	$vars = array(
		'sidenav' => $sidenav,
		'period' => $period,
		'periodpulldown' => $periodpulldown
	);

	// notices
	$statistics = $sheel->admincp_dashboard->fetch();
	$show['connectdomain'] = false; // (($sheel->config['license_type'] == 'leased' AND empty($sheel->config['custom_domain'])) ? true : false);
	$show['paymentgateway'] = false;
	$show['postitems'] = false;
	$show['addpages'] = false;
	$show['sso'] = false;
	$show['autoupdates'] = false;
	$show['usersmoderated'] = ((isset($statistics['userspending']) and $statistics['userspending'] > 0) ? true : false);
	$show['paymentdisputes'] = ((isset($statistics['paymentdisputes']) and $statistics['paymentdisputes'] > 0) ? true : false);
	$show['itemsmoderated'] = ((isset($statistics['itemspending']) and $statistics['itemspending'] > 0) ? true : false);
	$show['attachmentsmoderated'] = ((isset($statistics['attachmentspending']) and $statistics['attachmentspending'] > 0) ? true : false);
	$show['withdrawspending'] = ((isset($statistics['withdrawspending']) and $statistics['withdrawspending'] > 0) ? true : false);
	$show['diskspacewarning'] = false;
	$left = $sheel->currency->currencies[$sheel->config['globalserverlocale_defaultcurrency']]['symbol_left'];
	$right = $sheel->currency->currencies[$sheel->config['globalserverlocale_defaultcurrency']]['symbol_right'];
	$local = $sheel->currency->currencies[$sheel->config['globalserverlocale_defaultcurrency']]['symbol_local'];
	$currency['symbol_left'] = ((!empty($left)) ? $local : '');
	$currency['symbol_right'] = ((!empty($right)) ? $local : '');
	$space = $sheel->admincp_dashboard->fetch_diskspace();
	if (isset($space['percent']) and $space['percent'] >= 70) {
		$show['diskspacewarning'] = true;
	}

	$sheel->template->fetch('main', 'dashboard.html', 1);
	$sheel->template->parse_hash('main', array('ilpage' => $sheel->ilpage, 'currency' => $currency, 'visitors' => $visitors, 'revenue' => $revenue, 'sales' => $sales, 'percent' => $percent, 'space' => $space, 'statistics' => $statistics));
	$sheel->template->parse_loop('main', $loops, false);
	$sheel->template->pprint('main', $vars);
	exit();
} else {
	refresh(HTTPS_SERVER_ADMIN . 'signin/?redirect=' . urlencode('/admin/'));
	exit();
}
?>