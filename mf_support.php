<?php
error_reporting(0);
function mf_osticket_cmp($a, $b)
{
    return strcmp($a["created"], $b["created"]);
}

class MF_OST_REST_API
{
    static function post($url,$body,$header=null,$setToken=true)
    {
        if( !$header )
            $header = array('Content-type: application/json');
        array_push($header,'Content-type: application/json');

        if( $setToken )
        {
            $token = get_option("osticket_token_key","");
            $user_email = get_option("admin_email","");
            if( strlen($user_email) < 3 ) {
                MF_OSTicketSupport::getAdminSite();
                $user_email = get_option("admin_email", "");
            }

            array_push($header,'token: '.$token);
            array_push($header,'useremail: '.$user_email);
        }
        $ch = curl_init();
        $body = json_encode($body);
        curl_setopt($ch, CURLOPT_URL,            $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_POST,           1 );
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $body );
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $header);
        $exec = curl_exec($ch);
        return json_decode($exec);
    }

    static function get($url,$header=null,$setToken=true)
    {
        if( !$header )
            $header = array('Content-type: application/json');
        else
            array_push($header,'Content-type: application/json');

        if( $setToken )
        {
            $token = get_option("osticket_token_key","");
            $user_email = get_option("admin_email","");
//            if( strlen($user_email) < 3 ) {
//                MF_OSTicketSupport::getAdminSite();
//                $user_email = get_option("admin_email", "");
//            }

            array_push($header,'token: '.$token);
            array_push($header,'useremail: '.$user_email);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $header);
        return json_decode(curl_exec($ch));
    }

    static function put($url,$body,$header=null,$setToken=true)
    {
        if( !$header )
            $header = array('Content-type: application/json');
        array_push($header,'Content-type: application/json');

        if( $setToken )
        {
            $token = get_option("osticket_token_key","");
            $user_email = get_option("admin_email","");
            if( strlen($user_email) < 3 ) {
                MF_OSTicketSupport::getAdminSite();
                $user_email = get_option("admin_email", "");
            }
            array_push($header,'token: '.$token);
            array_push($header,'useremail: '.$user_email);
        }
        $body = json_encode($body);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,            $url );
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($ch, CURLOPT_HTTPHEADER,     $header);
        curl_setopt($ch, CURLOPT_POSTFIELDS,     $body );
        return json_decode(curl_exec($ch));
    }


}

class MF_Topics
{
    var $Data;
    function __construct($data)
    {
        $this->Data['topic'] = array();
        $this->set($data);
    }
    public function set($data)
    {
        if( is_null($data) || empty($data) )
            return ;
        if( !is_array($data ) )
            return ;

        foreach($data as $key=>$value)
        {
            switch($key)
            {
                case 'topic':
                    @$this->Data['topic'] = array('id'=>$value['id'],'name'=>$value['name'],
                        'isEnable'=>$value['isEnable'],'isActive'=>$value['isActive'],'dynamic_form'=>$value['form'] );
                    break;
            }

        }
    }

    public function get($section,$key)
    {
        if( isset($this->Data[$section],$this->Data[$section][$key]) )
            return $this->Data[$section][$key];
        return null;
    }

    public function getId()
    {
        return $this->get('topic','id');
    }

    public function getName()
    {
        return $this->get('topic','name');
    }

    public function getIsEnable()
    {
        return (bool)$this->get('topic','isEnable');
    }

    public function getIsActive()
    {
        return (bool)$this->get('topic','isActive');
    }

    public function getDynamicForm()
    {
        return $this->get('topic','dynamic_form');
    }
}

class MF_TicketThread
{
    var $Data;
    function __construct($data)
    {
        $this->Data['response'] = array();
        $this->set($data);
    }
    public function set($data)
    {
        if( is_null($data) || empty($data) )
            return ;
        if( !is_array($data ) )
            return ;

        foreach($data as $key=>$value)
        {
            switch($key)
            {
                case 'response':
                    @$this->Data['response'] = array('id'=>$value['id'],'created'=>$value['created'],
                        'message'=>$value['message'],'name'=>$value['name'] );
                    break;
            }

        }
    }

    public function get($section,$key)
    {
        if( isset($this->Data[$section],$this->Data[$section][$key]) )
            return $this->Data[$section][$key];
        return null;
    }

    public function getId()
    {
        return $this->get('response','id');
    }

    public function getPoster()
    {
        return $this->get('response','name');
    }

    public function getMessage()
    {
        return $this->get('response','message');
    }

    public function getCreatedDate()
    {
        return $this->get('response','created');
    }
}

class MF_Ticket
{
	var $Data;
	function __construct($data)
	{
			$this->Data['user'] = array();
			$this->Data['staff'] = array();
			$this->Data['ticket-data'] = array();
			$this->Data['ticket-thread'] = array();
			$this->set($data);
	}

	public function set($data)
	{
		if( is_null($data) || empty($data) )
			return ;
		if( !is_array($data ) )
			return ;

		foreach($data as $key=>$value)
		{
			switch($key)
			{
                case 'user':
					@$this->Data['user'] = array('user_id'=>$value['user_id'],'username'=>$value['username'],'email'=>$value['email'] );
					break;
				case 'staff':
					@$this->Data['staff'] = array('staff_id'=>$value['staff_id'],'full_name'=>$value['full_name']);
					break;
				case 'ticket-data':
					@$this->Data['ticket-data'] = array('id'=>$value['id'],'number'=>$value['number'],
                        'subject'=>$value['subject'],'created'=>$value['created'],'updated'=>$value['updated'],
                        'due_date'=>$value['due_date'],'priority'=>$value['priority'],'department'=>$value['department'],
                        'topic'=>$value['topic'],'closed'=>$value['closed']);
					break;
				case 'ticket-thread':
                    @$this->Data['ticket-thread'] = null;
                    foreach($value as $thead_key=>$thread_value)
                    {
                        $this->Data['ticket-thread']['data'][] = new MF_SupportTicketThread($thread_value);
                    }
					break;
			}

		}
	}

	public function get($section,$key)
	{
		if( isset($this->Data[$section],$this->Data[$section][$key]) )
			return $this->Data[$section][$key];
		return null;
	}

	public function getUserId()
    {
        return $this->get('user','user_id');
    }

    public function getUsername()
    {
        return $this->get('user','username');
    }

