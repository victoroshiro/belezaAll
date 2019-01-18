#!/usr/bin/env node

var app = require('express')();
var http = require('http').Server(app);

// HTTPS config
var fs = require('fs');
var credentials = {
    key: fs.readFileSync('/etc/letsencrypt/live/node.aplicativobeleza.com/privkey.pem'), 
    cert: fs.readFileSync('/etc/letsencrypt/live/node.aplicativobeleza.com/fullchain.pem'),
    ca: fs.readFileSync('/etc/letsencrypt/live/node.aplicativobeleza.com/chain.pem')
};
var https = require('https');
var httpsServer = https.createServer(credentials, app);

// Socket config
var ioServer = require('socket.io');
var io = new ioServer();
io.attach(http);
io.attach(httpsServer);
io.origins('http://localhost:* https://plataforma.aplicativobeleza.com:*');

var web = 'https://plataforma.aplicativobeleza.com';

io.on('connection', function(socket){
    socket.on('scheduling:created', function(data){
        pushProvider({
            content: 'Foi solicitado por ' + data.name + ' os serviços (' + data.services + ') para ' + data.date + ' ' + data.time,
            title: 'Há uma nova solicitação de serviço',
            subtitle: '',
            data: {scheduling: true, id: data.id},
            group: 'scheduling:created',
            users: [data.push, data.push_web]
        });
    });

    socket.on('scheduling:edited', function(data){
        pushProvider({
            content: 'O agendamento de ' + data.name + ' para ' + data.date + ' ' + data.time + ' foi editado',
            title: 'Agendamento editado',
            subtitle: '',
            data: {scheduling: true, id: data.id},
            group: 'scheduling:edited',
            users: [data.push, data.push_web]
        });
    });

    socket.on('scheduling:canceled:user', function(data){
        pushProvider({
            content: 'O agendamento solicitado por ' + data.user + ' para ' + data.date + ' ' + data.time + ' foi cancelado pelo usuário',
            title: 'Agendamento cancelado',
            subtitle: '',
            data: {scheduling: true, id: data.id},
            group: 'scheduling:canceled',
            users: [data.push, data.push_web]
        });
    });

    socket.on('scheduling:canceled', function(data){
        push({
            content: 'Seu agendamento para ' + data.date + ' ' + data.time + ' foi cancelado por ' + data.provider,
            title: 'Agendamento cancelado',
            subtitle: '',
            data: {scheduling: true, id: data.id},
            group: 'scheduling:canceled',
            users: [data.push]
        });
    });

    socket.on('scheduling:finalized', function(data){
        push({
            content: 'Seu agendamento realizado por ' + data.provider + ' foi finalizado com sucesso!',
            title: 'Serviço finalizado',
            subtitle: '',
            data: {scheduling: true, id: data.id},
            group: 'scheduling:finalized',
            users: [data.push]
        });
    });

    socket.on('chat:join', function(data){
        socket.join("chat_" + data.chat);
    });

    socket.on('chat:message', function(data){
        var room = io.sockets.adapter.rooms["chat_" + data.chat_room];

        if(room && room.length > 1){
            socket.broadcast.to("chat_" + data.chat_room).emit("chat:message", data);
        }
        else{
            if(data.to_provider){
                pushProvider({
                    content: data.message,
                    title: data.name,
                    subtitle: '',
                    data: {chat: true, chat_room: data.chat_room, from_user: data.from_user},
                    group: 'chat:' + data.chat_room,
                    users: [data.push, data.push_web]
                });
            }
            else{
                push({
                    content: data.message,
                    title: data.name,
                    subtitle: '',
                    data: {chat: true, chat_room: data.chat_room, from_user: data.from_user},
                    group: 'chat:' + data.chat_room,
                    users: [data.push]
                });
            }
        }
    });
});

setInterval(function(){
    getSchedulingNotice(function(data){
        var i = 0;

        for(; i < data.length; i = i + 1){
            push({
                content: 'Você tem um agendamento com ' + data[i].provider + ' ' + data.today == 1 ? 'dentro de 30min' : 'dentro de 24h',
                title: 'Serviço próximo',
                subtitle: '',
                data: {scheduling: true, id: data[i].id},
                group: 'scheduling:notice',
                users: [data[i].user_push]
            });
    
            pushProvider({
                content: 'Você tem um agendamento com ' + data[i].user + ' ' + data.today == 1 ? 'dentro de 30min' : 'dentro de 24h',
                title: 'Serviço próximo',
                subtitle: '',
                data: {scheduling: true, id: data[i].id},
                group: 'scheduling:notice',
                users: [data[i].provider_push, data[i].push_web]
            });
        }
    });
}, 60000);

function getSchedulingNotice(callback) {
    var options = {
        host: "plataforma.aplicativobeleza.com",
        port: 443,
        path: "/api/scheduling/notice/",
        method: "POST"
    };

    var req = https.request(options, function(res){
        res.on('data', (d) => {
            d = JSON.parse(d);

            callback(d);
        });
    });
    req.end();
}

function push(message) {
    var message = { 
        app_id: "a4a98272-dd91-4952-955a-4adb4e5369cf",
        android_accent_color: "FFF53593",
        android_group: message.group,
        android_led_color: "FF943E95",
        big_picture: "screen",
        contents: {"en": message.content},
        data: message.data,
        url: message.url,
        headings: {"en": message.title},
        include_player_ids: message.users,
        subtitle: {"en": message.subtitle}
    };

    sendPush(message);
}

function sendPush(data) {
    var headers = {
        "Content-Type": "application/json; charset=utf-8",
        "Authorization": "Basic N2E4MTc1NzYtMmIxMS00NmRlLTk2YzgtYjBmOTQ1YmE1OTVm"
    };
  
    var options = {
        host: "onesignal.com",
        port: 443,
        path: "/api/v1/notifications",
        method: "POST",
        headers: headers
    };
  
    var req = https.request(options);
  
    req.write(JSON.stringify(data));
    req.end();
};

function pushProvider(message) {
    var message = { 
        app_id: "77439b23-d81d-4373-88b4-1ad160be23d9",
        android_accent_color: "FFF53593",
        android_group: message.group,
        android_led_color: "FF943E95",
        big_picture: "screen",
        contents: {"en": message.content},
        data: message.data,
        url: message.url,
        headings: {"en": message.title},
        include_player_ids: message.users,
        subtitle: {"en": message.subtitle}
    };

    sendPushProvider(message);
}

function sendPushProvider(data) {
    var headers = {
        "Content-Type": "application/json; charset=utf-8",
        "Authorization": "Basic NTdkMTdkN2UtYzQxYy00ZjI3LTkyYTItNzI3N2UxM2I0OWNm"
    };
  
    var options = {
        host: "onesignal.com",
        port: 443,
        path: "/api/v1/notifications",
        method: "POST",
        headers: headers
    };
  
    var req = https.request(options);
  
    req.write(JSON.stringify(data));
    req.end();
};

http.listen('9998', function(){
    console.log('listening on *: 9998');
});
httpsServer.listen('9997', function(){
    console.log('listening secure on *: 9997');
});