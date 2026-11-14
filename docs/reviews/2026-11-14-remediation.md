# ตรวจยืนยันรายงาน BIA E-Learning — 14 พฤศจิกายน 2026

## ขอบเขตและข้อจำกัด

เทียบรายงาน `bia-learn-review.md` กับซอร์สล่าสุดจาก origin/main ของเก้า repository และตรวจ production แบบอ่านอย่างเดียวผ่าน BIA E-Learning WordPress abilities กับ HTTP แบบเรียงลำดับ ไม่มีการแก้ข้อมูลหรือตั้งค่า production และไม่มี deploy/publish release

ฐานธีมคือ commit `11fce74` หลัง fast-forward จาก origin/main; Authorizenter และ CourseBundle เริ่มจาก main ล่าสุด การแก้จัดทำบน branch `codex/report-remediation-20261114` ในสาม repository นี้ จากนั้นผู้ใช้อนุมัติให้นำเข้า `main` โดย commit ใช้ `[skip release]` เพื่อไม่เผยแพร่ release อัตโนมัติ หลักฐานการ sync หลัง push เก็บแยกใน `main-integration.json` ของโฟลเดอร์หลักฐาน เอกสาร DOCX/PDF เดิมและไฟล์ README ที่มี case collision ใน CertPSU ไม่ได้แก้ไข

Production ที่อ่านได้: WordPress 7.1.4, Tutor LMS 4.1.3, PHP 8.5.12, MariaDB 10.6.28, ธีม 1.0.111 ใช้ CertPSU Connector + TutorLMS Bridge สำหรับเกียรติบัตร จึงไม่ควรสรุปว่าเพิ่ม Tutor Pro certificate template แล้ว B6 จะหาย

ทดสอบ runtime แยกด้วย WordPress 7.1.4, Tutor 4.1.3, PHP 8.5.8, MySQL ในเครื่อง และ Chrome headless บน Mac ข้อมูลในสภาพแวดล้อมทดสอบเป็น fixture ไม่ใช่สำเนาข้อมูล production ไม่ได้ทำ load test บน production

**“แก้แล้ว” ในรายงานนี้หมายถึงแก้ซอร์สและทดสอบในเครื่องแล้ว ไม่ได้หมายถึง production เปลี่ยนพฤติกรรมแล้ว**

## ผลเทียบทีละข้อ

