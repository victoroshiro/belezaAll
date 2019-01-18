import { Component } from '@angular/core';
import { IonicPage, ViewController, AlertController, LoadingController, ModalController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the AddressModalPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-address-modal',
  templateUrl: 'address-modal.html',
})
export class AddressModalPage {

  web: any = WEB;
  address: any = {};
  states: any = [];
  cities: any = [];

  constructor(public viewCtrl: ViewController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public modalCtrl: ModalController, public http: Http) {
    this.getStates();
  }

  setAddress(address) {
    let loading = this.loadingCtrl.create({
      content: 'Salvando...'
    });
    loading.present();

    let user = JSON.parse(localStorage.getItem("usrblz"));

    address.value.id = user.id;

    this.http.post(this.web.api + "/address/create/", JSON.stringify(address.value))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.dismissModal(success);
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

  getStates() {
    this.http.post(this.web.api + "/states/", null)
    .map(res => res.json())
    .subscribe(success => {
      this.states = success;
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

  getCities(uf, callback = null) {
    let loading = this.loadingCtrl.create({
      content: 'Carregando cidades...'
    });
    loading.present();

    this.http.post(this.web.api + "/state/" + uf + "/cities/", null)
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.cities = success;

      if(typeof callback === "function"){
        callback(success);
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

  getAddressByCep(cep) {
    if(cep){
      let loading = this.loadingCtrl.create({
        content: 'Carregando endereço...'
      });
      loading.present();
  
      this.http.get("https://api.postmon.com.br/v1/cep/" + cep)
      .map(res => res.json())
      .subscribe(success => {
        this.address.street = success.logradouro;
        this.address.neighborhood = success.bairro;
        this.address.state = success.estado;
  
        this.getCities(success.estado, (data) => {
          for(let i = 0; i < data.length; i = i + 1){
            if(data[i].name == success.cidade){
              this.address.city = data[i].id;
              break;
            }
          }
        });
  
        loading.dismiss();
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

  showCitiesModal() {
    if(this.address.state){
      let citiesModal = this.modalCtrl.create('CitiesPage', {cities: this.cities});

      citiesModal.onDidDismiss(data => {
        if(typeof data != "undefined"){
          this.address.city = data;
        }
      });

      citiesModal.present();
    }
  }

  dismissModal(data = null) {
    this.viewCtrl.dismiss(data);
  }

}
