import { Component, ViewChild } from '@angular/core';
import { IonicPage, NavParams, AlertController, LoadingController, Events } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import io from 'socket.io-client';

/**
 * Generated class for the ChatPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-chat',
  templateUrl: 'chat.html',
})
export class ChatPage {
  @ViewChild("chatList") chatList;

  web: any = WEB;
  chatId: any;
  message: any;
  chat: any = {};
  user: any = JSON.parse(localStorage.getItem("usrblz"));
  socket: any;

  constructor(public navParams: NavParams, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events) {
    this.getChatRoom(this.navParams.get("provider"));

    this.socket = io(this.web.socket, {
      transports: ['websocket']
    });

    this.socket.on('chat:message', (data) => {
      this.chat.chat.push(data);
      
      window.setTimeout(() => {
        this.chatList.nativeElement.scrollTop = this.chatList.nativeElement.scrollHeight;
      }, 100);
    });

    this.events.subscribe("chat:refresh", () => {
      this.getChatRoom(this.navParams.get("provider"));
    });

    this.events.publish("chat:badge:clear", this.navParams.get("provider"));
  }

  sendMessage(message) {
    if(this.chat && this.chat.chat_room && message){
      this.saveMessage(message);
    }
  }

  saveMessage(message) {
    let body = {
      chat_room: this.chat.chat_room,
      message: message,
      from_user: this.user.id,
      to_user: this.chat.provider,
      to_provider: true,
      name: this.user.name,
      photo: this.user.photo,
      push: this.chat.push,
      push_web: this.chat.push_web,
      datetime: 'Agora'
    }

    this.socket.emit('chat:message', body);

    this.message = '';

    this.http.post(this.web.api + "/chat/add/", JSON.stringify(body))
    .map(res => res.json())
    .subscribe(success => {
    },
    error => {
      console.log(error);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: error._body,
        buttons: ['OK']
      });
      alert.present();
    });

    this.chat.chat.push(body);
    window.setTimeout(() => {
      this.chatList.nativeElement.scrollTop = this.chatList.nativeElement.scrollHeight;
    }, 100);
  }

  getChatRoom(provider) {
    let loading = this.loadingCtrl.create({
      content: 'Carregando conversa...'
    });
    loading.present();

    let user = JSON.parse(localStorage.getItem("usrblz"));

    this.http.post(this.web.api + "/chat-room/", JSON.stringify({user: user.id, provider: provider}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.chat = success;
      this.socket.emit("chat:join", {chat: this.chat.chat_room});
    },
    error => {
      loading.dismiss();
      console.log(error);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: error._body,
        buttons: ['OK']
      });
      alert.present();
    });
  }

  ionViewDidLeave() {
    this.socket.disconnect();
  }

}
