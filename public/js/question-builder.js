(() => {
    const root = document.getElementById('expedienteDesigner');
    if (!root) return;
    const types = {text:'Texto corto', textarea:'Párrafo', email:'Correo electrónico', tel:'Teléfono', number:'Número', date:'Fecha', select:'Lista desplegable', radio:'Una opción', checkbox:'Varias opciones'};
    const choiceTypes = ['select', 'radio', 'checkbox'];
    const copy = value => JSON.parse(JSON.stringify(value));
    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[char]));
    const uid = () => 'pregunta_' + Date.now().toString(36) + Math.random().toString(36).slice(2,10);
    const doc = document.getElementById('designDocument');
    const inspector = document.getElementById('questionInspector');
    const outline = document.getElementById('sectionOutline');
    const status = document.getElementById('designStatus');
    const errorBox = document.getElementById('designError');
    const archiveDialog = document.getElementById('archiveDialog');
    let revision, state, selected, saved, preview = false, saving = false;
    let undoStack = [], redoStack = [], lastEdit = {key:null,time:0};
    let dragged = null;

    function load(data) {
        revision = data.revision;
        state = {sections:[], archived:copy(data.archivadas), deleted:[]};
        state.archived.forEach(question => { question.configuracion = Array.isArray(question.configuracion) ? {} : (question.configuracion || {}); });
        data.preguntas.forEach(question => {
            let section = state.sections.find(item => item.title === question.seccion);
            if (!section) { section = {title:question.seccion, questions:[]}; state.sections.push(section); }
            question.configuracion = Array.isArray(question.configuracion) ? {} : (question.configuracion || {});
            section.questions.push(copy(question));
        });
        selected = state.sections.flatMap(section => section.questions).find(question => question.clave === selected)?.clave || state.sections[0]?.questions[0]?.clave;
        saved = JSON.stringify(state);
        undoStack = []; redoStack = [];
    }
    load(window.expedienteDesign);
    const questions = () => state.sections.flatMap(section => section.questions);
    const current = () => questions().find(question => question.clave === selected);
    const sectionOf = key => state.sections.find(section => section.questions.some(question => question.clave === key));
    const icon = (action, symbol, label, extras = '') => `<button type="button" class="df-icon" data-action="${action}" title="${esc(label)}" aria-label="${esc(label)}" ${extras}><i class="bi bi-${symbol}" aria-hidden="true"></i></button>`;
    function record(key = null) {
        const now = Date.now();
        if (!key || lastEdit.key !== key || now - lastEdit.time > 800) undoStack.push(copy(state));
        if (undoStack.length > 50) undoStack.shift();
        redoStack = []; lastEdit = {key,time:now};
    }
    function updateStatus() {
        status.textContent = saving ? 'Guardando diseño…' : JSON.stringify(state) === saved ? 'Diseño guardado' : 'Cambios sin guardar';
        root.querySelector('[data-action="undo"]').disabled = !undoStack.length || saving;
        root.querySelector('[data-action="redo"]').disabled = !redoStack.length || saving;
        document.getElementById('archiveCount').textContent = state.archived.length;
    }
    function fieldPreview(question, enabled = false) {
        const settings = question.configuracion || {};
        const disabled = enabled ? '' : ' disabled';
        const name = `respuestas[${question.clave}]`;
        const attributes = (enabled && question.requerido ? ' required' : '') + ['min','max','maxlength','pattern'].filter(key=>settings[key]).map(key=>` ${key}="${esc(settings[key] === 'today' ? new Date().toISOString().slice(0,10) : settings[key])}"`).join('');
        const options = question.opciones || [];
        if (choiceTypes.includes(question.tipo)) {
            if (question.tipo === 'select') return `<select class="ef-control" name="${name}"${disabled}${attributes}><option value="">Selecciona una opción</option>${options.map(option => `<option value="${esc(option)}">${esc(settings.etiquetas?.[option] ?? option)}</option>`).join('')}</select>`;
            return `<div class="ef-options">${options.map((option,index) => `<label class="ef-option"><input type="${question.tipo}" name="${name}${question.tipo === 'checkbox' ? '[]' : ''}" value="${esc(option)}"${disabled}${enabled && question.requerido && question.tipo === 'radio' && index === 0 ? ' required' : ''}><span>${esc(settings.etiquetas?.[option] ?? option)}</span></label>`).join('')}</div>`;
        }
        if (question.tipo === 'textarea') return `<textarea class="ef-control" name="${name}" rows="3" placeholder="${esc(settings.placeholder || 'Escribe tu respuesta')}"${disabled}${attributes}></textarea>`;
        return `<input class="ef-control" type="${esc(question.tipo)}" name="${name}" placeholder="${esc(settings.placeholder || 'Escribe tu respuesta')}"${disabled}${attributes}>`;
    }
    function renderOutline() {
        outline.innerHTML = state.sections.map((section,index) => `<a href="#design-section-${index}" class="${section === sectionOf(selected) ? 'is-active' : ''}">${index+1}. ${esc(section.title)}<small class="d-block">${section.questions.length} ${section.questions.length === 1 ? 'pregunta' : 'preguntas'}</small></a>`).join('');
    }
    function card(question, index) {
        const active = question.clave === selected;
        const managedMunicipalities = Boolean(question.configuracion?.municipios_por_estado);
        const required = question.requerido ? '<span class="ef-required">*</span>' : '';
        if (preview) return `<div class="ef-question" data-key="${question.clave}" data-required="${question.requerido ? 1:0}" data-condition="${esc(JSON.stringify(question.configuracion?.condicion || null))}" ${managedMunicipalities ? `data-municipios="${esc(JSON.stringify(question.configuracion.municipios_por_estado))}" data-estado-clave="${esc(question.configuracion.estado_clave)}" data-etiquetas="${esc(JSON.stringify(question.configuracion.etiquetas || {}))}"` : ''}><label class="ef-question-title">${esc(question.titulo)} ${required}</label>${question.descripcion ? `<p class="ef-description">${esc(question.descripcion)}</p>`:''}${fieldPreview(question,true)}</div>`;
        const options = (question.opciones || []).map((option,optionIndex) => `<div class="df-option-row"><i class="bi bi-${question.tipo === 'checkbox' ? 'square' : 'circle'}"></i><input class="df-option-input" aria-label="Opción ${optionIndex+1}" data-prop="option" data-index="${optionIndex}" value="${esc(question.configuracion?.etiquetas?.[option] ?? option)}">${icon('option-up','arrow-up','Subir opción',`data-index="${optionIndex}"`)}${icon('option-down','arrow-down','Bajar opción',`data-index="${optionIndex}"`)}${icon('remove-option','x','Quitar opción',`data-index="${optionIndex}"`)}</div>`).join('');
        return `<article class="ef-question df-question ${active ? 'is-selected':''} ${!question.activo ? 'is-hidden':''}" data-key="${question.clave}">
            <div class="df-card-heading"><button type="button" draggable="true" class="df-icon df-drag" aria-label="Arrastrar pregunta" title="Arrastrar pregunta"><i class="bi bi-grip-vertical"></i></button><small>${index+1} · ${esc(types[question.tipo])}${!question.activo ? ' · Oculta':''}</small><div>${icon('move-up','arrow-up','Subir pregunta')}${icon('move-down','arrow-down','Bajar pregunta')}</div></div>
            ${active ? `<textarea class="df-title-input" data-prop="titulo" aria-label="Título de la pregunta" rows="2" maxlength="255">${esc(question.titulo)}</textarea><div class="df-type-row"><label class="sr-only" for="questionType">Tipo de respuesta</label><select id="questionType" data-prop="tipo" ${managedMunicipalities ? 'disabled' : ''}>${Object.entries(types).map(([value,label])=>`<option value="${value}" ${question.tipo === value?'selected':''}>${label}</option>`).join('')}</select></div><textarea class="df-description-input" data-prop="descripcion" aria-label="Descripción de la pregunta" placeholder="Añadir descripción o instrucciones" rows="2" maxlength="2000">${esc(question.descripcion || '')}</textarea>${managedMunicipalities ? `<div class="alert alert-info">Los municipios se filtran según el estado seleccionado. ${root.dataset.catalogsUrl ? `<a href="${esc(root.dataset.catalogsUrl)}?lista=${encodeURIComponent(question.clave)}">Editar municipios por estado</a>` : 'La administración puede editar estas listas desde Catálogos.'}</div>` : choiceTypes.includes(question.tipo) ? `<div class="df-option-editor">${options}<button type="button" class="df-add-option" data-action="add-option">+ Añadir opción</button></div>` : fieldPreview(question)}
            <div class="df-card-bottom"><div>${icon('duplicate','files','Duplicar pregunta')}${icon('remove-question','archive','Archivar pregunta')}${icon('toggle-visible',question.activo?'eye':'eye-slash',question.activo?'Ocultar pregunta':'Mostrar pregunta')}${icon('tools','sliders','Herramientas de pregunta')}</div><label class="df-switch"><input type="checkbox" data-prop="requerido" ${question.requerido?'checked':''}> Obligatoria</label></div>` : `<button type="button" class="df-summary-title" data-action="select">${esc(question.titulo)} ${required}</button>${question.descripcion?`<p class="ef-description">${esc(question.descripcion)}</p>`:''}${fieldPreview(question)}<div class="df-card-state mt-3">${question.configuracion?.condicion ? 'Pregunta condicional · ':''}Haz clic en el título para editar</div>`}
        </article>`;
    }
    function renderInspector() {
        const question = current();
        if (!question) { inspector.innerHTML = '<h2>Herramientas</h2><p class="text-muted">Selecciona una pregunta para editarla.</p>'; return; }
        const settings = question.configuracion || {};
        const condition = settings.condicion || {};
        const parent = questions().find(item => item.clave === condition.clave);
        const parentOptions = parent?.opciones || [];
        inspector.innerHTML = `${icon('tools','x-lg','Cerrar herramientas','data-close-tools')}<h2>Herramientas de pregunta</h2><p class="df-inspector-title">${esc(question.titulo)}</p>
            <label for="questionSection">Sección</label><select id="questionSection" data-prop="seccion">${state.sections.map((section,index)=>`<option value="${index}" ${section === sectionOf(selected)?'selected':''}>${esc(section.title)}</option>`).join('')}</select>
            <label for="questionPlaceholder">Texto de ayuda dentro del campo</label><input id="questionPlaceholder" data-setting="placeholder" value="${esc(settings.placeholder || '')}" placeholder="Ej. Escribe tu respuesta" maxlength="255">
            <details ${condition.clave?'open':''}><summary>Mostrar según una respuesta</summary><small>Muestra esta pregunta solo cuando se cumpla la condición.</small>
            <label for="conditionParent">Pregunta anterior</label><select id="conditionParent" data-setting="condition-parent"><option value="">Mostrar siempre</option>${questions().filter(item=>item.clave!==question.clave).map(item=>`<option value="${item.clave}" ${condition.clave===item.clave?'selected':''}>${esc(item.titulo)}</option>`).join('')}</select>
            ${condition.clave ? `<label for="conditionValue">La respuesta debe ser</label>${parentOptions.length ? `<select id="conditionValue" data-setting="condition-value">${parentOptions.map(value=>`<option value="${esc(value)}" ${condition.valor===value?'selected':''}>${esc(parent.configuracion?.etiquetas?.[value] ?? value)}</option>`).join('')}</select>` : `<input id="conditionValue" data-setting="condition-value" value="${esc(condition.valor || '')}" placeholder="Respuesta que activa la pregunta">`}`:''}</details>
            <details><summary>Límites de respuesta</summary>${['text','textarea','email','tel'].includes(question.tipo) ? `<label for="questionMaxLength">Máximo de caracteres</label><input id="questionMaxLength" type="number" min="1" max="10000" data-setting="maxlength" value="${esc(settings.maxlength || '')}" placeholder="Sin límite adicional">` : ['number','date'].includes(question.tipo) ? `<label for="questionMin">Mínimo</label><input id="questionMin" type="${question.tipo === 'date'?'date':'number'}" data-setting="min" value="${esc(settings.min || '')}"><label for="questionMax">Máximo</label><input id="questionMax" type="${question.tipo === 'date'?'date':'number'}" data-setting="max" value="${esc(settings.max === 'today' ? new Date().toISOString().slice(0,10) : settings.max || '')}">` : '<small>Para este tipo de pregunta, edita sus opciones directamente en la tarjeta.</small>'}</details>
            ${choiceTypes.includes(question.tipo) && !settings.municipios_por_estado ? `<details><summary>Agregar varias opciones</summary><label for="bulkOptions">Una opción por línea</label><textarea id="bulkOptions" rows="5" placeholder="Opción A&#10;Opción B"></textarea><button type="button" class="btn btn-outline-primary btn-sm mt-2" data-action="bulk-options">Añadir opciones</button></details>`:''}
            <details><summary>Organización</summary><button type="button" class="btn btn-light btn-sm mt-2" data-action="duplicate">Duplicar pregunta</button><button type="button" class="btn btn-light btn-sm mt-2" data-action="toggle-visible">${question.activo?'Ocultar del expediente':'Mostrar en el expediente'}</button><small>Ocultar o archivar conserva las respuestas anteriores.</small></details>`;
    }
    function render(focusKey = null) {
        if (!current()) selected = questions()[0]?.clave;
        root.classList.toggle('is-preview',preview);
        document.getElementById('previewNotice').hidden = !preview;
        document.getElementById('previewLabel').textContent = preview ? 'Volver a editar' : 'Vista de alumno';
        doc.innerHTML = `<div class="ef-cover"><span class="ef-eyebrow">ITSVA · Programa Institucional de Tutorías</span><h1>Expediente del estudiante</h1><p>Tu información es confidencial y se utiliza para el acompañamiento educativo. Responde con sinceridad.</p></div>${preview?'<div class="ef-identity"><div><small>Estudiante</small><strong>Nombre del estudiante</strong></div><div><small>Matrícula</small><strong>Matrícula del estudiante</strong></div></div>':''}<${preview?'form':'div'} id="${preview?'designerPreview':'designSections'}" data-no-loading>${state.sections.map((section,index)=>`<section class="ef-section df-section" id="design-section-${index}" data-section="${index}"><div class="df-section-head"><div class="ef-section-heading">${preview?`<h2>${esc(section.title)}</h2>`:`<input class="df-section-title" data-section-title="${index}" aria-label="Título de la sección ${index+1}" value="${esc(section.title)}" maxlength="120"><div class="df-section-actions"><small>${section.questions.length} ${section.questions.length === 1 ? 'pregunta' : 'preguntas'}</small><div>${icon('section-up','arrow-up','Subir sección',`data-section="${index}"`)}${icon('section-down','arrow-down','Bajar sección',`data-section="${index}"`)}${icon('add-question','plus-lg','Añadir pregunta aquí',`data-section="${index}"`)}${icon('remove-section','archive','Archivar sección',`data-section="${index}"`)}</div></div>`}</div></div><div class="df-question-list" data-section="${index}">${section.questions.filter(question=>!preview || question.activo).map(card).join('')}${!section.questions.length&&!preview?'<div class="df-empty">Añade una pregunta a esta sección.</div>':''}</div></section>`).join('')}</${preview?'form':'div'}>`;
        renderOutline();
        renderInspector();
        updateStatus();
        if (preview) { const form = document.getElementById('designerPreview'); form.addEventListener('submit',event=>event.preventDefault()); window.ExpedienteForm?.init(form); }
        if (focusKey) {
            const article = [...doc.querySelectorAll('.df-question')].find(item=>item.dataset.key===focusKey);
            article?.scrollIntoView({behavior:'smooth',block:'center'});
            article?.querySelector('.df-title-input')?.focus({preventScroll:true});
        }
    }
    function newQuestion(section) {
        return {id:null, clave:uid(), titulo:'Pregunta sin título', descripcion:'', tipo:'text', seccion:section.title, seccion_orden:1, orden:1, opciones:[], requerido:false, activo:true, configuracion:{}};
    }
    function archiveQuestion(question) {
        if (question.id) {
            if (!state.deleted.includes(question.id)) state.deleted.push(question.id);
            if (!state.archived.some(item=>item.id===question.id)) state.archived.push(copy(question));
        }
        sectionOf(question.clave).questions = sectionOf(question.clave).questions.filter(item=>item.clave!==question.clave);
        questions().forEach(item=>{
            const condition = item.configuracion?.condicion;
            if (condition?.clave===question.clave || condition?.alternativa===question.clave) delete item.configuracion.condicion;
        });
    }
    function archiveList() {
        document.getElementById('archiveList').innerHTML = state.archived.length ? state.archived.map(question=>`<div class="df-archive-item"><div><strong>${esc(question.titulo)}</strong><small>${esc(question.seccion)}</small></div><button type="button" class="btn btn-outline-primary btn-sm" data-action="restore" data-id="${question.id}">Recuperar</button></div>`).join('') : '<p class="text-muted">No hay preguntas archivadas.</p>';
    }
    async function save() {
        if (saving) return;
        errorBox.hidden = true;
        if (state.sections.some(section=>!section.questions.length)) { errorBox.textContent='Añade una pregunta a cada sección o elimina las secciones vacías.'; errorBox.hidden=false; return; }
        saving = true; root.inert = true; updateStatus();
        try {
            const response = await fetch(root.dataset.saveUrl,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({revision,secciones:state.sections.map(section=>({titulo:section.title,preguntas:section.questions})),eliminadas:state.deleted})});
            const data = await response.json();
            if (!response.ok) throw new Error(data.errors ? [...new Set(Object.values(data.errors).flat())].slice(0,5).join(' ') : data.message || 'No se pudo guardar el diseño.');
            load(data); render();
        } catch (error) { errorBox.textContent=error.message; errorBox.hidden=false; errorBox.scrollIntoView({behavior:'smooth',block:'center'}); }
        finally { saving=false; root.inert=false; updateStatus(); }
    }
    root.addEventListener('click', event => {
        const button = event.target.closest('[data-action]');
        if (!button || saving) return;
        const action = button.dataset.action;
        const article = button.closest('[data-key]');
        if (article) selected = article.dataset.key;
        const question = current();
        const section = sectionOf(selected);
        const sectionIndex = Number(button.dataset.section ?? state.sections.indexOf(section));
        const optionIndex = Number(button.dataset.index);
        if (action==='save') { save(); return; }
        if (action==='preview') { preview=!preview; render(); return; }
        if (action==='tools') { root.classList.toggle('tools-open'); renderInspector(); return; }
        if (action==='select') { render(); return; }
        if (action==='archive') { archiveList(); archiveDialog.showModal(); return; }
        if (action==='close-archive') { archiveDialog.close(); return; }
        if (action==='undo' || action==='redo') {
            const source = action==='undo'?undoStack:redoStack;
            const target = action==='undo'?redoStack:undoStack;
            if (source.length) { target.push(copy(state)); state=source.pop(); render(); }
            return;
        }
        if (action==='remove-question' && !window.confirm('¿Archivar esta pregunta? Sus respuestas anteriores se conservarán.')) return;
        if (action==='remove-section' && !window.confirm('¿Archivar esta sección y sus preguntas? Sus respuestas anteriores se conservarán.')) return;
        record();
        if (action==='add-question') {
            let target = state.sections[sectionIndex] || state.sections.at(-1);
            if (!target) { target={title:'Nueva sección',questions:[]}; state.sections.push(target); }
            const added = newQuestion(target);
            const position = target.questions.findIndex(item=>item.clave===selected);
            target.questions.splice(position < 0 ? target.questions.length : position+1,0,added);
            selected=added.clave; render(selected); return;
        }
        if (action==='add-section') {
            let title='Nueva sección', number=2;
            while(state.sections.some(item=>item.title===title)) title=`Nueva sección ${number++}`;
            const added={title,questions:[]}; added.questions.push(newQuestion(added));
            state.sections.push(added); selected=added.questions[0].clave; render(selected); return;
        }
        if (action==='duplicate' && question) { const added=copy(question); added.id=null; added.clave=uid(); added.titulo+=' (copia)'; section.questions.splice(section.questions.indexOf(question)+1,0,added); selected=added.clave; }
        if (action==='remove-question' && question) { const index=section.questions.indexOf(question); archiveQuestion(question); selected=section.questions[Math.min(index,section.questions.length-1)]?.clave || questions()[0]?.clave; }
        if (action==='toggle-visible' && question) question.activo=!question.activo;
        if ((action==='move-up'||action==='move-down') && question) {
            const index=section.questions.indexOf(question), offset=action==='move-up'?-1:1;
            if (section.questions[index+offset]) [section.questions[index],section.questions[index+offset]]=[section.questions[index+offset],section.questions[index]];
            else { const adjacent=state.sections[state.sections.indexOf(section)+offset]; if(adjacent) {section.questions.splice(index,1); if(offset<0) adjacent.questions.push(question); else adjacent.questions.unshift(question);} }
        }
        if (action==='section-up'||action==='section-down') { const offset=action==='section-up'?-1:1; if(state.sections[sectionIndex+offset]) [state.sections[sectionIndex],state.sections[sectionIndex+offset]]=[state.sections[sectionIndex+offset],state.sections[sectionIndex]]; }
        if (action==='remove-section') { [...state.sections[sectionIndex].questions].forEach(archiveQuestion); state.sections.splice(sectionIndex,1); selected=questions()[0]?.clave; }
        if (action==='add-option' && question) { let label=`Opción ${question.opciones.length+1}`; while(question.opciones.includes(label)) label+=' nueva'; question.opciones.push(label); }
        if (action==='remove-option' && question) question.opciones.splice(optionIndex,1);
        if ((action==='option-up'||action==='option-down') && question) {const offset=action==='option-up'?-1:1; if(question.opciones[optionIndex+offset]!==undefined) [question.opciones[optionIndex],question.opciones[optionIndex+offset]]=[question.opciones[optionIndex+offset],question.opciones[optionIndex]];}
        if (action==='bulk-options' && question) {const values=document.getElementById('bulkOptions').value.split(/\r?\n/).map(value=>value.trim()).filter(Boolean); question.opciones=[...new Set([...question.opciones,...values])];}
        if (action==='restore') {
            const restored=state.archived.find(item=>item.id===Number(button.dataset.id));
            if(restored) {let target=state.sections.find(item=>item.title===restored.seccion); if(!target){target={title:restored.seccion,questions:[]};state.sections.push(target);} target.questions.push(copy(restored)); state.archived=state.archived.filter(item=>item.id!==restored.id); state.deleted=state.deleted.filter(id=>id!==restored.id); selected=restored.clave; archiveList(); archiveDialog.close();}
        }
        render();
    });
    function edit(event) {
        if (saving) return;
        const input=event.target;
        if (!input.matches('[data-prop],[data-setting],[data-section-title]')) return;
        const article=input.closest('[data-key]');
        if(article) selected=article.dataset.key;
        const question=current();
        const field=input.dataset.prop || input.dataset.setting || 'section-title';
        record(`${selected}:${field}`);
        if(input.hasAttribute('data-section-title')) {state.sections[Number(input.dataset.sectionTitle)].title=input.value;renderOutline();updateStatus();return;}
        if(!question) return;
        question.configuracion ||= {};
        if(input.dataset.prop==='seccion') { const source=sectionOf(selected); source.questions=source.questions.filter(item=>item.clave!==selected); state.sections[Number(input.value)].questions.push(question); render(); return; }
        if(input.dataset.prop==='option') {
            const index=Number(input.dataset.index), value=question.opciones[index];
            if(question.id) {question.configuracion.etiquetas ||= {};question.configuracion.etiquetas[value]=input.value;}
            else {question.opciones[index]=input.value; if(question.configuracion.etiquetas) delete question.configuracion.etiquetas[value];}
        } else if(input.dataset.prop) question[input.dataset.prop]=input.type==='checkbox'?input.checked:input.value;
        else if(input.dataset.setting==='condition-parent') {
            const parent=questions().find(item=>item.clave===input.value);
            if(parent) question.configuracion.condicion={clave:parent.clave,valor:parent.opciones[0] || ''}; else delete question.configuracion.condicion;
            renderInspector();
        } else if(input.dataset.setting==='condition-value') question.configuracion.condicion.valor=input.value;
        else if(input.value==='') delete question.configuracion[input.dataset.setting];
        else question.configuracion[input.dataset.setting]=input.dataset.setting==='maxlength'?Number(input.value):input.value;
        if(input.dataset.prop==='tipo') {if(choiceTypes.includes(question.tipo) && !question.opciones.length) question.opciones=['Opción 1']; render();}
        else if(input.type==='checkbox') {render();}
        else {const title=inspector.querySelector('.df-inspector-title');if(title) title.textContent=question.titulo;updateStatus();}
    }
    root.addEventListener('input',event=>{if(!['checkbox','select-one'].includes(event.target.type)) edit(event);});
    root.addEventListener('change',event=>{if(['checkbox','select-one'].includes(event.target.type)) edit(event);});
    root.addEventListener('dragstart',event=>{
        if(!event.target.closest('.df-drag')) {event.preventDefault();return;}
        const article=event.target.closest('[data-key]');dragged=article.dataset.key;selected=dragged;article.classList.add('is-dragging');event.dataTransfer.effectAllowed='move';event.dataTransfer.setData('text/plain',dragged);
    });
    root.addEventListener('dragover',event=>{if(dragged && event.target.closest('.df-question-list')){event.preventDefault();event.dataTransfer.dropEffect='move';}});
    root.addEventListener('drop',event=>{
        const list=event.target.closest('.df-question-list');if(!dragged||!list)return;event.preventDefault();
        const question=questions().find(item=>item.clave===dragged),targetArticle=event.target.closest('.df-question');
        if(targetArticle?.dataset.key===dragged)return;
        record();const source=sectionOf(dragged);source.questions=source.questions.filter(item=>item.clave!==dragged);const target=state.sections[Number(list.dataset.section)];
        let index=targetArticle?target.questions.findIndex(item=>item.clave===targetArticle.dataset.key):target.questions.length;
        if(targetArticle && event.clientY>targetArticle.getBoundingClientRect().top+targetArticle.getBoundingClientRect().height/2) index++;
        target.questions.splice(Math.max(0,index),0,question);dragged=null;render();
    });
    root.addEventListener('dragend',()=>{dragged=null;root.querySelectorAll('.is-dragging').forEach(item=>item.classList.remove('is-dragging'));});
    window.addEventListener('beforeunload',event=>{if(JSON.stringify(state)!==saved){event.preventDefault();event.returnValue='';}});
    document.addEventListener('keydown',event=>{
        if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='s'){event.preventDefault();save();}
        if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='z'&&!event.target.matches('input,textarea')){event.preventDefault();root.querySelector(`[data-action="${event.shiftKey?'redo':'undo'}"]`).click();}
    });
    render();
})();