| ข้อ | ข้อสรุปจากหลักฐานล่าสุด | การดำเนินการ / สถานะ |
|---|---|---|
| B1 | GET admin-ajax ตอบ Apache HTML 403 ก่อนเข้า WordPress | เป็นปัญหา server/ACL/WAF ที่ยังอยู่บน production; เตรียม Apache candidate แยกไว้ ไม่ apply |
| B2 | ใช้ Tutor AJAX เดียวกับ B1; ไม่ทดสอบส่งคำถามสาธารณะซ้ำ | ต้องตรวจหลังแก้ ACL/WAF บนสภาพแวดล้อมที่อนุญาต ยังไม่อ้างว่าใช้งานได้บน production |
| B3 | บันทึกโปรไฟล์พึ่ง AJAX; ไม่แก้ข้อมูลบัญชี production | เช่นเดียวกับ B1; ตรวจหน้า settings และ JS ใน local แล้ว |
| B4 | ธีมส่งต่อ Tutor filter ที่ใช้ AJAX และ `class_exists` มี namespace escaping ผิด ทำให้ทาง GET เดิมไม่ทำงาน | แก้ namespace, sanitize แบบ recursive + allowlist, เพิ่ม sorting GET form มี label/submit; Chrome ยืนยันลำดับ A–Z/Z–A กลับกันและไม่มี AJAX บน query-string archive |
| B5 | fallback form ส่งไป `#` หรือ mailto แต่ไม่มี server handler | เอาฟอร์มที่ทำให้เข้าใจว่าส่งได้ออก; ใช้ลิงก์อีเมลจริงเมื่อกำหนดไว้ แสดงสถานะเมื่อยังไม่มีช่องทาง และยังรองรับ shortcode form จาก page content |
| B6 | เกียรติบัตรผ่าน CertPSU แบบ async; global defaults มี template_id ว่าง แต่ยังไม่มีหลักฐาน course override/queue log/provider result | **ยังระบุ root cause ของ production ไม่ได้** ไม่สร้างใบประกาศจำลอง/ไม่เลือก template แทนผู้ดูแล; ลดคำสัญญาหน้าแรกให้ตรงเงื่อนไข และเตรียมรายการตรวจด้านล่าง |
| B7 | JS ของธีม intercept Next แล้ว POST complete; CSS ซ่อนปุ่ม complete ของ Tutor | เอา automatic completion ของธีมออก รวมถึง poll ตำแหน่ง seek 97%; คืนปุ่ม manual completion ให้ Tutor บังคับ policy ของตนเอง; regression ยืนยัน Next ไม่เรียกบันทึกและยัง navigate |
| B8 | ปุ่ม skip มีอยู่ใน Tutor และต้องแยก pre-test/optional กับ required post-test | ไม่เปลี่ยนเกณฑ์การเรียนหรือคะแนนโดยเดา ต้องยืนยันการตั้งค่ากับเจ้าของหลักสูตร |
| B9 | Tutor `CourseModel::get_total_estimated_time_spent()` ใช้ duration/progress จริง | เปลี่ยนชื่อเป็น “เวลาเรียนโดยประมาณ” และ override dialog อธิบายวิธีคำนวณ ไม่อ้างว่าเป็น measured time |
| B10 | ยืนยันใน Tutor 4.1.3 ว่า legacy review handler เรียก alert เมื่อไม่มีดาว/ข้อความ | capture validation แสดง inline role=alert ที่โฟกัสได้; ข้อมูลครบยังไป native handler ไม่ส่งรีวิว production |
| B11 | รายงานมี timeout หลัง request พร้อมกัน แต่ไม่มี server log/metrics ให้เทียบ | ไม่ทำ load ซ้ำบน production ไม่เพิ่ม rate limit แบบเดา ต้องใช้ log ช่วงเกิดเหตุและ staging load test |
| S1 | anonymous users REST ตอบ 200 และ author query redirect ไป slug จริง | ปิด Core user routes เฉพาะ anonymous, redirect anonymous author archive; authenticated /users/me ยังผ่านใน runtime test เป็น hardening ไม่ใช่หลักฐาน authentication bypass |
| S2 | หน้าหลักขาด headers ที่ระบุ แต่ REST มี nosniff อยู่แล้ว | เพิ่ม conservative wp_headers defaults โดยเก็บ policy เดิมถ้ามี (รวมชื่อ header ตัวพิมพ์เล็ก); HSTS/static/cached responses อยู่ใน Apache candidate ไม่อ้างว่า CSP ครอบคลุมทุก resource หรือผ่าน security audit ทั้งระบบ |
| S3 | readme.html ยังตอบ 200; theme ไม่เคยถอด generator | ถอด wp_head generator; Apache candidate ปิด readme; static file บน production ยังไม่เปลี่ยน และยังมีการ fingerprint เวอร์ชันทางอื่นได้ |
| S4 | เป็น B1 เดียวกัน | candidate เปิดเฉพาะ admin-ajax ไม่เปิด wp-admin ทั้งหมด; ต้องตรวจ upstream WAF/rewrite แยก |
| D1 | homepage/statistics ใช้ helper เดียวกันอยู่แล้ว; reproduce cache stale เมื่อเพิ่ม lesson ได้ แต่ตัวเลข 4/2/9/6 ของรายงานยังเทียบย้อนหลังไม่ได้ | เพิ่ม cache invalidation สำหรับ lesson/deletion/user-role changes และไม่ fallback นับ staff ทั้งหมดเป็นผู้เรียนเมื่อไม่มี subscriber; เลิกเริ่มตัวเลขที่ 0 ด้วย animation ในสองหน้า; ทดสอบ publish lesson ก่อนแก้ stale และหลังแก้ refresh ทันที |
| D2 | ข้อความ “เพื่อนอีก N” ใช้ total enrollment รวมตัวผู้ชม | เปลี่ยนเป็น “มีผู้ลงทะเบียนเรียน N คน” ใช้ได้ทั้งผู้ลงทะเบียนและผู้ชมทั่วไป ไม่ลบ 1 แบบเดา |
| D3 | duration เป็นข้อมูลที่ผู้สอนตั้ง ไม่ใช่ผลรวมวิดีโออัตโนมัติ | ไม่แทน 12:30 ด้วย 4 นาที เพราะยังมีแบบทดสอบ/กิจกรรม ต้องแก้ข้อมูลหลักสูตรจากค่าที่อนุมัติ |
| D4 | หน้าแรก 3 ขั้นตอน แต่ tutorial 4 และมีข้อความอ้างวิดีโอโดยไม่รับรองว่ามี | ปรับ tutorial เป็น 3 ขั้นตอน เอาการอ้างวิดีโอ/หมวดหมู่แบบเหมารวมออก เกียรติบัตรขึ้นกับเงื่อนไข |
| D5 | บวก + และบังคับอย่างน้อย 1 ใน stats | เอา + และ floor 1 ออก แสดงค่าจริงจาก server แม้ไม่มี JS |
| C1 | WordPress read-only ยืนยัน note อยู่ใน lesson 251 post_content ไม่อยู่ใน template | เตรียม cleanup CLI แบบ preview ก่อน; apply ได้เฉพาะ local/staging ทดสอบแล้ว; **ข้อมูล production ยังไม่เปลี่ยน** |
| C2 | พบ developer tip ใน contact fallback template | ลบพร้อมกับ dummy form |
| C3 | sample news เป็นข้อมูลใน WordPress | cleanup CLI เปลี่ยนเฉพาะ slug hello-world + ชื่อ Hello world! ที่ publish ให้เป็น draft; ไม่ลบถาวร ทดสอบใน local; production ยังไม่เปลี่ยน |
| C4 | field ตัวอย่างเป็น course metadata | CLI ล้างเฉพาะค่า placeholder ตรงตัว ไม่ล้างประโยคที่ผู้สอนเขียนจริง; production ยังไม่เปลี่ยน |
| C5 | content ที่อ่านจาก production มี overview h2 ซ้ำกับ Tutor heading จริง | CLI เอาเฉพาะ h2 ใน wrapper bia-course-overview ที่รู้จักออก เก็บคำอธิบายไว้; การซ้ำ benefits ที่ผู้สอนกรอกยังต้องทบทวนเนื้อหา |
| C6 | cover ไม่ได้กำหนด; ธีมมี fallback ตามปกติ | ต้องใช้ภาพที่เจ้าของหลักสูตรอนุมัติ ไม่คัดลอกภาพ EP1 ไปอ้างว่าเป็น EP2–4 |
| C7 | slug เป็นข้อมูลถาวร/ลิงก์เดิม | ไม่เปลี่ยน URL เดิมโดยไม่มี redirect plan เป็นข้อเสนอความสม่ำเสมอ ไม่ใช่ functional bug ที่ยืนยันจาก source |
| C8 | contact options ว่างได้จริง; footer bottom มี bia@psu.ac.th hardcode อยู่แล้ว จึงไม่ใช่ไม่มีช่องทางทุกส่วน | contact/footer column มี explicit empty state; ไม่ใส่เบอร์/ที่อยู่ใหม่โดยเดา ต้องกำหนดช่องทางที่ผู้ดูแลยืนยัน |
| C9 | search title ในซอร์สใช้ “ผลการค้นหา” ถูกต้องอยู่แล้ว; ชื่อ auth page เป็นข้อมูลในฐานข้อมูล | CLI แก้เฉพาะ title ผิดตรงตัว; แก้ search escaping ซ้ำด้วย; production title ยังไม่เปลี่ยน |
| Q1 | empty search กลายเป็นผลรวมเนื้อหาได้ | pre_get_posts คืน 0 ผลลัพธ์สำหรับคำว่าง/ช่องว่าง, input required |
| Q2 | ยังไม่มี policy ตัด workflow pages | จำกัด public post types/status และ exclude system page templates + shortcode pages แม้ใช้ custom slug; หน้า about/เนื้อหาทั่วไปยังค้นได้ |
| Q3 | search.php มี form และ content-none เพิ่มอีก form | เอา form ซ้ำใน search empty state ออก |
| Q4 | get_search_query() escape แล้ว อาจถูกใช้ซ้ำ/เติมค่าไม่ตรง; input ไม่มี min-w-0 | ใช้ get_search_query(false) + esc_attr ครั้งเดียว; Chrome คืนค่าพิเศษถูกต้องและไม่ล้นจอ |
| Q5 | native Tutor AJAX serialize internal data เข้า URL; action name ไม่ใช่ secret | GET sort ใหม่ allowlist parameter ไม่ส่ง action/nonce/internal layout state; native AJAX filters/pagination อื่นยังต้องแก้ B1 |
| U1 | **reproduce จริงในหน้า account/settings ของ Tutor 4.1.3:** dark text rgb(240,241,241) บน card ขาว | scope CSS ครอบคลุมทั้ง old dashboard, tutor-dashboard-layout และ tutor-account-page-wrapper; Chrome หลังแก้ได้ dark surface rgb(31,36,47) |
| U2 | question indicator ใช้ crimson โดยไม่ปรับตาม dark mode | ใช้ paired Tutor text/surface tokens กับ question indicator/title |
| U3 | theme heading utilities บางส่วน override Tutor text token | ใช้ token กับ dashboard headings และ quiz dialog title; ตรวจ contrast ใน fixture ทั้งสอง mode |
| U4 | theme force white card ชน native dark mode | card/empty certificate/bundle surfaces ใช้ Tutor tokens; ตรวจจริงใน settings และ fixture |
| U5 | footer ข้อความเล็กใช้ paper-500 บนพื้นมืด | เพิ่มเป็น paper-300; ส่วนข้อความอื่นยังต้องตรวจทั้งเว็บเมื่อมีข้อมูลจริง |
| U6 | bia-tutor-btn มี shape แต่ไม่มี primary background | continue-learning และ certificate CTA ใช้ btn-primary; fixture contrast 7.97:1 |
| U7 | sort control อยู่ใน native กล่องใหญ่และพึ่ง JS | form ใหม่เป็นแถว compact พร้อม label และ submit button |
| U8 | billing/social/bundles เป็น native/active plugin capabilities | ไม่ซ่อนฟีเจอร์ของ plugin ที่เปิดใช้งานจากสมมติฐานว่าไม่มีวันใช้; ถ้าต้องการ free-only product ต้องกำหนดนโยบายนี้ก่อน |
| U9 | Bundle plugin ส่ง icon-font class แต่ Tutor 4 dashboard เรียก SvgIcon::name() | ใช้ TUTOR\Icon::BUNDLE เมื่อรองรับ คง fallback รุ่นเก่า; unit regression ก่อนแก้ fail หลังแก้ pass; bundle colors ปรับตาม theme |
| U10 | standalone shortcode กับ dashboard แสดง list เดียวกัน | dedicated alias page ที่มี shortcode ล้วน redirect ไป dashboard/my-certificates; embedded list ในหน้าเนื้อหาอื่นไม่ redirect; runtime HTTP 302 ยืนยัน |
| U11 | footer fallback ไม่มี tutorial/statistics | เพิ่มสองลิงก์ใน fallback; ถ้าใช้ custom widget/menu ต้องแก้รายการเมนูในฐานข้อมูลแยก |
| U12 | logged-in topbar + navbar มี dashboard CTA ซ้ำ | เอา CTA ใน desktop topbar ออก เหลือ navbar; mobile menu ยังคงเข้าถึงได้ |
| U13 | footer fallback ใช้ pagoda ต่างจาก header fallback | ใช้ asset BIA × PSU เดียวกัน; custom logo ที่ผู้ดูแลกำหนดยังต้องตรวจ branding ตามไซต์จริง |
| U14 | breadcrumb ไม่มีกรณี is_home | เพิ่มชื่อ news page/current blog ใน breadcrumb |
| U15 | enrolled locked card อาจแสดง “เริ่มเรียน” | เปลี่ยนเป็น “ดูเงื่อนไขการปลดล็อก” เมื่อมี episode lock; ไม่เปลี่ยน server-side access policy |
| U16 | no active courses หลังเรียนจบเป็น empty state ที่ถูกต้อง แต่ควรแนะนำตอนต่อไป | แปลข้อความและปรับ surfaces/CTA ให้ชัด; **ยังไม่ได้เพิ่ม recommendation engine/auto-select EP ถัดไป** |
| U17 | latest-news แสดงเมื่อมีเพียงหนึ่งโพสต์ | ซ่อน homepage news section ถ้ามีน้อยกว่า 3; archive/search ยังสะท้อนสถานะ publish จริง ต้องใช้ C3 cleanup |
| 6.2 ภาษา | มี PHP และ wp.i18n strings หลาย domain รวม contextual greeting | เพิ่ม Thai fallbacks เฉพาะ frontend/domain ที่เกี่ยวข้อง และใช้ settings-nav hook แก้ Billing Address ที่ Tutor register เป็นข้อความดิบไม่ผ่าน gettext เก็บ locale อื่นและ admin ไว้; Chrome ทดสอบ wp.i18n จริงแล้ว ไม่อ้างว่าแปลครบทุก string ของ third-party/plugins |
| 6.3 ป้าย/ข้อผิดพลาด | Update Review ใช้กับรีวิวใหม่ด้วย; Authorizenter แสดง raw token error; preflight อ้าง time limit เสมอ | ใช้ “บันทึกรีวิว”, missing/expired pending link มีข้อความเข้าใจง่าย+ลิงก์ sign-in (PHP+Thai fallback), preflight อ้างรายละเอียดที่มีจริง; error อื่นของ Tutor บางส่วนเพิ่ม fallback แต่ HTTP 403 ยังต้องแก้ B1 |
| 6.5 ตัวเลือกข้อสอบ | ความยาว/ตำแหน่งคำตอบเป็นเนื้อหาและ quiz setting | ไม่แก้ answer keys/สุ่มคำตอบแทนเจ้าของหลักสูตร; ต้องปรับพร้อมตรวจ valid scoring และ locked historical attempts |
| 6.6 Mobile | hero มี overflow-hidden อยู่แล้ว; source search input มี intrinsic min-width | เพิ่ม min-w-0; local Chrome homepage/search 390px: scrollWidth=390, ไม่มี JS error ไม่สรุปแทนทุก route/อุปกรณ์ |