    public function getUserEmail()
    {
        return $this->get('user','email');
    }

    public function getStaffId()
    {
        return $this->get('staff','staff_id');
    }

    public function getStaffFullName()
    {
        return $this->get('staff','full_name');
    }

    public function getTicketId()
    {
        return $this->get('ticket-data','id');
    }

    public function getTicketNumber()
    {
        return $this->get('ticket-data','number');
    }

    public function getTicketSubject()
    {
        return $this->get('ticket-data','subject');
    }

    public function getTicketCreated()
    {
        return $this->get('ticket-data','created');
    }

    public function getTicketUpdated()
    {
        return $this->get('ticket-data','updated');
    }

    public function getTicketDueDate()
    {
        return $this->get('ticket-data','due_date');
    }

    public function getTicketPriority()
    {
        return $this->get('ticket-data','priority');
    }

    public function getTicketDeptId()
    {
        return $this->get('ticket-data','department');
    }

    public function getTopicId()
    {
        return $this->get('ticket-data','topic');
    }

    public function getTicketIsClosed()
    {
        return (bool)$this->get('ticket-data','closed');
    }

    public function getThreads()
    {
        return $this->get('ticket-thread','data');
    }
}

define("OSTICKET_DOMAIN_DEF",'http://support.appchar.com/');
class MF_OSTicketSupport
{
	var $TicketData;
	var $Topics;
	var $current_user;
	var $extraFields;
	const OSTICKET_DOMAIN = OSTICKET_DOMAIN_DEF;
	const REST_API_PATH = "api/rest/public";
    const REST_TOPIC_URL = self::OSTICKET_DOMAIN.self::REST_API_PATH."/topics" ;
    const REST_TICKETS_URL = self::OSTICKET_DOMAIN.self::REST_API_PATH."/tickets" ;
    const REST_TICKETS_REPLY_URL = self::OSTICKET_DOMAIN.self::REST_API_PATH."/tickets/%d/reply" ;
    const REST_TOPICS_URL = self::OSTICKET_DOMAIN.self::REST_API_PATH."/topics" ;
    const REST_TICKETS_ATTACHMENT_URL = self::REST_TICKETS_URL."/attachment";
    const REST_USER_REGISTER_URL = self::OSTICKET_DOMAIN.self::REST_API_PATH."/user";
    const REST_USER_ACTIVATION_URL = self::OSTICKET_DOMAIN.self::REST_API_PATH."/user/%d/activation";
    const REST_USER_TOKEN_URL = self::OSTICKET_DOMAIN.self::REST_API_PATH."/user/token";
    const REST_USER_CHANGE_PASS_URL = self::OSTICKET_DOMAIN.self::REST_API_PATH."/user/change_password";
    const REST_USER_FORGET_PASS_URL = self::OSTICKET_DOMAIN.self::REST_API_PATH."/user/forget_password";

    function __construct()
    {
        require "mf_extra_fileds.php";
    	$this->extraFields = new MF_Wizard_ExtraFields();
        add_action('parse_request', array($this, 'sniff_requests'), 0);
        add_action( 'admin_init',             array( $this, 'plugin_initiallize' ) );
        add_action('admin_menu', array(&$this,'createMenu'));
        add_action("admin_init", array( $this,"addSettingsPageOptions"));
        add_action( 'wpmu_new_blog', array($this , 'wporg_wpmu_new_blog'), 10, 6 );
    }

    function reset_user()
    {
        update_option('osticket_user_id',null);
        update_option('osticket_token_key',null);
        update_option('osticket_activation_key',null);
        update_option('osticket_password',null);
        update_option('osticket_retry_password',null);
    }

    static function getAdminSite()
    {
//        $blogusers = get_users( array( 'blog_id' => get_current_blog_id() ) );
//        $shopMGR = array();
//        foreach ( $blogusers as $user ) {
//            if( in_array("shop_manager",$user->roles) )
//                $shopMGR[$user->ID] = $user->ID;
//        }
//        ksort($shopMGR);
//        $user_id = reset($shopMGR);

        global $wpdb;
        //Get all users in the DB
        $wp_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY ID LIMIT 1");
        $user = get_user_by( 'id', $wp_user_search['0']->ID );
        update_option('admin_email',$user->user_email);
        return $user;
    }

