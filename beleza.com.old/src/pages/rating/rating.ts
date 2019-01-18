import { Component } from '@angular/core';
import { IonicPage, NavParams, AlertController } from 'ionic-angular';
import { UtilProvider } from '../../providers/util/util';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

/**
 * Generated class for the RatingPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-rating',
  templateUrl: 'rating.html',
})
export class RatingPage {

  web: any = WEB;
  rating: number = 0;

  constructor(public navParams: NavParams, public app: UtilProvider, public alertCtrl: AlertController, public http: Http) {
  }

  submitRating() {
    this.http.post(this.web.api + "/rate/", JSON.stringify({rating: this.rating, scheduling: this.navParams.get("id")}))
    .map(res => res.json())
    .subscribe(success => {
      let alert = this.alertCtrl.create({
        title: 'Sucesso',
        message: 'Serviço avaliado com sucesso!',
        buttons: [{
          text: 'Ok',
          handler: () => {
            this.app.rootPage("SchedulingPage", {id: this.navParams.get("id")});
          }
        }]
      });
      alert.present();
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

  setRating(number) {
    this.rating = number;
  }

}
