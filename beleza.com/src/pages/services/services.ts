import { Component } from '@angular/core';
import { IonicPage, NavParams } from 'ionic-angular';

/**
 * Generated class for the ServicesPage tabs.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-services',
  templateUrl: 'services.html'
})
export class ServicesPage {
  waitingRoot = 'WaitingPage'
  canceledRoot = 'CanceledPage'
  finalizedRoot = 'FinalizedPage'

  selectedIndex: any;

  constructor(public navParams: NavParams) {
    this.selectedIndex = this.navParams.get("tab") || 0;
  }

}
