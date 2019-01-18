import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-schedule-automatic',
  templateUrl: 'schedule-automatic.html',
})
export class ScheduleAutomaticPage {

  web: any = WEB;
  times: any = [];

  constructor(public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http) {
    this.getTimes();
  }

  save(schedule) {
    let loading = this.loadingCtrl.create({
      content: 'Salvando...'
    });
    loading.present();

    schedule.value.id = JSON.parse(localStorage.getItem('usrblzpvd')).id;

    this.http.post(this.web.api + '/schedule/auto/', JSON.stringify(schedule.value))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      let alert = this.alertCtrl.create({
        title: 'Sucesso',
        message: 'Agenda atualizada com sucesso!',
        buttons: ['Ok']
      });
      alert.present();
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

  getTimes() {
    this.http.post(this.web.api + "/times/", null)
    .map(res => res.json())
    .subscribe(success => {
      this.times = success;
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
