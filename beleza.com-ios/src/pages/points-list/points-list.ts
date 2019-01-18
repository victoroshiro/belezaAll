import { Component } from '@angular/core';
import { IonicPage, LoadingController, AlertController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the PointsListPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-points-list',
  templateUrl: 'points-list.html',
})
export class PointsListPage {

  web: any = WEB;
  points: any = [];

  constructor(public loadingCtrl: LoadingController, public alertCtrl: AlertController, public http: Http) {
    this.getPoints();
  }

  refreshPoints(refresher) {
    this.getPoints();

    refresher.complete();
  }

  getPoints() {
    let loading = this.loadingCtrl.create({
      content: 'Carregando pontos...'
    });
    loading.present();

    let user = JSON.parse(localStorage.getItem("usrblz"));

    this.http.post(this.web.api + "/points/", JSON.stringify({user: user.id}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.points = success;
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

}
