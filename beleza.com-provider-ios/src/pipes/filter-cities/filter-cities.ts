import { Pipe, PipeTransform } from '@angular/core';

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
