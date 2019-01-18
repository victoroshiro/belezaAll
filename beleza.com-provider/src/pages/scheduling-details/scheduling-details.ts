import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, AlertController, LoadingController, Events } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import io from 'socket.io-client';

@IonicPage()
@Component({
  selector: 'page-scheduling-details',
  templateUrl: 'scheduling-details.html',
})
export class SchedulingDetailsPage {

  web: any = WEB;
  scheduling: any = {};
  socket: any = io(this.web.socket, {
    transports: ['websocket']
  });

  constructor(public navCtrl: NavController, public navParams: NavParams, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public events: Events) {
    this.getScheduling(this.navParams.get('id'));

    this.events.subscribe('scheduling:refresh', () => {
      this.getScheduling(false);
    });
  }

  refreshScheduling(refresher) {
    this.getScheduling(this.navParams.get('id'), false);

    refresher.complete();
  }

  getScheduling(id, showLoading = true) {
    let loading;

    if(showLoading){
      loading = this.loadingCtrl.create({
        content: 'Carregando...'
      });
      loading.present();
    }

    this.http.post(this.web.api + "/scheduling-provider/details/", JSON.stringify({id: id}))
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
        
              this.getScheduling(this.navParams.get('id'), false);
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
        
              this.getScheduling(this.navParams.get('id'), false);
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

}
