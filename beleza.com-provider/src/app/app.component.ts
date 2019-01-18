import { Component, ViewChild } from '@angular/core';
import { Platform, Nav, AlertController, Events } from 'ionic-angular';
import { WEB } from './config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';
import { OneSignal } from '@ionic-native/onesignal';

@Component({
  templateUrl: 'app.html'
})
export class MyApp {
  @ViewChild(Nav) nav: Nav;

  rootPage:any = 'LoginPage';

  pages: Array<{name: string, component?: string, icon: string, callback?: any, badge?: number}> = [
    {name: 'Agendamentos', component: 'SchedulingPage', icon: 'list'},
    {name: 'Agenda', component: 'ScheduleRedirectPage', icon: 'calendar'},
    {name: 'Agendar manualmente', component: 'SchedulingRegisterPage', icon: 'add'},
    {name: 'Serviços', component: 'ServicesPage', icon: 'briefcase'},
    {name: 'Financeiro', component: 'FinancesPage', icon: 'cash'},
    {name: 'Anúncios', component: 'AdsPage', icon: 'star'},
    {name: 'Chat', component: 'ChatListPage', icon: 'chatbubbles', badge: 0},
    {name: 'Perfil', component: 'ProfilePage', icon: 'person'},
    {name: 'Métodos de pagamento', component: 'PaymentMethodsPage', icon: 'cash'},
    {name: 'Termos de uso', component: 'PrivacyPage', icon: 'information-circle'},
    {name: 'Sair', component: 'LoginPage', icon: 'exit', callback: this.logout}
  ];

  web: any = WEB;
  user: any = {};

  constructor(platform: Platform, statusBar: StatusBar, splashScreen: SplashScreen, public oneSignal: OneSignal, public alertCtrl: AlertController, public http: Http, public events: Events) {
    platform.ready().then(() => {
      statusBar.backgroundColorByHexString('#943E95');
      splashScreen.hide();

      this.setProfile();
      this.pushNotifications();
      this.initChatBadges();

      this.events.subscribe('user:logged', user => {
        this.setProfile();
      });

      this.events.subscribe('chat:badge:clear', (id) => {
        this.clearChatBadges(id);
      });
    });
  }

  pushNotifications() {
    this.oneSignal.startInit('77439b23-d81d-4373-88b4-1ad160be23d9', '406734157533');

    this.oneSignal.inFocusDisplaying(this.oneSignal.OSInFocusDisplayOption.Notification);

    this.oneSignal.handleNotificationReceived().subscribe((data) => {
      if(data.payload.additionalData){
        if(data.payload.additionalData.scheduling){
          this.events.publish('scheduling:refresh');
        }
        else if(data.payload.additionalData.chat){
          this.events.publish('chat:refresh');

          this.chatBadges(data.payload.additionalData.from_user);
        }
      }
    });

    this.oneSignal.handleNotificationOpened().subscribe((data) => {
      if(data.notification.payload.additionalData){
        if(data.notification.payload.additionalData.scheduling){
          this.nav.setRoot('SchedulingDetailsPage', {id: data.notification.payload.additionalData.id});
        }
        else if(data.notification.payload.additionalData.chat){
          this.nav.setRoot('ChatPage', {user: data.notification.payload.additionalData.from_user});
        }
      }
    });

    this.oneSignal.endInit();
  }

  setPush(id) {
    this.oneSignal.getIds()
    .then((data) => {
      let body = {
        user: id,
        push: data.userId
      }

      this.http.post(this.web.api + '/push-provider/', body)
      .map(res => res.json())
      .subscribe(success => {},
      error => {
        console.log(error);
  
        let alert = this.alertCtrl.create({
          title: 'Erro',
          message: error._body,
          buttons: ['OK']
        });
        alert.present();
      });
    })
    .catch((err) => {
      console.log(err);
    });
  }

  initChatBadges() {
    let badges = JSON.parse(localStorage.getItem('usrblz_badge_ChatPage'));

    if(!badges){
      badges = [];
      localStorage.setItem('usrblzpvd_badge_ChatPage', JSON.stringify(badges));
    }

    this.setBadges('ChatListPage', badges.length);
  }

  setBadges(page, count) {
    for(let i = 0; i < this.pages.length; i = i + 1){
      if(this.pages[i].component == page){
        this.pages[i].badge = count;

        break;
      }
    }
  }

  chatBadges(id) {
    let badges = JSON.parse(localStorage.getItem('usrblzpvd_badge_ChatPage'));

    let found = false;

    for(let i = 0; i < badges.length; i = i + 1){
      if(badges[i].id == id){
        badges[i].count = Number(badges[i].count) + 1;

        found = true;
        break;
      }
    }

    if(!found){
      badges.push({
        id: id,
        count: 1
      });
    }

    this.setBadges('ChatListPage', badges.length);

    localStorage.setItem('usrblzpvd_badge_ChatPage', JSON.stringify(badges));
  }

  clearChatBadges(id) {
    let badges = JSON.parse(localStorage.getItem('usrblzpvd_badge_ChatPage'));
    let index;

    if(badges){
      for(let i = 0; i < badges.length; i = i + 1){
        if(badges[i].id == id){
          index = i;
  
          break;
        }
      }
  
      badges.splice(index, 1);
  
      this.setBadges('ChatListPage', badges.length);
  
      localStorage.setItem('usrblzpvd_badge_ChatPage', JSON.stringify(badges));
    }
  }

  setProfile() {
    let user = JSON.parse(localStorage.getItem('usrblzpvd'));

    if(user){
      this.user = user;
      this.setPush(user.id);
    }
  }

  logout() {
    localStorage.removeItem('usrblzpvd');
  }

  setPage(component, callback) {
    if(typeof callback == 'function'){
      callback();
    }

    if(component){
      this.nav.setRoot(component);
    }
  }
}

