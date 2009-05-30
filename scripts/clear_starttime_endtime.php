<?php

/*
 *  initialize TYPO3 pathes
 *	read t3lib_div and extMenager
 *  initialize database
 */

if ($_SERVER['PHP_SELF']) {
	if (!defined('PATH_thisScript')) define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', $_SERVER['PHP_SELF'])));
} else {
	if (!defined('PATH_thisScript')) define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', $_ENV['_'])));
}
if (!defined('PATH_site')) define('PATH_site', dirname(dirname(dirname(dirname(dirname(PATH_thisScript))))).'/');
if (!defined('PATH_t3lib')) if (!defined('PATH_t3lib')) define('PATH_t3lib', PATH_site.'t3lib/');

define('PATH_typo3conf', PATH_site.'typo3conf/');
define('TYPO3_mainDir', 'typo3/');
if (!defined('PATH_typo3')) define('PATH_typo3', PATH_site.TYPO3_mainDir);
if (!defined('PATH_tslib')) {
	if (@is_dir(PATH_site.'typo3/sysext/cms/tslib/')) {
		define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
	} elseif (@is_dir(PATH_site.'tslib/')) {
		define('PATH_tslib', PATH_site.'tslib/');
	}
}
define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
define('TYPO3_MODE', 'BE');

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');
require_once(PATH_t3lib.'config_default.php');
require_once(PATH_typo3conf.'localconf.php');
require_once(PATH_t3lib.'class.t3lib_befunc.php');
require_once(PATH_t3lib.'class.t3lib_tsparser.php');

if (isset($_POST['GLOBALS']) || isset($_GET['GLOBALS'])) die();

require_once(PATH_t3lib.'class.t3lib_db.php');
$GLOBALS['TYPO3_DB'] = t3lib_div::makeInstance('t3lib_DB');
if( !$GLOBALS['TYPO3_DB']->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password)) {
	die("Couldn't connect to database at " . TYPO3_db_host);
}
$GLOBALS['TYPO3_DB']->sql_select_db(TYPO3_db);


ini_set("display_errors", "1");
error_reporting (E_ALL ^ E_NOTICE);

require_once(t3lib_extMgm::extPath('ttnewscache'). 'class.tx_ttnewscache_tcemainproc.php');

$tx_ttnewscache_tcemainproc = t3lib_div::makeInstance('tx_ttnewscache_tcemainproc');
$tx_ttnewscache_tcemainproc->init();

$bufferInSeconds = 5*60; // bufor is 5 minutes
$timestamp = time();


// select all the tt_news records that have starttime or endtime in the nearest $bufferInSeconds
$select_fields = '*';
$table = 'tt_news';
$where_clause = ' starttime BETWEEN ' . $timestamp .' AND ' . (string)($timestamp + $bufferInSeconds) . ' OR endtime BETWEEN '. $timestamp .' AND ' . (string)($timestamp + $bufferInSeconds);
$affectedNewsRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($select_fields, $table, $where_clause, $groupBy='', $orderBy='', $limit='');

$status = 'update';

foreach($affectedNewsRows as $affectedNewsRow) {

	//check what is the status of the news - it will appear od disappear
	if( ($affectedNewsRow['starttime'] > $timestamp) && ($affectedNewsRow['starttime'] < $timestamp + $bufferInSeconds) ) {
		
		$timeField = 'starttime'; 
		$timeValue =  $timestamp;
		$fields_values = array ('starttime' => $timeValue);
		
	} elseif ( ($affectedNewsRow['endtime'] > $timestamp) && ($affectedNewsRow['endtime'] < $timestamp + $bufferInSeconds) ) {
		
		$timeField = 'endtime'; 
		$timeValue = $timestamp;
		$fields_values = array ('endtime' => $timeValue);
		
	}
	
	//update the starttime or endtime to $timestamp so if cache is cleared the news will be properly rendered in FE
	$where = ' uid = '. $affectedNewsRow['uid'];
	$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields_values);	

	list($thisRef, $fieldArray) = $tx_ttnewscache_tcemainproc->prepareFakeDataForProcessDatamap($affectedNewsRow);
	$fieldArray[$timeField] = $timeValue;
			
	$id = $affectedNewsRow['uid'];

	$tx_ttnewscache_tcemainproc->processDatamap_afterDatabaseOperations($status, $table, $id, &$fieldArray, &$thisRef);
}

?>