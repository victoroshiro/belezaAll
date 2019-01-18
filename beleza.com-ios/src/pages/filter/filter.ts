import { Component } from '@angular/core';
import { IonicPage, ViewController, AlertController, LoadingController, ModalController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the FilterPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-filter',
  templateUrl: 'filter.html',
})
export class FilterPage {

  web: any = WEB;
  states: any = [];
  cities: any = [];
  filter: any = {
    location: "geo",
    distance: 20
  };

  constructor(public viewCtrl:ViewController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public modalCtrl: ModalController, public http: Http) {
    this.getStates();

    let savedFilter = localStorage.getItem("usrblz_provider_filter");

    if(savedFilter){
      this.filter = JSON.parse(savedFilter);
      this.getFilter();
    }
  }

  filterApply(filter) {
    localStorage.setItem("usrblz_provider_filter", JSON.stringify(filter.value));

    this.dismiss(filter.value);
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

  getCities(uf) {
    let loading = this.loadingCtrl.create({
      content: 'Carregando cidades...'
    });
    loading.present();

    this.http.post(this.web.api + "/state/" + uf + "/cities/", null)
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.cities = success;
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

  getFilter() {
    if(this.filter.location == "address" && this.filter.state){
      this.getCities(this.filter.state);
    }
  }

  showCitiesModal() {
    if(this.filter.state){
      let citiesModal = this.modalCtrl.create('CitiesPage', {cities: this.cities});

      citiesModal.onDidDismiss(data => {
        if(typeof data != "undefined"){
          this.filter.city = data;
        }
      });

      citiesModal.present();
    }
  }

  dismiss(data = null) {
    this.viewCtrl.dismiss(data);
  }

}
