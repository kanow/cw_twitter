<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}
//
//if (!is_array($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries'])) {
//    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries'] = array();
//}
//if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['frontend'])) {
//    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['frontend'] = 't3lib_cache_frontend_VariableFrontend';
//}
//if (\TYPO3\CMS\Core\Utility\VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < '4006000') {
//    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['backend'])) {
//        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['backend'] = 't3lib_cache_backend_DbBackend';
//    }
//    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options'])) {
//        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options'] = array();
//    }
//    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options']['cacheTable'])) {
//        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options']['cacheTable'] = 'tx_cwtwitter_queries';
//    }
//    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options']['tagsTable'])) {
//        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options']['tagsTable'] = 'tx_cwtwitter_queries_tags';
//    }
//}



// cache settings TYPO3 7.6
if( !is_array( $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries'] ) ) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries'] = array();
}
if( !isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['frontend'] ) ) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['frontend'] = 'TYPO3\\CMS\\Core\\Cache\\Frontend\\StringFrontend';
}
if( !isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['backend'] ) ) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cw_twitter']['backend'] = 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend';
}
if( !isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options'] ) ) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cw_twitter']['options'] = array( 'defaultLifetime' => 0 );
}
if( !isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cw_twitter']['groups'] ) ) {
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cw_twitter']['groups'] = array( 'pages' );
}
//    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options']['cacheTable'])) {
//        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options']['cacheTable'] = 'tx_cwtwitter_queries';
//    }
//    if (!isset($GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options']['tagsTable'])) {
//        $GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries']['options']['tagsTable'] = 'tx_cwtwitter_queries_tags';
//    }

/** @noinspection PhpUndefinedVariableInspection */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'CmsWorks'.$_EXTKEY,
	'Pi1',
	array(
		'Tweet' => 'list',
	),
	// non-cacheable actions
	array(
		'Tweet' => 'list',
	)
);

