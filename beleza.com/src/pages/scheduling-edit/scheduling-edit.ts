import { Component } from '@angular/core';
import { IonicPage, NavParams, ModalController, AlertController, LoadingController } from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import io from 'socket.io-client';

/**
 * Generated class for the SchedulingEditPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-scheduling-edit',
  templateUrl: 'scheduling-edit.html',
})
export class SchedulingEditPage {

  web: any = WEB;
  date: any;
  time: any;
  schedulingDetails: any = {};
  schedulingOld: any = {};
  home_care: any = 0;
  addresses: any = [];
  categories: any = [];
  specialties: any = [];
  services: any = [];
  chosenServices = [];
  servicesDetails: any = [];
  provider: any = {};
  times: any = [];
  paymentPhoto: any;
  paymentName: any;
  socket: any;

  constructor(public navParams: NavParams, public app: UtilProvider, public modalCtrl: ModalController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http) {
    this.getEditSchedulingContent();

    this.socket = io(this.web.socket, {
      transports: ['websocket']
    });
  }

  getEditSchedulingContent(refresher = null) {
    let loading;

    if(!refresher){
      loading = this.loadingCtrl.create({
        content: 'Carregando...'
      });
      loading.present();
    }

    this.date = this.time = null;

    this.getScheduling(this.navParams.get("scheduling"));
    this.getCategorySpecialty();
    this.getAddresses();

    if(refresher){
      refresher.complete();
    }
    else{
      loading.dismiss();
    }
  }

  getScheduling(id) {
    this.http.post(this.web.api + "/scheduling/details/", JSON.stringify({id: id}))
    .map(res => res.json())
    .subscribe(success => {
      this.schedulingDetails = success;

      let services = [];
      for(let i = 0; i < success.services.length; i = i + 1){
        services.push(success.services[i].id);
      }

      this.home_care = success.home_care;
      this.schedulingOld.address = success.address_id;
      this.schedulingOld.payment = success.payment_method_id;
      this.chosenServices = services;

      this.getProvider(this.navParams.get("id"));
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

  getProvider(id) {
    this.http.post(this.web.api + "/provider/", JSON.stringify({id: id}))
    .map(res => res.json())
    .subscribe(success => {
      this.provider = success;

      if(this.provider.dates){
        for(let i = 0; i < this.provider.dates.length; i = i + 1){
          if(this.provider.dates[i].date == this.schedulingDetails.date){
            this.date = this.provider.dates[i].id;

            break;
          }
        }
      }

      this.getAvailableTime(this.date);
      this.changePayment(this.schedulingOld.payment);
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

  getAvailableTime(date) {
    let loading = this.loadingCtrl.create({
      content: 'Carregando...'
    });
    loading.present();

    this.http.post(this.web.api + "/provider/schedule/date/", JSON.stringify({id: date}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.times = success;
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

  changePayment(payment) {
    this.servicesDetails = [];

    for(let i = 0; i < this.chosenServices.length; i = i + 1){
      for(let j = 0; j < this.provider.services.length; j = j + 1){
        if(this.chosenServices[i] == this.provider.services[j].id){
          for(let k = 0; k < this.provider.services[j].prices.length; k = k + 1){
            if(this.provider.services[j].prices[k].payment_method == payment){
              this.servicesDetails.push({
                name: this.provider.services[j].name,
                description: this.provider.services[j].description,
                price: this.provider.services[j].prices[k].price
              });

              break;
            }
          }

          break;
        }
      }
    }

    if(this.provider && this.provider.payments){
      for(let i = 0; i < this.provider.payments.length; i = i + 1){
        if(this.provider.payments[i].id == payment){
          this.paymentPhoto = this.provider.payments[i].photo;
          this.paymentName = this.provider.payments[i].name;

          break;
        }
      }
    }
  }

  chooseService(event, service, payment) {
    let checkService = this.chosenServices.indexOf(service);

    if(event.checked){
      if(checkService == -1){
        this.chosenServices.push(service);
      }
    }
    else{
      if(checkService != -1){
        this.chosenServices.splice(checkService, 1);
      }
    }

    if(payment){
      this.changePayment(payment);
    }
  }

  getServices(specialty) {
    let services = [];

    if(this.provider && this.provider.services){
      for(let i = 0; i < this.provider.services.length; i = i + 1){
        if(this.provider.services[i].specialty == specialty){
          services.push(this.provider.services[i]);
        }
      }
    }

    this.services = services;
  }

  getSpecialties(category) {
    for(let i = 0; i < this.categories.length; i = i + 1){
      if(this.categories[i].id == category){
        this.specialties = this.categories[i].specialty;

        break;
      }
    }
  }

  getCategorySpecialty() {
    this.http.post(this.web.api + "/category-specialty-provider/", JSON.stringify({id: this.navParams.get("id")}))
    .map(res => res.json())
    .subscribe(success => {
      this.categories = success;
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

  getServicePrice(service){
    let total = 0;

    for(let i = 0; i < this.chosenServices.length; i = i + 1){
      for(let j = 0; j < this.provider.services.length; j = j + 1){
        if(this.chosenServices[i] == this.provider.services[j].id){
          for(let k = 0; k < this.provider.services[j].prices.length; k = k + 1){
            if(this.provider.services[j].prices[k].payment_method == service.payment){
              total = total + parseFloat(this.provider.services[j].prices[k].price.replace(",", "."));

              break;
            }
          }

          break;
        }
      }
    }

    return (String(total.toFixed(2))).replace(".", ",");
  }
  
  editScheduling(service) {
    let alert = this.alertCtrl.create({
      title: 'Aviso',
      message: 'Tem certeza que deseja editar este agendamento (total de R$ ' + this.getServicePrice(service) + ')?',
      buttons: [{
        text: 'Cancelar'
      },{
        text: 'Ok',
        handler: () => {
          let loading = this.loadingCtrl.create({
            content: 'Editando...'
          });
          loading.present();

          service.value.id = this.navParams.get("scheduling");
          service.value.services = this.chosenServices;
          service.value.user = JSON.parse(localStorage.getItem("usrblz")).id;
          service.value.provider = this.provider.id;

          this.http.post(this.web.api + "/scheduling/edit/", JSON.stringify(service.value))
          .map(res => res.json())
          .subscribe(success => {
            loading.dismiss();

            this.socket.emit('scheduling:edited', success);

            let alert = this.alertCtrl.create({
              title: 'Sucesso',
              message: 'Agendamento editado com sucesso!',
              buttons: [{
                text: 'Ok',
                handler: () => {
                  this.app.rootPage("SchedulingPage", {id: this.navParams.get("scheduling")});
                }
              }]
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

}
