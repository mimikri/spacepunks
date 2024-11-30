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

class Session
{
	static private $obj = NULL;
	static private bool $iniSet	= false;
	private $data = NULL;

	/**
	 * Set PHP session settings
	 *
	 * @return bool
	 */

	static public function init(): bool
	{
		if(self::$iniSet === true)
		{
			return false;
		}
		self::$iniSet = true;

		ini_set('session.use_cookies', '1');
		ini_set('session.use_only_cookies', '1');
		ini_set('session.use_trans_sid', 0);
		ini_set('session.auto_start', '0');
		ini_set('session.serialize_handler', 'php');  
		ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
		ini_set('session.gc_probability', '1');
		ini_set('session.gc_divisor', '1000');
		ini_set('session.bug_compat_warn', '0');
		ini_set('session.bug_compat_42', '0');
		ini_set('session.cookie_httponly', true);
		ini_set('session.save_path', CACHE_PATH.'sessions');
		ini_set('upload_tmp_dir', CACHE_PATH.'sessions');
		
		$HTTP_ROOT = MODE === 'INSTALL' ? dirname(HTTP_ROOT) : HTTP_ROOT;
		
		session_set_cookie_params(SESSION_LIFETIME, $HTTP_ROOT, NULL, HTTPS, true);
		session_cache_limiter('nocache');
		session_name('spacepunks');

		return true;
	}

	static private function getTempPath()
	{
		require_once 'includes/libs/wcf/BasicFileUtil.class.php';
		return BasicFileUtil::getTempFolder();
	}


	/**
	 * Create an empty session
	 *
	 * @return String
	 */

