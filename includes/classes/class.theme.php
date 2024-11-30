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
 
class Theme
{
	/**
  * @var never[]
  */
 public $skininfo;
 public $skin;
 /**
  * @var string
  */
 public $template;
 public $customtpls;
 static public $Themes;
	private ?array $THEMESETTINGS = null;
	
	function __construct()
	{	
		$this->skininfo = [];
		$this->skin		= $_SESSION['dpath'] ?? DEFAULT_THEME;
		$this->setUserTheme($this->skin);
	}
	
	function isHome(): void {
		$this->template		= ROOT_PATH.'styles/home/';
		$this->customtpls	= [];
	}
	
	function setUserTheme(string $Theme) {
		if(!file_exists(ROOT_PATH.'styles/theme/'.$Theme.'/style.cfg'))
			return false;
			
		$this->skin		= $Theme;
		$this->parseStyleCFG();
		$this->setStyleSettings();
	}
		
	function getTheme(): string {
		return './styles/theme/'.$this->skin.'/';
	}
	
	function getThemeName() {
		return $this->skin;
	}
	
	function getTemplatePath(): string {
		return ROOT_PATH.'/styles/templates/'.$this->skin.'/';
	}
		
	function isCustomTPL($tpl) {
		if(!isset($this->customtpls))
			return false;
			
		return in_array($tpl, $this->customtpls);
	}
	
	function parseStyleCFG(): void {
		require(ROOT_PATH.'styles/theme/'.$this->skin.'/style.cfg');
		$this->skininfo		= $Skin;
		$this->customtpls	= (array) $Skin['templates'];	
	}
	
	function setStyleSettings(): void {
		if(file_exists(ROOT_PATH.'styles/theme/'.$this->skin.'/settings.cfg')) {
			require(ROOT_PATH.'styles/theme/'.$this->skin.'/settings.cfg');
		}
		
		$this->THEMESETTINGS	= array_merge(['PLANET_ROWS_ON_OVERVIEW' => 2, 'SHORTCUT_ROWS_ON_FLEET1' => 2, 'COLONY_ROWS_ON_FLEET1' => 2, 'ACS_ROWS_ON_FLEET1' => 1, 'TOPNAV_SHORTLY_NUMBER' => 0], $THEMESETTINGS);
	}
	
	function getStyleSettings(): ?array {
		return $this->THEMESETTINGS;
	}
	
	static function getAvalibleSkins() {
		if(!isset(self::$Themes))
		{
			if(file_exists(ROOT_PATH.'cache/cache.themes.php'))
			{
				self::$Themes	= unserialize(file_get_contents(ROOT_PATH.'cache/cache.themes.php'));
			} else {
				$Skins	= array_diff(scandir(ROOT_PATH.'styles/theme/'), ['..', '.', '.svn', '.htaccess', 'index.htm']);
				$Themes	= [];
				foreach($Skins as $Theme) {
					if(!file_exists(ROOT_PATH.'styles/theme/'.$Theme.'/style.cfg'))
						continue;
						
					require(ROOT_PATH.'styles/theme/'.$Theme.'/style.cfg');
					$Themes[$Theme]	= $Skin['name'];
				}
				file_put_contents(ROOT_PATH.'cache/cache.themes.php', serialize($Themes));
				self::$Themes	= $Themes;
			}
		}
		return self::$Themes;
	}
}
