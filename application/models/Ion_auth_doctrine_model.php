<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
* Name:  Ion Auth Model
*
* Author:  Ben Edmunds
* 		   ben.edmunds@gmail.com
*	  	   @benedmunds
*
* Added Awesomeness: Phil Sturgeon
*
* Location: http://github.com/benedmunds/CodeIgniter-Ion-Auth
*
* Created:  10.01.2009
*
* Last Change: 3.22.13
*
* Changelog:
* * 3-22-13 - Additional entropy added - 52aa456eef8b60ad6754b31fbdcc77bb
*
* Description:  Modified auth system based on redux_auth with extensive customization.  This is basically what Redux Auth 2 should be.
* Original Author name has been kept but that does not mean that the method has not been modified.
*
* Requirements: PHP5 or above
* 
* ===============================
* Adaptation for ORM Doctrine
* 
* Description: The Doctrine Project is the home to several PHP libraries primarily focused on database storage and object mapping.
* Are external dependencies for to bewith your project: Doctrine - http://www.doctrine-project.org
* 
* Author:  Sandro Cândido
* 		   sandro.webdesigner@gmail.com
* 
* Change:  01.04.2014
* ===============================
*/

class Ion_auth_doctrine_model extends MY_Model
{
    /**
	 * Holds an array of tables used
	 *
	 * @var array
	 **/
	public $tables = array();

	/**
	 * activation code
	 *
	 * @var string
	 **/
	public $activation_code;

	/**
	 * forgotten password key
	 *
	 * @var string
	 **/
	public $forgotten_password_code;

	/**
	 * new password
	 *
	 * @var string
	 **/
	public $new_password;

	/**
	 * Identity
	 *
	 * @var string
	 **/
	public $identity;

	/**
	 * Where
	 *
	 * @var array
	 **/
	public $_ion_where = array();

	/**
	 * Select
	 *
	 * @var array
	 **/
	public $_ion_select = array();

	/**
	 * Like
	 *
	 * @var array
	 **/
	public $_ion_like = array();

	/**
	 * Limit
	 *
	 * @var string
	 **/
	public $_ion_limit = NULL;

	/**
	 * Offset
	 *
	 * @var string
	 **/
	public $_ion_offset = NULL;

	/**
	 * Order By
	 *
	 * @var string
	 **/
	public $_ion_order_by = NULL;

	/**
	 * Order
	 *
	 * @var string
	 **/
	public $_ion_order = NULL;

	/**
	 * Hooks
	 *
	 * @var object
	 **/
	protected $_ion_hooks;

	/**
	 * Response
	 *
	 * @var string
	 **/
	protected $response = NULL;

	/**
	 * message (uses lang file)
	 *
	 * @var string
	 **/
	protected $messages;

	/**
	 * error message (uses lang file)
	 *
	 * @var string
	 **/
	protected $errors;

	/**
	 * error start delimiter
	 *
	 * @var string
	 **/
	protected $error_start_delimiter;

	/**
	 * error end delimiter
	 *
	 * @var string
	 **/
	protected $error_end_delimiter;

	/**
	 * caching of users and their groups
	 *
	 * @var array
	 **/
	public $_cache_user_in_group = array();

	/**
	 * caching of groups
	 *
	 * @var array
	 **/
	protected $_cache_groups = array();

    private $_users_groups = false;

	/**
	 * User table
	 *
	 * @var string
	 **/
	private $table_user;

	/**
	 * Groups table
	 *
	 * @var string
	 **/
	private $table_groups;

	/**
	 * Users_groups table
	 *
	 * @var string
	 **/
	private $table_users_groups;

	/**
	 * Users_groups table
	 *
	 * @var string
	 **/
	private $table_login_attempts;

	public function __construct()
	{
		parent::__construct();

		$this->load->config('ion_auth', TRUE);
		$this->load->helper('cookie');
		$this->load->helper('date');
		$this->lang->load('ion_auth');

		//initialize db tables data
		$this->tables  = $this->config->item('tables', 'ion_auth');

        //initializa names tables use orm doctrine
        $this->table_user = 'models\entities\\'.ucfirst($this->tables['users']);
        $this->table_groups = 'models\entities\\'.ucfirst($this->tables['groups']);
        $this->table_users_groups = 'models\entities\\'.ucfirst($this->tables['users_groups']);
        $this->table_login_attempts = 'models\entities\\'.ucfirst($this->tables['login_attempts']);
        
		//initialize data
		$this->identity_column = $this->config->item('identity', 'ion_auth');
		$this->store_salt      = $this->config->item('store_salt', 'ion_auth');
		$this->salt_length     = $this->config->item('salt_length', 'ion_auth');
		$this->join			   = $this->config->item('join', 'ion_auth');


		//initialize hash method options (Bcrypt)
		$this->hash_method = $this->config->item('hash_method', 'ion_auth');
		$this->default_rounds = $this->config->item('default_rounds', 'ion_auth');
		$this->random_rounds = $this->config->item('random_rounds', 'ion_auth');
		$this->min_rounds = $this->config->item('min_rounds', 'ion_auth');
		$this->max_rounds = $this->config->item('max_rounds', 'ion_auth');


		//initialize messages and error
		$this->messages    = array();
		$this->errors      = array();
		$delimiters_source = $this->config->item('delimiters_source', 'ion_auth');

		//load the error delimeters either from the config file or use what's been supplied to form validation
		if ($delimiters_source === 'form_validation')
		{
			//load in delimiters from form_validation
			//to keep this simple we'll load the value using reflection since these properties are protected
			$this->load->library('form_validation');
			$form_validation_class = new ReflectionClass("CI_Form_validation");

			$error_prefix = $form_validation_class->getProperty("_error_prefix");
			$error_prefix->setAccessible(TRUE);
			$this->error_start_delimiter = $error_prefix->getValue($this->form_validation);
			$this->message_start_delimiter = $this->error_start_delimiter;

			$error_suffix = $form_validation_class->getProperty("_error_suffix");
			$error_suffix->setAccessible(TRUE);
			$this->error_end_delimiter = $error_suffix->getValue($this->form_validation);
			$this->message_end_delimiter = $this->error_end_delimiter;
		}
		else
		{
			//use delimiters from config
			$this->message_start_delimiter = $this->config->item('message_start_delimiter', 'ion_auth');
			$this->message_end_delimiter   = $this->config->item('message_end_delimiter', 'ion_auth');
			$this->error_start_delimiter   = $this->config->item('error_start_delimiter', 'ion_auth');
			$this->error_end_delimiter     = $this->config->item('error_end_delimiter', 'ion_auth');
		}


		//initialize our hooks object
		$this->_ion_hooks = new stdClass;

		//load the bcrypt class if needed
		if ($this->hash_method == 'bcrypt') {
			if ($this->random_rounds)
			{
				$rand = rand($this->min_rounds,$this->max_rounds);
				$rounds = array('rounds' => $rand);
			}
			else
			{
				$rounds = array('rounds' => $this->default_rounds);
			}

			$this->load->library('bcrypt',$rounds);
		}

		$this->trigger_events('model_constructor');
	}

