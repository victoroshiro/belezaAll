import { Component } from '@angular/core';
import { IonicPage, AlertController, LoadingController } from 'ionic-angular';
import { WEB } from '../../app/config';
import { Http } from '@angular/http';
import 'rxjs/add/operator/map';

import { IMyDpOptions, IMyDateModel } from 'mydatepicker';

@IonicPage()
@Component({
  selector: 'page-schedule-manual',
  templateUrl: 'schedule-manual.html',
})
export class ScheduleManualPage {

  web: any = WEB;
  user: any = JSON.parse(localStorage.getItem('usrblzpvd')).id;
  curdate: any;
  dateFormated: any;
  times: any = [];
  morningTimes: any = ['00:00', '00:30', '01:00', '01:30', '02:00', '02:30', '03:00', '03:30', '04:00', '04:30', '05:00', '05:30', '06:00', '06:30', '07:00', '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30'];
  afternoonTimes: any = ['12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30', '22:00', '22:30', '23:00', '23:30'];

  myDatePickerOptions: IMyDpOptions = {
    dayLabels: {su: 'Dom', mo: 'Seg', tu: 'Ter', we: 'Qua', th: 'Qui', fr: 'Sex', sa: 'Sab'},
    monthLabels: { 1: 'Janeiro', 2: 'Fevereiro', 3: 'Março', 4: 'Abril', 5: 'Maio', 6: 'Junho', 7: 'Julho', 8: 'Agosto', 9: 'Setembro', 10: 'Outubro', 11: 'Novembro', 12: 'Dezembro' },
    firstDayOfWeek: 'su',
    sunHighlight: false,
    highlightDates: [],
    selectorWidth: '100%',
    selectorHeight: 'auto',
    showTodayBtn: false,
    inline: true
  };

  constructor(public alertCtrl: AlertController, public loadingCtrl: LoadingController, public http: Http) {
    this.getTimes(new Date());
  }

  organizeSchedule() {
    let loading = this.loadingCtrl.create({
      content: 'Salvando...'
    });
    loading.present();

    this.http.post(this.web.api + '/schedule/organize/', JSON.stringify({id: this.user, date: this.curdate, time: this.times}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      let date = this.curdate.split('-');

      date[0] = Number(date[0]);
      date[1] = Number(date[1]);
      date[2] = Number(date[2]);

      let dateFound = false;
      let dateIndex = 0;
      let copy = this.getCopyOfOptions();

      for(let i = 0; i < copy.highlightDates.length; i = i + 1){
        if(copy.highlightDates[i].year == date[0] && copy.highlightDates[i].month == date[1] && copy.highlightDates[i].day == date[2]){
          dateFound = true;
          dateIndex = i;

          break;
        }
      }

      if(this.times.length){
        if(!dateFound){
          copy.highlightDates.push({
            year: date[0],
            month: date[1],
            day: date[2]
          });

          this.myDatePickerOptions = copy;
        }
      }
      else{
        if(dateFound){
          copy.highlightDates.splice(dateIndex, 1);

          this.myDatePickerOptions = copy;
        }
      }

      let alert = this.alertCtrl.create({
        title: 'Sucesso',
        message: 'Agenda atualizada com sucesso!',
        buttons: ['OK']
      });
      alert.present();
    },
    error => {
      loading.dismiss();
      console.log(error);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: error._body,
        buttons: ['OK']
      });
      alert.present();
    });
  }

  getTimes(date) {
    let loading = this.loadingCtrl.create({
      content: 'Carregando...'
    });
    loading.present();

    this.dateFormated = ('0' + date.getDate()).slice(-2) + '/' + ('0' + (date.getMonth() + 1)).slice(-2) + '/' + date.getFullYear();

    date = date.getFullYear() + '-' + ('0' + (date.getMonth() + 1)).slice(-2) + '-' + ('0' + date.getDate()).slice(-2);

    this.curdate = date;

    this.http.post(this.web.api + '/schedule/date/', JSON.stringify({id: this.user, date: date}))
    .map(res => res.json())
    .subscribe(success => {
      loading.dismiss();

      this.times = success;
    },
    error => {
      loading.dismiss();
      console.log(error);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: error._body,
        buttons: ['OK']
      });
      alert.present();
    });
  }

  getDates() {
    this.http.post(this.web.api + "/dates/", JSON.stringify({id: this.user}))
    .map(res => res.json())
    .subscribe(success => {
      for(let i = 0; i < success.length; i = i + 1){
        success[i].year = Number(success[i].year);
        success[i].month = Number(success[i].month);
        success[i].day = Number(success[i].day);
      }

      let copy = this.getCopyOfOptions();
      copy.highlightDates = success;
      this.myDatePickerOptions = copy;
    },
    error => {
      console.log(error);

      let alert = this.alertCtrl.create({
        title: 'Erro',
        message: error._body,
        buttons: ['OK']
      });
      alert.present();
    });
  }

  chooseTime(event, time) {
    let checkTime = this.times.indexOf(time);

    if(event.checked){
      if(checkTime == -1){
        this.times.push(time);
      }
    }
    else{
      if(checkTime != -1){
        this.times.splice(checkTime, 1);
      }
    }
  }

  onDateChanged(event: IMyDateModel) {
    console.log(event);
    this.getTimes(event.jsdate);
  }

  getCopyOfOptions(): IMyDpOptions {
    return JSON.parse(JSON.stringify(this.myDatePickerOptions));
  }

  ionViewWillEnter() {
    this.getDates();
  }

}
