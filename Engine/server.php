<?php

/**
 * Created for Amidex-IT.
 * User: Munna
 * Date: 8/13/2017
 * Time: 11:16 PM
 */
require_once('handle.php');
class server extends handle{
    protected $master;                    //main socket connection
    protected $null=NULL;
    protected $host;                     //server host
    protected $port;                     //server port

    public function __construct($host,$port)
    {
        $master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or die("Failed: socket_create()");
        socket_set_option($master, SOL_SOCKET, SO_REUSEADDR, 1) or die("Failed: socket_option()");
        socket_bind($master, WS_HOST, WS_PORT) or die("Failed: socket_bind()");


        socket_listen($master,100);
        $this->master=$master;
    }

    Public function run(){
        global $sock;                  //Connected socket array
        global $user;                  //connected username array
        global $users;                 //Connected Clients connection
        $users=array($this->master);


        while (true) {
            $changed = $users;
            if(socket_select($changed, $null, $null, 0, 0)!==FALSE){

                if (in_array($this->master, $changed)) {
                    $new_socket = socket_accept($this->master);
                    $users[] = $new_socket;
                    $data = socket_read($new_socket, 10240);
                    if($this->doHandShake($data,$new_socket)){

                        self::log("Successfully Complete Handshake \n");
                        self::ready_signal($new_socket);
                    }else{
                        self::log("Error To HandShake \n");
                    }
                    $found_socket = array_search($this->master, $changed);
                    unset($changed[$found_socket]);

                    // do things with the new user
                    socket_getpeername($new_socket, $ip);
                }

            }else{
                self::log("Error ot Select Socket \n");
            }



            foreach ($changed as $changed_socket) {


                //check for any incoming data
                while(socket_recv($changed_socket, $buffer, 10240, 0) >=0) {
                    $data=$this->Unmtpack($buffer);
                    //print_r($data);
                    if($data==!false) {
                        if ( $data->user !== NULL || $data->user !== '' ) {
                            $sock[]= $changed_socket;
                            $user[]= $data->user;
                            self::get_action($data);
                            break 2;
                        }
                        break;
                    }else{
                        $dis=self::disconnect($changed_socket);
                        global $user;
                        global $sock;


                        $all=array_combine($user,$sock);
                        $dis_user=$this->get_key($dis,$all);            //get username

                        $this->log("Disconnect \"$dis_user\"". "\n");

                        $rem_sock=$this->get_key($dis,$sock);
                        unset($sock[$rem_sock]);
                        $rem_usr=$this->get_key($dis_user,$user);
                        unset($user[$rem_usr]);
                        self::offline($dis_user);
                        break;
                    }
                }
            }

        }

    }

    protected function log($msg){
        echo date('h:i:s D-M-Y',time())." : ".$msg;
    }
    private function get_action($data){
        switch ($data->action){
            case('send_text'):
                if(strlen($data->to)>0){
                    self::send_to($data->to,$this->mtpack($data));
                    self::send_to($data->user,$this->mtpack($data));
                }else{
                    self::send_all($this->mtpack($data));
                }

                self::log( "Action Send msg");
                break;
            case('notification'):
                //notification here
                break;
            case('ready'):
                self::log( "\"$data->user\" Ready To Perform..\n");
                self::chat_list();
                break;
            case('type'):
                if(strlen($data->to)>0){
                    self::typing($data->user,$data->to);
                }else{
                    self::typing($data->user);
                }

                break;
        }
    }

    private function typing($from,$to=null){
        $data=['action'=>'type','from'=>$from,'to'=>$to];
        if(isset($to)){
            self::send_to($to,$this->mtpack($data));
        }else{
            self::send_all($this->mtpack($data));
        }

    }


    private function disconnect($client){
        global $users;
        foreach ($users as $key=>$val){
            if($client==$val){
                $mk=$key;
                break;
            }
        }
        $un=$users[$mk];
        unset($users[$mk]);
        return $un;
    }
    private function send_to($to,$message){
        global $user;
        global $sock;
        global $users;
        $all=array_combine($user,$sock);
        $get_sock=$all[$to];
        foreach ($users as $val){
            if($val==$get_sock){

                @socket_write($val,$message,strlen($message));
            }
        }
    }


    private function send_all($message){
        global $user;
        global $sock;
        global $users;

        $all=array_combine($user ,$sock);
        foreach ( $users as $val){
            @socket_write($val,$message,strlen($message));
        }
    }

    protected function ready_signal($socket){
        $data=['action'=>'ready'];
        $packet=$this->mtpack($data);
        socket_write($socket,$packet,strlen($packet));
    }


    private function chat_list(){
        /*TODO: Add filter to show own friend*/
        global $user;
        global $users;
        $online=[];
        foreach ($user as $on){
            $online[]=$on;
        }
        $data=['action'=>'list','users'=>$online];
        $pack=$this->mtpack($data);
        foreach ($users as $socket){
            @socket_write($socket,$pack,strlen($pack));
        }

    }

    private function offline($of_user){
        global $user;
        global $users;
        $online=[];
        $data=['action'=>'offline','users'=>$of_user];
        $pack=$this->mtpack($data);
        foreach ($users as $socket){
            @socket_write($socket,$pack,strlen($pack));
        }
    }

}
