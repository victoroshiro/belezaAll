import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController, Events} from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the RefusedPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-canceled',
  templateUrl: 'canceled.html',
})
export class CanceledPage {

  web: any = WEB;
  user: any = JSON.parse(localStorage.getItem("usrblz")).id;
  scheduling: any = [];

  constructor(public app: UtilProvider, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events) {
    this.events.subscribe("scheduling:refresh", () => {
      this.getScheduling();
    });
  }

  refreshScheduling(refresher) {
    this.getScheduling();

    refresher.complete();
  }

  getScheduling() {
    let loading = this.loadingCtrl.create({
      content: 'Carregando...'
    });
    loading.present();

    this.http.post(this.web.api + "/scheduling/list/", JSON.stringify({user: this.user, status: 2}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.scheduling = success;
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

  ionViewWillEnter() {
    this.getScheduling();
  }

}
