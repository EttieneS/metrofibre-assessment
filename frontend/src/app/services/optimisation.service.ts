import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { Inventory, OptimisationResponse, Recipe } from '../models/optimisation.models';

@Injectable({ providedIn: 'root' })
export class OptimisationService {
  private readonly apiUrl = '/api/optimise';
  readonly ingredients = ['cucumber', 'olives', 'lettuce', 'meat', 'tomato', 'cheese', 'dough'];
  readonly recipes: Recipe[] = [
    { name: 'burger', feeds: 1, ingredients: { lettuce: 1, meat: 1, tomato: 1, cheese: 1, dough: 1 } },
    { name: 'pie', feeds: 1, ingredients: { meat: 2, dough: 2 } },
    { name: 'sandwich', feeds: 1, ingredients: { cucumber: 1, dough: 1 } },
    { name: 'pasta', feeds: 2, ingredients: { dough: 2, tomato: 1, cheese: 2, meat: 1 } },
    { name: 'salad', feeds: 3, ingredients: { lettuce: 2, tomato: 2, cucumber: 1, cheese: 2, olives: 1 } },
    { name: 'pizza', feeds: 4, ingredients: { dough: 3, tomato: 2, cheese: 3, olives: 1 } }
  ];
  readonly defaultInventory: Inventory = { cucumber: 2, olives: 2, lettuce: 3, meat: 6, tomato: 6, cheese: 8, dough: 10 };
  constructor(private readonly http: HttpClient) {}
  optimise(inventory: Inventory): Observable<OptimisationResponse> { return this.http.post<OptimisationResponse>(this.apiUrl, { ingredients: inventory }); }
}
