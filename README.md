# BIA Learn — WordPress Theme

ธีม WordPress สำหรับแพลตฟอร์มเรียนรู้ **bia-learn.psu.ac.th** ของหอจดหมายเหตุพุทธทาส อินทปัญโญ (BIA) — ออกแบบเชิงวิชาการ/ธรรมะ โทน **แดงครั่ง–กระดาษสา** พร้อม integration สำหรับ **Tutor LMS** (archive, course loop, dashboard styling, หน้า home ที่ดึงข้อมูลคอร์ส/ผู้สอน) พัฒนาด้วย **TailwindCSS** + **Alpine.js**

A classic WordPress theme for the Buddhadasa Indapanno Archives learning platform, with Tutor LMS-aware archive, course loop, and dashboard integration, built with TailwindCSS and Alpine.js.

---

## ความต้องการระบบ (Requirements)

- WordPress ≥ 6.4, PHP ≥ 7.4
- ปลั๊กอิน **Tutor LMS** (สำหรับคอร์ส/บทเรียน/แบบทดสอบ/ใบเกียรติบัตร) — ธีมทำงานได้แม้ไม่มี แต่ส่วนที่เป็น LMS จะถูกซ่อน และบางหน้า/การ์ดจะ fallback เป็น WordPress ปกติ
- Node.js ≥ 18 + npm (สำหรับ build CSS/JS)

## การติดตั้ง (Install)

```bash
# 1) ติดตั้ง dependencies
npm install

# 2) build assets (production)
npm run build

# 3) นำทั้งโฟลเดอร์ไปวางที่ wp-content/themes/bia-learn แล้ว Activate ในแอดมิน
```

เมื่อ Activate ธีมจะสร้างหน้า **เกี่ยวกับเรา / ติดต่อ / FAQ / ผู้สอน / สถิติ** ให้อัตโนมัติ (ผูกกับ page template ที่ถูกต้องแล้ว) — เพียงสร้างเมนู `Primary` แล้วเลือกหน้าเหล่านี้

## การพัฒนา (Development)

```bash
npm run dev      # watch — คอมไพล์ CSS + JS อัตโนมัติเมื่อแก้ไฟล์
npm run build    # production — minify CSS + JS
```

- แก้สไตล์ที่ [src/css/main.css](src/css/main.css) — **อย่าแก้** `assets/css/main.css` (เป็นไฟล์ที่ build แล้ว)
- แก้ JS ที่ [src/js/main.js](src/js/main.js)
- สี/ฟอนต์/spacing ทั้งหมดอยู่ที่ [tailwind.config.js](tailwind.config.js) (single source of truth)

> Tailwind สแกนคลาสจากไฟล์ `**/*.php` ทั้งหมด ถ้าเพิ่มคลาสใหม่ในเทมเพลตต้องรัน build ใหม่

## เว็บฟอนต์ (Self-hosted fonts)

ฟอนต์ **Sarabun** (เนื้อหา) + **Noto Serif Thai** (หัวข้อ) เสิร์ฟจาก `assets/fonts/` เอง (ไม่พึ่ง Google Fonts CDN) — โหลดเร็วขึ้นและไม่เรียกข้ามโดเมน (เหมาะกับ PDPA). `@font-face` (subset ไทย+ละติน, `font-display: swap`) ฝังอยู่ต้นไฟล์ [src/css/main.css](src/css/main.css) และฟอนต์ไทยหลักถูก `preload` ใน [inc/enqueue.php](inc/enqueue.php)

> หากต้องการเพิ่ม/เปลี่ยนน้ำหนักฟอนต์: ดึง CSS จาก Google Fonts (พร้อม User-Agent ของเบราว์เซอร์เพื่อให้ได้ `woff2`), ดาวน์โหลดไฟล์ลง `assets/fonts/` ด้วยชื่อ `{family}-{weight}[i]-{subset}.woff2` แล้วเพิ่มบล็อก `@font-face` ใน `src/css/main.css` ให้ชี้ `url(../fonts/...)` จากนั้น `npm run build`

## อัปเดตธีมอัตโนมัติ (Auto-update จาก GitHub)

