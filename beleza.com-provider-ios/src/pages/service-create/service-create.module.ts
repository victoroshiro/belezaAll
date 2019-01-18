import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { ServiceCreatePage } from './service-create';

import { BrMaskerModule } from 'brmasker-ionic-3';

@NgModule({
  declarations: [
    ServiceCreatePage,
  ],
  imports: [
    IonicPageModule.forChild(ServiceCreatePage),
    BrMaskerModule
  ],
})
export class ServiceCreatePageModule {}
