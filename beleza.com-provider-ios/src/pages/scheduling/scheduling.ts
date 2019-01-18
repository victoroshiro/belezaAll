import { Component } from '@angular/core';
import { IonicPage, NavController, AlertController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

@IonicPage()
@Component({
  selector: 'page-scheduling',
  templateUrl: 'scheduling.html'
})
export class SchedulingPage {

  schedulingWaitingRoot = 'SchedulingWaitingPage'
  schedulingCanceledRoot = 'SchedulingCanceledPage'
  schedulingFinalizedRoot = 'SchedulingFinalizedPage'

  web: any = WEB;
  user: any = JSON.parse(localStorage.getItem('usrblzpvd')).id;
  completeRegister: boolean = true;

  constructor(public navCtrl: NavController, public http: Http, public alertCtrl: AlertController) {
    this.checkProvider();
  }

  checkProvider() {
    this.http.post(this.web.api + '/check-provider/', JSON.stringify({id: this.user}))
    .map(res => res.json())
    .subscribe(success => {
      if(!success.success){
        this.navCtrl.setRoot('ProfilePage', {completeRegister: true});
      }
      else{
        this.completeRegister = false;
      }
    },
    error => {
      console.log(error);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: error._body,
        buttons: [
          {
            text: 'Ok'
          },
          {
            text: 'Sair',
            handler: () => {
              localStorage.removeItem('usrblzpvd');

              this.navCtrl.setRoot('LoginPage');
            }
          }
        ]
      });
      alert.present();
    });
  }

}
