<?php
/**
 * ************************************************************
 *  Copyright notice
 *
 *  (c) Krystian Szymukowicz (typo3@prolabium.com)
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/o*r modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 *
 */

/**
 * Clear cache function for ttnewscache exetension. Uses sql LIKE to search for markers in HTML field of cache_pages table.
 *
 * $Id: ttnewscacheClearLike $
 *
 * @author    Krystian Szymukowicz <typo3@prolabium.com>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   54: class tx_ttnewscacheClearLike
 *   65:     function clearCache($params, &$thisRef)
 *  169:     function extraGlobalMarkerProcessor(&$pObj, $markerArray)
 *  190:     function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj)
 *
 * TOTAL FUNCTIONS: 3
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

/**
 * Clear cache function for ttnewscache exetension. Uses sql LIKE to search for markers in HTML field of cache_pages table.
 *
 * @author    Krystian Szymukowicz <typo3@prolabium.com>
 */
class tx_ttnewscacheClearLike {

	var $extKey = 'ttnewscache_clearlike';

	/**
	 * Clear cache of tt_news extension using data from ttnewscache ext.
	 *
	 * @param	array		Array of parameters
	 * @param	object		Calling object.
	 * @return	void
	 */
	function clearCache($params, &$thisRef) {

		$confArr = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ttnewscache_clearlike']);
		if (isset($confArr['searchIfNoPid'])) $searchIfNoPid = $confArr['searchIfNoPid'];

		$this->debug = $thisRef->debug;

		if ($this->debug) {
			$startTime = $thisRef->TT->mtime();
			t3lib_div::devLog('[CL] Clearlike /hook/ [START]', $this->extKey);
		}

		//SINGLE
		$rowsAffected = 0;
		foreach ($params['viewsData']['single.']['data.'] as $uid => $view) {

			if (isset($view['action']) && $view['action'] == 'view-marker') {
				$uid = substr($uid, 0, -1);

				if (isset($view['clearUids'])) {
					$clearUids = explode(',',t3lib_div::rm_endcomma($view['clearUids']));
					foreach ($clearUids as $clearUid) {
						if(!$searchIfNoPid && !isset($view['pid'])){
							if ($this->debug) t3lib_div::devLog('[CL] No pid set and "Search If No Pid" is set to 0 so no searching.', $this->extKey);
						} else {

							$where = "HTML LIKE '%tt-news-single-uid-". $GLOBALS['TYPO3_DB']->escapeStrForLike($uid,'cache_pages') ."-record-". intval($clearUid) ." %'";

							$noPidSet = 0;
							if( isset($view['pid']) && intval($view['pid']) > 0) {
								$where .= ' AND page_id = '. $view['pid'];
							}else {
								if ($this->debug) t3lib_div::devLog('[CL] Attention '. $typeOfView .' with uid '. $uid.' has no pid set. Searches for marker will be processed in all cache_pages records. This can be time consuming. Set the pid if possible.', $this->extKey,3);
							}

							$res = $GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pages', $where);
							$rowsAffected += $GLOBALS['TYPO3_DB']->sql_affected_rows();
							if ($this->debug) t3lib_div::devLog('[CL] single "$where" built: '.$where, $this->extKey);
						}
					}
				}
			}
		}
		if ($this->debug) t3lib_div::devLog('[CL] Cache clearing in single. Rows affected: '.$rowsAffected, $this->extKey);


		foreach (array_keys($params['viewsData']) as $typeOfView) {
			$typeOfView = substr($typeOfView, 0, -1);

			$rowsAffected = 0;
			if ($typeOfView == 'single') continue;
			foreach ($params['viewsData'][$typeOfView.'.']['data.'] as $uid => $view) {
				$uid = substr($uid, 0, -1);

				if(!$searchIfNoPid && !isset($view['pid'])){
					if ($this->debug) t3lib_div::devLog('[CL] No pid set and "Search If No Pid" is set to 0 so no searching.', $this->extKey);
				} else {

					if (isset($view['action'])) {

						switch ($view['action']) {

							case 'record-marker':
								$where = ' HTML LIKE \'%tt-news-'. $GLOBALS['TYPO3_DB']->escapeStrForLike($typeOfView,'cache_pages') .'-uid-'.$GLOBALS['TYPO3_DB']->escapeStrForLike($uid,'cache_pages') .'-record-'. intval($params['newsData']['id']) .' %\'';
								break;

							case 'view-marker':
								$where = ' HTML LIKE \'%tt-news-'. $GLOBALS['TYPO3_DB']->escapeStrForLike($typeOfView,'cache_pages').'-uid-'. $GLOBALS['TYPO3_DB']->escapeStrForLike($uid,'cache_pages') .' %\'';
								break;

							default:
								$where = 0;
						}

						$noPidSet = 0;
						if( isset($view['pid']) && intval($view['pid']) > 0) {
							$where .= ' AND page_id = '. $view['pid'];
						}else {
							if ($this->debug) t3lib_div::devLog('[CL] Attention '. $typeOfView .' with uid '. $uid.' has no pid set. Searches for marker will be processed in all cache_pages records. This can be time consuming. Set the pid if possible.', $this->extKey,3);
						}

						if ($where) {
							$res = $GLOBALS['TYPO3_DB']->exec_DELETEquery('cache_pages', $where);
							$rowsAffected += $GLOBALS['TYPO3_DB']->sql_affected_rows();
							if ($this->debug) t3lib_div::devLog('[CL] '. $typeOfView .' "$where" built: '.$where, $this->extKey);
						}
					}
				}
			}
			if ($this->debug) t3lib_div::devLog('[CL] Cache clearing in '. $typeOfView .'. Rows affected: '.$rowsAffected, $this->extKey);
		}
		if ($this->debug) {
			$endTime = $thisRef->TT->mtime();
			t3lib_div::devLog('[CL] Clearlike /hook/ [END]. Time taken in: '. $endTime .' - '. $startTime . ' = ' . ($endTime - $startTime) .'ms', $this->extKey);
		}
	}

