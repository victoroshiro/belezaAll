import { Component } from '@angular/core';
import { IonicPage, NavController } from 'ionic-angular';

/**
 * Generated class for the TutorialPage page.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-tutorial',
  templateUrl: 'tutorial.html',
})
export class TutorialPage {

  constructor(public navCtrl: NavController) {
  }

  skip() {
    localStorage.setItem('usrblz_showtutorial', JSON.stringify(true));
    this.navCtrl.setRoot('LoginPage', null, {animate: true});
  }

}
