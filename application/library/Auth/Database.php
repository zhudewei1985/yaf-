<?php
/**
 * File Auth driver.
 * [!!] this Auth driver does not support roles nor autologin.
 *
 * @package    Elixir/Auth
 * @author    Not well-known man
 * @copyright  (c) 2007-2012 Elixir Team
 * @license
 */
class Auth_Database extends Elixir_Auth {

	// User list
	protected $_users;

	/**
	 * Constructor loads the user list into the class.
	 */
	public function __construct($config = array(),$name)
	{
		parent::__construct($config,$name);
	}

	/**
	 * Logs a user in.
	 *
	 * @param   string   $username  Username
	 * @param   string   $password  Password
	 * @param   boolean  $remember  Enable autologin (not supported)
	 * @return  boolean
	 */
	protected function _login(array $credentials, bool $remember):bool
	{
		$model = new $this->_config['model'];

        $user = $model->getUser($credentials);

        if(!empty($user) && $remember === TRUE  && array_has($credentials, 'remember_token') 
            && $credentials['remember_token'] === $this->_cache->get(crc32($user->id))) {
            return $this->complete_login($user);
        }
        
        if(!array_has($credentials, 'password')) {
            return FALSE;
        }

        $is_pass = bcrypt_check($credentials['password'], $user->password);
        if (!empty($user) && $is_pass) {
            $remember_token = '';
            if(isset($user) && $remember === TRUE) {
                $remember_token = str_random(32).time();
                $this->_cache->set(crc32($user->id), $remember_token,86400*7); // 客户端用户记住登录，保持7天
            }
            
            $user->remember_token = $remember_token;
            $model->afterLogin($user->id);
		    unset($user->password);
		    return $this->complete_login($user);
		}

		// Login failed
		return FALSE;
	}

	/**
	 * Forces a user to be logged in, without specifying a password.
	 *
	 * @param   mixed    $username  Username
	 * @return  boolean
	 */
	public function force_login($username)
	{
		// Complete the login
		return $this->complete_login($username);
	}

	/**
	 * Get the stored password for a username.
	 *
	 * @param   mixed   $username  Username
	 * @return  string
	 */
	public function password($user):string
	{
		return $user->password;
	}

	/**
	 * Compare password with original (plain text). Works for current (logged in) user
	 *
	 * @param   string   $password  Password
	 * @return  boolean
	 */
	public function check_password(string $password)
	{
		$user = $this->get();

		if ($user === FALSE)
		{
			return FALSE;
		}

		return ($password === $this->password($user));
	}

} // End Auth File
