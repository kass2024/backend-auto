(function () {
    if (window.NeameeInvoiceReminder) return;

    const modal = document.getElementById('neamee-reminder-modal');
    if (!modal) return;

    const form = document.getElementById('neamee-reminder-form');
    const sendForm = document.getElementById('neamee-reminder-send-form');
    const clearForm = document.getElementById('neamee-reminder-clear-form');
    const titleEl = document.getElementById('neamee-reminder-title');
    const metaEl = document.getElementById('neamee-reminder-meta');
    const statusEl = document.getElementById('neamee-reminder-status');
    const previewText = document.getElementById('neamee-reminder-preview-text');
    const timeline = document.getElementById('neamee-reminder-timeline');
    const timelineEarly = document.getElementById('neamee-timeline-early-time');
    const timelineService = document.getElementById('neamee-timeline-service-time');
    const timelineRepeat = document.getElementById('neamee-timeline-repeat');
    const timelineRepeatLabel = document.getElementById('neamee-timeline-repeat-label');
    const scheduleModeInput = document.getElementById('neamee-reminder-schedule-mode');
    const notesInput = document.getElementById('neamee-reminder-notes');
    const offsetInput = document.getElementById('neamee-reminder-offset');
    const offsetUnitLabel = document.getElementById('neamee-reminder-offset-unit-label');
    const clearBtn = document.getElementById('neamee-reminder-clear-btn');
    const sendBtn = document.getElementById('neamee-reminder-send-btn');
    const applyOffsetBtn = document.getElementById('neamee-reminder-apply-offset');
    const datetimeInput = document.getElementById('neamee-reminder-datetime');
    const dialog = modal.querySelector('.neamee-reminder-modal__dialog');

    let picker = null;
    let activeTab = 'calendar';

    const repeatLabels = {
        none: 'One time only',
        weekly: 'Every week',
        biweekly: 'Every 2 weeks',
        monthly: 'Every month',
        quarterly: 'Every 3 months',
        yearly: 'Every year',
    };

    function selectedUnit() {
        return form.querySelector('input[name="reminder_unit"]:checked')?.value || 'days';
    }

    function selectedRepeat() {
        return form.querySelector('input[name="next_service_repeat"]:checked')?.value || 'none';
    }

    function formatDisplay(date) {
        return date.toLocaleString(undefined, {
            weekday: 'short', month: 'short', day: 'numeric', year: 'numeric',
            hour: 'numeric', minute: '2-digit',
        });
    }

    function pad(n) { return String(n).padStart(2, '0'); }

    function toFormValue(date) {
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:00`;
    }

    function getServiceDate() {
        if (picker?.selectedDates?.[0]) return picker.selectedDates[0];
        if (datetimeInput.value) return new Date(datetimeInput.value.replace(' ', 'T'));
        return null;
    }

    function addOffset(base, unit, amount) {
        const d = new Date(base);
        if (unit === 'minutes') d.setMinutes(d.getMinutes() + amount);
        else if (unit === 'hours') d.setHours(d.getHours() + amount);
        else d.setDate(d.getDate() + amount);
        return d;
    }

    function tomorrowAtNine() {
        const d = new Date();
        d.setDate(d.getDate() + 1);
        d.setHours(9, 0, 0, 0);
        return d;
    }

    function setDatetime(date) {
        if (picker) picker.setDate(date, true);
        else datetimeInput.value = toFormValue(date);
        scheduleModeInput.value = 'datetime';
        updatePreview();
    }

    function updatePreview() {
        const unit = selectedUnit();
        const repeat = selectedRepeat();
        offsetUnitLabel.textContent = unit;

        const serviceDate = getServiceDate();
        if (!serviceDate || Number.isNaN(serviceDate.getTime())) {
            previewText.textContent = 'Choose a date to preview when alerts go out.';
            timeline.hidden = true;
            return;
        }

        const early = new Date(serviceDate);
        if (unit === 'minutes') early.setMinutes(early.getMinutes() - 5);
        else if (unit === 'hours') early.setHours(early.getHours() - 1);
        else early.setDate(early.getDate() - 5);

        previewText.textContent = `Customer gets email + dashboard notification before each service.`;
        timeline.hidden = false;
        timelineEarly.textContent = formatDisplay(early);
        timelineService.textContent = formatDisplay(serviceDate);

        if (repeat !== 'none') {
            timelineRepeat.hidden = false;
            timelineRepeatLabel.textContent = repeatLabels[repeat] || repeat;
        } else {
            timelineRepeat.hidden = true;
        }
    }

    function setTab(tab) {
        activeTab = tab;
        modal.querySelectorAll('.neamee-reminder-tab').forEach((btn) => {
            btn.classList.toggle('is-active', btn.dataset.tab === tab);
        });
        modal.querySelectorAll('.neamee-reminder-tab-panel').forEach((panel) => {
            panel.classList.toggle('is-active', panel.dataset.panel === tab);
        });
    }

    function openFromButton(button) {
        const d = button.dataset;
        titleEl.textContent = d.invoiceNumber || 'Invoice';
        metaEl.textContent = [d.customerName, d.customerEmail, d.vehicle].filter(Boolean).join(' · ');

        form.action = d.storeUrl || '#';
        sendForm.action = d.sendUrl || '#';
        clearForm.action = d.clearUrl || '#';

        const hasReminder = d.hasReminder === '1';
        statusEl.hidden = !hasReminder;
        if (hasReminder && d.nextServiceAt) {
            const repeatTxt = d.repeat && d.repeat !== 'none' ? ` · Repeats: ${repeatLabels[d.repeat] || d.repeat}` : '';
            statusEl.innerHTML = `<strong>Active:</strong> ${d.nextServiceAt}${repeatTxt}`;
        } else {
            statusEl.hidden = false;
            statusEl.innerHTML = '<strong>Not saved yet</strong> — pick a date and click Save reminder.';
        }
        clearBtn.hidden = !hasReminder;
        sendBtn.hidden = !hasReminder;

        form.querySelectorAll('input[name="reminder_unit"]').forEach((input) => {
            input.checked = input.value === (d.reminderUnit || 'days');
        });
        form.querySelectorAll('input[name="next_service_repeat"]').forEach((input) => {
            input.checked = input.value === (d.repeat || 'none');
        });

        notesInput.value = d.notes || '';

        if (hasReminder && d.nextServiceAt) {
            const parsed = new Date(d.nextServiceAt.replace(' ', 'T'));
            if (!Number.isNaN(parsed.getTime())) setDatetime(parsed);
        } else {
            datetimeInput.value = '';
            if (picker) picker.clear();
            updatePreview();
        }

        setTab('calendar');
        modal.hidden = false;
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('neamee-reminder-modal-open');
        dialog.classList.remove('neamee-reminder-modal__dialog--closing');
        dialog.classList.add('neamee-reminder-modal__dialog--open');
        updatePreview();
    }

    function close() {
        dialog.classList.remove('neamee-reminder-modal__dialog--open');
        dialog.classList.add('neamee-reminder-modal__dialog--closing');
        setTimeout(() => {
            modal.hidden = true;
            modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('neamee-reminder-modal-open');
            dialog.classList.remove('neamee-reminder-modal__dialog--closing');
        }, 220);
    }

    function initPicker() {
        if (typeof flatpickr === 'undefined' || picker) return;

        picker = flatpickr(datetimeInput, {
            enableTime: true,
            dateFormat: 'Y-m-d H:i:S',
            altInput: true,
            altFormat: 'F j, Y \\a\\t h:i K',
            minDate: 'today',
            defaultHour: 9,
            defaultMinute: 0,
            minuteIncrement: 5,
            disableMobile: true,
            animate: true,
            onChange: () => {
                scheduleModeInput.value = 'datetime';
                updatePreview();
            },
        });
    }

    document.addEventListener('click', (event) => {
        const openBtn = event.target.closest('[data-reminder-open]');
        if (openBtn) {
            event.preventDefault();
            initPicker();
            openFromButton(openBtn);
            return;
        }
        if (event.target.closest('[data-reminder-close]')) close();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) close();
    });

    modal.querySelectorAll('.neamee-reminder-tab').forEach((btn) => {
        btn.addEventListener('click', () => setTab(btn.dataset.tab));
    });

    form.querySelectorAll('input[name="reminder_unit"], input[name="next_service_repeat"]').forEach((input) => {
        input.addEventListener('change', updatePreview);
    });

    document.getElementById('neamee-reminder-presets')?.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-preset]');
        if (!btn) return;
        const map = {
            tomorrow9: tomorrowAtNine(),
            days7: addOffset(new Date(), 'days', 7),
            days30: addOffset(new Date(), 'days', 30),
            days90: addOffset(new Date(), 'days', 90),
        };
        setTab('calendar');
        setDatetime(map[btn.dataset.preset]);
    });

    applyOffsetBtn?.addEventListener('click', () => {
        scheduleModeInput.value = 'offset';
        setDatetime(addOffset(new Date(), selectedUnit(), parseInt(offsetInput.value, 10) || 1));
        setTab('calendar');
    });

    form.addEventListener('submit', () => {
        if (activeTab !== 'quick' || scheduleModeInput.value !== 'offset') {
            scheduleModeInput.value = 'datetime';
            if (picker?.selectedDates?.[0]) {
                datetimeInput.value = toFormValue(picker.selectedDates[0]);
            }
        }
    });

    sendBtn?.addEventListener('click', () => {
        if (confirm('Send reminder now? Email + customer dashboard notification.')) sendForm.submit();
    });

    clearBtn?.addEventListener('click', () => {
        if (confirm('Clear this service reminder?')) clearForm.submit();
    });

    window.NeameeInvoiceReminder = { open: openFromButton, close };
})();