	/**
	 * Misc functions
	 *
	 * Hash password : Hashes the password to be stored in the database.
	 * Hash password db : This function takes a password and validates it
	 * against an entry in the users table.
	 * Salt : Generates a random salt value.
	 *
	 * @author Mathew
	 */

	/**
	 * Hashes the password to be stored in the database.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function hash_password($password, $salt=false, $use_sha1_override=FALSE)
	{
		if (empty($password))
		{
			return FALSE;
		}

		//bcrypt
		if ($use_sha1_override === FALSE && $this->hash_method == 'bcrypt')
		{
			return $this->bcrypt->hash($password);
		}


		if ($this->store_salt && $salt)
		{
			return  sha1($password . $salt);
		}
		else
		{
			$salt = $this->salt();
			return  $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
		}
	}

	/**
	 * This function takes a password and validates it
	 * against an entry in the users table.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function hash_password_db($id, $password, $use_sha1_override=FALSE)
	{
		if (empty($id) || empty($password))
		{
			return FALSE;
		}
        
        $qb = $this->em->createQueryBuilder();

        $qb->select( 'u' )
            ->from( $this->table_user, 'u' )
            ->where( $qb->expr()->eq('u.id', ':id' ) )
            ->setParameter('id', $id)
            ->setMaxResults('1');

        $query = $qb->getQuery()->getResult();

		if ( count( $query ) !== 1)
		{
			return FALSE;
		}
        
		$hash_password_db = $query[0];

		// bcrypt
		if ($use_sha1_override === FALSE && $this->hash_method == 'bcrypt')
		{
			if ($this->bcrypt->verify($password,$hash_password_db->getPassword()))
			{
				return TRUE;
			}

			return FALSE;
		}

		// sha1
		if ($this->store_salt)
		{
			$db_password = sha1($password . $hash_password_db->getSalt());
		}
		else
		{
			$salt = substr($hash_password_db->getPassword(), 0, $this->salt_length);

			$db_password =  $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
		}

		if($db_password == $hash_password_db->getPassword())
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	/**
	 * Generates a random salt value for forgotten passwords or any other keys. Uses SHA1.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function hash_code($password)
	{
		return $this->hash_password($password, FALSE, TRUE);
	}

	/**
	 * Generates a random salt value.
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function salt()
	{
		return substr(md5(uniqid(rand(), true)), 0, $this->salt_length);
	}

	/**
	 * Activation functions
	 *
	 * Activate : Validates and removes activation code.
	 * Deactivae : Updates a users row with an activation code.
	 *
	 * @author Mathew
	 */

	/**
	 * activate
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function activate($id, $code = false)
	{
		$this->trigger_events('pre_activate');

		if ($code !== FALSE)
		{
            $qb = $this->em->createQueryBuilder();

            $qb->select( 'u' )
                ->from( $this->table_user, 'u' )
                ->where( $qb->expr()->eq('u.activation_code', ':columm' ) )
                ->setParameter('columm', $code)
                ->setMaxResults('1');

            $result_user = $qb->getQuery()->getResult();
            $user = $result_user[0];

			if( count( $user ) !== 1 )
			{
				$this->trigger_events(array('post_activate', 'post_activate_unsuccessful'));
				$this->set_error('activate_unsuccessful');
				return FALSE;
			}
		}
		else
		{
			$this->trigger_events('extra_where');

            $user =$this->em->getRepository($this->table_user)->find($id);
		}

        $user->setActivationCode( null );
        $user->setActive( 1 );            

        $this->em->getConnection()->beginTransaction();

        try{

            $this->em->persist( $user );
            $this->em->flush();

            $this->em->getConnection()->commit();

			$this->trigger_events(array('post_activate', 'post_activate_successful'));
			$this->set_message('activate_successful');
            
            return true;

        } catch (Exception $e) {

            $this->em->getConnection()->rollback();
            $this->em->close();

			$this->trigger_events(array('post_activate', 'post_activate_unsuccessful'));
			$this->set_error('activate_unsuccessful');

            return false;
        }        
	}

	/**
	 * Deactivate
	 *
	 * @return void
	 * @author Mathew
	 **/
	public function deactivate($id = NULL)
	{
		$this->trigger_events('deactivate');

		if (!isset($id))
		{
			$this->set_error('deactivate_unsuccessful');
			return FALSE;
		}

		$activation_code       = sha1(md5(microtime()));
		$this->activation_code = $activation_code;

		$data = array(
		    'activation_code' => $activation_code,
		    'active'          => 0
		);

		$this->em->getConnection()->beginTransaction();

        $user = $this->user($id)->row();
        try {

            $user->setActivationCode( $data['activation_code'] );
            $user->setActive($data['active']);
            
            $this->trigger_events('extra_where');

            $this->em->persist( $user );
            $this->em->flush();

            $this->em->getConnection()->commit();

			$this->set_message('deactivate_successful');
            
            return true;
            
        } catch (Exception $e) {
            
            $this->em->getConnection()->rollback();
            $this->em->close();
            
			$this->set_error('deactivate_unsuccessful');

            return false;
        }
	}

