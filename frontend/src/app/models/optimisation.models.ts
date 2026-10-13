export interface Recipe {
  name: string;
  feeds: number;
  ingredients: Record<string, number>;
}

export interface Inventory { [ingredient: string]: number; }

export interface OptimisationCombination {
  recipes: Record<string, number>;
  used: Inventory;
  remaining: Inventory;
}

export interface OptimisationResult {
  people: number;
  combinations: OptimisationCombination[];
}

export interface OptimisationResponse {
  success: boolean;
  data: OptimisationResult;
  error?: { message: string };
}
