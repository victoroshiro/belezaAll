import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { SchedulingRegisterPage } from './scheduling-register';

@NgModule({
  declarations: [
    SchedulingRegisterPage,
  ],
  imports: [
    IonicPageModule.forChild(SchedulingRegisterPage),
  ],
})
export class SchedulingRegisterPageModule {}