	public function clear_forgotten_password_code($code) {

		if (empty($code))
		{
			return FALSE;
		}

        $user = $this->where('forgotten_password_code', $code)->users()->row();
        
        $this->em->getConnection()->beginTransaction();
        try {

            $user->setForgottenPasswordCode( null );
            $user->setForgottenPasswordTime( null );

            $this->em->persist( $user );
            $this->em->flush();

            $this->em->getConnection()->commit();

            return true;
            
        } catch (Exception $e) {
            
            $this->em->getConnection()->rollback();
            $this->em->close();

            return false;
        }

	}

	/**
	 * reset password
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function reset_password($identity, $new) {
		$this->trigger_events('pre_change_password');

		if (!$this->identity_check($identity)) {
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			return FALSE;
		}

		$this->trigger_events('extra_where');

        $qb = $this->em->createQueryBuilder();

        $qb->select( 'u' )
            ->from( $this->table_user, 'u' )
            ->where( $qb->expr()->eq('u.'.$this->identity_column, ':columm' ) )
            ->setParameter('columm', $identity)
            ->setMaxResults('1');

        $query = $qb->getQuery()->getResult();
        
		if ( count( $query )  !== 1){
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
			return FALSE;
        }
            
        $this->em->getConnection()->beginTransaction();
        try {

            $user = $query[0];
            $new = $this->hash_password($new, $user->getSalt());

            //store the new password and reset the remember code so all remembered instances have to re-login
            //also clear the forgotten password code
            $user->setPassword( $new );
            $user->setRememberCode( null );
            $user->setForgottenPasswordCode( null );
            $user->setForgottenPasswordTime( null );

            $this->em->persist( $user );
            $this->em->flush();

            $this->em->getConnection()->commit();

			$this->trigger_events(array('post_change_password', 'post_change_password_successful'));
			$this->set_message('password_change_successful');

            return true;

        } catch (Exception $e) {

            $this->em->getConnection()->rollback();
            $this->em->close();

			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');

            return false;
        }
	}

	/**
	 * change password
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function change_password($identity, $old, $new)
	{
		$this->trigger_events('pre_change_password');

		$this->trigger_events('extra_where');

        $qb = $this->em->createQueryBuilder();

        $qb->select( 'u' )
            ->from( $this->table_user, 'u' )
            ->where( $qb->expr()->eq('u.'.$this->identity_column, ':columm' ) )
            ->setParameter('columm', $identity)
            ->setMaxResults('1');

        $query = $qb->getQuery()->getResult();
        
		if ( count( $query )  !== 1)
		{
			$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
			$this->set_error('password_change_unsuccessful');
			return FALSE;
		}

		$user = $query[0];

		$old_password_matches = $this->hash_password_db($user->getId(), $old);

		if ($old_password_matches === TRUE)
		{
			//store the new password and reset the remember code so all remembered instances have to re-login
			$hashed_new_password  = $this->hash_password($new, $user->getSalt());
			$this->trigger_events('extra_where');

            $this->em->getConnection()->beginTransaction();
            try {
            
                $user->setPassword( $hashed_new_password );
                $user->setRememberCode( null );

                $this->em->persist( $user );
                $this->em->flush();

                $this->em->getConnection()->commit();

				$this->trigger_events(array('post_change_password', 'post_change_password_successful'));
				$this->set_message('password_change_successful');
                
                return TRUE;
                
            } catch (Exception $e) {

                $this->em->getConnection()->rollback();
                $this->em->close();

				$this->trigger_events(array('post_change_password', 'post_change_password_unsuccessful'));
				$this->set_error('password_change_unsuccessful');

                return FALSE;
            }
		}

		$this->set_error('password_change_unsuccessful');
		return FALSE;
	}

	/**
	 * Checks username
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function username_check($username = '')
	{
		$this->trigger_events('username_check');

		if (empty($username))
		{
			return FALSE;
		}

		$this->trigger_events('extra_where');

        return count( $this->em->getRepository($this->table_user)->findByUsername( $username ) ) > 0;
	}

	/**
	 * Checks email
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function email_check($email = '')
	{
		$this->trigger_events('email_check');

		if (empty($email))
		{
			return FALSE;
		}

		$this->trigger_events('extra_where');
        
        return count( $this->em->getRepository($this->table_user)->findByEmail( $email ) ) > 0;
	}

	/**
	 * Identity check
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function identity_check($identity = '')
	{
		$this->trigger_events('identity_check');

		if (empty($identity))
		{
			return FALSE;
		}
        
        $identity_column = 'findBy'.ucfirst($this->identity_column);        
        return count( $this->em->getRepository($this->table_user)->$identity_column( $identity ) ) > 0;
	}

	/**
	 * Insert a forgotten password key.
	 *
	 * @return bool
	 * @author Mathew
	 * @updated Ryan
	 * @updated 52aa456eef8b60ad6754b31fbdcc77bb
	 **/
	public function forgotten_password($identity)
	{
		if (empty($identity))
		{
			$this->trigger_events(array('post_forgotten_password', 'post_forgotten_password_unsuccessful'));
			return FALSE;
		}

		//All some more randomness
		$activation_code_part = "";
		if(function_exists("openssl_random_pseudo_bytes")) {
			$activation_code_part = openssl_random_pseudo_bytes(128);
		}

		for($i=0;$i<1024;$i++) {
			$activation_code_part = sha1($activation_code_part . mt_rand() . microtime());
		}

		$key = $this->hash_code($activation_code_part.$identity);

		$this->forgotten_password_code = $key;

		$this->trigger_events('extra_where');
        
        $user = $this->where($this->identity_column, $identity)->users()->row();

        $this->em->getConnection()->beginTransaction();
        try {

            $user->setForgottenPasswordCode( $key );
            $user->setForgottenPasswordTime( time() );

            $this->em->persist( $user );
            $this->em->flush();

            $this->em->getConnection()->commit();

			$this->trigger_events(array('post_forgotten_password', 'post_forgotten_password_successful'));
            
            return true;
            
        } catch (Exception $e) {
            
            $this->em->getConnection()->rollback();
            $this->em->close();
            

			$this->trigger_events(array('post_forgotten_password', 'post_forgotten_password_unsuccessful'));

            return false;
        }
	}

