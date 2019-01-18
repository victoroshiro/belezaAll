import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { SchedulingWaitingPage } from './scheduling-waiting';

@NgModule({
  declarations: [
    SchedulingWaitingPage,
  ],
  imports: [
    IonicPageModule.forChild(SchedulingWaitingPage),
  ],
})
export class SchedulingWaitingPageModule {}
