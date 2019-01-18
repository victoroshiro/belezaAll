import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { AdEditPage } from './ad-edit';

@NgModule({
  declarations: [
    AdEditPage,
  ],
  imports: [
    IonicPageModule.forChild(AdEditPage),
  ],
})
export class AdEditPageModule {}
