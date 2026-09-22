# Rule 02: Unified UI Design System & Coursera Brand Tokens

## 1. Brand Color Palette (Strict Enforcement)
* **Primary Brand Blue**: `#0056D2` (`bg-primary`, `text-primary`).
  * Hover / Active Blue: `#00419E` (`hover:bg-primary-hover`).
* **Soft Blue Accent**: `#EBF3FF` (`bg-primary-50`) for active sidebar links, badges, and progress indicators.
* **Neutral Palette (Slate)**:
  * Page Background: `#F8FAFC` (`bg-slate-50`).
  * Surface / Cards Background: `#FFFFFF` (`bg-white`) with subtle borders `border-slate-200`.
  * Primary Text / Headings: `#0F172A` (`text-slate-900`).
  * Body / Academic Reading Text: `#334155` (`text-slate-700`) with `leading-relaxed`.
  * Secondary / Meta Text: `#64748B` (`text-slate-500`).
* **Semantic States**:
  * Success / Completed: `#0F8458` (`text-emerald-700`, `bg-emerald-50`).
  * Warning / Deadlines: `#D97706` (`text-amber-700`, `bg-amber-50`).
  * Danger / Error: `#DC2626` (`text-red-700`, `bg-red-50`).

## 2. Typography Hierarchy
* **English Primary Font**: `Inter`, system-ui, sans-serif.
* **Arabic Primary Font**: `IBM Plex Sans Arabic`, `Cairo`, sans-serif.
* **Line Heights**: Academic text must have `leading-relaxed` or `leading-loose` (especially Arabic) to prevent glyph collisions.

## 3. Cards & Component Anatomy
* **Course Cards**:
  * Aspect ratio `16:9` for course thumbnail.
  * Institution / Partner logo at top left/right.
  * Title clamped to 2 lines max (`line-clamp-2`).
  * Meta pills (Difficulty, Estimated Hours, Certificate available).
* **Border Radii & Shadows**:
  * Standard components: `rounded-lg` (8px).
  * Feature cards and modals: `rounded-xl` (12px).
  * Shadows: Soft elevation `shadow-sm` and `shadow-md`. Avoid harsh black shadows.