## เกณฑ์ที่ต้องยืนยันนอกซอร์สรอบนี้

1. B1/S4: หาต้นทาง deny จาก Apache error/access log, rewrite และ WAF ให้ชัด ก่อนปรับ exact-path exception; GET/POST ที่ไม่มี action ของ admin-ajax ต้องถึง WordPress (ปกติได้ body 0 / HTTP 400 ตาม request) แล้วทดสอบ Q&A/profile/filter ด้วยบัญชี staging
2. B6: ตรวจ `_certpsu_course_settings` ของ course 249 เทียบ global defaults, template/organization/endorsers ที่อนุมัติ, Action Scheduler group `certpsu-tutorlms`, outcome/error ของ Completion_Handler และ provider connectivity; ไม่ replay งานที่ remote outcome ไม่ชัดเจนโดยเดา
3. เนื้อหา: ตรวจ preview ของ `bin/review-content.php` บนสำเนาข้อมูลก่อน apply; ค่า duration, quiz requirements, answer choices, cover และช่องทางติดต่อยังต้องมีข้อมูลที่อนุมัติ สคริปต์ไม่สร้างข้อมูลเหล่านี้
4. B11: ตรวจ metrics/log ย้อนเวลาและทำ load test แบบมีขอบเขตบน staging; ไม่มีหลักฐานพอว่าปัญหาคือ PHP capacity, DB, rate limit หรือ WAF
5. U8/U16: ตัดสินขอบเขต free-only และพฤติกรรมแนะนำคอร์สต่อไปแยกจาก bug fix

