import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController, ModalController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-ads',
  templateUrl: 'ads.html',
})
export class AdsPage {

  web: any = WEB;
  ads: any = [];

  constructor(public alertCtrl: AlertController, public loadingCtrl: LoadingController, public modalCtrl: ModalController, public http: Http) {
    this.getAds();
  }

  refreshAds(refresher) {
    this.getAds(false);

    refresher.complete();
  }

  getAds(showLoading = true) {
    let loading;

    if(showLoading){
      loading = this.loadingCtrl.create({
        content: 'Carregando...'
      });
      loading.present();
    }

    let user = JSON.parse(localStorage.getItem("usrblzpvd")).id;

    this.http.post(this.web.api + "/ads/", JSON.stringify({id: user}))
    .map(res => res.json())
    .subscribe(success => {
      if(showLoading){
        loading.dismiss();
      }

      this.ads = success;
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

  deleteAd(id, index) {
    let loading = this.loadingCtrl.create({
      content: 'Deletando...'
    });
    loading.present();

    this.http.post(this.web.api + "/ad/delete/", JSON.stringify({id: id}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      if(this.ads[index]){
        this.ads.splice(index, 1);
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

  editAd(id) {
    let adModal = this.modalCtrl.create("AdEditPage", {id: id});
    adModal.onDidDismiss(data => {
      if(data){
        this.getAds();
      }
    });
    adModal.present();
  }

  addAd() {
    let adModal = this.modalCtrl.create("AdCreatePage");
    adModal.onDidDismiss(data => {
      if(data){
        this.ads.unshift(data);
      }
    });
    adModal.present();
  }

}