    public function checkOsTicketToken()
    {
        $user = $this->getAdminSite();
        $admin_email = $user->user_email;
        $user_meta = get_user_meta($user->ID);
        $first_name = $user_meta['first_name']['0'];
        $last_name = $user_meta['last_name']['0'];
        $phone_number = $this->extraFields->get($user->ID,'phone_number');

        $token = get_option('osticket_token_key');
        if(empty($token) OR is_null($token)){
            $necessary_fields = [];
            if(empty($first_name) OR is_null($first_name))
                $necessary_fields[] = 'نام';

            if(empty($last_name) OR is_null($last_name))
                $necessary_fields[] = 'نام خانوادگی';

            if(empty($phone_number) OR is_null($phone_number))
                $necessary_fields[] = 'شماره موبایل';

            if(empty($admin_email) OR is_null($admin_email))
                $necessary_fields[] = 'ایمیل مدیریت';

            if(count($necessary_fields) >= 1) {
                ?>
                <br/><br/><br/>
                <div class="notice notice-error" style="padding: 20px;">
                    <p><strong>خطای نقص اطلاعات</strong></p>
                    <p>
                        لطفا اطلاعات هویتی خود را با مراجعه <strong><a href="<?= get_edit_user_link($user->ID) ?>">ویرایش
                                اطلاعات کاربری</a></strong> تکمیل نمایید .
                        <br/>
                        <br/>
                        اطلاعات مورد نیاز :‌ <br/>
                    <ol class="strong">
                        <?php
                        foreach ($necessary_fields AS $field) {
                            echo '<li>' . $field . '</li>';
                        }
                        ?>
                    </ol>
                    </p>
                </div>
                <?php
                die();

            } else {
                $password = mt_rand(10000000,99999999);
                $userData = array(
                    "name"  => $first_name . ' ' . $last_name,
                    "email" => $admin_email,
                    "password1" => $password,
                    "password2" => $password,
                    "timezone_id"   => 18,
                    "phone"  => $phone_number
                );

                $user_response = MF_OST_REST_API::post(self::REST_USER_REGISTER_URL,$userData,null,false);
                if(!$user_response->status && $user_response->code == 311) {
                    $token = array(
                        "email" => $admin_email,
                        "password" => get_option('osticket_password')
                    );
                    $user_response = MF_OST_REST_API::post(self::REST_USER_TOKEN_URL, $token ,null,false);

                    if( ISSET($user_response->token) )
                    {
                        update_option("osticket_token_key",$user_response->token);
                        update_option('osticket_user_id',$user_response->user->id);
                        update_option('osticket_email_register',$admin_email);
                    }
                    else
                    {
                        echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>در فرایند های درخواست خطایی رخ داده است لطفاً مجدداً تلاش نمایید.</p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
                        die();

                    }
                } else if( !ISSET($user_response->status) )
                {
                    echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>. در فرایند های درخواست خطایی رخ داده است لطفاً مجدداً تلاش نمایید.</p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
                    die();

                }
                else
                {
                    update_option('osticket_user_id',$user_response->user_id);
                    update_option('osticket_password',$password);
                    update_option('osticket_email_register',$admin_email);
                    $token = array(
                        "email"=> $admin_email,
                        "password"=>$password
                    );
                    $user_response = MF_OST_REST_API::post(self::REST_USER_TOKEN_URL, $token ,null,false);

                    if( $user_response->status || $user_response->token )
                    {
                        update_option("osticket_token_key",$user_response->token);

                    }
                    else
                    {
                        echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>در فرایند های درخواست خطایی رخ داده است لطفاً مجدداً تلاش نمایید.</p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
                        die();


                    }
                }
            }

        }
    }
    function createNewUserInOSTicket($password = null)
    {
        $user = $this->getAdminSite();
        $admin_email = $user->user_email;
        $user_meta = get_user_meta($user->ID);
        $phone_number = $this->extraFields->get($user->ID,'first_name');

//        $blog_domain = get_blog_details( get_current_blog_id() );
//        $blog_domain = explode(".",$blog_domain->domain)[0] ;

        if(is_multisite()){
            $blog_domain = get_blog_option(get_current_blog_id(),'siteurl');
        }else{
            $blog_domain = get_option('siteurl');
        }

        $userData = array(
            "name"  => $blog_domain."_".$user->ID."_".$user->display_name,
            "email" => $admin_email,
            "password1" => $password,
            "password2" => $password,
            "timezone_id"   => 18,
            "phone"  => $phone_number
        );

        $user_response = MF_OST_REST_API::post(self::REST_USER_REGISTER_URL,$userData,null,false);

        if( !$user_response->status )
        {
            echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>
                            '.$user_response->errors.'
                                </p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
            return false;
        }
        else
        {
            update_option('osticket_user_id',$user_response->user_id);
            echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>لطفاً بعد از دریافت اس ام اس فعال سازی ، کد فعال سازی را در بخش مربوطه بزنید
                                </p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
        }
        return true;


    }

    function addSettingsPageOptions()
    {
        //section name, display name, callback to print description of section, page to which section is attached.
        add_settings_section("header_section1", "تنظیمات دسترسی API", array($this,"display_header_options_content"), "osticket-options");

        //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
        //last field section is optional.
//        add_settings_field("osticket_rest_api_url", "Rest Api URL", array($this,"display_rest_api_url"), "osticket-options", "header_section1");

        add_settings_field("osticket_token_key", "توکن", array($this,"display_token_key"), "osticket-options", "header_section1");

        //section name, form element name, callback for sanitization
//        register_setting("header_section1", "osticket_rest_api_url");

        register_setting("header_section1", "osticket_token_key");




        //section name, display name, callback to print description of section, page to which section is attached.
        add_settings_section("osticket_generate_key_options", "تنظیمات دسترسی API", array($this,"display_header_options_content"), "osticket-gen-key-options");

        //setting name, display name, callback to print form element, page in which field is displayed, section to which it belongs.
        //last field section is optional.
        add_settings_field("osticket_password", "کلمه عبور", array($this,"display_password"), "osticket-gen-key-options", "osticket_generate_key_options");
        add_settings_field("osticket_retry_password", "تکرار کلمه عبور", array($this,"display_retry_password"), "osticket-gen-key-options", "osticket_generate_key_options");

        //section name, form element name, callback for sanitization
        register_setting("osticket_generate_key_options", "osticket_password");
        register_setting("osticket_generate_key_options", "osticket_retry_password");
//        register_setting("osticket_generate_key_options", "osticket_rest_api_url");

        //section name, display name, callback to print description of section, page to which section is attached.
        add_settings_section("osticket_generate_key_step2_options", "اعمال کد فعال سازی", array($this,"display_activate_key"), "osticket-gen-key-step2-options");


        add_settings_field("osticket_activation_key", "Activation Key", array($this,"display_activation_key"), "osticket-gen-key-step2-options", "osticket_generate_key_step2_options");

        //section name, form element name, callback for sanitization
        register_setting("osticket_generate_key_step2_options", "osticket_activation_key");

    }

