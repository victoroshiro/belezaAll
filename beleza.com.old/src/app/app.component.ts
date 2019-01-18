import { Component, ViewChild } from '@angular/core';
import { Nav, Platform, Events, ToastController, AlertController } from 'ionic-angular';

import { StatusBar } from '@ionic-native/status-bar';
import { SplashScreen } from '@ionic-native/splash-screen';
import { Geolocation } from '@ionic-native/geolocation';
import { OneSignal } from '@ionic-native/onesignal';

import { WEB } from './config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@Component({
  templateUrl: 'app.html'
})
export class MyApp {
  @ViewChild(Nav) nav: Nav;

  web: any = WEB;
  photo: any;
  social: any;

  rootPage: any = "LoginPage";

  pages: Array<{title: string, component: any, callback: any, badge: number}>;

  constructor(public platform: Platform, public statusBar: StatusBar, public splashScreen: SplashScreen, public events: Events, public geolocation: Geolocation, public toastCtrl: ToastController, public oneSignal: OneSignal, public alertCtrl: AlertController, public http: Http) {
    this.initializeApp();

    // used for an example of ngFor and navigation
    this.pages = [
      { title: 'Home - Beleza.com', component: "HomePage", callback: null, badge: 0 },
      { title: 'Procurar profissionais', component: "ProfissionalPage", callback: null, badge: 0 },
      { title: 'Minha agenda', component: "ServicesPage", callback: null, badge: 0 },
      { title: 'Meus pontos', component: "AwardsPage", callback: null, badge: 0 },
      { title: 'Meus endereços', component: "AddressPage", callback: null, badge: 0 },
      { title: 'Lista de chat', component: "ChatListPage", callback: null, badge: 0 },
      { title: 'Editar perfil', component: "ProfilePage", callback: null, badge: 0 },
      { title: 'Tutorial', component: "TutorialPage", callback: null, badge: 0 },
      { title: 'Termos de uso', component: "PrivacyPage", callback: null, badge: 0 },
      { title: 'Sair', component: "LoginPage", callback: function(){
        localStorage.removeItem("usrblz");
        localStorage.removeItem("usrblz_geolocation");
        localStorage.removeItem("usrblz_provider_filter");
      }, badge: 0 },
    ];
  }

  initializeApp() {
    this.platform.ready().then(() => {
      // Okay, so the platform is ready and our plugins are available.
      // Here you can do any higher level native things you might need.
      if(this.platform.is('ios')){
        this.statusBar.overlaysWebView(false);
      }

      this.statusBar.backgroundColorByHexString('#943E95');
      this.splashScreen.hide();

      this.setProfile();

      this.events.subscribe("user:logged", (user) => {
        this.photo = user.photo;
        this.social = user.social;

        this.setPush();
      });

      this.events.subscribe("user:modified", (user) => {
        this.photo = user.photo;
      });

      this.events.subscribe("chat:badge:clear", (id) => {
        this.clearChatBadges(id);
      });

      this.getGeolocation();
      this.pushNotifications();
      this.initChatBadges();

      document.querySelector("ion-menu ion-list button").classList.add("selected");
    });
  }

  pushNotifications() {
    this.oneSignal.startInit("a4a98272-dd91-4952-955a-4adb4e5369cf", "471219654579");

    this.oneSignal.inFocusDisplaying(this.oneSignal.OSInFocusDisplayOption.Notification);

    this.oneSignal.handleNotificationReceived().subscribe((data) => {
      if(data.payload.additionalData){
        if(data.payload.additionalData.scheduling){
          this.events.publish("scheduling:refresh");
        }
        else if(data.payload.additionalData.chat){
          this.events.publish("chat:refresh");

          this.chatBadges(data.payload.additionalData.from_user);
        }
      }
    });

    this.oneSignal.handleNotificationOpened().subscribe((data) => {
      if(data.notification.payload.additionalData){
        if(data.notification.payload.additionalData.scheduling){
          this.nav.setRoot("SchedulingPage", {id: data.notification.payload.additionalData.id});
        }
        else if(data.notification.payload.additionalData.chat){
          this.nav.setRoot("ChatPage", {provider: data.notification.payload.additionalData.from_user});
        }
      }
    });

    this.oneSignal.endInit();
  }

  setPush() {
    let user = JSON.parse(localStorage.getItem("usrblz"));

    if(user.id){
      this.oneSignal.getIds()
      .then((data) => {
        let body = {
          user: user.id,
          push: data.userId
        }

        this.http.post(this.web.api + "/push/", body)
        .map(res => res.json())
        .subscribe(success => {
          console.log("success", data.userId);
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
      })
      .catch((err) => {
        console.log(err);
      });
    }
  }

  getGeolocation() {
    this.geolocation.watchPosition().subscribe(pos => {
      if(pos && pos.coords){
        let coords = {
          latitude: pos.coords.latitude,
          longitude: pos.coords.longitude
        };

        localStorage.setItem("usrblz_geolocation", JSON.stringify(coords));
      }
      else{
        const toast = this.toastCtrl.create({
          message: 'Ative a localização para encontrar profissionais',
          duration: 6000,
          position: 'top'
        });

        toast.present();
      }
    });
  }

  setBadges(page, count) {
    for(let i = 0; i < this.pages.length; i = i + 1){
      if(this.pages[i].component == page){
        this.pages[i].badge = count;

        break;
      }
    }
  }

  initChatBadges() {
    let badges = JSON.parse(localStorage.getItem("usrblz_badge_ChatPage"));

    if(!badges){
      badges = [];
      localStorage.setItem("usrblz_badge_ChatPage", JSON.stringify(badges));
    }

    this.setBadges("ChatListPage", badges.length);
  }

  chatBadges(id) {
    let badges = JSON.parse(localStorage.getItem("usrblz_badge_ChatPage"));

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

    this.setBadges("ChatListPage", badges.length);

    localStorage.setItem("usrblz_badge_ChatPage", JSON.stringify(badges));
  }

  clearChatBadges(id) {
    let badges = JSON.parse(localStorage.getItem("usrblz_badge_ChatPage"));
    let index;

    if(badges){
      for(let i = 0; i < badges.length; i = i + 1){
        if(badges[i].id == id){
          index = i;
  
          break;
        }
      }
  
      badges.splice(index, 1);
  
      this.setBadges("ChatListPage", badges.length);
  
      localStorage.setItem("usrblz_badge_ChatPage", JSON.stringify(badges));
    }
  }

  setProfile() {
    let user = JSON.parse(localStorage.getItem("usrblz"));

    if(user){
      this.photo = user.photo;
      this.social = user.social;
    }
  }

  goProfile() {
    this.nav.setRoot('ProfilePage');
  }

  openPage(page, e) {
    // Reset the content nav to have just this page
    // we wouldn't want the back button to show in this scenario

    if(typeof page.callback === "function"){
      page.callback();
    }

    if(page.component){
      this.nav.setRoot(page.component);
    }

    let buttons = document.querySelectorAll("ion-menu ion-list button");

    for(let i = 0; i < buttons.length; i = i + 1){
      buttons[i].classList.remove("selected");
    }

    e.target.parentNode.parentNode.classList.add("selected");
  }
}
