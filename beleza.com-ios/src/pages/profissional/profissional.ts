import { Component } from '@angular/core';
import { IonicPage, NavParams, ModalController, AlertController, LoadingController, ToastController } from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the ProfissionalPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-profissional',
  templateUrl: 'profissional.html',
})
export class ProfissionalPage {

  web: any = WEB;
  specialty: any;
  providers: any = [];

  constructor(public navParams: NavParams, public app: UtilProvider, public modalCtrl: ModalController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public toastCtrl: ToastController) {
    this.specialty = this.navParams.get("specialty");
    this.getProvidersBySavedFilter();
  }

  getProviders(filter) {
    let loading = this.loadingCtrl.create({
      content: 'Carregando profissionais...'
    });
    loading.present();

    filter.specialty = this.specialty;

    if(filter.location == "geo"){
      let geolocation = JSON.parse(localStorage.getItem("usrblz_geolocation"));

      if(!geolocation|| !geolocation.latitude || !geolocation.longitude){
        loading.dismiss();

        const toast = this.toastCtrl.create({
          message: 'Ative a localização para encontrar profissionais',
          duration: 6000,
          position: 'bottom'
        });

        toast.present();
      }
      else{
        filter.coord_x = geolocation.latitude;
        filter.coord_y = geolocation.longitude;
      }
    }

    this.http.post(this.web.api + "/providers/", JSON.stringify(filter))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.providers = success;
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

  getProvidersBySavedFilter(refresher = null) {
    let savedFilter = localStorage.getItem("usrblz_provider_filter"), filter;

    if(savedFilter){
      filter = JSON.parse(savedFilter);
    }
    else{
      filter = {
        location: "geo",
        distance: 20
      };
    }

    this.getProviders(filter);

    if(refresher){
      refresher.complete();
    }
  }

  filterModal() {
    let modal = this.modalCtrl.create("FilterPage");
    modal.onDidDismiss(data => {
      if(data){
        this.getProviders(data);
      }
    });
    modal.present();
  }

}
