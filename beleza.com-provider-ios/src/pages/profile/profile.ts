import { Component } from '@angular/core';
import { IonicPage, NavController, NavParams, AlertController, LoadingController, MenuController, ActionSheetController, ModalController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import { Camera, CameraOptions } from '@ionic-native/camera';

@IonicPage()
@Component({
  selector: 'page-profile',
  templateUrl: 'profile.html',
})
export class ProfilePage {

  web: any = WEB;

  user: any = {};
  states: any = [];
  cities: any = [];
  completeRegister: boolean = false;

  constructor(public navCtrl: NavController, public navParams: NavParams, public alertCtrl: AlertController, public loadingCtrl: LoadingController, public menu: MenuController, public actionSheetCtrl: ActionSheetController, public modalCtrl: ModalController, public http: Http, public camera: Camera) {
    this.completeRegister = this.navParams.get('completeRegister');

    this.getProvider();
    this.getStates();
  }

  edit(user) {
    let loading = this.loadingCtrl.create({
      content: 'Salvando...'
    });
    loading.present();

    this.http.get('https://maps.google.com/maps/api/geocode/json?key=AIzaSyA31qhfrQoLVgQe7DVC3lVrofvQIB8x554&address=' + this.user.street + ', ' + this.user.number + ' - ' + this.user.neighborhood + ' - ' + this.user.cep)
    .map(res => res.json())
    .subscribe(success => {
      if(success.status == "OK" && success.results[0] && success.results[0].geometry.location){
        user.value.coord_x = success.results[0].geometry.location.lat;
        user.value.coord_y = success.results[0].geometry.location.lng;

        this.http.post(this.web.api + '/edit-provider/', JSON.stringify(user.value))
        .map(res => res.json())
        .subscribe(success => {
          loading.dismiss();

          if(this.completeRegister){
            this.navCtrl.setRoot('SchedulingPage');
          }
          else{
            let alert = this.alertCtrl.create({
              title: 'Sucesso',
              message: 'Perfil salvo com sucesso!',
              buttons: ['OK']
            });
            alert.present();
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
      else{
        loading.dismiss();
        
        let alert = this.alertCtrl.create({
          title: 'Erro',
          message: 'Não foi possível localizar o endereço',
          buttons: ['OK']
        });
        alert.present();
      }
    },
    error => {
      console.log(error);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: 'Não foi possível localizar o endereço',
        buttons: ['OK']
      });
      alert.present();
    });
  }

  getProvider() {
    let user = JSON.parse(localStorage.getItem('usrblzpvd')).id;

    this.http.post(this.web.api + '/get-provider/', JSON.stringify({id: user}))
    .map(res => res.json())
    .subscribe(success => {
      this.user = success;

      if(!this.user.privacy_accepted){
        this.navCtrl.setRoot('PrivacyPage', {readOnly: false});
      }
      else{
        this.getCities(this.user.state);
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

  getStates() {
    this.http.post(this.web.api + '/states/', null)
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
      content: 'Carregando...'
    });
    loading.present();

    this.http.post(this.web.api + '/state/' + uf + '/cities/', null)
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.cities = success;

      if(typeof callback === 'function'){
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
        content: 'Carregando...'
      });
      loading.present();
  
      this.http.get('https://api.postmon.com.br/v1/cep/' + cep)
      .map(res => res.json())
      .subscribe(success => {
        this.user.street = success.logradouro;
        this.user.neighborhood = success.bairro;
        this.user.state = success.estado;
  
        this.getCities(success.estado, (data) => {
          for(let i = 0; i < data.length; i = i + 1){
            if(data[i].name == success.cidade){
              this.user.city = data[i].id;
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
    if(this.user.state){
      let citiesModal = this.modalCtrl.create('CitiesPage', {cities: this.cities});

      citiesModal.onDidDismiss(data => {
        if(typeof data != 'undefined'){
          this.user.city = data;
        }
      });

      citiesModal.present();
    }
  }

  photoOptions() {
    let actionSheet = this.actionSheetCtrl.create({
      title: 'Foto de perfil',
      buttons: [
        {
          text: 'Tirar uma foto',
          icon: 'camera',
          handler: () => {
            this.getCameraPhoto();
          }
        },{
          text: 'Escolher da galeria',
          icon: 'image',
          handler: () => {
            this.getGalleryPhoto();
          }
        },{
          text: 'Cancelar',
          icon: 'close-circle',
          role: 'cancel'
        }
      ]
    });
    actionSheet.present();
  }

  getCameraPhoto() {
    const options: CameraOptions = {
      allowEdit: true,
      targetWidth: 200,
      targetHeight: 200,
      destinationType: this.camera.DestinationType.DATA_URL,
      encodingType: this.camera.EncodingType.PNG,
      saveToPhotoAlbum: false
    }

    this.camera.getPicture(options).then((imageData) => {
      let base64Image = 'data:image/png;base64,' + imageData;

      this.user.photo = base64Image;
      this.user.newPhoto = base64Image;
    }, (err) => {
      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: 'Ocorreu um erro na captura da foto',
        buttons: ['OK']
      });
      alert.present();
    });
  }

  getGalleryPhoto() {
    const options: CameraOptions = {
      allowEdit: true,
      targetWidth: 200,
      targetHeight: 200,
      destinationType: this.camera.DestinationType.DATA_URL,
      encodingType: this.camera.EncodingType.PNG,
      mediaType: this.camera.MediaType.PICTURE,
      sourceType: this.camera.PictureSourceType.PHOTOLIBRARY
    }

    this.camera.getPicture(options).then((imageData) => {
      let base64Image = 'data:image/png;base64,' + imageData;

      this.user.photo = base64Image;
      this.user.newPhoto = base64Image;
    }, (err) => {
      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: 'Ocorreu um erro na escolha da foto',
        buttons: ['OK']
      });
      alert.present();
    });
  }

  logout() {
    localStorage.removeItem('usrblzpvd');

    this.navCtrl.setRoot('LoginPage');
  }

  ionViewWillEnter() {
    if(this.completeRegister){
      this.menu.enable(false);
    }
  }

  ionViewWillLeave() {
    this.menu.enable(true);
  }

}
