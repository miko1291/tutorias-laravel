(() => {
    function init(form) {
    if (!form) return;
    const fields = [...form.querySelectorAll('.ef-question')];
    const byKey = new Map(fields.map(field => [field.dataset.key, field]));
    const answers = key => {
        const field = byKey.get(key);
        if (!field) return [];
        return [...field.querySelectorAll('input,select,textarea')].filter(control => !['radio','checkbox'].includes(control.type) || control.checked).map(control => control.value).filter(Boolean);
    };
    const visible = (field, seen = []) => {
        if (!field || seen.includes(field.dataset.key)) return false;
        const condition = JSON.parse(field.dataset.condition || 'null');
        if (!condition) return true;
        const parent = byKey.get(condition.clave);
        const next = [...seen, field.dataset.key];
        const primary = visible(parent, next) && answers(condition.clave).includes(condition.valor);
        const alternative = condition.alternativa && visible(byKey.get(condition.alternativa), next) && answers(condition.alternativa).includes(condition.valor);
        return Boolean(primary || alternative);
    };
    function update() {
        let required = 0;
        let completed = 0;
        fields.forEach(field => {
            const show = visible(field);
            field.hidden = !show;
            field.querySelectorAll('input,select,textarea').forEach(control => { control.disabled = !show; });
            const checkbox = field.querySelector('input[type="checkbox"]');
            const answered = answers(field.dataset.key).length > 0;
            if (checkbox) checkbox.setCustomValidity(show && field.dataset.required === '1' && !answered ? 'Selecciona al menos una opción.' : '');
            const valid = [...field.querySelectorAll('input,select,textarea')].every(control => !control.willValidate || control.validity.valid);
            if (show && field.dataset.required === '1') { required++; if (answered && valid) completed++; }
        });
        form.querySelectorAll('.ef-section').forEach(section => { section.hidden = ![...section.querySelectorAll('.ef-question')].some(field => !field.hidden); });
        const percent = required ? Math.round(completed / required * 100) : 100;
        const progressLabel = form.closest('.ef-document')?.querySelector('#formProgressLabel');
        const progressBar = form.closest('.ef-document')?.querySelector('#formProgressBar');
        if (progressLabel) progressLabel.textContent = `${completed} de ${required} preguntas obligatorias · ${percent}%`;
        if (progressBar) { progressBar.style.width = `${percent}%`; progressBar.parentElement.setAttribute('aria-valuenow', String(percent)); }
        form.dispatchEvent(new CustomEvent('expediente:updated'));
    }
    form.addEventListener('input', update);
    form.addEventListener('change', update);
    form.addEventListener('reset', () => window.setTimeout(update, 0));
    update();
    }
    window.ExpedienteForm = {init};
    init(document.getElementById('actualizacionForm'));
})();
