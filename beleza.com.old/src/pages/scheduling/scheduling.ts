import { Component } from '@angular/core';
import { IonicPage, NavParams, AlertController, LoadingController, Events } from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import io from 'socket.io-client';

/**
 * Generated class for the SchedulingPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-scheduling',
  templateUrl: 'scheduling.html',
})
export class SchedulingPage {

  web: any = WEB;
  scheduling: any = {};
  user: any = JSON.parse(localStorage.getItem("usrblz")).id;
  socket: any;

  constructor(public navParams: NavParams, public app: UtilProvider, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events) {
    this.getScheduling(this.navParams.get("id"));

    this.socket = io(this.web.socket, {
      transports: ['websocket']
    });

    this.events.subscribe("scheduling:refresh", () => {
      this.getScheduling(this.navParams.get("id"));
    });
  }

  refreshScheduling(refresher) {
    this.getScheduling(this.navParams.get("id"));

    refresher.complete();
  }

  getScheduling(id) {
    let loading = this.loadingCtrl.create({
      content: 'Carregando...'
    });
    loading.present();

    this.http.post(this.web.api + "/scheduling/details/", JSON.stringify({id: id}))
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

  cancelScheduling(id) {
    let loading = this.loadingCtrl.create({
      content: 'Cancelando...'
    });
    loading.present();

    this.http.post(this.web.api + "/scheduling/cancel/", JSON.stringify({id: id, user: this.user}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.socket.emit('scheduling:canceled:user', success);

      this.getScheduling(this.navParams.get("id"));
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
