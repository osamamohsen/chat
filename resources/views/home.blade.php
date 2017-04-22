@extends('layouts.app')

@section('content')

    <script type="text/javascript" src="./js/chat/jquery-1.11.2.min.js"></script>
    <script type="text/javascript" src="./js/chat/jquery-migrate-1.2.1.min.js"></script>
    <script type="text/javascript" src="./js/chat/socket.io-1.3.4.js"></script>


    <div class="container spark-screen">
        <input type="hidden" id="user_id" value="{{ \Auth::user()->id }}">
        <input type="hidden" id="user_name" value="{{ \Auth::user()->name }}">
        <div class="row">
            <div class="col-md-4">
                <div class="well">
                    <h3>Online Users</h3>
                    {{--<button class="hi" onclick="pub_room()">Public Room</button>--}}
                    <div id="users"></div>
                    <div class="list-group" id="users"></div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="chat" id="chat_room">Public Room</div>
                {{--<div class="chat hidden"  id="public_chat"></div>--}}
                <div class="chat" id="private_chat"></div>
                <form id="messageForm">
                    <div class="form-group">
                        <label>Enter Message</label>
                        <input id="user_to" type="hidden" value="">
                        <textarea class="form-control" id="message"></textarea>
                        <br />
                        <input id="sendMessage" class="btn btn-primary" type="submit" value="Send Message" />
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>

    var socket,private_chat,pr_childs,public_chat,chat_room,users,messageForm,message,
            user_id,user_to,user_name,all_users;

        jQuery(document).ready(function($) {


            socket = io.connect('http://localhost:8890');

            messageForm = $('#messageForm');
            message = $('#message');
            user_id = $('#user_id');
            user_to = $('#user_to');
            user_name = $('#user_name');
            private_chat = $('#private_chat');
            public_chat = $('#public_chat');
            chat_room = $('#chat_room');
            users = $('#users');

            socket.emit('new user',user_id.val(),user_name.val());

            //online users
            socket.emit('check users',user_id.val(),user_name.val());

            messageForm.submit(function(e){
                e.preventDefault();
                if(user_to.val() == "")
                    send_message(1);
                else
                    send_message(user_to.val());
            });

            //listen for new message
            socket.on('new message', function (data) {
                var channel_append = $('#private_chat'+user_to.val());
                append(channel_append,data.user,data.msg);

            });

            socket.on('display users',function(data){
//                alert(data.users_name);
                all_users = [];
                users.html("");
                private_chat.html("");
                pr_childs = document.getElementById('private_chat');
                for(var i=0;i < data.users_name.length; i++){
                    if(all_users.indexOf(data.users_id[i]) == -1 && data.users_id[i] != user_id.val()){
                        all_users.push(data.users_id[i]);
                        var x = data.users_id[i].trim();
                        var y = data.users_name[i];
                        var div = document.createElement('div');
                        div.setAttribute('id','private_chat'+x);
                        div.setAttribute('class','lib');
                        pr_childs.appendChild(div);
                        users.append('<input id="btnPrivateMessage" ' +
                                'onclick="prv_room('+x+',\''+y+'\');" ' +
                                'class="btn btn-warn" type="submit" ' +
                                'value="'+data.users_name[i]+'" />');
                    }
                }
                //get public lib at init
                pub_room();
            });

        });//end jquery

        //append message data
        function append(room,user,message){
            room.append('<div class="well"><div class="label label-success">'+
                    user+'</div>     '+
                    message+'' +
                    '</div>');
        }

        //send message
        function send_message(user_to_id){
            var msg = message.val().trim();
            socket.emit('send message',msg,user_name.val());
            if(msg != ''){
                message.val('');
                $.ajax({

                    type: "POST",

                    url: '/send_message',

                    data: {id: user_id.val(),user_to: user_to_id, message: msg},

                    success:function(response){

                    }

                });
            }
        }

        //hide all private lib
        function private_chat_hide(){
            for(var i=0;i<all_users.length;i++){
                $('#private_chat'+all_users[i]).hide();
            }
        }

        //get message for private_room
        function get_message(user_to){
            //hide all channels
            private_chat_hide();
            //show current channel
            var pv_chat = null;
            if(user_to != 0){
                $('#private_chat'+user_to).show();
                pv_chat = $('#private_chat'+user_to);
                pv_chat.html("");
            }else{
                pv_chat = public_chat;
            }
            $.ajax({
                type: 'POST',
                url: '/get_message',
                data:{user_to: user_to},
                success: function(response){
                    for(var i=0; i<response.messages.length;i++){
                        if(response.messages[i].user_id == user_id.val())
                            append(pv_chat,user_name.val(),response.messages[i].message);
                        else
                            append(pv_chat,chat_room.html(),response.messages[i].message);
                    }//end for loop
                }//end syccess
            });//end ajax
            return true;
        }//end get message function

        // get private user room event click
        function prv_room(user_to_id,user_name)
        {
                user_to.val(user_to_id);
                public_chat.hide();
                private_chat.show();
                chat_room.html(user_name);
                get_message(user_to_id);
        }//en private user room

        //public chat room
        function pub_room(){
            private_chat.hide();
            public_chat.show();
            get_message(0);
        }
        //end public chat room

    </script>

@endsection