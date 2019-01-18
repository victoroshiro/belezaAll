import { Component } from '@angular/core';
import { IonicPage, NavController } from 'ionic-angular';

@IonicPage()
@Component({
  selector: 'page-schedule-redirect',
  templateUrl: 'schedule-redirect.html',
})
export class ScheduleRedirectPage {

  constructor(public navCtrl: NavController) {
    this.navCtrl.setRoot('SchedulePage');
  }

}
