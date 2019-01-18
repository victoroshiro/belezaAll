import { NgModule } from '@angular/core';
import { IonicPageModule } from 'ionic-angular';
import { PointsListPage } from './points-list';

@NgModule({
  declarations: [
    PointsListPage,
  ],
  imports: [
    IonicPageModule.forChild(PointsListPage),
  ],
})
export class PointsListPageModule {}
