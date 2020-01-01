<?php

namespace phpbb\console\command\post;

use phpbb\console\command\command;
use phpbb\db\driver\driver_interface;
use phpbb\language\language;
use phpbb\user;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use GuzzleHttp\Client;

class add extends command
{
	protected $db;
	
	protected $user;
	
	private $_fType;
	
	private $_fValue;
	
	protected $language;

	public function __construct(user $user, driver_interface $db, language $language)
	{
		$this->db = $db;
		
		$this->user = $user;
		
		$this->language = $language;
		
		parent::__construct($this->user);
	}

	private function init($type)
	{
	    global $apiConfig;
	    
	    $this->_fType = $type;
	    
	    if (!isset($apiConfig['forum_content'][$this->_fType])) {
	        throw new \Exception('Forum Error');
	    }
	    
	    $this->_fValue = $apiConfig['forum_content'][$this->_fType];
	}
	
	protected function configure()
	{
	    $this->addArgument('type', InputArgument::REQUIRED, 'Please input an input');
	    
		$this->setName('post:add');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
	    $type = $input->getArgument('type');
	    
	    $this->init($type);
	    
	    $this->doPost($type);
	}
	
	private function doPost($type)
	{
	    global $apiConfig;
	    
	    $data = $this->getShoudSyncData($type);
	    
	    if ($data) {
	        foreach ($data as $val) {
	            $message = '<iframe width="100%" height="500" src="'.$apiConfig['v_url'].'/v/'.$val['video_url'].'" frameborder="0" allowfullscreen></iframe>';
	            $title = ''.$val['name'].'';
	            $username = 'Uploader';
	            
	            submit_post('post', $title, $username, POST_NORMAL,
	                $poll_ary = [],
	                $data_ary = [
	                    'forum_id' => $this->_fValue,
	                    'topic_id' => 0,
	                    'icon_id' => false,
	                    
	                    'enable_bbcode' => true,
	                    'enable_smilies' => true,
	                    'enable_urls' => true,
	                    'enable_sig' => true,
	                    
	                    'message' => $message,
	                    'message_md5' => md5($message),
	                    
	                    'bbcode_bitfield' => '',
	                    'bbcode_uid' => '',
	                    
	                    'post_edit_locked' => 0,
	                    'topic_title' => $title,
	                    
	                    'notify_set' => false,
	                    'notify' => false,
	                    'post_time' => 0,
	                    'forum_name' => '',
	                    
	                    'enable_indexing' => true,
	                    
	                    'force_approved_state' => true,
	                    
	                    'force_visibility' => true,
	                    'topic_thumb' => ''.$val['thumb_url'].'',
	                ]
	            );
	        }
	    }
	}
	
	private function getShoudSyncData($type)
	{
	    $result = [];
	    
	    global $apiConfig;
	    
	    $client = new Client();
	    
	    $response = $client->post($apiConfig['service_upstream_url'].DIRECTORY_SEPARATOR.'api/getShoudSyncData', [
	        'body' => [
	            'type' => $type,
	        ],
	    ]);
	    
	    $res = json_decode($response->getBody(), true);
	    
	    if (isset($res['retcode']) && $res['retcode'] == 200) {
	        if (isset($res['data']) && $res['data']) {
	            $result = $res['data'];
	        }
	    }
	    
	    return $result;
	}
}
