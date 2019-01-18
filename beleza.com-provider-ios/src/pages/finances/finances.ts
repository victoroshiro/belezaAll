import { Component } from '@angular/core';
import { IonicPage, NavController, AlertController, LoadingController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-finances',
  templateUrl: 'finances.html',
})
export class FinancesPage {

  web: any = WEB;
  amount: any;
  franchiseeTax: any;
  finances: any = [];

  constructor(public navCtrl: NavController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http) {
    this.getFinances();
  }

  refreshFinances(refresher) {
    this.getFinances(false);

    refresher.complete();
  }

  getFinances(showLoading = true) {
    let loading;

    if(showLoading){
      loading = this.loadingCtrl.create({
        content: 'Carregando...'
      });
      loading.present();
    }

    let user = JSON.parse(localStorage.getItem("usrblzpvd")).id;

    this.http.post(this.web.api + "/finances/", JSON.stringify({id: user}))
    .map(res => res.json())
    .subscribe(success => {
      if(showLoading){
        loading.dismiss();
      }

      this.amount = success.amount;
      this.franchiseeTax = success.franchisee_tax;
      this.finances = success.finances;
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

}
