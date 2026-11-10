const {test}=require('node:test');const assert=require('node:assert/strict');const {JSDOM}=require('jsdom');const fs=require('node:fs');const {buildSync}=require('esbuild');
const main=buildSync({entryPoints:['src/js/main.js'],bundle:true,write:false}).outputFiles[0].text;
const settle=()=>new Promise(r=>setTimeout(r,20));
for(const result of [{ok:false,json:async()=>({success:false})},{ok:true,json:async()=>({success:false})},new Error('network')])test('Next keeps lesson and exposes completion failure '+String(result.ok),async()=>{
 const d=new JSDOM('<form><input name="tutor_action" value="tutor_complete_lesson"></form><a href="/next">Next</a>',{url:'http://example.test/lesson',runScripts:'outside-only'});const w=d.window;w.matchMedia=()=>({matches:true});w.fetch=async()=>{if(result instanceof Error)throw result;return result;};w.eval(main);await settle();w.document.querySelector('a').click();await settle();assert.match(w.document.querySelector('[role="alert"]')?.textContent||'',/.+/);assert.equal(w.location.pathname,'/lesson');d.window.close();
});
test('Tutor state events update matching counts without DOM input',async()=>{
 const d=new JSDOM('<html lang="en"><form id="quiz" class="tutor-quiz-submission"><div class="tutor-quiz-progress-bar-wrapper"></div></form></html>',{runScripts:'outside-only',pretendToBeVisual:true});const w=d.window;let value='';w.TutorCore={form:{hasForm:()=>true,getFormState:()=>({values:{'attempt[1][quiz_question_ids][]':[1],'attempt[1][quiz_question][1]':value}})}};w.eval(fs.readFileSync('assets/js/bia-quiz-ux.js','utf8'));await settle();value='matched';w.document.dispatchEvent(new w.CustomEvent('tutor-form-state-change',{detail:{id:'quiz'}}));await settle();assert.equal(w.document.querySelector('.bia-quiz-answered-count').textContent,'Answered 1 of 1 questions');d.window.close();
});

test('matching edits stay reactive after Tutor dirty state stopped emitting events',async()=>{
 const d=new JSDOM('<html lang="en"><form id="quiz" class="tutor-quiz-submission"><div class="tutor-quiz-progress-bar-wrapper"></div></form></html>',{runScripts:'outside-only',pretendToBeVisual:true});const w=d.window;
 w.eval(buildSync({stdin:{contents:"import Alpine from 'alpinejs'; window.Alpine=Alpine;",resolveDir:process.cwd()},bundle:true,write:false}).outputFiles[0].text);
 const values=w.Alpine.reactive({'attempt[1][quiz_question_ids][]':[1,2],'attempt[1][quiz_question][1]':'','attempt[1][quiz_question][2]':''});w.TutorCore={form:{hasForm:()=>true,getFormState:()=>({values:{...values}})}};
 w.eval(fs.readFileSync('assets/js/bia-quiz-ux.js','utf8'));await settle();w.dispatchEvent(new w.PageTransitionEvent('pagehide',{persisted:true}));w.dispatchEvent(new w.PageTransitionEvent('pageshow',{persisted:true}));values['attempt[1][quiz_question][1]']='matched';await settle();values['attempt[1][quiz_question][2]']='ordered';await settle();assert.equal(w.document.querySelector('.bia-quiz-answered-count').textContent,'Answered 2 of 2 questions');values['attempt[1][quiz_question][1]']='';await settle();assert.equal(w.document.querySelector('.bia-quiz-answered-count').textContent,'Answered 1 of 2 questions');d.window.close();
});