	/**
	 * Forgotten Password Complete
	 *
	 * @return string
	 * @author Mathew
	 **/
	public function forgotten_password_complete($code, $salt=FALSE)
	{
		$this->trigger_events('pre_forgotten_password_complete');

		if (empty($code))
		{
			$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful'));
			return FALSE;
		}

		$profile = $this->where('forgotten_password_code', $code)->users()->row(); //pass the code to profile

		if ($profile) {

			if ($this->config->item('forgot_password_expiration', 'ion_auth') > 0) {
				//Make sure it isn't expired
				$expiration = $this->config->item('forgot_password_expiration', 'ion_auth');
				if (time() - $profile->forgotten_password_time > $expiration) {
					//it has expired
					$this->set_error('forgot_password_expired');
					$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful'));
					return FALSE;
				}
			}

			$password = $this->salt();

			$data = array(
			    'password'                => $this->hash_password($password, $salt),
			    'forgotten_password_code' => NULL,
			    'active'                  => 1,
			 );

			$this->db->update($this->tables['users'], $data, array('forgotten_password_code' => $code));

			$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_successful'));
			return $password;
		}

		$this->trigger_events(array('post_forgotten_password_complete', 'post_forgotten_password_complete_unsuccessful'));
		return FALSE;
	}

	/**
	 * register
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function register($username, $password, $email, $additional_data = array(), $groups = array())
	{
		$this->trigger_events('pre_register');

		$manual_activation = $this->config->item('manual_activation', 'ion_auth');

		if ($this->identity_column == 'email' && $this->email_check($email))
		{
			$this->set_error('account_creation_duplicate_email');
			return FALSE;
		}
		elseif ($this->identity_column == 'username' && $this->username_check($username))
		{
			$this->set_error('account_creation_duplicate_username');
			return FALSE;
		}

		// If username is taken, use username1 or username2, etc.
		if ($this->identity_column != 'username')
		{
			$original_username = $username;
			for($i = 0; $this->username_check($username); $i++)
			{
				if($i > 0)
				{
					$username = $original_username . $i;
				}
			}
		}

        $ip_address = $this->_prepare_ip($this->input->ip_address());
		$salt       = $this->store_salt ? $this->salt() : FALSE;
		$password   = $this->hash_password($password, $salt);
        
		// Users table.        
        $user = new $this->table_user;
        $user->setUsername( $username );
        $user->setPassword( $password );
        $user->setEmail( $email );
        $user->setIpAddress( $ip_address );
        $user->setCreatedOn( time() );
        $user->setLastLogin( time() );
        $user->setActive( $manual_activation === false ? 1 : 0 );
        
		if ($this->store_salt)
		{
            $user->setSalt( $salt );
		}

        $user->setFirstName( $additional_data['first_name'] );
        $user->setLastName( $additional_data['last_name'] );
        $user->setCompany( $additional_data['company'] );
        $user->setPhone( $additional_data['phone'] );
        
        $groups = $this->em->getRepository($this->table_groups)->findBy( array('name' => $this->config->item('default_group', 'ion_auth')) );
        if(empty($groups)){
            $this->set_error('Group '.$this->config->item('default_group', 'ion_auth').' not exists');
			return FALSE;
        }
        
        $users_groups = new $this->table_users_groups;
        $groups[0]->addUsersGroup( $users_groups );
        $users_groups->setGroups( $groups[0] );
        $users_groups->setUsers( $user );

        $user->addUsersGroup( $users_groups );

		$this->trigger_events('extra_set');

		$this->em->getConnection()->beginTransaction();

        try{

            $this->em->persist( $user );
            $this->em->flush();
            
            $this->trigger_events('post_register');
            
            $this->em->getConnection()->commit();

            return $user->getId();
            
        } catch (Exception $e) {
            
            $this->em->getConnection()->rollback();
            $this->em->close();

            return false;
        }
	}

	/**
	 * login
	 *
	 * @return bool
	 * @author Mathew
	 **/
	public function login($identity, $password, $remember=FALSE)
	{
		$this->trigger_events('pre_login');

		if (empty($identity) || empty($password))
		{
			$this->set_error('login_unsuccessful');
			return FALSE;
		}

		$this->trigger_events('extra_where');

        $qb = $this->em->createQueryBuilder();

        $qb->select( 'u' )
            ->from( $this->table_user, 'u' )
            ->where( $qb->expr()->eq('u.'.$this->identity_column, ':columm' ) )
            ->setParameter('columm', $identity)
            ->setMaxResults('1');

        $query = $qb->getQuery()->getResult();
        
		if($this->is_time_locked_out($identity))
		{
			//Hash something anyway, just to take up time
			$this->hash_password($password);

			$this->trigger_events('post_login_unsuccessful');
			$this->set_error('login_timeout');

			return FALSE;
		}

		if ( count( $query ) === 1 )
		{
			$user = $query[0];

			$password = $this->hash_password_db($user->getId(), $password);

			if ($password === TRUE)
			{
				if ($user->getActive() == 0)
				{
					$this->trigger_events('post_login_unsuccessful');
					$this->set_error('login_unsuccessful_not_active');

					return FALSE;
				}

				$this->set_session($user);

				$this->update_last_login($user);

				if ($remember && $this->config->item('remember_users', 'ion_auth'))
				{
					$this->remember_user($user->getId());
				}

				$this->trigger_events(array('post_login', 'post_login_successful'));
				$this->set_message('login_successful');

				return TRUE;
			}
		}

		//Hash something anyway, just to take up time
		$this->hash_password($password);

		$this->trigger_events('post_login_unsuccessful');
		$this->set_error('login_unsuccessful');

		return FALSE;
	}

