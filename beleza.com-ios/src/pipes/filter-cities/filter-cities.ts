import { Pipe, PipeTransform } from '@angular/core';

/**
 * Generated class for the FilterCitiesPipe pipe.
 *
 * See https://angular.io/api/core/Pipe for more info on Angular Pipes.
 */
@Pipe({
  name: 'filterCities',
})
export class FilterCitiesPipe implements PipeTransform {
  transform(value: any, query: string, field: string): any {
    if(query){
      return query.length >= 2 ? value.reduce((prev, next) => {
        if (next[field].toLowerCase().includes(query.toLowerCase())) { prev.push(next); }
        return prev;
      }, []) : [];
    }
    
    return [];
  }
}
