import { Component } from '@angular/core';
import { IonicPage, ViewController, AlertController, ActionSheetController, LoadingController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import { Camera, CameraOptions } from '@ionic-native/camera';
import { Crop } from '@ionic-native/crop';

@IonicPage()
@Component({
  selector: 'page-ad-create',
  templateUrl: 'ad-create.html',
})
export class AdCreatePage {

  web: any = WEB;
  photo: any;

  constructor(public viewCtrl: ViewController, public alertCtrl: AlertController, public actionSheetCtrl: ActionSheetController, public loadingCtrl: LoadingController, public http: Http, public camera: Camera, public crop: Crop) {
  }

  create(ad) {
    let loading = this.loadingCtrl.create({
      content: 'Criando...'
    });
    loading.present();

    ad.value.id = JSON.parse(localStorage.getItem('usrblzpvd')).id;
    ad.value.photo = this.photo;

    this.http.post(this.web.api + '/ad/create/', JSON.stringify(ad.value))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      let alert = this.alertCtrl.create({
        title: 'Sucesso',
        message: 'Anúncio criado com sucesso!',
        buttons: [{
          text: 'Ok',
          handler: () => {
            this.dismiss(success);
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

  photoOptions() {
    let actionSheet = this.actionSheetCtrl.create({
      title: 'Foto do anúncio',
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
      targetWidth: 800,
      targetHeight: 300,
      destinationType: this.camera.DestinationType.DATA_URL,
      encodingType: this.camera.EncodingType.PNG,
      saveToPhotoAlbum: false
    }

    this.camera.getPicture(options).then((imageData) => {
      let base64Image = 'data:image/png;base64,' + imageData;

      this.photo = base64Image;
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
      targetWidth: 800,
      targetHeight: 300,
      destinationType: this.camera.DestinationType.FILE_URI,
      encodingType: this.camera.EncodingType.PNG,
      mediaType: this.camera.MediaType.PICTURE,
      sourceType: this.camera.PictureSourceType.PHOTOLIBRARY
    }

    this.camera.getPicture(options).then((imageData) => {
      this.cropImage(imageData);
    }, (err) => {
      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: 'Ocorreu um erro na escolha da foto',
        buttons: ['OK']
      });
      alert.present();
    });
  }

  cropImage(image) {
    this.crop.crop(image, {targetWidth: 800, targetHeight: 300})
    .then(
      newImage => {
        var img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = () => {
          var canvas = document.createElement('canvas');
          canvas.width = 800;
          canvas.height = 300;
          var ctx = canvas.getContext('2d');
          ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
          var dataURL = canvas.toDataURL();

          this.photo = dataURL;
        }
        img.src = newImage;
      },
      error => {
        let alert = this.alertCtrl.create({
          title: 'Erro',
          message: 'Ocorreu um erro na escolha da foto',
          buttons: ['OK']
        });
        alert.present();
      }
    );
  }

  dismiss(data = null) {
    this.viewCtrl.dismiss(data);
  }

}
