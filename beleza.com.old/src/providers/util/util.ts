import { Injectable } from '@angular/core';
import { NavController, App } from 'ionic-angular';

/*
  Generated class for the UtilProvider provider.

  See https://angular.io/guide/dependency-injection for more info on providers
  and Angular DI.
*/
@Injectable()
export class UtilProvider {

  private nav: Array<NavController>;

  constructor(public app: App) {
    this.nav = app.getActiveNavs();
  }

  pushPage(page, params = null, opts = null, done = null){
    if(this.nav.length){
      this.nav[0].push(page, params, opts, done);
    }
  }

  rootPage(page, params = null, opts = null, done = null) {
    if(this.nav.length){
      this.nav[0].setRoot(page, params, opts, done);
    }
  }

}
