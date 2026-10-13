import { Component } from '@angular/core';
import { NgForm } from '@angular/forms';
import { MatSnackBar } from '@angular/material/snack-bar';
import { OptimisationCombination, OptimisationResult, Inventory } from './models/optimisation.models';
import { OptimisationService } from './services/optimisation.service';

export type PeopleFedState = 'idle' | 'loading' | 'success' | 'error';

@Component({ selector: 'app-root', templateUrl: './app.component.html', styleUrls: ['./app.component.scss'] })
export class AppComponent {
  inventory: Inventory;
  result: OptimisationResult | null = null;
  peopleFedState: PeopleFedState = 'idle';
  error = '';
  selectedCombination = 0;
  private requestVersion = 0;

  constructor(public readonly optimisation: OptimisationService, private readonly snackBar: MatSnackBar) {
    this.inventory = { ...optimisation.defaultInventory };
  }

  get loading(): boolean { return this.peopleFedState === 'loading'; }
  get selected(): OptimisationCombination | null { return this.result ? this.result.combinations[this.selectedCombination] || null : null; }
  get peopleFedDisplay(): string {
    switch (this.peopleFedState) {
      case 'loading': return '…';
      case 'success': return this.result && Number.isFinite(this.result.people) ? String(this.result.people) : '—';
      case 'idle': return 'Run optimisation to see results';
      default: return '—';
    }
  }
  get peopleFedLabel(): string {
    switch (this.peopleFedState) {
      case 'loading': return 'Calculating best combinations';
      case 'success': return 'maximum people fed';
      case 'error': return 'Unable to load results';
      default: return 'No results yet';
    }
  }
  get hasResults(): boolean { return this.peopleFedState === 'success' && this.result !== null; }

  submit(form: NgForm): void {
    this.requestVersion += 1;
    const requestVersion = this.requestVersion;
    this.error = '';
    this.result = null;
    this.selectedCombination = 0;
    if (form.invalid || !this.hasValidInventory()) {
      this.setError('Enter a whole number of zero or more for every ingredient.');
      return;
    }
    this.peopleFedState = 'loading';
    this.optimisation.optimise(this.inventory).subscribe({
      next: response => {
        if (requestVersion !== this.requestVersion) return;
        if (!response.success || !response.data || !Number.isFinite(response.data.people)) {
          this.setError(response.error?.message || 'The optimisation could not be completed.');
          return;
        }
        this.result = response.data;
        this.peopleFedState = 'success';
      },
      error: () => {
        if (requestVersion !== this.requestVersion) return;
        this.setError('The optimisation service is unavailable. Check the API and try again.');
      }
    });
  }

  reset(): void {
    this.requestVersion += 1;
    this.inventory = { ...this.optimisation.defaultInventory };
    this.result = null;
    this.error = '';
    this.selectedCombination = 0;
    this.peopleFedState = 'idle';
  }

  inventoryChanged(): void {
    this.requestVersion += 1;
    this.result = null;
    this.selectedCombination = 0;
    this.error = '';
    this.peopleFedState = 'idle';
  }

  combinationLabel(combination: OptimisationCombination): string { return this.optimisation.recipes.filter(r => combination.recipes[r.name] > 0).map(r => `${combination.recipes[r.name]} × ${this.title(r.name)}`).join(' · ') || 'No recipes'; }
  title(value: string): string { return value.charAt(0).toUpperCase() + value.slice(1); }
  ingredientSummary(ingredients: Record<string, number>): string { return Object.keys(ingredients).map(name => `${ingredients[name]} ${name}`).join(' · '); }
  trackByName(_: number, value: string): string { return value; }
  private hasValidInventory(): boolean { return this.optimisation.ingredients.every(name => Number.isInteger(this.inventory[name]) && this.inventory[name] >= 0); }
  private setError(message: string): void { this.result = null; this.peopleFedState = 'error'; this.error = message; this.snackBar.open(message, 'Dismiss', { duration: 5000, horizontalPosition: 'right', verticalPosition: 'top' }); }
}