	static public function getClientIp()
    {
		if(!empty($_SERVER['HTTP_CLIENT_IP']))
        {
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'];
        }
		elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        {
			$ipAddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        elseif(!empty($_SERVER['HTTP_X_FORWARDED']))
        {
			$ipAddress = $_SERVER['HTTP_X_FORWARDED'];
        }
        elseif(!empty($_SERVER['HTTP_FORWARDED_FOR']))
        {
			$ipAddress = $_SERVER['HTTP_FORWARDED_FOR'];
        }
        elseif(!empty($_SERVER['HTTP_FORWARDED']))
        {
			$ipAddress = $_SERVER['HTTP_FORWARDED'];
        }
        elseif(!empty($_SERVER['REMOTE_ADDR']))
        {
			$ipAddress = $_SERVER['REMOTE_ADDR'];
        }
        else
        {
			$ipAddress = 'UNKNOWN';
        }
        return $ipAddress;
	}

	/**
	 * Create an empty session
	 *
	 * @return Session
	 */

	static public function create()
	{
		if(!self::existsActiveSession())
		{
			self::$obj	= new self;
			register_shutdown_function([self::$obj, 'save']);
            try {
                @session_start();
            } catch (\Throwable) {
                //throw $th;
            }
		}

		return self::$obj;
	}

	/**
	 * Wake an active session
	 *
	 * @return Session
	 */

	static public function load()
	{
		if(!self::existsActiveSession())
		{
			self::init();
			session_start();
			if(isset($_SESSION['obj']))
			{
				self::$obj	= unserialize($_SESSION['obj']);
				register_shutdown_function([self::$obj, 'save']);
			}
			else
			{
				self::create();
			}
		}

		return self::$obj;
	}

	/**
	 * Check if an active session exists
	 *
	 * @return bool
	 */

	static public function existsActiveSession(): bool
	{
		return isset(self::$obj);
	}

	public function __construct()
	{
		self::init();
	}

	public function __sleep()
	{
		return ['data'];
	}

	public function __wakeup()
	{

	}

	public function __set($name, $value)
	{
		$this->data[$name]	= $value;
	}

	public function __get($name)
	{
		if(isset($this->data[$name]))
		{
			return $this->data[$name];
		}
		else
		{
			return NULL;
		}
	}

	public function __isset($name)
	{
		return isset($this->data[$name]);
	}

	public function save(): void
	{
	    // do not save an empty session
	    $sessionId = session_id();
	    if(empty($sessionId)) {
	        return;
	    }

	    // sessions require an valid user.
	    if(empty($this->data['userId'])) {
	        $this->delete();
			return;
	    }

        $userIpAddress = self::getClientIp();

		$sql	= 'REPLACE INTO %%SESSION%% SET
		sessionID	= :sessionId,
		userID		= :userId,
		lastonline	= :lastActivity,
		userIP		= :userAddress;';

		$db		= Database::get();

		$db->replace($sql, [':sessionId'	=> session_id(), ':userId'		=> $this->data['userId'], ':lastActivity'	=> TIMESTAMP, ':userAddress'	=> $userIpAddress]);

		$sql = 'UPDATE %%USERS%% SET
		onlinetime	= :lastActivity,
		user_lastip = :userAddress
		WHERE
		id = :userId;';

		$db->update($sql, [':userAddress'	=> $userIpAddress, ':lastActivity'	=> TIMESTAMP, ':userId'		=> $this->data['userId']]);

		$this->data['lastActivity']  = TIMESTAMP;
		$this->data['sessionId']	 = session_id();
		$this->data['userIpAddress'] = $userIpAddress;
		$this->data['requestPath']	 = $this->getRequestPath();

		$_SESSION['obj']	= serialize($this);

		@session_write_close();
	}

	public function delete(): void
	{
		$sql	= 'DELETE FROM %%SESSION%% WHERE sessionID = :sessionId;';
		$db		= Database::get();

		$db->delete($sql, [':sessionId'	=> session_id()]);

		@session_destroy();
	}

	public function isValidSession(): bool
	{
		if(empty($this->data['userIpAddress']) || $this->compareIpAddress($this->data['userIpAddress'], self::getClientIp(), COMPARE_IP_BLOCKS) === false)
		{
			return false;
		}

		if($this->data['lastActivity'] < TIMESTAMP - SESSION_LIFETIME)
		{
			return false;
		}

		$sql = 'SELECT COUNT(*) as record FROM %%SESSION%% WHERE sessionID = :sessionId;';
		$db		= Database::get();

		$sessionCount = $db->selectSingle($sql, [':sessionId'	=> session_id()], 'record');

		if($sessionCount == 0)
		{
			return false;
		}

		return true;
	}

	public function selectActivePlanet(): void
	{
		$httpData	= HTTP::_GP('cp', 0);

		if(!empty($httpData))
		{
			$sql	= 'SELECT id FROM %%PLANETS%% WHERE id = :planetId AND id_owner = :userId;';

			$db	= Database::get();
			$planetId	= $db->selectSingle($sql, [':userId'	=> $this->data['userId'], ':planetId'	=> $httpData], 'id');

			if(!empty($planetId))
			{
				$this->data['planetId']	= $planetId;
			}
		}
	}

	private function getRequestPath(): string
	{
		return HTTP_ROOT.(!empty($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : '');
	}
	
	private function compareIpAddress($ip1, $ip2, int $blockCount): bool
	{
		if (str_contains((string) $ip2, ':') && str_contains((string) $ip1, ':'))
		{
			$s_ip = $this->short_ipv6($ip1, $blockCount);
			$u_ip = $this->short_ipv6($ip2, $blockCount);
		}
		else
		{
			$s_ip = implode('.', array_slice(explode('.', (string) $ip1), 0, $blockCount));
			$u_ip = implode('.', array_slice(explode('.', (string) $ip2), 0, $blockCount));
		}
		
		return ($s_ip == $u_ip);
	}

	private function short_ipv6($ip, int $length)
	{
		if ($length < 1)
		{
			return '';
		}

		$blocks = substr_count((string) $ip, ':') + 1;
		if ($blocks < 9)
		{
			$ip = str_replace('::', ':' . str_repeat('0000:', 9 - $blocks), $ip);
		}
		if ($ip[0] == ':')
		{
			$ip = '0000' . $ip;
		}
		if ($length < 4)
		{
			$ip = implode(':', array_slice(explode(':', (string) $ip), 0, 1 + $length));
		}

		return $ip;
	}
}