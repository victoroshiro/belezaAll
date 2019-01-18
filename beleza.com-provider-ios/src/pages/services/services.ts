import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController, ModalController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-services',
  templateUrl: 'services.html',
})
export class ServicesPage {

  web: any = WEB;
  services: any = [];

  constructor(public alertCtrl: AlertController, public loadingCtrl: LoadingController, public modalCtrl: ModalController, public http: Http) {
    this.getServices();
  }

  refreshServices(refresher) {
    this.getServices(false);

    refresher.complete();
  }

  getServices(showLoading = true) {
    let loading;

    if(showLoading){
      loading = this.loadingCtrl.create({
        content: 'Carregando...'
      });
      loading.present();
    }

    let user = JSON.parse(localStorage.getItem("usrblzpvd")).id;

    this.http.post(this.web.api + "/services/", JSON.stringify({id: user}))
    .map(res => res.json())
    .subscribe(success => {
      if(showLoading){
        loading.dismiss();
      }

      this.services = success;
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

  toggleService(id, index) {
    this.http.post(this.web.api + "/service/toggle/", JSON.stringify({id: id}))
    .map(res => res.json())
    .subscribe(success => {
      if(this.services[index]){
        this.services[index].active = success;
      }
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

  deleteService(id, index) {
    let loading = this.loadingCtrl.create({
      content: 'Deletando...'
    });
    loading.present();

    this.http.post(this.web.api + "/service/delete/", JSON.stringify({id: id}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      if(this.services[index]){
        this.services.splice(index, 1);
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

  editService(id) {
    let servicesModal = this.modalCtrl.create("ServiceEditPage", {id: id});
    servicesModal.onDidDismiss(data => {
      if(data){
        this.getServices();
      }
    });
    servicesModal.present();
  }

  addService() {
    let servicesModal = this.modalCtrl.create("ServiceCreatePage");
    servicesModal.onDidDismiss(data => {
      if(data){
        this.services.unshift(data);
      }
    });
    servicesModal.present();
  }

}
