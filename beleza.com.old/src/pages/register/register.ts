import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController, Events, MenuController } from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';
 
/**
 * Generated class for the RegisterPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-register',
  templateUrl: 'register.html',
})
export class RegisterPage {

  web: any = WEB;
  privacy: any;

  constructor(public app: UtilProvider, public menu: MenuController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events) {
    this.getPrivacyPolicy();
  }

  register(user) {
    let loading = this.loadingCtrl.create({
      content: 'Cadastrando...'
    });
    loading.present();

    let body = JSON.stringify(user.value);

    this.http.post(this.web.api + "/register/", body)
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      localStorage.setItem("usrblz", JSON.stringify(success));
      this.app.rootPage("HomePage");
      this.events.publish("user:logged", success);
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

  privacyPolicy() {
    let alert = this.alertCtrl.create({
      title: 'Termos de uso',
      message: this.privacy,
      buttons: ['OK']
    });
    alert.present();
  }

  getPrivacyPolicy() {
    this.http.post(this.web.api + "/privacy-user/", null)
    .map(res => res.json())
    .subscribe(success => {
      this.privacy = success;
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

  ionViewDidEnter() {
    this.menu.enable(false);
  }

  ionViewWillLeave() {
    this.menu.enable(true);
  }

}
