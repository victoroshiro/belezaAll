import { Component } from '@angular/core';
import { IonicPage, NavController, AlertController, LoadingController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import io from 'socket.io-client';

@IonicPage()
@Component({
  selector: 'page-scheduling-register',
  templateUrl: 'scheduling-register.html',
})
export class SchedulingRegisterPage {

  web: any = WEB;
  date: any;
  time: any;
  home_care: any = 0;
  addresses: any = [];
  categories: any = [];
  specialties: any = [];
  services: any = [];
  chosenServices = [];
  servicesDetails: any = [];
  provider: any = {};
  specialty: any;
  times: any = [];
  paymentPhoto: any;
  paymentName: any;
  socket: any;

  constructor(public navCtrl: NavController, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http) {
    this.getDetailsContent();

    this.socket = io(this.web.socket, {
      transports: ['websocket']
    });
  }

  getDetailsContent(refresher = null) {
    let loading;

    if(!refresher){
      loading = this.loadingCtrl.create({
        content: 'Carregando...'
      });
      loading.present();
    }

    this.date = this.time = null;

    this.getProvider();
    this.getCategorySpecialty();

    if(refresher){
      refresher.complete();
    }
    else{
      loading.dismiss();
    }
  }

  getProvider() {
    let id = JSON.parse(localStorage.getItem('usrblzpvd')).id;

    this.http.post(this.web.api + '/provider/', JSON.stringify({id: id}))
    .map(res => res.json())
    .subscribe(success => {
      this.provider = success;

      if(this.specialty){
        this.getServices(this.specialty);
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

  getAvailableTime(date) {
    let loading = this.loadingCtrl.create({
      content: 'Carregando...'
    });
    loading.present();

    this.http.post(this.web.api + '/provider/schedule/date/', JSON.stringify({id: date}))
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

  chooseService(event, service) {
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
    let id = JSON.parse(localStorage.getItem('usrblzpvd')).id;

    this.http.post(this.web.api + '/category-specialty-provider/', JSON.stringify({id: id}))
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

  addService(service) {
    let alert = this.alertCtrl.create({
      title: 'Aviso',
      message: 'Tem certeza que deseja realizar este agendamento (total de R$ ' + this.getServicePrice(service) + ')?',
      buttons: [{
        text: 'Cancelar'
      },{
        text: 'Ok',
        handler: () => {
          let loading = this.loadingCtrl.create({
            content: 'Solicitando...'
          });
          loading.present();

          service.value.services = this.chosenServices;
          service.value.provider = this.provider.id;

          this.http.post(this.web.api + "/scheduling-manual/", JSON.stringify(service.value))
          .map(res => res.json())
          .subscribe(success => {
            loading.dismiss();

            let alert = this.alertCtrl.create({
              title: 'Sucesso',
              message: 'Agendamento realizado com sucesso!',
              buttons: [{
                text: 'Ok',
                handler: () => {
                  this.navCtrl.setRoot('SchedulingDetailsPage', {id: success.id});
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
