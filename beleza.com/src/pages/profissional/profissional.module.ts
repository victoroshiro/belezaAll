import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { ProfissionalPage } from './profissional';

import { PipesModule } from '../../pipes/pipes.module';

@NgModule({
  declarations: [
    ProfissionalPage,
  ],
  imports: [
    IonicPageModule.forChild(ProfissionalPage),
    PipesModule
  ],
})
export class ProfissionalPageModule {}