    public function plugin_initiallize()
	{
		global $wpdb ;
/*        update_option('osticket_user_id',null);
        update_option('osticket_token_key',null);
        update_option('osticket_rest_api_url',null);
        update_option('osticket_activation_key',null);
        update_option('osticket_password',null);
        update_option('osticket_retry_password',null);*/

      /*  $args['per_page'] = -1;

        $tickets = incsub_support_get_tickets( $args );
        foreach($tickets as $ticket)
        {*/
           /* if( $ticket->ticket_id == 21 )
            {
                print_r($ticket);
                $replys = incsub_support_get_ticket_replies($ticket->ticket_id);
                foreach($replys as $reply)
                {
                     $reply->message;
                     $reply->is_main_reply;
                }
                exit;
            }*/

           /* switch_to_blog($ticket->blog_id);
            $ticket_data = array('origin'=>'Web','alertUser'=>1,'alertStaff'=>1,'ticketData'=>array());

            $user = $this->getAdminSite();
            $admin_email = $user->user_email;
            $blog_domain = get_blog_details( get_current_blog_id() );
            $blog_domain = explode(".",$blog_domain->domain)[0] ;

            $ticket_data['ticketData'] = $_POST;
            $ticket_data['ticketData']['name'] = $blog_domain."_".$user->ID."_".$user->display_name;
            $ticket_data['ticketData']['email'] = $admin_email;
            $ticket_data['ticketData']['phone'] = $this->extraFields->get($this->current_user->ID,'phone_number');

            $ticket->title;
            $ticket->ticket_priority;
            $ticket->category->cat_id;
            $ticket->blog_id;
            $ticket->ticket_updated;
            restore_current_blog();
            $replys = incsub_support_get_ticket_replies($ticket->ticket_id);
            $first = true;
            foreach($replys as $reply)
            {
                $reply->message;
                $reply->is_main_reply;
                if( $first )
                {
                    $first = false;
                    $ticket_data['origin'] = 'Web';
                    $ticket_data['alertStaff'] = 1;
                    $ticket_data['alertUser'] = 1;
                    $ticket_data['ticketData']['subject'] = $ticket->title;
                    $ticket_data['ticketData']['message'] = $reply->message;
                    $ticket_data['ticketData']['topicId'] = $ticket->category->cat_id;
                    $ticket_response = MF_OST_REST_API::post(self::REST_TICKETS_URL,$ticket_data,null,true) ;
                }
            }

        }
        exit;

        $blog_list = get_blog_list( 0, 'all' );
        foreach ($blog_list AS $blog) {
            switch_to_blog($blog['blog_id']);
            $blog['blog_id']  =146;*/

           /* $user = $this->getAdminSite();
            $token = array(
                "email"=> $user->user_email,
                "password"=>get_option('osticket_password')
            );*/



            /*$user_response = MF_OST_REST_API::post(self::REST_USER_TOKEN_URL, $token ,null,false);
            if( $user_response->status || $user_response->token ) {
                update_option("osticket_token_key", $user_response->token);
            }*/
            /*if( strlen($this->extraFields->get($user->ID , "phone_number") ) < 5 ) {
                $phone = explode("r09", $user->user_login);
                if (count($phone) == 2) {
                    $phone = "09" . $phone[1];
                } else $phone = "0913" . $blog['blog_id'] . "0912121212";
                $phone = substr($phone, 0, 11);
                $this->extraFields->set($user->ID, "phone_number", $phone);
                $a = rand() % 100;
                $b = rand() % 100;
                $pass = "forooshgahan" . $a . "_" . $b;
                update_option('osticket_password', $pass);
                update_option('osticket_retry_password', $pass);
                echo "start---<br/>" . $phone . "<br/>";
                echo $user->ID;
                $this->createNewUserInOSTicket($pass);
            }*/
          /*  restore_current_blog();
        }*/
       // exit;
        $this->get_current_userInfo();
        $this->Topics = null;

    }

    function wporg_wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
        $user = get_user_by('id', 1);
        $phone = $this->extraFields->get($user->ID , "phone_number");
        if( strlen( $phone ) != 11 ) {
            $phone = explode("r09", $user->user_login);
            if (count($phone) == 2) {
                $phone = "09" . $phone[1];
            } else $phone = "0913" . $blog_id . "0912121212";
            $phone = substr($phone, 0, 11);
            $this->extraFields->set($user->ID, "phone_number", $phone);
        }

