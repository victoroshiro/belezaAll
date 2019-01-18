import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController, MenuController } from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the RegisterCompletePage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-register-complete',
  templateUrl: 'register-complete.html',
})
export class RegisterCompletePage {

  web: any = WEB;
  user: any = JSON.parse(localStorage.getItem("usrblz"));

  constructor(public app: UtilProvider, public menu: MenuController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http) {
  }

  complete(user) {
    let loading = this.loadingCtrl.create({
      content: 'Salvando...'
    });
    loading.present();

    let body = JSON.stringify(user.value);

    this.http.post(this.web.api + "/register-complete/", body)
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      localStorage.setItem("usrblz", JSON.stringify(success));
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
