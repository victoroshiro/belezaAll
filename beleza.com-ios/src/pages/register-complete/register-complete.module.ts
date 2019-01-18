import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { RegisterCompletePage } from './register-complete';

import { BrMaskerModule } from 'brmasker-ionic-3';

@NgModule({
  declarations: [
    RegisterCompletePage,
  ],
  imports: [
    IonicPageModule.forChild(RegisterCompletePage),
    BrMaskerModule
  ],
})
export class RegisterCompletePageModule {}
