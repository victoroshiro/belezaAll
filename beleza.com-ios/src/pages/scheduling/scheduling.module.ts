import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { SchedulingPage } from './scheduling';

@NgModule({
  declarations: [
    SchedulingPage,
  ],
  imports: [
    IonicPageModule.forChild(SchedulingPage),
  ],
})
export class SchedulingPageModule {}
