import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { AwardRequestPage } from './award-request';

@NgModule({
  declarations: [
    AwardRequestPage,
  ],
  imports: [
    IonicPageModule.forChild(AwardRequestPage),
  ],
})
export class AwardRequestPageModule {}
