import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController, ModalController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the AwardRequestPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-award-request',
  templateUrl: 'award-request.html',
})
export class AwardRequestPage {

  web: any = WEB;
  points: number = 0;
  awards: any = [];
  addresses: any = [];

  constructor(public alertCtrl: AlertController, public loadingCtrl: LoadingController, public modalCtrl: ModalController, public http: Http) {
    this.getAwardRequestContent();
  }

  getAwardRequestContent(refresher = null) {
    let loading;

    if(!refresher){
      loading = this.loadingCtrl.create({
        content: 'Carregando...'
      });
      loading.present();
    }

    this.getPoints();
    this.getAwards();
    this.getAddresses();

    if(refresher){
      refresher.complete();
    }
    else{
      loading.dismiss();
    }
  }

  request(awardRequest) {
    let user = JSON.parse(localStorage.getItem("usrblz"));

    let alert = this.alertCtrl.create({
      title: 'Aviso',
      message: 'Tem certeza que deseja solicitar essa premiação?',
      buttons: [{
        text: 'Cancelar'
      },{
        text: 'Ok',
        handler: () => {
          let loading = this.loadingCtrl.create({
            content: 'Solicitando...'
          });
          loading.present();

          awardRequest.value.user = user.id;

          this.http.post(this.web.api + "/award-request/", JSON.stringify(awardRequest.value))
          .map(res => res.json())
          .subscribe(success => {
            loading.dismiss();

            let alert = this.alertCtrl.create({
              title: 'Sucesso',
              message: 'Solicitação de premiação realizada com sucesso! Aguarde a entrega do seu prêmio no endereço escolhido.',
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
      }]
    });
    alert.present();
  }

  getPoints() {
    let user = JSON.parse(localStorage.getItem("usrblz"));

    this.http.post(this.web.api + "/user-points/", JSON.stringify({user: user.id}))
    .map(res => res.json())
    .subscribe(success => {
      this.points = success;
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

  getAwards() {
    this.http.post(this.web.api + "/awards/", null)
    .map(res => res.json())
    .subscribe(success => {
      this.awards = success;
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

  getAddresses() {
    let user = JSON.parse(localStorage.getItem("usrblz"));

    this.http.post(this.web.api + "/addresses/", JSON.stringify({id: user.id}))
    .map(res => res.json())
    .subscribe(success => {
      this.addresses = success;
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

  addAddress() {
    let addressModal = this.modalCtrl.create("AddressModalPage");
    addressModal.onDidDismiss(data => {
      if(data){
        this.addresses.unshift(data);
      }
    });
    addressModal.present();
  }
}
