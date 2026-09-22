# Rule 06: Automated Testing & Verification Gateways

## 1. The Verification Contract
* **No Feature is Complete Without Automated Tests**:
  * Every new domain feature (Action, Controller endpoint, Policy) MUST have an accompanying Feature Test using Pest or PHPUnit.
* **Coverage Requirements**:
  1. **Happy Path**: Successful execution and expected database/response state.
  2. **Unauthorized Access (403)**: Verifying that unauthorized roles cannot view/modify the resource.
  3. **Validation Failures (422)**: Verifying required inputs, types, and edge cases.

## 2. Pre-Completion Checklist
Before declaring any task or milestone complete, the following commands MUST pass with zero errors:
1. `php artisan test` -> All tests pass.
2. `npm run build` -> TypeScript types compile cleanly without `any` errors, Vite bundles successfully.
3. `composer pint --test` -> Code conforms to PSR-12 and Laravel style conventions.
