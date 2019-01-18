import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController, Events} from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import io from 'socket.io-client';

/**
 * Generated class for the WaitingPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-waiting',
  templateUrl: 'waiting.html',
})
export class WaitingPage {

  web: any = WEB;
  user: any = JSON.parse(localStorage.getItem("usrblz")).id;
  scheduling: any = [];
  socket: any;

  constructor(public app: UtilProvider, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events) {
    this.socket = io(this.web.socket, {
      transports: ['websocket']
    });

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

    this.http.post(this.web.api + "/scheduling/list/", JSON.stringify({user: this.user, status: 1}))
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

  cancelScheduling(id, index) {
    let loading = this.loadingCtrl.create({
      content: 'Cancelando...'
    });
    loading.present();

    this.http.post(this.web.api + "/scheduling/cancel/", JSON.stringify({id: id, user: this.user}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.socket.emit('scheduling:canceled:user', success);

      if(this.scheduling[index]){
        this.scheduling.splice(index, 1);
      }
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
