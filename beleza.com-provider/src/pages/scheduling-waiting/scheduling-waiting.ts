import { Component } from '@angular/core';
import { IonicPage, NavController, AlertController, LoadingController, Events } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import io from 'socket.io-client';

@IonicPage()
@Component({
  selector: 'page-scheduling-waiting',
  templateUrl: 'scheduling-waiting.html',
})
export class SchedulingWaitingPage {

  web: any = WEB;
  scheduling: any = [];
  socket: any = io(this.web.socket, {
    transports: ['websocket']
  });

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

    this.http.post(this.web.api + "/scheduling-provider/list/", JSON.stringify({provider: user, status: 1}))
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

  cancelScheduling(id) {
    let alert = this.alertCtrl.create({
      title: 'Aviso',
      message: 'Tem certeza que deseja cancelar esse agendamento?',
      buttons: [
        {
          text: 'Não',
          role: 'cancel'
        },
        {
          text: 'Sim',
          handler: () => {
            let loading = this.loadingCtrl.create({
              content: 'Cancelando...'
            });
            loading.present();
        
            this.http.post(this.web.api + "/scheduling-provider/cancel/", JSON.stringify({id: id}))
            .map(res => res.json())
            .subscribe(success => {
              loading.dismiss();

              this.socket.emit('scheduling:canceled', success);
        
              this.getScheduling(false);
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
      ]
    });
    alert.present();
  }
  
  finalizeScheduling(id) {
    let alert = this.alertCtrl.create({
      title: 'Aviso',
      message: 'Tem certeza que você concluiu o serviço com sucesso e deseja finalizar esse agendamento?',
      buttons: [
        {
          text: 'Não',
          role: 'cancel'
        },
        {
          text: 'Sim',
          handler: () => {
            let loading = this.loadingCtrl.create({
              content: 'Finalizando...'
            });
            loading.present();
        
            this.http.post(this.web.api + "/scheduling-provider/finalize/", JSON.stringify({id: id}))
            .map(res => res.json())
            .subscribe(success => {
              loading.dismiss();

              this.socket.emit('scheduling:finalized', success);
        
              this.getScheduling(false);
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
      ]
    });
    alert.present();
  }

  ionViewWillEnter() {
    this.getScheduling();
  }

}
