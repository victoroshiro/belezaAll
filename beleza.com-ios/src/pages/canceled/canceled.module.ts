import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { CanceledPage } from './canceled';

@NgModule({
  declarations: [
    CanceledPage,
  ],
  imports: [
    IonicPageModule.forChild(CanceledPage),
  ],
})
export class CanceledPageModule {}
