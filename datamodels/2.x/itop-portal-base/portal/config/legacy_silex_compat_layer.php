<?php

/**
 * Copyright (C) 2013-2020 Combodo SARL
 *
 * This file is part of iTop.
 *
 * iTop is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iTop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 */

// Disable PhpUnhandledExceptionInspection as the exception handling is made by the file including this one
/** @noinspection PhpUnhandledExceptionInspection */

// Loading file
use Combodo\iTop\Portal\DependencyInjection\SilexCompatBootstrap\PortalXmlConfiguration\Basic;
use Combodo\iTop\Portal\DependencyInjection\SilexCompatBootstrap\PortalXmlConfiguration\Forms;
use Combodo\iTop\Portal\DependencyInjection\SilexCompatBootstrap\PortalXmlConfiguration\Lists;

if (! defined('PORTAL_ID'))
{
	//this mean that we are currently not inside a portal!
	return;
}

// Note: ModuleDesign service is not available yet as this script is processed before services generation,
// that's why we have to instantiate it manually.
$oModuleDesign = new ModuleDesign($_ENV['PORTAL_ID']);

// TODO: The following code needs to be refactored to more independent and atomic services.

// Load portal conf. such as properties, themes, templates, ...
// Append into %combodo.portal.instance.conf%
$oBasicCompat = new Basic($oModuleDesign);
$oBasicCompat->Process($container);

// Load portal forms definition
// Append into %combodo.portal.instance.conf%
$oFormsCompat = new Forms($oModuleDesign);
$oFormsCompat->Process($container);

// Load portal lists definition
// Append into %combodo.portal.instance.conf%
$oListsCompat = new Lists($oModuleDesign);
$oListsCompat->Process($container);

// Generating CSS files
// Note: We do this here as it is not user dependent and therefore can be cached for everyone.
// A dedicated listener 'CssFromSassCompiler' exists to compile files again when by-passing HTTP cache.
// This is to keep developers comfort when tuning the SCSS files.
$aImportPaths = array($_ENV['COMBODO_PORTAL_BASE_ABSOLUTE_PATH'].'css/');
$aPortalConf = $container->getParameter('combodo.portal.instance.conf');
foreach ($aPortalConf['properties']['themes'] as $sKey => $value)
{
	if (!is_array($value))
	{
		$aPortalConf['properties']['themes'][$sKey] = $_ENV['COMBODO_ABSOLUTE_URL'].utils::GetCSSFromSASS('env-'.utils::GetCurrentEnvironment().'/'.$value,
				$aImportPaths);
	}
	else
	{
		$aValues = array();
		foreach ($value as $sSubValue)
		{
			$aValues[] = $_ENV['COMBODO_ABSOLUTE_URL'].utils::GetCSSFromSASS('env-'.utils::GetCurrentEnvironment().'/'.$sSubValue,
					$aImportPaths);
		}
		$aPortalConf['properties']['themes'][$sKey] = $aValues;
	}
}
$container->setParameter('combodo.portal.instance.conf', $aPortalConf);