<html>
    <head>
        <script src="js/jquery.min.js"></script>
        <script>
            var ws;
            var usr;
            $(document).ready(function(){

                var person = prompt("Please enter your name", "Munna");

                if (person == null || person == "") {
                    location.reload();
                } else {
                    usr = person;
                    $('<span style="color: #d9534f;font-size: 20px"><b>'+usr+'</b></span>').appendTo('#usr');
                }


                serverUrl=("ws://192.168.199.7:9000/socket/my/server.php");
               if (window.MozWebSocket) {
                   ws = new MozWebSocket(serverUrl);
               } else if (window.WebSocket) {
                   ws = new WebSocket(serverUrl);
               }

                ws.onopen = function(ev) {
                    console.log("connected");
                };


                ws.onerror = function(ev) {
                    console.log($ev.data) ;
                }
                ws.onmessage = function(ev) {
                    response(ev.data);

                }
                ws.onclose=function(res){
                    ws.close();
                    console.log('close');
                }
            });

        </script>
    </head>
    <body>
    <div id="online" style="border: solid;color: black;background-color: black">
    <span style="color: #505aff"><b>Online</b></span>
    </div>
    <br>
    <label for="to">Send to:</label>
        <input type="text" id="to">
    <br>
    <br>
    <label for="usr">Your Name:</label>
    <div id="usr">

    </div>
    <br>
    <br>
    <label for="txt_snd">Message:</label>
        <input type="text" id="txt_snd">
    <br>
    <br>
        <input type="button" id="msg" value="Message">
    <br><br>

    <div>
        <div id="typeing" hidden></div>
    </div>
    <div id="get_msg">

    </div>
    <br>
    <br>

    </body>
    <script>
        $('.online_usr').click(function(){
           console.log('mk');
            //$('#to').val(this.value());
        });
        $('#msg').click(function(){
            text=$('#txt_snd').val();
            snd_to=$('#to').val();

            ws.send(request('send_text',text,snd_to));
            $('#txt_snd').val('')
        });
        $('#txt_snd').keypress (function(ev) {
            snd_to=$('#to').val();
            ws.send(request('type','',snd_to))
        });
        //--------------------------------------------
        var request=function(act,msg,dt_for){
            data={action:act,user:usr,message:msg,to:dt_for};
            return JSON.stringify(data);
        };

        var response=function(data){
            dat=JSON.parse(data);
            switch(dat.action){
                case('send_text'):
                //set your function.......

                    add_msg(dat);

                    //console.log('get data');
                break;
                case('ready'):
                    //console.log('Ready To Perfrom');
                    ws.send(request('ready','i am ready'));
                break;
                case('offline'):
                    //console.log('Remove offline');
                    $('#'+dat.users).remove();
                break;
                case('list'):
                    //console.log(dat.users);
                    get_online(dat);
                break;
                case('type'):
                    //console.log(dat.users);
                    $('#typeing').html('<label style="color: red;"><b>'+dat.from +' Typing....<b></label>')
                    $('#typeing').show("fast",function(){
                        $("#typeing").hide(2000);
                    });
                break;

            }
        }

        function add_msg(serv){
            if(serv.user==usr){
                $('<br><div style="float: left"><b style="background-color: #d9534f;border-radius: 3px">'+serv.user+'</b> : '+serv.message+'</div>').appendTo('#get_msg')
            }else{
                $('<br><div style="float: right"><b style="background-color: #777620;border-radius: 3px">'+serv.user+'</b> : '+serv.message+'</div>').appendTo('#get_msg')
            }

        }

        function get_online(dat){
            for(i=0;i<dat.users.length;i++) {
                //console.log(dat.users[i]);
                if($("#" + dat.users[i]).length == 0) {
                    $('<br><a class="online_usr" id="' + dat.users[i] + '" style="color: #38b800">&#9757; ' + dat.users[i] + '</a>').appendTo('#online');
                };

            }
        }
    </script>
</html>



