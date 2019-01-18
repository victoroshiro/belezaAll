import { Component } from '@angular/core';
import { IonicPage } from 'ionic-angular';

/**
 * Generated class for the AwardsPage tabs.
 *
 * See https://ionicframework.com/docs/components/#navigation for more info on
 * Ionic pages and navigation.
 */

@IonicPage()
@Component({
  selector: 'page-awards',
  templateUrl: 'awards.html'
})
export class AwardsPage {

  pointsListRoot = 'PointsListPage'
  awardRequestRoot = 'AwardRequestPage'


  constructor() {}

}
