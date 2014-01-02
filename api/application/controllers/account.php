<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Account extends REST_Controller {

        /**
         * Account information
         * @param GET token
         * @return object{status, object}
         */
	public function info_get($token)
	{
            $response = new stdClass();
            
            $token_entry = new Token();
            $token_entry->get_by_valid_token($token)->get();
            if($token_entry->exists())
            {
                $user = new User();
                $user->get_by_id($token_entry->user_id);
                $response->status = true;
                $response->user = new stdClass();
                $response->user->firstname = $user->firstname;
                $response->user->lastname = $user->lastname;
                $response->user->username = $user->username;
                $response->user->gitlab_private_key = $user->gitlab_private_key;
            }
            else
            {
                $response->status = false;
                $response->error = 'Token not found or session expired';
            }
            $this->response($response);
	}
        
        /**
         * Session check
         * @param POST token
         * @return object{status}
         */
        public function check_post() 
        {
            $response = new stdClass();
            $response->status=false;
            $token_entry = new Token();
            //Looking for valid token in DB
            $token_entry->get_by_valid_token($this->post('token'))->get();
            //Token found, session is ok
            if($token_entry->exists())
            {
                $user = new User();
                $user->get_by_id($token_entry->user_id);
                $response->status=true;
                $response->is_admin = ($user->is_admin) ? TRUE : FALSE;
                $response->firstname = $user->firstname;
                $response->lastname = $user->lastname;
            }
            $this->response($response);
        }
        
        /**
         * Account login
         * @param POST username
         * @param POST password
         * @param POST client_secret_uuid
         * @return object{status, token}
         */
        public function login_post()
	{
            $response = new stdClass();
            //Parameters check
            if(!empty($this->post('username')) && !empty($this->post('password')) && !empty($this->post('client_secret_uuid')))
            {
                $user = new User();
                $user->where('username', $this->post('username'))->where('password', sha1($this->post('password')))->get();
                //Record found
                if($user->exists()) 
                {
                    $token = uniqid(md5(rand()), true);
                    $token_entry = new Token();
                    $token_entry->token = $token;
                    $token_entry->user_id = $user->id;
                    //Token expire after 1 year
                    $token_entry->token_expire = time() + 60*60*24*365;
                    $token_entry->client_secret_uuid = $this->post('client_secret_uuid');
                    if($token_entry->save())
                    {
                        $response->status = true;
                        $response->token = $token;
                    }
                    else
                    {
                        $response->status = false;
                        $response->error = "Something wrong in creating Auth Token";
                    }
                } 
                else
                {
                    $response->status = false;
                    $response->error = 'Username / Password wrong';
                }
            }
            else 
            {
                $response->status = false;
                $response->error = 'You must provide username, password and client_secret_uuid';
            }
            $this->response($response);
	}
        
        /**
         * Account logout
         * @param token
         * @return object{status}
         */
        public function logout_post()
	{
            $response = new stdClass();
            
            $token_entry = new Token();
            $token_entry->get_by_valid_token($this->post('token'))->get();
            if($token_entry->exists())
            {
                $token_entry->delete();
                $response->status=true;
            }
            else 
            {
               $response->status=false;
               $response->error='Token not found or session expired';
            }
            $this->response($response);
	}
        
        /**
         * Account edit
         */
        public function index_put()
	{
            //TODO
	}
}

/* End of file account.php */
/* Location: ./application/controllers/account.php */