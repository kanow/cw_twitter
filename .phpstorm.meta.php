<?php
/*
 * This file configures factory methods in TYPO3 CMS
 */

namespace PHPSTORM_META {

    $STATIC_METHOD_TYPES = [
        \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('') => [
            '' == '@',
        ],
        \TYPO3\CMS\Extbase\Object\ObjectManagerInterface::get('') => [
            '' == '@',
        ],
    ];
}