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

require('includes/libs/Smarty/Smarty.class.php');
		
class template extends Smarty
{
	protected $window	= 'full';
	public $jsscript	= [];
	public $script		= [];
	
	function __construct()
	{	
		parent::__construct();
		$this->smartySettings();
	}

	private function smartySettings(): void
	{
		$this->php_handling = 2;//Smarty::PHP_REMOVE;

		$this->setForceCompile(false);
		$this->setMergeCompiledIncludes(true);
		$this->setCompileCheck(true);#Set false for production!
		$this->setCacheLifetime(604800);
		$this->setCaching(Smarty::CACHING_LIFETIME_CURRENT);
		$this->setCompileDir(is_writable(CACHE_PATH) ? CACHE_PATH : $this->getTempPath());
		$this->setCacheDir($this->getCompileDir().'templates');
		$this->setTemplateDir('styles/templates/');
	}

	private function getTempPath()
	{
		$this->setForceCompile(true);
		$this->setCaching(Smarty::CACHING_OFF);

		require_once 'includes/libs/wcf/BasicFileUtil.class.php';
		return BasicFileUtil::getTempFolder();
	}
		
	public function assign_vars($var, $nocache = true): void 
	{		
		parent::assign($var, NULL, $nocache);
	}

	public function loadscript($script): void
	{
		$this->jsscript[]			= substr((string) $script, 0, -3);
	}

	public function execscript($script): void
	{
		$this->script[]				= $script;
	}
	
	private function adm_main(): void
	{
		global $LNG, $USER;
		
		$dateTimeServer		= new DateTime("now");
		if(isset($USER['timezone'])) {
			try {
				$dateTimeUser	= new DateTime("now", new DateTimeZone($USER['timezone']));
			} catch (Exception) {
				$dateTimeUser	= $dateTimeServer;
			}
		} else {
			$dateTimeUser	= $dateTimeServer;
		}

		$config	= Config::get();

		$this->assign_vars(['scripts'			=> $this->script, 'title'				=> $config->game_name.' - '.$LNG['adm_cp_title'], 'fcm_info'			=> $LNG['fcm_info'], 'lang'    			=> $LNG->getLanguage(), 'REV'				=> substr($config->VERSION, -4), 'date'				=> explode("|", date('Y\|n\|j\|G\|i\|s\|Z', TIMESTAMP)), 'Offset'			=> $dateTimeUser->getOffset() - $dateTimeServer->getOffset(), 'VERSION'			=> $config->VERSION, 'dpath'				=> 'styles/theme/gow/', 'bodyclass'			=> 'full']);
	}
	
	public function show($file): void
	{		
		global $LNG, $THEME;

		if($THEME->isCustomTPL($file))
		{
			$this->setTemplateDir($THEME->getTemplatePath());
		}

		$tplDir	= $this->getTemplateDir();
			
		if(MODE === 'INSTALL') {
			$this->setTemplateDir($tplDir[0].'install/');
		} elseif(MODE === 'ADMIN') {
			$this->setTemplateDir($tplDir[0].'adm/');
			$this->adm_main();
		}

		$this->assign_vars(['scripts'		=> $this->jsscript, 'execscript'	=> implode("\n", $this->script)]);

		$this->assign_vars(['LNG'			=> $LNG], false);
		
		$this->compile_id	= $LNG->getLanguage();
		
		parent::display($file);
	}
	
	public function display($file = NULL, $cache_id = NULL, $compile_id = NULL, $parent = NULL): void
	{
		global $LNG;
		$this->compile_id	= $LNG->getLanguage();
		parent::display($file);
	}
	
	public function gotoside($dest, $time = 3): void
	{
		$this->assign_vars(['gotoinsec'	=> $time, 'goto'		=> $dest]);
	}
	
	public function message($mes, $dest = false, $time = 3, $Fatal = false): void
	{
		global $LNG, $THEME;
	
		$this->assign_vars(['mes'		=> $mes, 'fcm_info'	=> $LNG['fcm_info'], 'Fatal'		=> $Fatal, 'dpath'		=> $THEME->getTheme()]);
		
		$this->gotoside($dest, $time);
		$this->show('error_message_body.tpl');
	}
	
	public static function printMessage($Message, $fullSide = true, $redirect = NULL): void {
		$template	= new self;
		if(!isset($redirect)) {
			$redirect	= [false, 0];
		}
		
		$template->message($Message, $redirect[0], $redirect[1], !$fullSide);
		exit;
	}
	
    /**
    * Workaround  for new Smarty Method to add custom props...
    */

    public function __get($name)
    {
        $allowed = ['template_dir' => 'getTemplateDir', 'config_dir' => 'getConfigDir', 'plugins_dir' => 'getPluginsDir', 'compile_dir' => 'getCompileDir', 'cache_dir' => 'getCacheDir'];

        if (isset($allowed[$name])) {
            return $this->{$allowed[$name]}();
        } else {
            return $this->{$name};
        }
    }
	
    public function __set($name, $value)
    {
        $allowed = ['template_dir' => 'setTemplateDir', 'config_dir' => 'setConfigDir', 'plugins_dir' => 'setPluginsDir', 'compile_dir' => 'setCompileDir', 'cache_dir' => 'setCacheDir'];

        if (isset($allowed[$name])) {
            $this->{$allowed[$name]}($value);
        } else {
            $this->{$name} = $value;
        }
    }
}
