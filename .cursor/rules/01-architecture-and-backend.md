# Rule 01: Backend Architecture, Domain Actions & Database Integrity

## 1. Controller Discipline
* **Controllers MUST BE Thin**: Never write business logic, database queries, calculations, or complex transformations inside Controllers.
* **Controller Responsibilities**:
  1. Receive HTTP Request & validate using dedicated `FormRequest`.
  2. Authorize action using dedicated `Policy` (e.g. `$this->authorize('update', $course)`).
  3. Call a dedicated `Action` class (e.g. `app(EnrollStudentAction::class)->execute($user, $course)`).
  4. Return an `Inertia::render()` response or redirect with flash messages.

## 2. Single-Action Classes Pattern
* Create dedicated, single-purpose Action classes in `app/Actions/{Domain}/`.
* Each Action class has one public method: `execute(...)`.
* Actions encapsulate all business rules and are reusable across Web, API, Console, and Queue jobs.

## 3. Database Standards & Data Integrity
* **Primary Keys**:
  * Internal DB primary key: `bigIncrements('id')` for maximum indexing efficiency.
  * Public external ID: Use `ulid` or `uuid` column with a unique index for all public URLs/APIs (e.g. `/courses/{ulid}`). Never expose raw auto-incrementing integer IDs to students.
* **Atomic Transactions**:
  * Any operation modifying multiple tables (e.g. checkout, enrollment, certificate issuance) MUST be wrapped inside `DB::transaction(function () { ... })`.
* **Foreign Key Constraints**:
  * Always define explicit foreign key constraints with `onDelete('restrict')` for courses, enrollments, certificates, and invoices.
* **Soft Deletes**:
  * Apply `SoftDeletes` to `User`, `Course`, `Module`, `Lesson`, `Quiz`, and `Certificate`.
* **Zero N+1 Queries**:
  * Never trigger lazy loading in loops or views. Always eager-load explicitly using `with(['relation1', 'relation2'])`.