	/**
	 * is_max_login_attempts_exceeded
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param string $identity
	 * @return boolean
	 **/
	public function is_max_login_attempts_exceeded($identity) {
		if ($this->config->item('track_login_attempts', 'ion_auth')) {
			$max_attempts = $this->config->item('maximum_login_attempts', 'ion_auth');
			if ($max_attempts > 0) {
				$attempts = $this->get_attempts_num($identity);
				return $attempts >= $max_attempts;
			}
		}
		return FALSE;
	}

	/**
	 * Get number of attempts to login occured from given IP-address or identity
	 * Based on code from Tank Auth, by Ilya Konyukhov (https://github.com/ilkon/Tank-Auth)
	 *
	 * @param	string $identity
	 * @return	int
	 */
	function get_attempts_num($identity)
	{
        if ($this->config->item('track_login_attempts', 'ion_auth')) {
            $ip_address = $this->_prepare_ip($this->input->ip_address());
            $this->db->select('1', FALSE);
            if ($this->config->item('track_login_ip_address', 'ion_auth')) $this->db->where('ip_address', $ip_address);
            else if (strlen($identity) > 0) $this->db->or_where('login', $identity);
            $qres = $this->db->get($this->tables['login_attempts']);
            return $qres->num_rows();
        }
        return 0;
	}

	/**
	 * Get a boolean to determine if an account should be locked out due to
	 * exceeded login attempts within a given period
	 *
	 * @return	boolean
	 */
	public function is_time_locked_out($identity) {

		return $this->is_max_login_attempts_exceeded($identity) && $this->get_last_attempt_time($identity) > time() - $this->config->item('lockout_time', 'ion_auth');
	}

	/**
	 * Get the time of the last time a login attempt occured from given IP-address or identity
	 *
	 * @param	string $identity
	 * @return	int
	 */
	public function get_last_attempt_time($identity) {
		if ($this->config->item('track_login_attempts', 'ion_auth')) {
			$ip_address = $this->_prepare_ip($this->input->ip_address());

			$this->db->select_max('time');
            if ($this->config->item('track_login_ip_address', 'ion_auth')) $this->db->where('ip_address', $ip_address);
			else if (strlen($identity) > 0) $this->db->or_where('login', $identity);
			$qres = $this->db->get($this->tables['login_attempts'], 1);

			if($qres->num_rows() > 0) {
				return $qres->row()->time;
			}
		}

		return 0;
	}

	public function limit($limit)
	{
		$this->trigger_events('limit');
		$this->_ion_limit = $limit;

		return $this;
	}

	public function offset($offset)
	{
		$this->trigger_events('offset');
		$this->_ion_offset = $offset;

		return $this;
	}

	public function where($where, $value = NULL)
	{
		$this->trigger_events('where');

		if (!is_array($where))
		{
			$where = array($where => $value);
		}

		array_push($this->_ion_where, $where);

		return $this;
	}

	public function like($like, $value = NULL, $position = 'both')
	{
		$this->trigger_events('like');

		if (!is_array($like))
		{
			$like = array($like => array(
				'value'    => $value,
				'position' => $position,
			));
		}

		array_push($this->_ion_like, $like);

		return $this;
	}

	public function select($select)
	{
		$this->trigger_events('select');

		$this->_ion_select[] = $select;

		return $this;
	}

	public function order_by($by, $order='desc')
	{
		$this->trigger_events('order_by');

		$this->_ion_order_by = $by;
		$this->_ion_order    = $order;

		return $this;
	}

	public function row()
	{
		$this->trigger_events('row');

		$row = $this->response->getResult();
		unset( $this->response );

        if( count($row) < 1 )
            return array();
        else        
            return $row[0];
	}

	public function row_array()
	{
		$this->trigger_events(array('row', 'row_array'));

		$row = $this->response->row_array();
		$this->response->free_result();

		return $row;
	}

	public function result()
	{
		$this->trigger_events('result');

		$result = $this->response->getResult();
		unset( $this->response );

        if( count($result) < 1 )
            return array();
        else{
            if( $this->_users_groups ){ //test for return user group default ion_auth
                $this->_users_groups = false;
                return $result[0]->getUsersGroups()->toArray();
            }else
                return $result;
        }
	}

	public function result_array()
	{
		$this->trigger_events(array('result', 'result_array'));

		$result = $this->response->result_array();
		$this->response->free_result();

		return $result;
	}

	public function num_rows()
	{
		$this->trigger_events(array('num_rows'));

		$result = $this->response->getResult();
		unset( $this->response );

		return count( $result );
	}

    /**
	 * users
	 *
	 * @return object Users
	 * @author Ben Edmunds
	 **/
	public function users()
	{
		$this->trigger_events('users');
        
        $qb = $this->em->createQueryBuilder();
        
        $qb->select( 'u' )
            ->from( $this->table_user, 'u' );

		//run each where that was passed
		if (isset($this->_ion_where) && !empty($this->_ion_where))
		{
			foreach ($this->_ion_where as $where)
			{
                $chave = array_keys($where);
                $valor = array_values($where);
                $qb->andWhere( $qb->expr()->eq('u.'.$chave[0], ':'.$chave[0] ) )
                    ->setParameter($chave[0], $valor[0]);
			}

			$this->_ion_where = array();
		}

        
        $result = $qb->getQuery();
        
        $this->response = $result;
        
        if( count($result) < 1 )
            return array();
        else
            return $this;
/*
		if (isset($this->_ion_like) && !empty($this->_ion_like))
		{
			foreach ($this->_ion_like as $like)
			{
				$this->db->or_like($like);
			}

			$this->_ion_like = array();
		}

		if (isset($this->_ion_limit) && isset($this->_ion_offset))
		{
			$this->db->limit($this->_ion_limit, $this->_ion_offset);

			$this->_ion_limit  = NULL;
			$this->_ion_offset = NULL;
		}
		else if (isset($this->_ion_limit))
		{
			$this->db->limit($this->_ion_limit);

			$this->_ion_limit  = NULL;
		}

		//set the order
		if (isset($this->_ion_order_by) && isset($this->_ion_order))
		{
			$this->db->order_by($this->_ion_order_by, $this->_ion_order);

			$this->_ion_order    = NULL;
			$this->_ion_order_by = NULL;
		}

		$this->response = $this->db->get($this->tables['users']);

		return $this;

 */
	}

