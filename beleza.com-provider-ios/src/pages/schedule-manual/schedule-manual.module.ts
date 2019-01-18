import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { ScheduleManualPage } from './schedule-manual';

import { MyDatePickerModule } from 'mydatepicker';

@NgModule({
  declarations: [
    ScheduleManualPage,
  ],
  imports: [
    IonicPageModule.forChild(ScheduleManualPage),
    MyDatePickerModule
  ],
})
export class ScheduleManualPageModule {}
