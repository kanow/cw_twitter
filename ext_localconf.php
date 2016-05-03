<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cw_twitter']);

$cacheConfiguration = [
	'frontend' => \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend::class,
	'backend' => \TYPO3\CMS\Core\Cache\Backend\Typo3DatabaseBackend::class,
	'options' => ['defaultLifetime' => intval($conf['lifetime']) ],
	'groups' => ['pages']
];

\TYPO3\CMS\Core\Utility\ArrayUtility::mergeRecursiveWithOverrule(
	$cacheConfiguration,
	$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries'] ?: []
);

$GLOBALS['TYPO3_CONF_VARS']['SYS']['caching']['cacheConfigurations']['cwtwitter_queries'] = $cacheConfiguration;

/** @noinspection PhpUndefinedVariableInspection */
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'CmsWorks.'.$_EXTKEY,
	'Pi1',
	array(
		'Tweet' => 'list',
	),
	// non-cacheable actions
	array(
		'Tweet' => 'list',
	)
);

