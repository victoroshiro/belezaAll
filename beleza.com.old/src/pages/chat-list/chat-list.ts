import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController, Events } from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the ChatListPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-chat-list',
  templateUrl: 'chat-list.html',
})
export class ChatListPage {

  web: any = WEB;
  chat: any = [];

  constructor(public app: UtilProvider, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events) {
    this.events.subscribe("chat:refresh", () => {
      this.getChatList();
    });
  }

  getChatList() {
    let loading = this.loadingCtrl.create({
      content: 'Carregando conversas...'
    });
    loading.present();

    let user = JSON.parse(localStorage.getItem("usrblz"));

    this.http.post(this.web.api + "/chat/list/", JSON.stringify({user: user.id}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      let chatBadges = JSON.parse(localStorage.getItem("usrblz_badge_ChatPage"));

      if(chatBadges){
        for(let i = 0; i < success.length; i = i + 1){
          let hasBadge = false;
          
          for(let j = 0; j < chatBadges.length; j = j + 1){
            if(success[i].provider == chatBadges[j].id){
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

  ionViewDidEnter() {
    this.getChatList();
  }

}
