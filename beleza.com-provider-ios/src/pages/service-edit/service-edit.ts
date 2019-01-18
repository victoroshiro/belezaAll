import { Component } from '@angular/core';
import { IonicPage, NavParams, AlertController, LoadingController, ViewController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-service-edit',
  templateUrl: 'service-edit.html',
})
export class ServiceEditPage {

  web: any = WEB;
  service: any = {};
  specialty: any = [];
  times: any = [];

  constructor(public navParams: NavParams, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public viewCtrl: ViewController, public http: Http) {
    this.getService(this.navParams.get('id'));
    this.getTimes();
  }

  edit(service) {
    let loading = this.loadingCtrl.create({
      content: 'Editando...'
    });
    loading.present();

    this.http.post(this.web.api + '/service/edit/', JSON.stringify(service.value))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      let alert = this.alertCtrl.create({
        title: 'Sucesso',
        message: 'Serviço editado com sucesso!',
        buttons: [{
          text: 'Ok',
          handler: () => {
            this.dismiss(success);
          }
        }]
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

  getService(id) {
    let loading = this.loadingCtrl.create({
      content: 'Carregando...'
    });
    loading.present();

    this.http.post(this.web.api + "/service/", JSON.stringify({id: id}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.service = success.service;
      this.specialty = success.specialty;
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
    this.http.post(this.web.api + "/service-times/", null)
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
  

  dismiss(data = null) {
    this.viewCtrl.dismiss(data);
  }

}
