import { Component } from '@angular/core';
import { IonicPage, NavController, AlertController, LoadingController, Events } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-chat-list',
  templateUrl: 'chat-list.html',
})
export class ChatListPage {

  web: any = WEB;
  chat: any = [];

  constructor(public navCtrl: NavController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events) {
    this.events.subscribe('chat:refresh', () => {
      this.getChatList(false);
    });
  }

  getChatList(showLoading = true) {
    let loading;

    if(showLoading){
      loading = this.loadingCtrl.create({
        content: 'Carregando...'
      });
      loading.present();
    }

    let user = JSON.parse(localStorage.getItem("usrblzpvd"));

    this.http.post(this.web.api + "/chat-provider/list/", JSON.stringify({user: user.id}))
    .map(res => res.json())
    .subscribe(success => {
      if(showLoading){
        loading.dismiss();
      }

      let chatBadges = JSON.parse(localStorage.getItem("usrblzpvd_badge_ChatPage"));

      if(chatBadges){
        for(let i = 0; i < success.length; i = i + 1){
          let hasBadge = false;
          
          for(let j = 0; j < chatBadges.length; j = j + 1){
            if(success[i].user == chatBadges[j].id){
              success[i].badge = chatBadges[j].count;
              
              hasBadge = true;
              break;
            }
          }
  
          if(!hasBadge){
            success[i].badge = 0;
          }
        }
      }

      this.chat = success;
    },
    error => {
      if(showLoading){
        loading.dismiss();
      }
      console.log(error);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: error._body,
        buttons: ['OK']
      });
      alert.present();
    });
  }

  ionViewDidEnter() {
    this.getChatList();
  }

}
