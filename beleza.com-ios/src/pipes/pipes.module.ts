import { NgModule } from '@angular/core';
import { FilterPipe } from './filter/filter';
import { FilterCitiesPipe } from './filter-cities/filter-cities';
@NgModule({
	declarations: [FilterPipe,
    FilterCitiesPipe],
	imports: [],
	exports: [FilterPipe,
    FilterCitiesPipe]
})
export class PipesModule {}
