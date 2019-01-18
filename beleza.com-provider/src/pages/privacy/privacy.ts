import { Component } from '@angular/core';
import { IonicPage, NavParams, NavController, MenuController, AlertController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-privacy',
  templateUrl: 'privacy.html',
})
export class PrivacyPage {

  web: any = WEB;
  privacy: any;
  readOnly: boolean = true;

  constructor(public navCtrl: NavController, public navParams: NavParams, public menu: MenuController, public alertCtrl: AlertController, public http: Http) {
    if(this.navParams.get('readOnly') == false){
      this.readOnly = false;
    }
    
    this.getPrivacyPolicy();
  }

  acceptPrivacy() {
    if(this.readOnly){
      this.navCtrl.setRoot('SchedulingPage');
    }
    else{
      let user = JSON.parse(localStorage.getItem('usrblzpvd'));

      this.http.post(this.web.api + '/accept-privacy-provider/', JSON.stringify({user: user.id}))
      .map(res => res.json())
      .subscribe(success => {
        this.navCtrl.setRoot('SchedulingPage');
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
  }

  getPrivacyPolicy() {
    this.http.post(this.web.api + '/privacy-provider/', null)
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

  logout() {
    localStorage.removeItem('usrblzpvd');

    this.navCtrl.setRoot('LoginPage');
  }

  ionViewDidEnter() {
    this.menu.enable(false);
  }

  ionViewWillLeave() {
    this.menu.enable(true);
  }

}
