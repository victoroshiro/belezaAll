import { Component } from '@angular/core';
import { IonicPage } from 'ionic-angular';

@IonicPage()
@Component({
  selector: 'page-schedule',
  templateUrl: 'schedule.html'
})
export class SchedulePage {
  scheduleManualRoot = 'ScheduleManualPage'
  scheduleAutomaticRoot = 'ScheduleAutomaticPage'

  constructor() {}

}
