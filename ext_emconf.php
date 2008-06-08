<?php

########################################################################
# Extension Manager/Repository config file for ext: "ttnewscache_clearlike"
#
# Auto generated 06-05-2008 12:14
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'ttnewscache clear cache \'LIKE\'',
	'description' => 'This is an extension which clear the cached tt_news pages using sql LIKE search in HTML field of \'cache_pages\' table',
	'category' => 'be',
	'author' => 'Krystian Szymukowicz',
	'author_email' => 'typo3@prolabium.com',
	'shy' => '',
	'dependencies' => 'ttnewscache',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'ttnewscache' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:9:{s:9:"ChangeLog";s:4:"be2f";s:33:"class.tx_ttnewscacheclearlike.php";s:4:"e2cc";s:21:"ext_conf_template.txt";s:4:"dca3";s:12:"ext_icon.gif";s:4:"f53a";s:17:"ext_localconf.php";s:4:"b918";s:15:"ext_php_api.dat";s:4:"f2a6";s:14:"doc/manual.sxw";s:4:"3db6";s:19:"doc/wizard_form.dat";s:4:"71b8";s:20:"doc/wizard_form.html";s:4:"e5a4";}',
);

?>