# Rule 03: Responsive Layouts & Adaptive Mobile UX

## 1. Mobile-First Standard (< 640px)
* **Sticky Mini-Player**:
  * When student is watching a video and scrolls down to read the transcript or quiz notes, the video player MUST float into a compact sticky view at top or bottom-end of the screen without pausing.
* **Bottom Sheet Curriculum Drawer**:
  * On mobile screens, modules and lessons MUST NOT use bulky desktop sidebars. Use a smooth bottom sheet / slide-over drawer accessible with the thumb.
* **Mobile Bottom Navigation Bar**:
  * Persistent fixed bottom bar (`h-16`) containing 4 core tabs:
    1. My Learning (تعلّمي)
    2. Explore / Catalog (استكشف)
    3. Notifications (الإشعارات)
    4. Account / Profile (حسابي)

## 2. Desktop Standards (> 1024px)
* **Split-Screen Learning Environment**:
  * 70% viewport width: Video Player + primary lesson content.
  * 30% viewport width: Collapsible curriculum checklist + interactive notes.
* **Theater / Focus Mode**:
  * Include a dedicated button to toggle off all navigation bars and sidebars, expanding the video to 100% viewport width for an immersive lecture experience.

## 3. Empty States & Skeletons (Mandatory UI States)
* **No Blank Screens**: Never leave a loading screen as a blank white area or a lone spinning circle. Always provide animated pulse Skeleton loaders (`animate-pulse bg-slate-200`) mimicking card shapes.
* **Illustrated Empty States**: If student has 0 enrolled courses or empty search results, render a meaningful illustration + action button (e.g. "Explore Popular Courses").
