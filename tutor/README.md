# Tutor LMS template overrides

Tutor LMS loads its front-end templates through `tutor_get_template()`, which
checks **this directory first** before falling back to the plugin. To restyle a
Tutor page wholesale, copy the template from the active plugin version and edit
the copy here — keep the same relative path.

```
wp-content/plugins/tutor/templates/<path>.php   →   <theme>/tutor/<path>.php
```

> Copy from the *installed* plugin version. Template paths/markup change
> between Tutor releases, so a file copied from the docs may not match. After
> upgrading Tutor, diff your overrides against the new plugin templates.

## Shipped overrides in this theme

ตอนนี้ธีมมี override ที่ commit มาแล้ว:

| Template | Purpose |
|------|---------|
| `archive-course.php` | ใช้ shell ของธีมกับหน้า archive และคง course filter/pagination ของ Tutor |
| `loop/course.php` | ใช้ [template-parts/cards/course-card.php](template-parts/cards/course-card.php) เป็นการ์ดคอร์สหลักของ Tutor loop |

## High-value templates to override next

| Page | Plugin template path |
|------|----------------------|
| Course archive wrapper | `archive-course.php` |
| Single course | `single-course.php` |
| Loop course card | `loop/course-loop-card.php` *(or `loop/*` for your version)* |
| Lesson | `single/lesson/lesson.php` |
| Quiz | `single/quiz/*.php` |
| Dashboard shell | `dashboard.php` |
| Dashboard: enrolled courses | `dashboard/enrolled-courses.php` |
| Dashboard: my certificates | `dashboard/my-certificates.php` |
| Instructor list | `instructor-list.php` / `shortcode/instructor-list.php` |
| Login / registration | `template-part/login-form.php`, `dashboard/registration.php` |

## What is already themed without copying

`inc/tutor.php` + the **Tutor LMS theming** layer in `src/css/main.css` map
Tutor's stable `.tutor-*` classes onto the BIA Learn palette, buttons, fonts,
cards, dashboard menu, progress bars, forms and badges. For most sites this is
enough — only copy a template here when you need to change its **structure**,
not just its colours.

After adding or editing styles, rebuild: `npm run build`.
