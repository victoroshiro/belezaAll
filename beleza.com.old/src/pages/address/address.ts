import { Component } from '@angular/core';
import { IonicPage, ModalController, AlertController, LoadingController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the AddressPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-address',
  templateUrl: 'address.html',
})
export class AddressPage {

  web: any = WEB;
  address: any;
  addresses: any = [];
  activeAddress: any = JSON.parse(localStorage.getItem("usrblz_address"));

  constructor(public modalCtrl: ModalController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http) {
    this.getAddresses();
  }

  refreshAddresses(refresher) {
    this.getAddresses();

    refresher.complete();
  }

  getAddresses() {
    let loading = this.loadingCtrl.create({
      content: 'Carregando endereços...'
    });
    loading.present();

    let user = JSON.parse(localStorage.getItem("usrblz"));

    this.http.post(this.web.api + "/addresses/", JSON.stringify({id: user.id}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.addresses = success;

      if(this.addresses.length){
        this.address = this.activeAddress || this.addresses[0].id;
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

  deleteAddress(id, index) {
    let loading = this.loadingCtrl.create({
      content: 'Deletando...'
    });
    loading.present();

    this.http.post(this.web.api + "/address/delete/", JSON.stringify({id: id}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      if(this.addresses[index]){
        this.addresses.splice(index, 1);
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
