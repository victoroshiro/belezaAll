import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController, ViewController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-service-create',
  templateUrl: 'service-create.html',
})
export class ServiceCreatePage {

  web: any = WEB;
  specialty: any = [];
  times: any = [];
  payments: any = [];

  constructor(public alertCtrl: AlertController, public loadingCtrl: LoadingController, public viewCtrl: ViewController, public http: Http) {
    this.getTimes();
    this.getServicePayments();
    this.getSpecialty();
  }

  create(service) {
    let loading = this.loadingCtrl.create({
      content: 'Criando...'
    });
    loading.present();

    service.value.id = JSON.parse(localStorage.getItem('usrblzpvd')).id;

    this.http.post(this.web.api + '/service/create/', JSON.stringify(service.value))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      let alert = this.alertCtrl.create({
        title: 'Sucesso',
        message: 'Serviço criado com sucesso!',
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

  getSpecialty() {
    let loading = this.loadingCtrl.create({
      content: 'Carregando...'
    });
    loading.present();

    this.http.post(this.web.api + "/specialty/", null)
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.specialty = success;
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

  getServicePayments() {
    this.http.post(this.web.api + "/service-payments/", JSON.stringify({user: JSON.parse(localStorage.getItem('usrblzpvd')).id}))
    .map(res => res.json())
    .subscribe(success => {
      this.payments = success;
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