## การทดสอบและหลักฐาน

หลักฐานเต็มอยู่ที่ `/Users/wachiravit/Documents/Codex/2026-11-14/bia-report-verification/` โดยแยก red/green logs, runtime, Chrome, syntax/lint และภาพไว้ รายละเอียดผลสุดท้ายใน `verification.json`

- PHPUnit theme รวม regression ใหม่; JS suite; production CSS/JS build
- Authorizenter full unit suite และ CourseBundle unit suite; PHPCS ของไฟล์ซอร์สที่แก้ และ PHPStan ของ Authorizenter (CourseBundle ไม่มี PHPStan config/binary ใน checkout)
- CertPSU, ProfileSync, Prerequisites, Analytics และ Cleanup ชุดทดสอบที่รันได้ในเครื่อง ใช้เป็น regression เพิ่ม ไม่ใช่ provider/production acceptance
- WordPress จริง: search public/system/empty/course-only, special characters, REST anonymous/authenticated, contact fallback/mailto, locale behavior, certificate alias redirect, stats lesson cache invalidation
- Chrome จริง: homepage/search ที่ 390px; sort A–Z/Z–A ไม่มี AJAX; sign-in ด้วย local learner และเปิด account/settings; Thai wp.i18n และ dark card contrast
- Contrast fixture ใช้ CSS ของ Tutor 4.1.3 จริง + compiled theme CSS: light label/question/dialog 18.86:1, muted 11.91:1; dark 13.73:1/9.97:1; CTA 7.97:1 ไม่ใช่ผล WCAG audit ทั้งระบบ
- Content cleanup: preview ไม่มี write, production-environment guard คืน exit 1 บน **local fixture**, local apply แก้ sample post/lesson/course metadata และรันซ้ำ preview เป็น 0 รายการ
- Apache candidate ผ่าน `httpd -t` ของ Apache 2.4.67 ในเครื่อง: Syntax OK; ยังไม่ยืนยันผล ACL/WAF บนเซิร์ฟเวอร์จริง

