import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { ServiceEditPage } from './service-edit';

import { BrMaskerModule } from 'brmasker-ionic-3';

@NgModule({
  declarations: [
    ServiceEditPage,
  ],
  imports: [
    IonicPageModule.forChild(ServiceEditPage),
    BrMaskerModule
  ],
})
export class ServiceEditPageModule {}
