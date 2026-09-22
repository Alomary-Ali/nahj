# Rule 05: Filament Admin & Instructor Portal Standards

## 1. Directory & Resource Organization
* Follow Filament v5 modular architecture:
  * Resource Form Schemas in `app/Filament/Resources/{Resource}/Schemas/{Resource}Form.php`.
  * Resource Tables in `app/Filament/Resources/{Resource}/Tables/{Resource}Table.php`.
  * Resource Infolists in `app/Filament/Resources/{Resource}/Schemas/{Resource}Infolist.php`.

## 2. Table Performance & Usability
* **Searchable & Sortable**: Always specify `searchable()` and `sortable()` on primary identifiers (name, title, email, status).
* **Toggleable Columns**: Non-essential columns (created_at, updated_at, description) must use `toggleable(isToggledHiddenByDefault: true)` to avoid slowing down database queries.
* **Eager Loading in Tables**: Always eager-load relationships displayed in columns (e.g. `roles`, `category`, `instructor`) to prevent N+1 queries.

## 3. Instructor Scoping & Multi-Tenancy
* Instructors must ONLY see and edit their own courses and materials.
* Use `getEloquentQuery()` in Resource classes to scope queries to the authenticated user if their role is `instructor`:
  ```php
  public static function getEloquentQuery(): Builder
  {
      $query = parent::getEloquentQuery();
      if (!auth()->user()->hasRole('admin')) {
          $query->where('instructor_id', auth()->id());
      }
      return $query;
  }
  ```
