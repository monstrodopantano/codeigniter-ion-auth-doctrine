<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class MY_Form_validation extends CI_Form_validation {

    protected $CI;

    public function __construct() {
        parent::__construct();
        
        $this->CI =& get_instance();
    }

	/**
	 * Match one field to another
	 *
	 * @access	public
	 * @param	string
	 * @param	field
	 * @return	bool
	 */
	public function is_unique($str, $field)
	{
        list($table, $field)=explode('.', $field);
        if( $this->CI->config->item('use_doctrine', 'ion_auth') ){
            $this->em = $this->CI->doctrine->em;
            
            $qb = $this->em->createQueryBuilder();

            $qb->select( 'u' )
                ->from( 'models\entities\\'.$table, 'u' )
                ->where( $qb->expr()->eq('u.'.$field, ':identity' ) )
                ->setParameter('identity', $str)
                ->setMaxResults('1');

            $query = $qb->getQuery()->getResult();
            
            if( empty( $query ) )
                return TRUE;
            
            return FALSE;
        }
    }
}