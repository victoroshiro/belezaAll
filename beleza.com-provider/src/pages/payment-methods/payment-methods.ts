import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-payment-methods',
  templateUrl: 'payment-methods.html',
})
export class PaymentMethodsPage {

  web: any = WEB;
  paymentMethods: any = [];
  chosenPaymentMethods: any = [];
  user: any = JSON.parse(localStorage.getItem('usrblzpvd')).id;

  constructor(public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http) {
    this.getPaymentMethods();
  }

  savePaymentMethods() {
    let loading = this.loadingCtrl.create({
      content: 'Salvando...'
    });
    loading.present();

    this.http.post(this.web.api + '/payment-methods/', JSON.stringify({id: this.user, payment_method: this.chosenPaymentMethods}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      let alert = this.alertCtrl.create({
        title: 'Sucesso',
        message: 'Métodos de pagamento salvos com sucesso!',
        buttons: ['OK']
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

  choosePaymentMethod(event, service) {
    let checkService = this.chosenPaymentMethods.indexOf(service);

    if(event.checked){
      if(checkService == -1){
        this.chosenPaymentMethods.push(service);
      }
    }
    else{
      if(checkService != -1){
        this.chosenPaymentMethods.splice(checkService, 1);
      }
    }
  }

  getPaymentMethods() {
    this.http.post(this.web.api + '/get-payment-methods/', JSON.stringify({id: this.user}))
    .map(res => res.json())
    .subscribe(success => {
      this.paymentMethods = success;
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