	/**
	 * this function is called by the Hook in the function extraGlobalMarkerProcessor() from class.tx_ttnews.php
	 *
	 * @param	array		$markerArray: the markerArray from the tt_news class
	 * @param	array		$row: the database row for the current news-record
	 * @param	array		$lConf: the TS setup array from tt_news (holds the TS vars from the current tt_news view)
	 * @param	object		$pObj: reference to the parent object
	 * @return	array		$markerArray: the processed markerArray
	 */
	function extraGlobalMarkerProcessor(&$pObj, $markerArray) {

		$markerName = '###VIEW_UID###';

		if(isset($pObj->conf['viewMarker'])){
			$markerArray[$markerName] = $pObj->conf['viewMarker'];
		}else{
			$markerArray[$markerName] = $pObj->cObj->data['uid'];
		}
		return $markerArray;
	}

	/**
	 * this function is called by the Hook in the function getItemMarkerArray() from class.tx_ttnews.php
	 *
	 * @param	array		$markerArray: the markerArray from the tt_news class
	 * @param	array		$row: the database row for the current news-record
	 * @param	array		$lConf: the TS setup array from tt_news (holds the TS vars from the current tt_news view)
	 * @param	object		$pObj: reference to the parent object
	 * @return	array		$markerArray: the processed markerArray
	 */
	function extraItemMarkerProcessor($markerArray, $row, $lConf, &$pObj) {

		$markerName = '###VIEW_UID###';

		if(isset($pObj->conf['viewMarker'])){
			$markerArray[$markerName] = $pObj->conf['viewMarker'];
		}else{
			$markerArray[$markerName] = $pObj->cObj->data['uid'];
		}
		return $markerArray;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ttnewscache_clearlike/class.tx_ttnewscacheclearlike.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ttnewscache_clearlike/class.tx_ttnewscacheclearlike.php']);
}

?>
