<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/**
* Name:  MY Model
*
* Author:  Sandro Cândido
* 		   sandro.webdesigner@gmail.com
*
* Location: http://github.com/benedmunds/CodeIgniter-Ion-Auth
*
* Created:  01.03.2014
*
* Adaptation for ORM Doctrine
* Description: The Doctrine Project is the home to several PHP libraries primarily focused on database storage and object mapping.
* Are external dependencies for to bewith your project: Doctrine - http://www.doctrine-project.org
*
* Requirements: PHP5 or above
* 
*/

class MY_Model extends CI_Model
{
	/*Instancia classe core codeigniter*/
	private $_ci;

	/*Instancia Doctrine Entity Manager*/
	protected $em;
	protected $tool;

	/**
	 * Response
	 *
	 * @var string
	 **/
	protected $response = NULL;
	/**
	 * Where
	 *
	 * @var array
	 **/
	public $_where = array();
    
    public $last_error = null;

	public function __construct(){
		parent::__construct();

        if( $this->config->item('use_doctrine', 'ion_auth') ){
            $this->load->library('doctrine');

            // Instantiate Doctrine's Entity manage so we don't have to everytime we want to use Doctrine
            $this->_ci = & get_instance();
            $this->em = $this->_ci->doctrine->em;
        }
	}

    public function getLast_erro(){
        return $this->last_erro;
    }

    public function setLast_erro($last_error){
        $this->last_erro = $last_error;
    }

    public function get_tool_schema(){
        
		$this->tool = $this->_ci->doctrine->tool;

        return $this->tool;
    }

	public function where($where, $value = NULL)
	{

		if (!is_array($where))
		{
			$where = array($where => $value);
		}

		array_push($this->_where, $where);

		return $this;
	}
}