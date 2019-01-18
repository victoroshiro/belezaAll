import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { ScheduleRedirectPage } from './schedule-redirect';

@NgModule({
  declarations: [
    ScheduleRedirectPage,
  ],
  imports: [
    IonicPageModule.forChild(ScheduleRedirectPage),
  ],
})
export class ScheduleRedirectPageModule {}
