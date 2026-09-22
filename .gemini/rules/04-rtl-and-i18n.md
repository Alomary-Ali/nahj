# Rule 04: Bidirectional Layouts (RTL / LTR) & Internationalization (i18n)

## 1. Strict Logical CSS Properties (Tailwind Enforcement)
* **FORBIDDEN Physical Classes**:
  * NEVER use `ml-*` or `mr-*`. Use `ms-*` (margin-inline-start) and `me-*` (margin-inline-end).
  * NEVER use `pl-*` or `pr-*`. Use `ps-*` (padding-inline-start) and `pe-*` (padding-inline-end).
  * NEVER use `left-*` or `right-*` for positioning. Use `start-*` and `end-*`.
  * NEVER use `text-left` or `text-right`. Use `text-start` and `text-end`.
  * NEVER use `rounded-l-*` or `rounded-r-*`. Use `rounded-s-*` and `rounded-e-*`.

## 2. Directional Icons & Chevrons
* Any directional icon indicating sequence or flow (Next Lesson `->`, Previous Lesson `<-`, Breadcrumbs, Accordion indicators) MUST automatically flip orientation in RTL mode:
  ```html
  <ChevronRightIcon class="h-4 w-4 rtl:rotate-180" />
  ```

## 3. String Internationalization
* Do NOT hardcode English or Arabic text directly in Vue templates.
* Use localized translation keys or centralized dictionary files (`$t('courses.explore')`) to allow seamless language switching between Arabic and English.
* The `<html>` tag must reflect the active locale and direction: `dir="rtl" lang="ar"` or `dir="ltr" lang="en"`.
