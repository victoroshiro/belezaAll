import { Component } from '@angular/core';
import { IonicPage, NavParams, ViewController } from 'ionic-angular';

/**
 * Generated class for the CitiesPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-cities',
  templateUrl: 'cities.html',
})
export class CitiesPage {

  cities: any = [];

  constructor(public navParams: NavParams, public viewCtrl: ViewController) {
    this.cities = this.navParams.get('cities');
  }

  dismissModal(data = undefined) {
    this.viewCtrl.dismiss(data);
  }

}
