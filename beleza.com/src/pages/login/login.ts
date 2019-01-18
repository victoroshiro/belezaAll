import { Component } from '@angular/core';
import { IonicPage, MenuController, AlertController, LoadingController, Events } from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http, Headers, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/map';

import { Facebook, FacebookLoginResponse } from '@ionic-native/facebook';
import { GooglePlus } from '@ionic-native/google-plus';

/**
 * Generated class for the LoginPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-login',
  templateUrl: 'login.html',
})
export class LoginPage {

  web: any = WEB;

  constructor(public app: UtilProvider, public menu: MenuController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events, public fb: Facebook, public googlePlus: GooglePlus) {
    this.checkUser();
  }

  login(user) {
    this.selectMenu();
    
    let loading = this.loadingCtrl.create({
      content: 'Entrando...'
    });
    loading.present();

    let body = JSON.stringify(user.value);

    this.http.post(this.web.api + "/login/", body)
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      localStorage.setItem("usrblz", JSON.stringify(success));
      this.events.publish("user:logged", success);
      this.app.rootPage("HomePage");
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

  loginFacebook() {
    this.selectMenu();

    this.fb.login(['public_profile', 'user_friends', 'email'])
    .then((res: FacebookLoginResponse) => {
      this.fb.api(res.authResponse.userID + "?fields=email,first_name,last_name,name,picture.type(normal)", ['public_profile', 'user_friends', 'email'])
      .then(res => {
        this.socialLoginFacebook(res);
      })
      .catch(e => {
        let alert = this.alertCtrl.create({
          title: 'Erro',
          message: 'Ocorreu um erro ao verificar suas informações do Facebook',
          buttons: ['OK']
        });
        alert.present();
      });
    })
    .catch(e => {
      console.log('Error logging into Facebook', e);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: 'Ocorreu um erro ao logar no Facebook',
        buttons: ['OK']
      });
      alert.present();
    });
  }

  socialLoginFacebook(data){
    let loading = this.loadingCtrl.create({
      content: 'Entrando...'
    });
    loading.present();

    let body = JSON.stringify({
      id: data.id,
      name: data.name,
      email: data.email,
      photo: data.picture.data.url
    });
    
    this.http.post(this.web.api + "/login/social/", body)
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      localStorage.setItem("usrblz", JSON.stringify(success));
      this.events.publish("user:logged", success);
      this.app.rootPage("HomePage");
    },
    error => {
      loading.dismiss();
      console.log(error);
      window.alert(JSON.stringify(error));

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: error._body,
        buttons: ['OK']
      });
      alert.present();
    });
  }

  loginGoogle() {
    this.googlePlus.login({})
    .then(res => {
      this.socialLoginGoogle(res);
    })
    .catch(e => {
      console.log(e);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: 'Ocorreu um erro ao logar no Google Plus',
        buttons: ['OK']
      });
      alert.present();
    });
  }

  socialLoginGoogle(data) {
    let loading = this.loadingCtrl.create({
      content: 'Entrando...'
    });
    loading.present();

    this.http.get('https://www.googleapis.com/plus/v1/people/' + data.userId + '?fields=image&key=' + this.web.googleApiKey)
    .map(res => res.json())
    .subscribe(success => {
      let body = JSON.stringify({
        id: data.userId,
        name: data.displayName,
        email: data.email,
        photo: success.image.url
      });
      
      this.http.post(this.web.api + "/login/social/", body)
      .map(res => res.json())
      .subscribe(success => {
        loading.dismiss();
  
        localStorage.setItem("usrblz", JSON.stringify(success));
        this.events.publish("user:logged", success);
        this.app.rootPage("HomePage");
      },
      error => {
        loading.dismiss();
        console.log(error);
        window.alert(JSON.stringify(error));
  
        let alert = this.alertCtrl.create({
          title: 'Erro',
          message: error._body,
          buttons: ['OK']
        });
        alert.present();
      });
    },
    error => {
      loading.dismiss();
      console.log(error);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: 'Ocorreu um erro ao logar no Google Plus',
        buttons: ['OK']
      });
      alert.present();
    });
  }

  passwordRecovery() {
    const alert = this.alertCtrl.create({
      title: 'Recuperação de conta',
      message: 'Insira seu email para iniciar a recuperação de conta',
      inputs: [
        {
          name: 'email',
          placeholder: 'Email',
          type: 'email'
        },
      ],
      buttons: [
        {
          text: 'Cancel',
          role: 'cancel',
          handler: data => {
            
          }
        },
        {
          text: 'Enviar',
          handler: data => {
            let email = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;

            if(!data.email || !email.test(data.email)){
              let alert = this.alertCtrl.create({
                title: 'Erro',
                message: 'É necessário um email',
                buttons: ['OK']
              });
              alert.present();
            }
            else{
              this.sendPasswordRecovery(data.email);
            }
          }
        }
      ]
    });
    alert.present();
  }

  sendPasswordRecovery(email) {
    let headers = new Headers({"Content-Type": "application/x-www-form-urlencoded"});
    let options = new RequestOptions({headers: headers});

    this.http.post(this.web.api + "/password-recovery/", "email=" + email, options)
    .map(res => res.json())
    .subscribe(success => {
      let alert = this.alertCtrl.create({
        title: 'Sucesso',
        message: 'Siga os passos enviados no seu email para continuar',
        buttons: ['OK']
      });
      alert.present();
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
  }

  checkUser(){
    let loading = this.loadingCtrl.create({
      content: 'Carregando...'
    });
    loading.present();

    let user = JSON.parse(localStorage.getItem("usrblz"));

    if(!localStorage.getItem('usrblz_showtutorial')){
      this.app.rootPage('TutorialPage');
    }
    else if(user){
      this.app.rootPage("HomePage");
    }

    loading.dismiss();
  }

  selectMenu() {
    let buttons = document.querySelectorAll("ion-menu ion-list button");

    for(let i = 0; i < buttons.length; i = i + 1){
      buttons[i].classList.remove("selected");
    }

    document.querySelector("ion-menu ion-list button").classList.add("selected");
  }

  ionViewDidEnter() {
    this.menu.enable(false);
  }

  ionViewWillLeave() {
    this.menu.enable(true);
  }

}