	/**
	 * user
	 *
	 * @return object
	 * @author Ben Edmunds
	 **/
	public function user($id = NULL)
	{
		$this->trigger_events('user');

		//if no id was passed use the current users id
		$id || $id = $this->session->userdata('user_id');
        
        $qb = $this->em->createQueryBuilder();

        $qb->select( 'u' )
            ->from( $this->table_user, 'u' )
            ->where( $qb->expr()->eq('u.id', ':id' ) )
            ->setParameter('id', $id)
            ->setMaxResults('1');
        
        $this->response = $qb->getQuery();
        
        return $this;
        
	}

	/**
	 * get_users_groups
	 *
	 * @return array
	 * @author Ben Edmunds
	 **/
	public function get_users_groups($id=FALSE)
	{

		$this->trigger_events('get_users_group');

		//if no id was passed use the current users id
		$id || $id = $this->session->userdata('user_id');

        $qb = $this->em->createQueryBuilder();
        
        $qb->select( 'u' )
            ->from( $this->table_user, 'u' )
            ->where( $qb->expr()->eq('u.id', ':id_user') )
            ->setParameter('id_user', (int)$id);

        $this->response = $qb->getQuery();

        $this->_users_groups = true;
        
        return $this;

	}

	/**
	 * remove_from_group
	 *
	 * @return bool
	 * @author Sandro C. Oliveira
	 **/
	public function remove_from_group($users_groups)
	{
		$this->trigger_events('remove_from_group');

		$this->em->getConnection()->beginTransaction();

        try {
            $this->em->remove( $users_groups );
            $this->em->flush();

            $this->em->getConnection()->commit();
            
            return true;
            
        } catch (Exception $e) {
            
            $this->em->getConnection()->rollback();
            $this->em->close();

			$this->trigger_events(array('post_delete_group', 'post_delete_group_unsuccessful'));
			$this->set_error('delete_unsuccessful');
            
            return false;
        }
	}

	/**
	 * groups
	 *
	 * @return object
	 * @author Ben Edmunds
	 **/
	public function groups()
	{
		$this->trigger_events('groups');
        
        $qb = $this->em->createQueryBuilder();
        
        $qb->select( 'g' )
            ->from( $this->table_groups, 'g' );

        $result = $qb->getQuery()->getResult();

        if( count($result) < 1 )
            return array();
        else
            return $result;
	}

	/**
	 * group
	 *
	 * @return object
	 * @author Ben Edmunds
	 **/
	public function group($id = NULL)
	{
		$this->trigger_events('group');

        $qb = $this->em->createQueryBuilder();
        
        $qb->select( 'g' )
            ->from( $this->table_groups, 'g' )
            ->where( $qb->expr()->eq('g.id', ':id_group') )
            ->setParameter('id_group', $id);

        $this->response = $qb->getQuery();

        return $this;
	}

	/**
	 * update
	 *
	 * @return bool
	 * @author Phil Sturgeon
	 **/
	public function update($id, array $data)
	{
		$this->trigger_events('pre_update_user');

		$this->em->getConnection()->beginTransaction();

        $user = $this->user($id)->row();
        try {

            if (array_key_exists($this->identity_column, $data) && $this->identity_check($data[$this->identity_column]) && $user->{$this->identity_column} !== $data[$this->identity_column])
            {
                $this->db->trans_rollback();
                $this->set_error('account_creation_duplicate_'.$this->identity_column);

                $this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
                $this->set_error('update_unsuccessful');

                return FALSE;
            }

            if (array_key_exists('username', $data) || array_key_exists('password', $data) || array_key_exists('email', $data))
            {
                if (array_key_exists('password', $data))
                {
                    if( ! empty($data['password']))
                    {
                        $user->setPassword( $this->hash_password($data['password'], $user->getSalt()) );
                    }
                    else
                    {
                        // unset password so it doesn't effect database entry if no password passed
                        unset($data['password']);
                    }
                }
            }
            $user->setFirstName($data['first_name']);
            $user->setLastName($data['last_name']);
            $user->setCompany($data['company']);
            $user->setPhone($data['phone']);
            
            $this->trigger_events('extra_where');

            $this->em->persist( $user );
            $this->em->flush();

            $this->em->getConnection()->commit();

            $this->trigger_events(array('post_update_user', 'post_update_user_successful'));
            $this->set_message('update_successful');
            
            return true;
            
        } catch (Exception $e) {
            
            $this->em->getConnection()->rollback();
            $this->em->close();
            
            $this->trigger_events(array('post_update_user', 'post_update_user_unsuccessful'));
            $this->set_error('update_unsuccessful');

            return false;
        }
	}

	/**
	* delete_user
	*
	* @return bool
	* @author Phil Sturgeon
	**/
	public function delete_user($id)
	{
		$this->trigger_events('pre_delete_user');

		$this->db->trans_begin();

		// remove user from groups
		$this->remove_from_group(NULL, $id);

		// delete user from users table should be placed after remove from group
		$this->db->delete($this->tables['users'], array('id' => $id));

		// if user does not exist in database then it returns FALSE else removes the user from groups
		if ($this->db->affected_rows() == 0)
		{
		    return FALSE;
		}

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$this->trigger_events(array('post_delete_user', 'post_delete_user_unsuccessful'));
			$this->set_error('delete_unsuccessful');
			return FALSE;
		}

		$this->db->trans_commit();

