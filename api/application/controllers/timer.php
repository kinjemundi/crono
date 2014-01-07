<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Timer extends REST_Controller {
    
    public function index_post()
    {
        
    }
    
    public function active_get($token)
    {
        $token_entry = new Token();
        $token_entry->get_by_valid_token($token)->get();
        $response = new stdClass();
        if($token_entry->exists())
        {
            //TODO
            $timer_entries = new Timer_entry();
            //Selecting the entry
            $timer_entries->where('user_id', $token_entry->user->get()->id)->where('active',1)->get();
            if($timer_entries->exists())
            {
                $response->status = true;
                $timer = stdClass();
                $timer->id = $timer_entries->id;
                $timer->task = $timer_entries->task;
                $response->active_timer = $timer;
            }
            else 
            {
                $response->status = true;
                $response->active_timer = null;
            }
        }
        else 
        {
            $response->status=false;
            $response->error='Token not found or session expired';
            $this->response($response);
        } 
    }
    
    public function all_get($only_current_user, $token)
    {
        $token_entry = new Token();
        $token_entry->get_by_valid_token($token)->get();
        if($token_entry->exists())
        {
            $response = [];
            $timer_entries = new Timer_entry();
            //Does it show only current user?
            if($only_current_user) 
            {
                $timer_entries->where('user_id', $token_entry->user->get()->id);
            }
            //Only not active time entries, order by stop time
            $timer_entries->where('active',0)->order_by('stop_time','DESC')->get();
            foreach($timer_entries as $timer_entry)
            {
                $t = new stdClass();
                $t->id = $timer_entry->id;
                $t->project_name = $timer_entry->project->get()->name;
                $t->project_id = $timer_entry->project_id;
                $t->task = $timer_entry->task;
                $t->start_time = $timer_entry->start_time;
                $t->stop_time = $timer_entry->stop_time;
                $t->duration = from_unix_timespan_to_string($timer_entry->start_time,$timer_entry->stop_time);
                array_push($response, $t);
            }
            $this->response($response);
        }
    }
    
    public function index_put()
    {
        
    }
    
    public function delete_delete($id, $token)
    {
        
    }
}