ธีมเช็คอัปเดตจาก **GitHub Releases** ของ repo นี้โดยตรง (ผ่าน Plugin Update Checker ที่ฝังใน [inc/lib/plugin-update-checker/](inc/lib/plugin-update-checker/) + bootstrap ที่ [inc/updater.php](inc/updater.php)) — เมื่อมี release ใหม่ที่เวอร์ชันสูงกว่า จะขึ้นปุ่มอัปเดตใน **แดชบอร์ด → อัปเดต** และ **ธีม** กดอัปเดตได้ในคลิกเดียว (repo เป็น public จึงไม่ต้องใช้ token)

### ปล่อยเวอร์ชันใหม่ — บนเครื่อง dev "แก้โค้ดแล้ว push" พอ

```bash
git push origin main
```

GitHub Action [.github/workflows/release.yml](.github/workflows/release.yml) จะทำให้อัตโนมัติทุกครั้งที่ push เข้า `main`:

1. คำนวณเวอร์ชันถัดไป — **patch +1** จาก tag ล่าสุด (เช่น `v1.0.3` → `v1.0.4`)
2. `npm ci && npm run build` (build CSS/JS สดในคลาวด์ — ไม่ต้อง build ในเครื่อง)
3. แพ็ก `bia-learn.zip` (โครงสร้างถูกต้อง รวม assets/fonts/PUC; ตัด node_modules/src/configs)
4. สร้าง tag + **GitHub Release** แนบ zip → ทุกไซต์ที่ใช้ธีมเห็นปุ่มอัปเดตภายใน ~12 ชม. (หรือกด “Check again”)

> - อยาก bump **minor/major**: แก้ `Version:` ใน [style.css](style.css) ให้สูงกว่า tag ล่าสุด (เช่น `1.1.0`) แล้ว push — Action จะใช้เวอร์ชันนั้นเป็น release และ patch ต่อจากนั้นไปเอง
> - ไม่อยากให้ push ครั้งนั้นออก release: ใส่ `[skip release]` ใน commit message
> - push ที่แตะแค่ `**.md`, `docs/**`, `.github/**` จะไม่ทริก release

## โครงสร้าง (Structure)

```
inc/            setup, enqueue, template-tags, widgets, customizer, tutor
template-parts/ header/ footer/ home/ cards/  (ชิ้นส่วนใช้ซ้ำ)
page-templates/ about, contact, faq, instructors, statistics
tutor/          override templates ของ Tutor LMS (ดู tutor/README.md)
src/            ซอร์ส CSS/JS (ก่อน build)
assets/         CSS/JS ที่ build แล้ว
```

## การปรับแต่งเนื้อหา (Customizing)

- **Appearance → Customize → ตั้งค่า BIA Learn**: Hero, ข้อมูลติดต่อ, โซเชียล, แถบ CTA, ส่วนท้าย
- **เมนู**: `Primary`, `Footer`, `Footer legal`, `Social`
- **โลโก้**: Customize → Site Identity → Logo
- **หน้าแรก**: ตั้ง Settings → Reading → "หน้าแรกแบบคงที่" (front-page.php ทำงานอัตโนมัติ); หน้า Posts สำหรับข่าวจะใช้ home.php
- **คอร์ส/ผู้สอน/เกียรติบัตร**: จัดการผ่าน Tutor LMS — ธีมมี archive override, course card override, dashboard styling และ helper data พร้อมใช้งาน (ดู `tutor/README.md`)
- **FAQ / Partners**: ปรับผ่าน filter `bia_learn_faq_items`, `bia_learn_partner_logos`

## i18n

สตริงทั้งหมดอยู่ใน text domain `bia-learn` สร้างไฟล์แปลด้วย:

```bash
wp i18n make-pot . languages/bia-learn.pot --domain=bia-learn
```

## หมายเหตุ

- ต้องเพิ่ม `screenshot.png` (1200×900) เองสำหรับภาพ preview ในหน้า Themes
- ธีมนี้เป็น classic theme; theme.json ใช้กำหนด palette/ฟอนต์ให้ block editor กลมกลืนเท่านั้น
