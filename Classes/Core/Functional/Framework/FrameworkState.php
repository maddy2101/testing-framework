<?php
declare(strict_types=1);
namespace TYPO3\TestingFramework\Core\Functional\Framework;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;

/**
 *
 */
class FrameworkState
{
    protected static $state = [];

    public static function push()
    {
        $state = [];
        $state['globals-server'] = $GLOBALS['_SERVER'];
        $state['globals-beUser'] = $GLOBALS['BE_USER'] ?: null;
        // InternalRequestContext->withGlobalSettings() may override globals, especially TYPO3_CONF_VARS
        $state['globals-typo3-conf-vars'] = $GLOBALS['TYPO3_CONF_VARS'] ?: null;

        $generalUtilityReflection = new \ReflectionClass(GeneralUtility::class);
        $generalUtilityIndpEnvCache = $generalUtilityReflection->getProperty('indpEnvCache');
        $generalUtilityIndpEnvCache->setAccessible(true);
        $state['generalUtilityIndpEnvCache'] = $generalUtilityIndpEnvCache->getValue();

        $state['generalUtilitySingletonInstances'] = GeneralUtility::getSingletonInstances();

        $rootlineUtilityReflection = new \ReflectionClass(RootlineUtility::class);
        $rootlineUtilityLocalCache = $rootlineUtilityReflection->getProperty('localCache');
        $rootlineUtilityLocalCache->setAccessible(true);
        $state['rootlineUtilityLocalCache'] = $rootlineUtilityLocalCache->getValue();
        $rootlineUtilityRootlineFields = $rootlineUtilityReflection->getProperty('rootlineFields');
        $rootlineUtilityRootlineFields->setAccessible(true);
        $state['rootlineUtilityRootlineFields'] = $rootlineUtilityRootlineFields->getValue();
        $rootlineUtilityPageRecordCache = $rootlineUtilityReflection->getProperty('pageRecordCache');
        $rootlineUtilityPageRecordCache->setAccessible(true);
        $state['rootlineUtilityPageRecordCache'] = $rootlineUtilityPageRecordCache->getValue();

        self::$state[] = $state;
    }

    public static function reset()
    {
        unset($GLOBALS['BE_USER']);

        $generalUtilityReflection = new \ReflectionClass(GeneralUtility::class);
        $generalUtilityIndpEnvCache = $generalUtilityReflection->getProperty('indpEnvCache');
        $generalUtilityIndpEnvCache->setAccessible(true);
        $generalUtilityIndpEnvCache = $generalUtilityIndpEnvCache->setValue([]);

        GeneralUtility::resetSingletonInstances([]);

        RootlineUtility::purgeCaches();
        $rootlineUtilityReflection = new \ReflectionClass(RootlineUtility::class);
        $rootlineFieldsDefault = $rootlineUtilityReflection->getDefaultProperties();
        $rootlineFieldsDefault = $rootlineFieldsDefault['rootlineFields'];
        $rootlineUtilityRootlineFields = $rootlineUtilityReflection->getProperty('rootlineFields');
        $rootlineUtilityRootlineFields->setAccessible(true);
        $state['rootlineUtilityRootlineFields'] = $rootlineFieldsDefault;
    }

    public static function pop()
    {
        $state = array_pop(self::$state);

        $GLOBALS['_SERVER'] = $state['globals-server'];
        if ($state['globals-beUser'] !== null) {
            $GLOBALS['BE_USER'] = $state['globals-beUser'];
        }
        if ($state['globals-typo3-conf-vars'] !== null) {
            $GLOBALS['TYPO3_CONF_VARS'] = $state['globals-typo3-conf-vars'];
        }

        $generalUtilityReflection = new \ReflectionClass(GeneralUtility::class);
        $generalUtilityIndpEnvCache = $generalUtilityReflection->getProperty('indpEnvCache');
        $generalUtilityIndpEnvCache->setAccessible(true);
        $generalUtilityIndpEnvCache->setValue($state['generalUtilityIndpEnvCache']);

        GeneralUtility::resetSingletonInstances($state['generalUtilitySingletonInstances']);

        $rootlineUtilityReflection = new \ReflectionClass(RootlineUtility::class);
        $rootlineUtilityLocalCache = $rootlineUtilityReflection->getProperty('localCache');
        $rootlineUtilityLocalCache->setAccessible(true);
        $rootlineUtilityLocalCache->setValue($state['rootlineUtilityLocalCache']);
        $rootlineUtilityRootlineFields = $rootlineUtilityReflection->getProperty('rootlineFields');
        $rootlineUtilityRootlineFields->setAccessible(true);
        $rootlineUtilityRootlineFields->setValue($state['rootlineUtilityRootlineFields']);
        $rootlineUtilityPageRecordCache = $rootlineUtilityReflection->getProperty('pageRecordCache');
        $rootlineUtilityPageRecordCache->setAccessible(true);
        $rootlineUtilityPageRecordCache->setValue($state['rootlineUtilityPageRecordCache']);
    }
}
