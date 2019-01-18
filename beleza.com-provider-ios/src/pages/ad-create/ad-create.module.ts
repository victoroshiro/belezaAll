import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { AdCreatePage } from './ad-create';

@NgModule({
  declarations: [
    AdCreatePage,
  ],
  imports: [
    IonicPageModule.forChild(AdCreatePage),
  ],
})
export class AdCreatePageModule {}
