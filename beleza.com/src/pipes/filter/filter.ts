import { Pipe, PipeTransform } from '@angular/core';

/**
 * Generated class for the FilterPipe pipe.
 *
 * See https://angular.io/api/core/Pipe for more info on Angular Pipes.
 */
@Pipe({
  name: 'filter',
})
export class FilterPipe implements PipeTransform {
  transform(value: any, query: string, field: string): any {
    return query ? value.reduce((prev, next) => {
      if (next[field].toLowerCase().includes(query.toLowerCase())) { prev.push(next); }
      return prev;
    }, []) : value;
  }
}
