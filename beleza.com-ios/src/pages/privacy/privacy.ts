import { Component } from '@angular/core';
import { IonicPage, NavParams, MenuController, AlertController } from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the PrivacyPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-privacy',
  templateUrl: 'privacy.html',
})
export class PrivacyPage {

  web: any = WEB;
  privacy: any;
  readOnly: boolean = true;

  constructor(public app: UtilProvider, public navParams: NavParams, public menu: MenuController, public alertCtrl: AlertController, public http: Http) {
    if(this.navParams.get("readOnly") == false){
      this.readOnly = false;
    }
    
    this.getPrivacyPolicy();
  }

  acceptPrivacy() {
    if(this.readOnly){
      this.app.rootPage("HomePage");
    }
    else{
      let user = JSON.parse(localStorage.getItem("usrblz"));

      this.http.post(this.web.api + "/accept-privacy-user/", JSON.stringify({user: user.id}))
      .map(res => res.json())
      .subscribe(success => {
        localStorage.setItem("usrblz", JSON.stringify(success));
        this.app.rootPage("HomePage");
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

  logout() {
    localStorage.removeItem("usrblz");
    localStorage.removeItem("usrblz_geolocation");
    localStorage.removeItem("usrblz_provider_filter");

    this.app.rootPage("LoginPage");
  }

  ionViewDidEnter() {
    this.menu.enable(false);
  }

  ionViewWillLeave() {
    this.menu.enable(true);
  }

}
