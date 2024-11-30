<?php

/**
 *  Spacepunks
 *   2moons by Jan-Otto Kröpke 2009-2016
 *   Spacepunks by mimikri 2023
 *
 * For the full copyright and license information, please view the LICENSE
 *
 * @package Spacepunks
 * @author mimikri
 * @copyright 2009 Lucky
 * @copyright 2016 Jan-Otto Kröpke <slaver7@gmail.com>
 * @copyright 2023 mimikri
 * @licence MIT
 * @version 0.0.1
 * @link https://github.com/mimikri/spacepunks
 */

// VARS DB -> SCRIPT WRAPPER

$cache	= Cache::get();
$cache->add('vars', 'VarsBuildCache');
extract($cache->getData('vars'));

$resource[901] = 'metal';
$resource[902] = 'crystal';
$resource[903] = 'deuterium';
$resource[911] = 'energy';
$resource[921] = 'darkmatter';

$reslist['ressources']  = [901, 902, 903, 911, 921];
$reslist['resstype'][1] = [901, 902, 903];
$reslist['resstype'][2] = [911];
$reslist['resstype'][3] = [921];