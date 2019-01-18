import { Component } from '@angular/core';
import { IonicPage, NavController, AlertController, LoadingController, Events } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-scheduling-canceled',
  templateUrl: 'scheduling-canceled.html',
})
export class SchedulingCanceledPage {

  web: any = WEB;
  scheduling: any = [];

  constructor(public navCtrl: NavController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events) {
    this.events.subscribe('scheduling:refresh', () => {
      this.getScheduling(false);
    });
  }

  refreshScheduling(refresher) {
    this.getScheduling(false);

    refresher.complete();
  }

  getScheduling(showLoading = true) {
    let loading;

    if(showLoading){
      loading = this.loadingCtrl.create({
        content: 'Carregando...'
      });
      loading.present();
    }

    let user = JSON.parse(localStorage.getItem("usrblzpvd")).id;

    this.http.post(this.web.api + "/scheduling-provider/list/", JSON.stringify({provider: user, status: 2}))
    .map(res => res.json())
    .subscribe(success => {
      if(showLoading){
        loading.dismiss();
      }

      this.scheduling = success;
    },
    error => {
      if(showLoading){
        loading.dismiss();
      }
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
