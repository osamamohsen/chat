/**
 * Created by osama on 17/04/17.
 */



var app = require('express')();

var port = 0;

var server = require('http').Server(app);

var io = require('socket.io').listen(server);

var redis = require('redis');

server.listen(8890);

var users_id = [];
var users_name = [];
var connections = [];
var users = {},con=[];

app.get('/', function (req,res) {
   res.sendFile(__dirname + '../resources/views/home.blade.php');
});

io.sockets.on('connection', function (socket) {

    //new Connection

    socket.on('new user', function (user_id,user_name) {
        if(users_name.indexOf(user_name) == -1) return false;
        else{

        connections[user_name] = user_name;
        users_name[user_name] = socket;
        //if(con.indexOf(user_name) == -1){
        //    socket.user = user_name;
        //    users_id.push(user_id);
        //    users_name.push(user_name);
            io.sockets.emit('display users',{users_name: con,users_id: users_id});
        //}
            return true;
        }
    });


    //Disconnect
    socket.on('disconnect',function(data){
        if(!connections.users_name) return;
        users_name.splice(users_name.indexOf(socket.user_name),1);
        connections.splice(connections.indexOf(socket.user_name),1);
        if(connections.length == 1) users_name = [];
        //unset(users_name[user_name]);

        //users_name.pop();
        //if(!socket.user) return;
        //else{
        //    con.splice(con.indexOf(socket.user),1);
        //    io.sockets.emit('display users',{users_name: con,users_id: users_id});
        //}
        io.sockets.emit('display users',{users_name: con,users_id: users_id});

    });

    //send message
    socket.on('send message', function (message,user_name) {
        io.sockets.emit('new message',{msg: message,user: user_name});
    });

    //online users
    socket.on('check users', function (user_id,user_name) {
        users_id.push(user_id);
        users_name.push(user_name);
        io.sockets.emit('display users',{users_name: users_name,users_id: users_id});
    });


});