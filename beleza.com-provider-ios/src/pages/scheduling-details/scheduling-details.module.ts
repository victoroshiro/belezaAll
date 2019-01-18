import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { SchedulingDetailsPage } from './scheduling-details';

@NgModule({
  declarations: [
    SchedulingDetailsPage,
  ],
  imports: [
    IonicPageModule.forChild(SchedulingDetailsPage),
  ],
})
export class SchedulingDetailsPageModule {}