        $a = rand() % 100;
        $b = rand() % 100;
        $pass = "forooshgahan" . $a . "_" . $b;
        update_option('osticket_password', $pass);
        update_option('osticket_retry_password', $pass);
        $this->createNewUserInOSTicket($pass);

    }

	function createMenu()
    {
//        add_menu_page('سیستم پشتیبانی', 'سیستم پشتیبانی', 'manage_options', 'osticket-ticket-list',array($this,'showListOfTicketsPage'));
//        add_submenu_page( 'osticket-ticket-list', 'لیست درخواست ها', 'لیست درخواست ها',
//            'manage_options','osticket-ticket-list', array($this,'showListOfTicketsPage'));
//        add_submenu_page( 'osticket-ticket-list', 'درخواست جدید', 'درخواست جدید',
//            'manage_options','osticket-new-ticket', array($this,'addNewTicketPage'));
//        add_submenu_page( 'osticket-ticket-list', 'تنظیمات', 'تنظیمات',
//            'manage_options','osticket-settings', array($this,'SettingsPage'));

    }


    function display_header_options_content()
    {
        echo "لطفاً قبل از استفاده از نصب Rest API بر روی سرور OSTicket خود مطمئن شوید";
        $user = $this->getAdminSite();

        $admin_email = $user->user_email;
        $phone_number = $this->extraFields->get($user->ID,'phone_number');
//        $blog_domain = get_blog_details( get_current_blog_id() );
//        $blog_domain = explode(".",$blog_domain->domain)[0] ;
        if(is_multisite()){
            $blog_domain = get_blog_option(get_current_blog_id(),'siteurl');
        }else{
            $blog_domain = get_option('siteurl');
        }
        $token = get_option('osticket_token_key');
        echo '<table class="wp-list-table widefat fixed striped posts">
                    <tbody>
                    <tr>
                        <th scope="row">اکانت کاربری</th>
                        <td>'.$blog_domain."_".$user->ID."_".$user->display_name.'</td>
                    </tr>
                    
                    <tr>
                        <th scope="row">ایمیل</th>
                        <td>'.$admin_email.'</td>
                    </tr>
                    
                    <tr>
                        <th scope="row">شماره همراه</th>
                        <td>'.$phone_number.'</td>
                    </tr>
                    
                    <tr>
                        <th scope="row">توکن</th>
                        <td><input type="text" name="osticket_token_key" id="osticket_token_key" size="50"  value="' . $token .'" /></td>
                    </tr>
                    </tbody>
                </table>';
    }
    function display_activate_key()
    {
        echo "کد فعال سازی برای شما اس ام اس می شود . لطفاً کد فعال سازی را در بخش مربوطه وارد نمایید";
    }
    function display_token_key()
    {
        //id and name of form element should be same as the setting name.
        ?>
<!--        <input type="text" name="osticket_token_key" id="osticket_token_key" size="50"  value="--><?php //echo get_option('osticket_token_key'); ?><!--" />-->
        <?php
    }


    function display_activation_key()
    {
        //id and name of form element should be same as the setting name.
        ?>
        <input type="text" name="osticket_activation_key" id="osticket_activation_key" value="<?php echo get_option('osticket_activation_key'); ?>" />
        <?php
    }

    function display_password()
    {
        //id and name of form element should be same as the setting name.
        ?>
        <input type="password" name="osticket_password" id="osticket_password" value="<?php echo get_option('osticket_password'); ?>" />
        <br/><span>حداکثر : ۸ کاراکتر</span>
        <?php
    }

    function display_retry_password()
    {
        //id and name of form element should be same as the setting name.
        ?>
        <input type="password" name="osticket_retry_password" id="osticket_retry_password" value="<?php echo get_option('osticket_retry_password'); ?>" />
        <br/><span></span>
        <?php
    }

    function SettingsPage()
    {
        $active_tab = '';
        if( isset( $_GET[ 'tab' ] ) ) {
            $active_tab = $_GET[ 'tab' ];
        }
        else $active_tab = 'display_options';
        ?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=osticket-settings&tab=display_options" class="nav-tab <?php echo $active_tab == 'display_options' ? 'nav-tab-active' : ''; ?>">فعال سازی با توکن</a>
<!--            <a href="?page=osticket-settings&tab=generate_key" class="nav-tab --><?php //echo $active_tab == 'generate_key' ? 'nav-tab-active' : ''; ?><!--">ساختن اکانت جدید در پشتیبانی</a>-->
        </h2>
        <?php
            if( $active_tab == 'generate_key' ) {
                $valid_data = true;
                if( strcmp(get_option('osticket_password','') , get_option('osticket_retry_password','') ) !== 0 )
                {
                    $valid_data = false;
                    echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>
                                رمز ورود با تکرار رمز ورود مطابقت ندارد
                                </p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
                    update_option('osticket_retry_password',"");
                }
                if( strlen(get_option('osticket_password') ) < 8 )
                {
                    $valid_data = false;
                    echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>
رمز ورود نباید کمتر از هشت کارکتر باشد
                                </p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
                    update_option('osticket_retry_password',"");
                }
                if( $valid_data && get_option("osticket_user_id",-1) <= 0 ) {

                    $user_response = $this->createNewUserInOSTicket(get_option('osticket_password'));
                }

                if( $valid_data && strlen(get_option('osticket_activation_key',-1))>= 4 && get_option('osticket_user_id',-1)>0 )
                {
                    $user_response = MF_OST_REST_API::post(sprintf(self::REST_USER_ACTIVATION_URL,get_option('osticket_user_id')),
                        array("key"=>get_option('osticket_activation_key')),null,false);

                    if( $user_response->status )
                    {
                        $user = $this->getAdminSite();
                        $admin_email = $user->user_email;
                        $token = array(
                            "email"=> $admin_email,
                            "password"=>get_option('osticket_password')
                        );
                        $user_response = MF_OST_REST_API::post(self::REST_USER_TOKEN_URL, $token ,null,false);
                        if( $user_response->status || $user_response->token )
                        {
                            update_option("osticket_token_key",$user_response->token);
                            echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>تراکنش با موفقیت به اتمام رسید.اکنون شما می توانید در پشتیبانی درخواست ثبت نمایید.</p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
                        }
                        else
                        {
                            echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>در فرایند های درخواست خطایی رخ داده است لطفاً مجدداً تلاش نمایید.</p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
                        }
                    }
                    else
                    {
                        echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>'.$user_response->errors.'</p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
                    }
                }
                else if ( get_option("osticket_user_id",-1)>0 )
                {

                    echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>لطفاً کد فعال سازی را در بخش مورد نظر وارد کنید</p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </button></div>';
                }
                ?>
                <form method="post" action="options.php">
                    <?php
                    //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
                    settings_fields("osticket_generate_key_options");

                    // all the add_settings_field callbacks is displayed here
                    do_settings_sections("osticket-gen-key-options");

                    // Add the submit button to serialize the options
                    submit_button();
                    ?>
                </form>

                <form method="post" action="options.php">
                    <?php

                    //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
                    settings_fields("osticket_generate_key_step2_options");

                    // all the add_settings_field callbacks is displayed here
                    do_settings_sections("osticket-gen-key-step2-options");
                    // Add the submit button to serialize the options
                    submit_button();
                    ?>
                </form>

                <?php
            }else {
                ?>
                <div class="wrap">
                    <div id="icon-options-general" class="icon32"></div>
                    <h1>تنظیمات سرور</h1>
                    <form method="post" action="options.php">
                        <?php

                        //add_settings_section callback is displayed here. For every new section we need to call settings_fields.
                        settings_fields("header_section1");

                        // all the add_settings_field callbacks is displayed here
                        do_settings_sections("osticket-options");

                        // Add the submit button to serialize the options
                        submit_button();

                        ?>
                    </form>
                </div>
                <?php
            }
            die();

    }

    function addNewTicketPage()
    {

        $this->checkOsTicketToken();



        if( isset($_POST['a']) && $_POST['a'] == 'register' )
        {
            $attahment_idx = array();
            $total = count($_FILES['attachment']['name']);
            for($i=0; $i<$total; $i++)
            {
                if( $_FILES['attachment']['error'][$i] <=0 )
                {
                    $data = file_get_contents($_FILES['attachment']['tmp_name'][$i]);
                    $data = base64_encode($data);
                    $body = array('file_type'=>$_FILES['attachment']['type'][$i],'file_name'=>$_FILES['attachment']['name'][$i],
                        'file_data'=>$data);
                    $attahment_response = MF_OST_REST_API::post(self::REST_TICKETS_ATTACHMENT_URL,$body,null,true);
                    if( isset($attahment_response->attachment_id) )
                        $attahment_idx [] = $attahment_response->attachment_id;
                }
            }

            $ticket_data = array('origin'=>'Web','alertUser'=>1,'alertStaff'=>1,'ticketData'=>array());
            unset($_POST['a']);
            unset($_POST['submit-new-ticket']);
            $user = $this->getAdminSite();
            $admin_email = $user->user_email;

            if(is_multisite()){
                $blog_domain = get_blog_option(get_current_blog_id(),'siteurl');
            }else{
                $blog_domain = get_option('siteurl');
            }
//            $blog_domain = get_blog_details( get_current_blog_id() );
//            $blog_domain = explode(".",$blog_domain->domain)[0] ;

            $ticket_data['ticketData'] = $_POST;
            $ticket_data['ticketData']['name'] = $blog_domain."_".$user->ID."_".$user->display_name;
            $ticket_data['ticketData']['email'] = $admin_email;
            $ticket_data['ticketData']['phone'] = $this->extraFields->get($this->current_user->ID,'phone_number');

            if( count($attahment_idx) )
            {
                $ticket_data['ticketData']['cannedattachments'] = $attahment_idx;
                $ticket_data['ticketData']['attach:response'] = $attahment_idx;
            }

            $ticket_response = MF_OST_REST_API::post(self::REST_TICKETS_URL,$ticket_data,null,true) ;
            if( $ticket_response->ticket_id ) {
                echo sprintf('<div class="updated settings-error notice is-dismissible"> 
<p>
درخواست شما با کد رهگیری %d ثبت گردید.<br/>
برای رویت درخواست می توانید از لینک 
<a href="%s">رویت درخواست</a>
استفاده کنید.
</p>
<button type="button" class="notice-dismiss">
<span class="screen-reader-text">بستن این اعلان.<span>
</button></div>', $ticket_response->ticket_id, "?page=osticket-ticket-list&a=open&&id=" . $ticket_response->ticket_id);
            }
        }
        if( !$this->Topics ) {
            $topics = MF_OST_REST_API::get(self::REST_TOPIC_URL);
            foreach ($topics as $topic) {
                $this->Topics[] = new MF_Topics(array("topic"=>(array)$topic));
            }
        }
        ?>
        <form method="post" action="" enctype="multipart/form-data">
            <input type="hidden" name="a" value="register" />
            <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
            <p>
                <span class="description">* کیل تمام فیلد ها ستاره دار اجباری می باشد .</span>
            </p>
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"><label for="ticket_subject">موضوع</label></th>
                        <td>
                            <input type="text" name="subject" class="widefat" maxlength="100" value=""><br>
                            <span class="description">(max: 100 characters)</span>
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><label for="site_name">دسته بندی</label></th>
                        <td>
                            <select id="topicId" name="topicId" onchange="javascript:
                                    var data = $(':input[name]', '#dynamic-form').serialize();
                                    $.ajax('<?php echo get_site_url(get_current_blog_id())."/?ticketing-support&ticketing-support-type=form&form=";?>' + this.value,
                                    {
                                    data: data,
                                    dataType: 'json',
                                    success: function(json) {
                                        $('.dynamic_forms').remove();
                                        $('#ticket_info').before(json.data.dynamic_form);
                                    }
                                    });">
                                <option value="" selected="selected">&mdash; <?php echo __('Select a Help Topic');?> &mdash;</option>
                                <?php
                                if($this->Topics) {
                                    foreach($this->Topics as $key =>$value) {
                                        echo sprintf('<option value="%d" %s>%s</option>',
                                            $value->getId() , 'selected="selected"', $value->getName());
                                    }
                                } else { ?>
                                    <option value="0" ><?php echo __('General Inquiry');?></option>
                                    <?php
                                } ?>
                            </select>
                            <font class="error">*&nbsp;</font>
                            <hr/>
                        </td>
                    </tr>


                    <tr valign="top" id="ticket_info">
                        <th scope="row"><label for="info">شرح درخواست</label></th>
                        <td>
                            <?php
                            $content = '';
                            $editor_id = 'message';

                            wp_editor( $content, $editor_id );
                            ?>
                            <font class="error">*&nbsp;</font>
                            <br/>
                            <input type="file" name="attachment[]" multiple="multiple">
                            <hr/>
                            <input type="submit" name="submit-new-ticket" id="submit-new-ticket" class="button button-primary" value="ایجاد تیکت جدید">
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>

        <hr />
       <?php
    }

    function setTrashTicket($id)
    {
        $id= (int)$id;
        $ticket = MF_OST_REST_API::get(self::REST_TICKETS_URL."/".$id,null,true);
        print_r($ticket);
    }

    function showTicket($id)
    {//attachments

        $this->checkOsTicketToken();



        $id= (int)$id;
        if( isset($_POST['do'] ) && $_POST['do'] == 'reply' )
        {
            $attahment_idx = array();
            $total = count($_FILES['attachment']['name']);
            for($i=0; $i<$total; $i++)
            {
                if( $_FILES['attachment']['error'][$i] <=0 )
                {
                    $data = file_get_contents($_FILES['attachment']['tmp_name'][$i]);
                    $data = base64_encode($data);
                    $body = array('file_type'=>$_FILES['attachment']['type'][$i],'file_name'=>$_FILES['attachment']['name'][$i],
                        'file_data'=>$data);
                    $attahment_response = MF_OST_REST_API::post(self::REST_TICKETS_ATTACHMENT_URL,$body,null,true);
                    if( isset($attahment_response->attachment_id) )
                        $attahment_idx [] = $attahment_response->attachment_id;
                }
            }

            $body = array('message'=>trim($_POST['message']),
                'ticket_id'=>$id,
                'cannedattachments'=>$attahment_idx,
                'attach:response'=>$attahment_idx);
            $reply_response = MF_OST_REST_API::post(sprintf(self::REST_TICKETS_REPLY_URL,$id),$body,null,true);

        }
        $ticket = MF_OST_REST_API::get(self::REST_TICKETS_URL."/".$id,null,true);
        if( isset($ticket->errors) )
        {
            echo '<div class="updated settings-error notice is-dismissible"> 
                                <p>'.$ticket->errors.'</p>
                                <button type="button" class="notice-dismiss">
                                <span class="screen-reader-text">بستن این اعلان.<span>
                                </span></span></button><button type="button" class="notice-dismiss"><span class="screen-reader-text">بستن این اعلان.</span></button></div>';
            exit;
        }
        ?>
        <style>
            #poststuff h3
            {
                text-align: center;
            }
            .ticket-fields li
            {
                background: #f1f1f1;
                border-bottom: 2px solid #8c8a8a;
            }
            .ticket-fields li p
            {
                text-align: center;
            }
        </style>
        <h2>درخواست شماره <?php echo $ticket->info->ticket_id; ?></h2>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="postbox-container-1" class="postbox-container">
                    <div id="side-sortables" class="meta-box-sortables">
                        <div id="support-system-ticket-details" class="postbox ">
                            <button type="button" class="handlediv button-link" aria-expanded="true">
                                <span class="screen-reader-text"></span>
                                <span class="toggle-indicator" aria-hidden="true"></span>
                            </button>
                            <h2 class="hndle">
                                <span>توضیحات درخواست</span>
                            </h2>
                            <div class="inside">
                                <ul class="ticket-fields">
                                    <li id="ticket-status">
                                        <h3 class="ticket-field-label">وضعیت فعلی</h3>
                                        <p class="ticket-field-value"><?php echo $ticket->info->state; ?></p>
                                    </li>
                                    <li id="ticket-status">
                                        <h3 class="ticket-field-label">اولویت</h3>
                                        <p class="ticket-field-value"><?php echo $ticket->info->priority_label; ?></p>
                                    </li>
                                    <li id="ticket-created">
                                        <h3 class="ticket-field-label">تاریخ ایجاد </h3>
                                        <p class="ticket-field-value"><?php echo date_i18n($ticket->info->created);?></p>
                                    </li>
                                    <li id="ticket-created">
                                        <h3 class="ticket-field-label">تاریخ انقضاء</h3>
                                        <p class="ticket-field-value"><?php echo date_i18n($ticket->info->duedate);?></p>
                                    </li>
                                    <li id="ticket-user">
                                        <h3 class="ticket-field-label">کاربر ایجاد کننده تیکت</h3>
                                        <p class="ticket-field-value"><?php echo $ticket->info->fullname;?></p>
                                    </li>
                                    <li id="ticket-user">
                                        <h3 class="ticket-field-label">ایمیل کاربر</h3>
                                        <p class="ticket-field-value"><?php echo $ticket->info->email;?></p>
                                    </li>
                                    <li id="ticket-updated">
                                        <h3 class="ticket-field-label">آخرین بروز رسانی </h3>
                                        <p class="ticket-field-value"><?php echo date_i18n($ticket->info->updated);?></p>
                                    </li>
                                    <div class="clear"></div>
                                </ul></div>
                        </div>
                    </div>
                </div>

                <div id="postbox-container-2" class="postbox-container">
                    <div id="normal-sortables" class="meta-box-sortables">
                        <div id="support-system-ticket-update" class="postbox ">
                            <button type="button" class="handlediv button-link" aria-expanded="true">
                                <span class="screen-reader-text"></span>
                                <span class="toggle-indicator" aria-hidden="true"></span>
                            </button>
                            <h2 class="hndle">
                                <span>فرم تکمیلی درخواست</span>
                            </h2>
                            <div class="inside">
                                <div class="submitbox" id="submitbox">
                                        <ul class="ticket-update-fields">
                                        <?php
                                            if( count($ticket->info->dynamic_form) )
                                            {
                                                foreach($ticket->info->dynamic_form as $dynamic_item) {
                                                    ?>
                                                    <li id="ticket-staff">
                                                        <p class="ticket-field-label">
                                                            <strong>
                                                                <?php echo $dynamic_item->key; ?>
                                                            </strong>
                                                            <span>
                                                                <?php echo $dynamic_item->value; ?>
                                                            </span>
                                                        </p>
                                                    </li>
                                                    <?php
                                                }
                                            }
                                        ?>
                                            <li id="ticket-staff">
                                                <hr/>
                                                <p class="ticket-field-label">
                                                    <strong>
                                                        عنوان درخواست
                                                    </strong>
                                                </p>
                                                <p style="background-color: #f9f9f9;
                                                        padding: 10px;
                                                        border: 1px solid #e5e5e5;
                                                        -webkit-box-shadow: 0 1px 1px rgba(0,0,0,.04);
                                                        box-shadow: 0 1px 1px rgba(0,0,0,.04);
                                                        ">
                                                        <?php echo $ticket->info->subject;?>
                                                </p>
                                            </li>
                                        </ul>
                                </div>
                            </div>
                        </div>

                        <div id="support-system-ticket-message" class="postbox ">
                            <button type="button" class="handlediv button-link" aria-expanded="true">
                                <span class="toggle-indicator" aria-hidden="true"></span>
                            </button>
                            <h2 class="hndle">
                                <span>دنباله درخواست</span>
                            </h2>
                            <div class="inside">
                                <table class="widefat fixed striped ticketshistory">
                                    <tbody id="ticket-replies-list">
                                    <?php
                                    $answers = $ticket->messages;
                                    $message_founded = false;

                                    $messages = array();
                                    if( count($answers) ) {
                                        foreach ($answers as $ans_item) {
                                            if (strlen($ans_item->message) > 0) {
                                                $message_founded = true;
                                                $messages [] = array('name'=>$ans_item->name,'created'=>$ans_item->created,
                                                    'message'=>$ans_item->message,'attachments'=>$ans_item->attachments);
                                            }
                                        }
                                    }

                                    usort($messages, "mf_osticket_cmp");
                                    if( count($messages) )
                                    {
                                        foreach($messages as $msg_item)
                                        {
                                            if( strlen($msg_item['message'])> 0) {
                                                ?>
                                                <tr>
                                                    <td class="poster column-poster has-row-actions column-primary"
                                                        data-colname="Author">
                                                        <p>
                                                            <span><strong><?php echo $msg_item['name']; ?></strong></span>
                                                            <span style="float:right" dir="ltr"><?php echo date_i18n($msg_item['created']); ?></span>
                                                        </p>

                                                        <p ><?php echo wp_specialchars_decode($msg_item['message']); ?></p>
                                                        <hr/>
                                                        <?php
                                                        if( count($msg_item['attachments']) > 0 ) {
                                                            ?>
                                                            <p>ضمایم</p>
                                                            <?php
                                                            foreach ($msg_item['attachments'] as $attach) {
                                                                echo "<a class=\"button button-primary\" target=\"_blank\" href='" . $attach->url . "'>" . $attach->name . "</a><br/>";
                                                            }
                                                        }
                                                        ?>
                                                    </td>

                                                </tr>
                                                <?php
                                            }
                                        }
                                    }

                                    if( !$message_founded )
                                        echo '<tr><td><p>دنباله درخواستی یافت نشد</p></td></tr>';


                                    ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="support-system-ticket-message" class="postbox ">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="do" value="reply" />
                                <button type="button" class="handlediv button-link" aria-expanded="true">
                                    <span class="toggle-indicator" aria-hidden="true"></span>
                                </button>
                                <h2 class="hndle">
                                    <span>ارسال پاسخ</span>
                                </h2>
                                <div class="">
                                    <table class="widefat fixed  ">
                                        <tbody id="ticket-replies-list">
                                        <tr>
                                            <td class="poster column-poster has-row-actions column-primary"
                                                data-colname="Author">

                                                <textarea style="width:100%;" id="message" name="message" rows="5" cols="10"></textarea>
                                                <br/>
                                                <input type="file" name="attachment[]" multiple="multiple"  >
                                                <br/>
                                                <input type="submit" name="submit-reply-ticket" class="button button-primary" value="ارسال پاسخ">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php
    }

    function showListOfTicketsPage()
        {
            $this->checkOsTicketToken();



            if( isset($_REQUEST['a']) )
            {
                switch($_REQUEST['a'])
                {
                    case 'open':
                        if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) )
                        {
                            $this->showTicket($_REQUEST['id']);
                            exit;
                        }
                        else
                        {

                        }
                        break;
                    case 'trash':
                        if( isset($_REQUEST['id']) && is_numeric($_REQUEST['id']) )
                        {
                            $this->setTrashTicket($_REQUEST['id']);
                            exit;
                        }
                        else
                        {

                        }
                        break;
                }
                exit;
            }
            require 'table.inc.php';
            $table_data = array();
            $tickets = MF_OST_REST_API::get(self::REST_TICKETS_URL,null,true);
            $current_page = $_SERVER['PHP_SELF'];
            $ticket_id_element='
                        <div>
                            <span class="id">ID: %d | </span>
                            <span class="view"><a href="'.$current_page.'?page=osticket-ticket-list&id=%d&a=open" rel="permalink" aria-label="نمایش درخواست">نمایش درخواست</a></span>
                        </div>';

            if( isset($tickets->data) && count($tickets->data) ) {
                foreach ($tickets->data as $ticket) {

                    $table_data[] = array('id'=>sprintf($ticket_id_element,$ticket->ticket_id,$ticket->ticket_id,$ticket->ticket_id,$ticket->ticket_id),'number'=>$ticket->number,'subject'=>$ticket->subject,
                        'status'=>$ticket->status,'created'=>date_i18n($ticket->created));
                }
            }
            $table = new MF_List_Table($table_data,array(
                'id'          => 'شناسه درخواست',
                'number'       => 'کد پیگیری',
                'subject'   => 'عنوان درخواست',
                'status'    => 'وضعیت',
                'created'   => 'تاریخ ایجاد'
            ));
            ?>
            <div style="width:95%;padding: 10px;">
                <a class="button button-primary" href="?page=osticket-new-ticket">ایجاد تیکت جدید
                </a>
                <br/>
                <h1>لیست درخواست ها</h1>
            <?php
            $table->prepare_items();
            $table->display();
            ?>
            <hr />
            </div>
            <?php
            die();
        }

	static public function get_loading_gif()
	{
		return plugin_dir_url( __FILE__ ).'img/loading.gif';
	}
    static function get_checked_icon()
    {
        return plugin_dir_url( __FILE__ ).'img/checked.png';
    }
    static function get_unchecked_icon()
    {
        return plugin_dir_url( __FILE__ ).'img/unchecked.png';
    }

	

    function get_current_userInfo()
    {
    	$this->current_user = wp_get_current_user();
    }

    function sniff_requests()
    {
        if( !isset($_REQUEST['ticketing-support'],$_REQUEST['ticketing-support-type']) )
            return ;

        ob_clean();
        ob_start();

        switch($_REQUEST['ticketing-support-type']) {
            case 'form':
                if( !isset($_REQUEST['form']) )
                {
                    wp_send_json_error(null);
                    exit;
                }
                $topic = MF_OST_REST_API::get(self::REST_TOPICS_URL . "/".(int)$_REQUEST['form'], null, false);
                if (!isset($topic->form))
                {
                    wp_send_json_error(null);
                    exit;
                }
                $form = $topic->form;
                $row_form_item = '<tr class="dynamic_forms" valign="top">
                    <th scope="row">
                        <label for="site_name">%s</label>
                    </th>
                    <td>
                        %s<br>
                        <span class="description">%s</span>
                    </td>
                </tr>';

                foreach ($form as $item) {
                    $required = (bool)$item->required;
                    $required_text = ' ';
                    $required_tag = ' ';
                    if ($required) {
                        $required_tag = '<font class="error">*&nbsp;</font>';
                        $required_text = ' required ';
                    }

                    $extra_meta = ' ';
                    switch ($item->type) {
                        case 'datetime':
                            $extra_meta = ' pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" maxlength="10" class="short hasDatepicker" ';
                        case 'text':

                        case 'phone':
                            echo sprintf($row_form_item, $item->label, sprintf('<input type="text" name="field_%d" %s value="%s" %s/>%s',
                                $item->id, $required, $item->default,$extra_meta,$required_tag), '');
                            break;
                        case 'memo':
                            echo sprintf($row_form_item, $item->label, sprintf('<textarea name="field_%d" %s>%s</textarea>%s',
                                $item->id, $required, $item->default,$required_tag), '');
                            break;
                        case 'bool':
                            echo sprintf($row_form_item, $item->label, sprintf('<input type="checkbox" %s name="_field-checkboxes[]" value="%s" %s/>%s',
                                $required,$item->id,$extra_meta,$required_tag), '');
                            break;
                        case 'choices':
                            $choices = " ";
                            foreach($item->configuration->choices as $choice)
                            {
                                $choices .= sprintf('<option value="%s">%s</option>',$choice->id,$choice->option);
                            }
                            echo sprintf($row_form_item, $item->label, sprintf('<select name="field_%d" %s value="%s" %s >%s</select>%s',
                                $item->id, $required, $item->default,$extra_meta,$choices,$required_tag), '');
                            break;
                    }
                }

                $output = array("dynamic_form"=>ob_get_contents());
                ob_end_clean();
                wp_send_json_success($output);

                break;
        }
        exit;

    }

 }
global $mf_ost;
$mf_ost = new MF_OSTicketSupport();
/*MF_OST_REST_API::post("http://localhost/osticket-1.8/api/rest/public/tickets/",array("token"=>"value"),
    array('Content-type: application/json','token: c6f9bdd6df18111572ab7d30aa97280e'));
exit;*/
?>