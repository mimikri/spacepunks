<!DOCTYPE html>
<!--[if lt IE 7 ]> <html lang="{$lang}" class="no-js ie6"> <![endif]-->
<!--[if IE 7 ]>    <html lang="{$lang}" class="no-js ie7"> <![endif]-->
<!--[if IE 8 ]>    <html lang="{$lang}" class="no-js ie8"> <![endif]-->
<!--[if IE 9 ]>    <html lang="{$lang}" class="no-js ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html lang="{$lang}" class="no-js"> <!--<![endif]-->
<head>
	<link rel="stylesheet" type="text/css" href="styles/resource/css/login/main.css?v={$REV}">
	<link rel="stylesheet" type="text/css" href="styles/resource/css/base/jquery.fancybox.css?v={$REV}">
	<link rel="shortcut icon" href="./favicon.ico" type="image/x-icon">
	<title>{block name="title"} - {$gameName}{/block}</title>
	<meta name="generator" content="spacepunks {$VERSION}">
	<!-- 
		This website is powered by spacepunks {$VERSION}
		spacepunks is a free Space Browsergame initially created by Jan-Otto Kröpke and licensed under MIT.
		spacepunks is copyright 2009-2016 of Jan-Otto Kröpke. Extensions are copyright of their respective owners.
		Information and contribution at https://github.com/mimikri/spacepunks/
	-->
	<meta name="keywords" content="Weltraum Browsergame, XNova, spacepunks, Space, Private, Server, Speed">
	<meta name="description" content="spacepunks Browsergame powerd by https://github.com/mimikri/spacepunks/"> <!-- Noob Check :) -->
	<!--[if lt IE 9]>
	<script src="scripts/base/html5.js"></script>
	<![endif]-->
	<script src="scripts/base/jquery.js?v={$REV}"></script>
	<script src="scripts/base/jquery.cookie.js?v={$REV}"></script>
	<script src="scripts/base/jquery.fancybox.js?v={$REV}"></script>
	<script src="scripts/login/main.js"></script>
	<script>{if isset($code)}var loginError = {$code|json};{/if}</script>
	{block name="script"}{/block}	
</head>
<body id="{if empty($smarty.get.page)}overview{else}{$smarty.get.page|htmlspecialchars}{/if}" class="{$bodyclass}">
	<div id="page">