(() => {
    const form = document.querySelector('[data-section-wizard]');
    if (!form) return;
    const sections = [...form.querySelectorAll('.ef-section')];
    if (!sections.length) return;
    const links = [...document.querySelectorAll('[data-step-link]')];
    const previous = form.querySelector('[data-step-previous]');
    const next = form.querySelector('[data-step-next]');
    const submit = document.getElementById('sendExpediente');
    const review = document.getElementById('updateReview');
    const label = document.getElementById('currentStepLabel');
    let current = sections.find(section=>section.querySelector('[role="alert"]')) || sections[0];
    let dirty = false;
    let invalidCycle = false;
    const visibleSections = () => sections.filter(section=>!section.hidden);
    function render(scroll = false) {
        const available = visibleSections();
        if (!available.includes(current)) current = available[0];
        sections.forEach(section=>section.classList.toggle('is-step-hidden',section!==current));
        links.forEach(link=>{
            const section = sections[Number(link.dataset.stepLink)];
            link.hidden = section.hidden;
            if(section===current) link.setAttribute('aria-current','step'); else link.removeAttribute('aria-current');
            const fields=[...section.querySelectorAll('.ef-question')].filter(field=>!field.hidden && field.dataset.required==='1');
            const complete=fields.length>0 && fields.every(field=>{
                const controls=[...field.querySelectorAll('input,select,textarea')];
                const answered=controls.some(control=>['radio','checkbox'].includes(control.type) ? control.checked : control.value.trim()!=='');
                return answered && controls.every(control=>!control.willValidate || control.validity.valid);
            });
            link.querySelector('i').hidden=!complete;
        });
        const index = available.indexOf(current);
        const last = index === available.length-1;
        previous.hidden = index <= 0;
        next.hidden = last || !current;
        submit.hidden = !last || !current;
        review.hidden = !last || !current;
        label.textContent = current ? 'Sección '+(index+1)+' de '+available.length : 'Sin preguntas disponibles';
        if(scroll && current){ current.scrollIntoView({behavior:'smooth',block:'start'}); current.querySelector('h2')?.focus({preventScroll:true}); }
    }
    links.forEach(link=>link.addEventListener('click',event=>{ event.preventDefault(); current=sections[Number(link.dataset.stepLink)]; render(true); }));
    previous.addEventListener('click',()=>{const available=visibleSections(); current=available[available.indexOf(current)-1] || current;render(true);});
    next.addEventListener('click',()=>{
        const invalid=[...current.querySelectorAll('input,select,textarea')].find(control=>control.willValidate && !control.validity.valid);
        if(invalid){invalid.reportValidity();return;}
        const available=visibleSections(); current=available[available.indexOf(current)+1] || current; render(true);
    });
    form.addEventListener('invalid',event=>{
        if (invalidCycle) return;
        invalidCycle = true;
        window.setTimeout(()=>{invalidCycle=false;},0);
        const section=event.target.closest('.ef-section');
        if(section && section!==current){current=section;render();}
    },true);
    form.addEventListener('expediente:updated',()=>render());
    form.addEventListener('input',()=>{dirty=true;});
    form.addEventListener('change',()=>{dirty=true;});
    form.addEventListener('submit',()=>{dirty=false;});
    window.addEventListener('beforeunload',event=>{if(dirty){event.preventDefault();event.returnValue='';}});
    render();
})();
