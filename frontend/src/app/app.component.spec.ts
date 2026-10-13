import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { FormsModule, NgForm } from '@angular/forms';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatCardModule } from '@angular/material/card';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatInputModule } from '@angular/material/input';
import { MatButtonModule } from '@angular/material/button';
import { MatTableModule } from '@angular/material/table';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSnackBarModule } from '@angular/material/snack-bar';
import { AppComponent } from './app.component';

describe('AppComponent', () => {
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule, FormsModule, BrowserAnimationsModule, MatToolbarModule, MatCardModule, MatFormFieldModule, MatInputModule, MatButtonModule, MatTableModule, MatProgressSpinnerModule, MatSnackBarModule],
      declarations: [AppComponent]
    });
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  function validForm(): NgForm { return { invalid: false } as NgForm; }

  it('should create the app', () => {
    expect(TestBed.createComponent(AppComponent).componentInstance).toBeTruthy();
  });

  it('should start with the default inventory', () => {
    expect(TestBed.createComponent(AppComponent).componentInstance.inventory['dough']).toEqual(10);
  });

  it('should render the inventory page', () => {
    const fixture = TestBed.createComponent(AppComponent);
    fixture.detectChanges();
    expect((fixture.nativeElement as HTMLElement).querySelector('h1')?.textContent).toContain('Recipe optimiser');
  });

  it('should show clear guidance before optimisation instead of an ambiguous dash', () => {
    const fixture = TestBed.createComponent(AppComponent);
    fixture.detectChanges();
    const app = fixture.componentInstance;
    expect(app.peopleFedState).toBe('idle');
    expect(app.peopleFedDisplay).toBe('Run optimisation to see results');
    expect(app.peopleFedLabel).toBe('No results yet');
    expect((fixture.nativeElement as HTMLElement).textContent).not.toContain('false');
  });

  it('should show the numeric maximum after successful optimisation', () => {
    const fixture = TestBed.createComponent(AppComponent);
    const app = fixture.componentInstance;
    app.submit(validForm());
    expect(app.peopleFedState).toBe('loading');
    http.expectOne('/api/optimise').flush({ success: true, data: { people: 12, combinations: [] } });
    expect(app.peopleFedState).toBe('success');
    expect(app.peopleFedDisplay).toBe('12');
    expect(app.peopleFedLabel).toBe('maximum people fed');
  });

  it('should display a valid zero result instead of treating zero as falsy', () => {
    const app = TestBed.createComponent(AppComponent).componentInstance;
    app.submit(validForm());
    http.expectOne('/api/optimise').flush({ success: true, data: { people: 0, combinations: [] } });
    expect(app.peopleFedState).toBe('success');
    expect(app.peopleFedDisplay).toBe('0');
  });

  it('should invalidate results when inventory changes', () => {
    const app = TestBed.createComponent(AppComponent).componentInstance;
    app.submit(validForm());
    http.expectOne('/api/optimise').flush({ success: true, data: { people: 12, combinations: [] } });
    app.inventoryChanged();
    expect(app.peopleFedState).toBe('idle');
    expect(app.result).toBeNull();
    expect(app.peopleFedDisplay).toBe('Run optimisation to see results');
  });

  it('should show an error state when the API fails', () => {
    const fixture = TestBed.createComponent(AppComponent);
    const app = fixture.componentInstance;
    app.submit(validForm());
    http.expectOne('/api/optimise').error(new ErrorEvent('Network error'));
    expect(app.peopleFedState).toBe('error');
    expect(app.peopleFedDisplay).toBe('\u2014');
    expect(app.peopleFedLabel).toBe('Unable to load results');
    expect(app.peopleFedDisplay).not.toMatch(/false|null|undefined|NaN/);
    fixture.destroy();
  });
});
