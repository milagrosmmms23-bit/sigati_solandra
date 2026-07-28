'use strict';
document.addEventListener('DOMContentLoaded',()=>{
 const sidebar=document.querySelector('#sidebar');
 document.querySelector('[data-toggle-sidebar]')?.addEventListener('click',()=>sidebar?.classList.toggle('open'));
 document.querySelector('[data-password-toggle]')?.addEventListener('click',e=>{const i=document.querySelector('#password');if(!i)return;i.type=i.type==='password'?'text':'password';e.currentTarget.textContent=i.type==='password'?'Ver':'Ocultar'});
 setTimeout(()=>document.querySelectorAll('[data-auto-dismiss]').forEach(x=>x.remove()),5000);
 const spec=document.querySelector('#specRows');
 document.querySelector('[data-add-spec]')?.addEventListener('click',()=>{spec?.insertAdjacentHTML('beforeend','<div class="spec-row"><input name="spec_key[]" placeholder="Ej. RAM"><input name="spec_value[]" placeholder="Ej. 16 GB"><button type="button" class="icon-btn danger" data-remove-row>×</button></div>')});
 document.addEventListener('click',e=>{if(e.target.matches('[data-remove-row]'))e.target.closest('.spec-row')?.remove();if(e.target.matches('[data-open-modal]'))document.getElementById(e.target.dataset.openModal)?.showModal();if(e.target.matches('[data-close-modal]'))e.target.closest('dialog')?.close()});
 document.querySelectorAll('[data-tab]').forEach(btn=>btn.addEventListener('click',()=>{document.querySelectorAll('[data-tab]').forEach(x=>x.classList.remove('active'));document.querySelectorAll('[data-pane]').forEach(x=>x.classList.remove('active'));btn.classList.add('active');document.querySelector(`[data-pane="${btn.dataset.tab}"]`)?.classList.add('active')}));
 const emp=document.querySelector('#employeeSelect');emp?.addEventListener('change',()=>{document.querySelector('#assignmentArea').value=emp.selectedOptions[0]?.dataset.area||''});
 const checks=[...document.querySelectorAll('[data-asset-check]')],count=document.querySelector('[data-selected-count]');const refresh=()=>{if(count)count.textContent=checks.filter(x=>x.checked).length};checks.forEach(x=>x.addEventListener('change',refresh));
 document.querySelector('[data-filter-assets]')?.addEventListener('input',e=>{const q=e.target.value.toLowerCase();document.querySelectorAll('[data-asset-row]').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(q)?'grid':'none')});
});
