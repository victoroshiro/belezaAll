import { Component } from '@angular/core';
import { IonicPage, NavController, AlertController, LoadingController, Events, MenuController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http, Headers, RequestOptions } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-login',
  templateUrl: 'login.html',
})
export class LoginPage {

  web: any = WEB;
  userChecked: boolean = true;

  constructor(public navCtrl: NavController, public menu: MenuController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events) {
    this.checkUser();
  }

  login(user) {
    let loading = this.loadingCtrl.create({
      content: 'Entrando...'
    });
    loading.present();

    let body = JSON.stringify(user.value);

    this.http.post(this.web.api + '/login-provider/', body)
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      localStorage.setItem('usrblzpvd', JSON.stringify(success));
      this.events.publish('user:logged', success);
      this.navCtrl.setRoot('SchedulingPage');
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
    let headers = new Headers({'Content-Type': 'application/x-www-form-urlencoded'});
    let options = new RequestOptions({headers: headers});

    this.http.post(this.web.api + '/password-recovery/', 'email=' + email, options)
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

  checkUser() {
    let user = JSON.parse(localStorage.getItem('usrblzpvd'));

    if(user){
      this.navCtrl.setRoot('SchedulingPage');
    }
    else{
      this.userChecked = false;
    }
  }

  ionViewWillEnter() {
    this.menu.enable(false);
  }

  ionViewWillLeave() {
    this.menu.enable(true);
  }

}
