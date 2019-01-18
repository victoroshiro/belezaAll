import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController, ActionSheetController, Events } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import { Camera, CameraOptions } from '@ionic-native/camera';

/**
 * Generated class for the ProfilePage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-profile',
  templateUrl: 'profile.html',
})
export class ProfilePage {

  web: any = WEB;
  user: any = JSON.parse(localStorage.getItem("usrblz"));

  constructor(public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http, public actionSheetCtrl: ActionSheetController, public events: Events, public camera: Camera) {
  }

  edit(user) {
    let loading = this.loadingCtrl.create({
      content: 'Salvando...'
    });
    loading.present();

    let body = JSON.stringify(user.value);

    this.http.post(this.web.api + "/edit/", body)
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      let alert = this.alertCtrl.create({
        title: 'Sucesso',
        message: 'Perfil salvo com sucesso!',
        buttons: ['OK']
      });
      alert.present();

      localStorage.setItem("usrblz", JSON.stringify(success));
      this.events.publish("user:modified", success);
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
      encodingType: this.camera.EncodingType.PNG
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
}