เอกสาร WordPress ที่ใช้ประกอบขอบเขต hooks: [pre_get_posts](https://developer.wordpress.org/reference/hooks/pre_get_posts/) และ [wp_headers](https://developer.wordpress.org/reference/hooks/wp_headers/) ส่วน Tutor ตรวจจาก source distribution 4.1.3 ของ WordPress.org

### ข้อสังเกตจากการตรวจคุณภาพเพิ่มเติม

Full theme PHPCS พบ flags เดิมนอกตารางรายงาน จึงแก้ numeric/text output ให้ escape และตรวจ request method ด้วย WordPress helpers ส่วน prepared-query variables, SQL fragments ที่เป็น closed literals และ read-only GET preview/navigation ใช้ annotations เฉพาะจุดพร้อมเหตุผล ไม่ได้ปิดกฎทั้งโครงการ; native Tutor nonce validation ยังทำงานตามเดิม

ข้อจำกัดของชุดทดสอบ: CertPSU มี 11 skipped tests และ fault-injection log ที่ตั้งใจทดสอบ; Authorizenter/ProfileSync มี deprecation ใน Reflection::setAccessible ของ test harness บน PHP 8.5; Bundle ไม่มี coverage driver จึงไม่มี coverage report ไม่ถือเป็นการยืนยันว่า provider หรือ production ผ่าน และไม่ได้รัน Duplicator WordPress integration harness ในรอบนี้

### ผลชุดทดสอบที่สังเกตได้

| ชุด | ผล |
|---|---|
| Theme PHPUnit | 56 tests / 161 assertions ผ่าน |
| Theme JavaScript | 15 tests ผ่าน |
| Authorizenter PHPUnit | 358 tests / 811 assertions ผ่าน; มี test-harness deprecation บน PHP 8.5 |
| CourseBundle unit | 88 tests / 236 assertions ผ่าน; ไม่ได้สร้าง coverage report |
| CertPSU | 118 tests / 324 assertions, skipped 11 |
| ProfileSync | 102 tests / 219 assertions ผ่าน; มี test-harness deprecation |
| Prerequisites unit | 14 tests / 29 assertions ผ่าน |
| Analytics unit | 68 tests / 242 assertions ผ่าน |
| Cleanup standalone | 142 assertions, failed 0 |
| PHP syntax | 583 ไฟล์ first-party/tests ในเก้า repo, errors 0; ไม่รวม vendor/build/dist |
| Static/build | Theme full PHPCS, Authorizenter full PHPCS + PHPStan, Bundle changed-source PHPCS และ npm build ผ่าน |

Browserslist แจ้ง metadata เก่าในขั้น build แต่ build สำเร็จ ไม่ได้อัปเดต dependency นอกขอบเขตนี้ Runtime smoke รอบสุดท้ายใช้ fresh schema และผ่าน 14 assertions โดยไม่มี log missing optional Tutor Pro tables จากการลบ fixture ของรอบเริ่มต้น

### จุดซอร์สสำคัญสำหรับตรวจต่อ

- [Search / headers / certificate aliases](/Users/wachiravit/Documents/GitHub/BIA-Learn-WPTheme/inc/public-site.php:11)
- [Next และ review validation](/Users/wachiravit/Documents/GitHub/BIA-Learn-WPTheme/src/js/main.js:128)
- [GET catalog sorting](/Users/wachiravit/Documents/GitHub/BIA-Learn-WPTheme/tutor/course-filter/course-archive-filter-bar.php:1)
- [Contact template](/Users/wachiravit/Documents/GitHub/BIA-Learn-WPTheme/page-templates/template-contact.php:1)
- [Thai fallback scope](/Users/wachiravit/Documents/GitHub/BIA-Learn-WPTheme/inc/localization.php:19)
- [Stats invalidation](/Users/wachiravit/Documents/GitHub/BIA-Learn-WPTheme/inc/tutor.php:620)
- [Content preview/apply script](/Users/wachiravit/Documents/GitHub/BIA-Learn-WPTheme/bin/review-content.php:1)
- [Authorizenter recovery](/Users/wachiravit/Documents/GitHub/Autorizenter/plugins/authorizenter-ui/includes/class-frontend.php:672)
- [Bundle SVG icon compatibility](/Users/wachiravit/Documents/GitHub/TutorLMS-CourseBundle/src/Tutor/DashboardIntegration.php:69)
