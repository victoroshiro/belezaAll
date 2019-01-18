import { Component } from '@angular/core';
import { IonicPage, NavParams, ViewController } from 'ionic-angular';

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
