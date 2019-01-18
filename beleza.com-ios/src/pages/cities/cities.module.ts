import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { CitiesPage } from './cities';

import { PipesModule } from '../../pipes/pipes.module';

@NgModule({
  declarations: [
    CitiesPage,
  ],
  imports: [
    IonicPageModule.forChild(CitiesPage),
    PipesModule
  ],
})
export class CitiesPageModule {}
