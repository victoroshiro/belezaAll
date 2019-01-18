import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController } from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the HomePage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-home',
  templateUrl: 'home.html',
})
export class HomePage {

  web: any = WEB;
  ads: any = [];
  categories: any = [];

  constructor(public app: UtilProvider, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http) {
    this.checkUser();
    this.getHomeContent();
  }

  getHomeContent(refresher = null) {
    this.getAd();
    this.getCategorySpecialty(refresher ? false : true);

    if(refresher){
      refresher.complete();
    }
  }

  getAd() {
    this.http.post(this.web.api + "/ad/", null)
    .map(res => res.json())
    .subscribe(success => {
      this.ads = success;
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

  getCategorySpecialty(showLoading = true) {
    let loading;

    if(showLoading){
      loading = this.loadingCtrl.create({
        content: 'Carregando...'
      });
      loading.present();
    }

    this.http.post(this.web.api + "/category-specialty/", null)
    .map(res => res.json())
    .subscribe(success => {
      if(showLoading){
        loading.dismiss();
      }

      this.categories = success;
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

  showSpecialty(category) {
    if(this.categories[category] && this.categories[category].specialty && this.categories[category].specialty.length){
      this.categories[category].selected = !this.categories[category].selected;
    }
  }

  checkUser() {
    let user = JSON.parse(localStorage.getItem("usrblz"));

    if(!user.privacy_accepted){
      this.app.rootPage("PrivacyPage", {readOnly: false});
    }
    else if(!user.celphone || !user.rg || !user.cpf){
      this.app.rootPage("RegisterCompletePage");
    }
  }

}
