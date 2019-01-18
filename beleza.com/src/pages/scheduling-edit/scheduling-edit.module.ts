import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { SchedulingEditPage } from './scheduling-edit';

@NgModule({
  declarations: [
    SchedulingEditPage,
  ],
  imports: [
    IonicPageModule.forChild(SchedulingEditPage),
  ],
})
export class SchedulingEditPageModule {}