		$this->trigger_events(array('post_delete_user', 'post_delete_user_successful'));
		$this->set_message('delete_successful');
		return TRUE;
	}

	/**
	 * update_last_login
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function update_last_login($user)
	{
		$this->trigger_events('update_last_login');

		$this->load->helper('date');
        
        $user->setLastLogin( time() );
        
        $this->em->persist( $user );
        $this->em->flush();
        
        return;
	}

	/**
	 * set_lang
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function set_lang($lang = 'en')
	{
		$this->trigger_events('set_lang');

		// if the user_expire is set to zero we'll set the expiration two years from now.
		if($this->config->item('user_expire', 'ion_auth') === 0)
		{
			$expire = (60*60*24*365*2);
		}
		// otherwise use what is set
		else
		{
			$expire = $this->config->item('user_expire', 'ion_auth');
		}

		set_cookie(array(
			'name'   => 'lang_code',
			'value'  => $lang,
			'expire' => $expire
		));

		return TRUE;
	}

	/**
	 * set_session
	 *
	 * @return bool
	 * @author jrmadsen67
	 **/
	public function set_session($user)
	{

		$this->trigger_events('pre_set_session');

        $identity = 'get'.ucfirst($this->identity_column);
		$session_data = array(
		    'identity'             => $user->$identity(),
		    'username'             => $user->getUsername(),
		    'email'                => $user->getEmail(),
		    'user_id'              => $user->getId(), //everyone likes to overwrite id so we'll use user_id
		    'old_last_login'       => $user->getLastLogin()
		);

		$this->session->set_userdata($session_data);

		$this->trigger_events('post_set_session');

		return TRUE;
	}

	/**
	 * remember_user
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function remember_user($id)
	{
		$this->trigger_events('pre_remember_user');

		if (!$id)
		{
			return FALSE;
		}

        $user = $this->em->getRepository( $this->table_user )->find( array('id' => $id) );

		$salt = sha1( $user->getPassword() );
        
		$this->em->getConnection()->beginTransaction();
        try {
        
            $user->setRememberCode( $salt );

            $this->em->persist( $user );
            $this->em->flush();
            
			// if the user_expire is set to zero we'll set the expiration two years from now.
			if($this->config->item('user_expire', 'ion_auth') === 0)
			{
				$expire = (60*60*24*365*2);
			}
			// otherwise use what is set
			else
			{
				$expire = $this->config->item('user_expire', 'ion_auth');
			}

            $identity = 'get'.ucfirst($this->identity_column );            
			set_cookie(array(
			    'name'   => 'identity',
			    'value'  => $user->$identity(),
			    'expire' => $expire
			));

			set_cookie(array(
			    'name'   => 'remember_code',
			    'value'  => $salt,
			    'expire' => $expire
			));

            $this->em->getConnection()->commit();
            
			$this->trigger_events(array('post_remember_user', 'remember_user_successful'));

			return TRUE;
        } catch (Exception $e) {
            
            $this->em->getConnection()->rollback();
            $this->em->close();
            
            $this->trigger_events(array('post_remember_user', 'remember_user_unsuccessful'));
            return FALSE;
        }

	}

	/**
	 * login_remembed_user
	 *
	 * @return bool
	 * @author Ben Edmunds
	 **/
	public function login_remembered_user()
	{
		$this->trigger_events('pre_login_remembered_user');

		//check for valid data
		if (!get_cookie('identity') || !get_cookie('remember_code') || !$this->identity_check(get_cookie('identity')))
		{
			$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_unsuccessful'));
			return FALSE;
		}

		//get the user
		$this->trigger_events('extra_where');
        
        $qb = $this->em->createQueryBuilder();

        $qb->select( 'u' )
            ->from( $this->table_user, 'u' )
            ->where( $qb->expr()->andX( 
                                    $qb->expr()->eq('u.'.$this->identity_column, ':columm' ),
                                    $qb->expr()->eq('u.remember_code', ':remember_code' )
                                    ) )
            ->setParameter('columm', get_cookie('identity'))
            ->setParameter('remember_code', get_cookie('remember_code'))
            ->setMaxResults('1');

        $query = $qb->getQuery()->getResult();
        
		//if the user was found, sign them in
		if ( count( $query ) === 1 )
		{
			$user = $query[0];

			$this->update_last_login($user);

			$this->set_session($user);

			//extend the users cookies if the option is enabled
			if ($this->config->item('user_extend_on_login', 'ion_auth'))
			{
				$this->remember_user($user->getId());
			}

			$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_successful'));
			return TRUE;
		}

		$this->trigger_events(array('post_login_remembered_user', 'post_login_remembered_user_unsuccessful'));
		return FALSE;
	}


	/**
	 * create_group
	 *
	 * @author aditya menon
	*/
	public function create_group($group_name = FALSE, $group_description = '', $additional_data = array())
	{
		// bail if the group name was not passed
		if(!$group_name)
		{
			$this->set_error('group_name_required');
			return FALSE;
		}

		// bail if the group name already exists

        $existing_group = count( $existing_group = $this->em->getRepository('models\entities\Groups')->findBy( array('name' => $group_name) ) );

		if($existing_group !== 0)
		{
			$this->set_error('group_already_exists');
			return FALSE;
		}

		$this->trigger_events('extra_group_set');

		// insert the new group        
        $group = new $this->table_groups;
        $group->setName( $group_name );
        $group->setDescription( $group_description );
        
        $this->em->persist( $group );
        $this->em->flush();        

		// report success
		$this->set_message('group_creation_successful');
		// return the brand new group id
		return $group->getId();
	}

	/**
	 * update_group
	 *
	 * @return bool
	 * @author aditya menon
	 **/
	public function update_group($group_id = FALSE, $group_name = FALSE, $additional_data = array())
	{
		if (empty($group_id)) return FALSE;

		if (!empty($group_name))
		{
            
			// we are changing the name, so do some checks

			// bail if the group name already exists            
            $group = $this->em->getRepository( $this->table_groups )->findBy( array('name' => $group_name) );
            if(!empty($group)){
                $obj_group = $group[0];
                $id_group = $group[0]->getId();
                if(isset($id_group) && $id_group != $group_id)
                {
                    $this->set_error('group_already_exists');
                    return FALSE;
                }
            }else{
                $group = $this->em->getRepository( $this->table_groups )->findBy( array('id' => $group_id) );
                $obj_group = $group[0];
            }
            $obj_group->setName( $group_name );

            // IMPORTANT!! Third parameter was string type $description; this following code is to maintain backward compatibility
            // New projects should work with 3rd param as array
            if (is_string($additional_data)) $additional_data = array('description' => $additional_data);

            $obj_group->setDescription( $additional_data['description'] );

            $this->em->persist( $obj_group );
            $this->em->flush();        
		}

		$this->set_message('group_update_successful');

		return TRUE;
	}

	/**
	* delete_group
	*
	* @return bool
	* @author aditya menon
	**/
	public function delete_group($group_id = FALSE)
	{
		// bail if mandatory param not set
		if(!$group_id || empty($group_id))
		{
			return FALSE;
		}

		$this->trigger_events('pre_delete_group');

		$this->db->trans_begin();

		// remove all users from this group
		$this->db->delete($this->tables['users_groups'], array($this->join['groups'] => $group_id));
		// remove the group itself
		$this->db->delete($this->tables['groups'], array('id' => $group_id));

		if ($this->db->trans_status() === FALSE)
		{
			$this->db->trans_rollback();
			$this->trigger_events(array('post_delete_group', 'post_delete_group_unsuccessful'));
			$this->set_error('group_delete_unsuccessful');
			return FALSE;
		}

		$this->db->trans_commit();

		$this->trigger_events(array('post_delete_group', 'post_delete_group_successful'));
		$this->set_message('group_delete_successful');
		return TRUE;
	}

	public function set_hook($event, $name, $class, $method, $arguments)
	{
		$this->_ion_hooks->{$event}[$name] = new stdClass;
		$this->_ion_hooks->{$event}[$name]->class     = $class;
		$this->_ion_hooks->{$event}[$name]->method    = $method;
		$this->_ion_hooks->{$event}[$name]->arguments = $arguments;
	}

	public function remove_hook($event, $name)
	{
		if (isset($this->_ion_hooks->{$event}[$name]))
		{
			unset($this->_ion_hooks->{$event}[$name]);
		}
	}

	public function remove_hooks($event)
	{
		if (isset($this->_ion_hooks->$event))
		{
			unset($this->_ion_hooks->$event);
		}
	}

	protected function _call_hook($event, $name)
	{
		if (isset($this->_ion_hooks->{$event}[$name]) && method_exists($this->_ion_hooks->{$event}[$name]->class, $this->_ion_hooks->{$event}[$name]->method))
		{
			$hook = $this->_ion_hooks->{$event}[$name];

			return call_user_func_array(array($hook->class, $hook->method), $hook->arguments);
		}

		return FALSE;
	}

	public function trigger_events($events)
	{
		if (is_array($events) && !empty($events))
		{
			foreach ($events as $event)
			{
				$this->trigger_events($event);
			}
		}
		else
		{
			if (isset($this->_ion_hooks->$events) && !empty($this->_ion_hooks->$events))
			{
				foreach ($this->_ion_hooks->$events as $name => $hook)
				{
					$this->_call_hook($events, $name);
				}
			}
		}
	}

	/**
	 * set_message_delimiters
	 *
	 * Set the message delimiters
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function set_message_delimiters($start_delimiter, $end_delimiter)
	{
		$this->message_start_delimiter = $start_delimiter;
		$this->message_end_delimiter   = $end_delimiter;

		return TRUE;
	}

	/**
	 * set_error_delimiters
	 *
	 * Set the error delimiters
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function set_error_delimiters($start_delimiter, $end_delimiter)
	{
		$this->error_start_delimiter = $start_delimiter;
		$this->error_end_delimiter   = $end_delimiter;

		return TRUE;
	}

	/**
	 * set_message
	 *
	 * Set a message
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function set_message($message)
	{
		$this->messages[] = $message;

		return $message;
	}

	/**
	 * messages
	 *
	 * Get the messages
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function messages()
	{
		$_output = '';
		foreach ($this->messages as $message)
		{
			$messageLang = $this->lang->line($message) ? $this->lang->line($message) : '##' . $message . '##';
			$_output .= $this->message_start_delimiter . $messageLang . $this->message_end_delimiter;
		}

		return $_output;
	}

	/**
	 * messages as array
	 *
	 * Get the messages as an array
	 *
	 * @return array
	 * @author Raul Baldner Junior
	 **/
	public function messages_array($langify = TRUE)
	{
		if ($langify)
		{
			$_output = array();
			foreach ($this->messages as $message)
			{
				$messageLang = $this->lang->line($message) ? $this->lang->line($message) : '##' . $message . '##';
				$_output[] = $this->message_start_delimiter . $messageLang . $this->message_end_delimiter;
			}
			return $_output;
		}
		else
		{
			return $this->messages;
		}
	}

	/**
	 * set_error
	 *
	 * Set an error message
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function set_error($error)
	{
		$this->errors[] = $error;

		return $error;
	}

	/**
	 * errors
	 *
	 * Get the error message
	 *
	 * @return void
	 * @author Ben Edmunds
	 **/
	public function errors()
	{
		$_output = '';
		foreach ($this->errors as $error)
		{
			$errorLang = $this->lang->line($error) ? $this->lang->line($error) : '##' . $error . '##';
			$_output .= $this->error_start_delimiter . $errorLang . $this->error_end_delimiter;
		}

		return $_output;
	}

	/**
	 * errors as array
	 *
	 * Get the error messages as an array
	 *
	 * @return array
	 * @author Raul Baldner Junior
	 **/
	public function errors_array($langify = TRUE)
	{
		if ($langify)
		{
			$_output = array();
			foreach ($this->errors as $error)
			{
				$errorLang = $this->lang->line($error) ? $this->lang->line($error) : '##' . $error . '##';
				$_output[] = $this->error_start_delimiter . $errorLang . $this->error_end_delimiter;
			}
			return $_output;
		}
		else
		{
			return $this->errors;
		}
	}
    
	protected function _prepare_ip($ip_address) {
		//just return the string IP address now for better compatibility
		return $ip_address;
	}

}
