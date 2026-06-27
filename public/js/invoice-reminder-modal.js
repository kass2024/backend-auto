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
    const tzBadge = document.getElementById('neamee-reminder-tz-badge');
    const tzHint = document.getElementById('neamee-reminder-tz-hint');
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

    const tzShort = {
        'America/Chicago': 'CT',
        'Africa/Nairobi': 'EAT',
    };

    const tzLabels = {
        'America/Chicago': 'Bowling Green, KY (US Central)',
        'Africa/Nairobi': 'Nairobi, Kenya (EAT)',
    };

    function selectedUnit() {
        return form.querySelector('input[name="reminder_unit"]:checked')?.value || 'days';
    }

    function selectedRepeat() {
        return form.querySelector('input[name="next_service_repeat"]:checked')?.value || 'none';
    }

    function selectedTimezone() {
        return form.querySelector('input[name="next_service_timezone"]:checked')?.value || 'America/Chicago';
    }

    function pad(n) { return String(n).padStart(2, '0'); }

    function parseWallClock(value) {
        if (!value) return null;
        const normalized = value.trim().replace('T', ' ');
        const [datePart, timePart = '00:00:00'] = normalized.split(' ');
        const [y, m, d] = datePart.split('-').map(Number);
        const timeBits = timePart.split(':').map(Number);
        if (!y || !m || !d) return null;
        return {
            y, m, d,
            hh: timeBits[0] || 0,
            mm: timeBits[1] || 0,
            ss: timeBits[2] || 0,
        };
    }

    function wallClockToString(wc) {
        return `${wc.y}-${pad(wc.m)}-${pad(wc.d)} ${pad(wc.hh)}:${pad(wc.mm)}:${pad(wc.ss)}`;
    }

    function formatWallClock(wc) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        const dt = new Date(wc.y, wc.m - 1, wc.d, wc.hh, wc.mm);
        const dayName = days[dt.getDay()];
        const hour12 = ((wc.hh + 11) % 12) + 1;
        const ampm = wc.hh >= 12 ? 'PM' : 'AM';
        return `${dayName}, ${months[wc.m - 1]} ${wc.d}, ${wc.y}, ${hour12}:${pad(wc.mm)} ${ampm}`;
    }

    function subtractWallClock(wc, unit) {
        const copy = { ...wc };
        if (unit === 'minutes') copy.mm -= 5;
        else if (unit === 'hours') copy.hh -= 1;
        else {
            const dt = new Date(copy.y, copy.m - 1, copy.d);
            dt.setDate(dt.getDate() - 5);
            copy.y = dt.getFullYear();
            copy.m = dt.getMonth() + 1;
            copy.d = dt.getDate();
        }
        if (copy.mm < 0) { copy.mm += 60; copy.hh -= 1; }
        if (copy.hh < 0) { copy.hh += 24; copy.d -= 1; }
        return copy;
    }

    function addWallClockOffset(wc, unit, amount) {
        const copy = { ...wc };
        if (unit === 'minutes') copy.mm += amount;
        else if (unit === 'hours') copy.hh += amount;
        else {
            const dt = new Date(copy.y, copy.m - 1, copy.d, copy.hh, copy.mm);
            dt.setDate(dt.getDate() + amount);
            copy.y = dt.getFullYear();
            copy.m = dt.getMonth() + 1;
            copy.d = dt.getDate();
            copy.hh = dt.getHours();
            copy.mm = dt.getMinutes();
        }
        return copy;
    }

    function wallClockPartsInTimezone(date, timezone) {
        const fmt = new Intl.DateTimeFormat('en-US', {
            timeZone: timezone,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        });
        const parts = Object.fromEntries(fmt.formatToParts(date).map((p) => [p.type, p.value]));
        return {
            y: Number(parts.year),
            m: Number(parts.month),
            d: Number(parts.day),
            hh: Number(parts.hour === '24' ? '0' : parts.hour),
            mm: Number(parts.minute),
            ss: Number(parts.second),
        };
    }

    function nowInTimezone(timezone) {
        return wallClockPartsInTimezone(new Date(), timezone);
    }

    function todayStringInTimezone(timezone) {
        const p = nowInTimezone(timezone);
        return `${p.y}-${pad(p.m)}-${pad(p.d)}`;
    }

    function getServiceWallClock() {
        const raw = picker?.input?.value || datetimeInput.value;
        return parseWallClock(raw);
    }

    function setWallClock(wc) {
        const value = wallClockToString(wc);
        datetimeInput.value = value;
        if (picker) picker.setDate(value, false);
        scheduleModeInput.value = 'datetime';
        updatePreview();
    }

    function updateTimezoneUi() {
        const tz = selectedTimezone();
        const short = tzShort[tz] || 'TZ';
        if (tzBadge) tzBadge.textContent = short;
        if (tzHint) tzHint.textContent = `Times below are in ${tzLabels[tz] || tz}.`;
        if (picker) {
            picker.set('minDate', todayStringInTimezone(tz));
        }
    }

    function updatePreview() {
        const unit = selectedUnit();
        const repeat = selectedRepeat();
        const tz = selectedTimezone();
        offsetUnitLabel.textContent = unit;
        updateTimezoneUi();

        const service = getServiceWallClock();
        if (!service) {
            previewText.textContent = 'Choose a date to preview when alerts go out.';
            timeline.hidden = true;
            return;
        }

        const early = subtractWallClock(service, unit);
        const tzLabel = tzShort[tz] || tz;

        previewText.textContent = `Customer gets email + dashboard notification (${tzLabel} time).`;
        timeline.hidden = false;
        timelineEarly.textContent = `${formatWallClock(early)} ${tzLabel}`;
        timelineService.textContent = `${formatWallClock(service)} ${tzLabel}`;

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
        const tz = d.timezone || 'America/Chicago';

        form.querySelectorAll('input[name="next_service_timezone"]').forEach((input) => {
            input.checked = input.value === tz;
        });

        if (hasReminder && d.nextServiceAt) {
            const repeatTxt = d.repeat && d.repeat !== 'none' ? ` · Repeats: ${repeatLabels[d.repeat] || d.repeat}` : '';
            const tzTxt = d.timezoneLabel ? ` (${d.timezoneLabel})` : '';
            statusEl.hidden = false;
            statusEl.innerHTML = `<strong>Active:</strong> ${d.nextServiceAt}${tzTxt}${repeatTxt}`;
        } else {
            statusEl.hidden = false;
            statusEl.innerHTML = '<strong>Not saved yet</strong> — pick a time zone, date, and click Save reminder.';
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
            const wc = parseWallClock(d.nextServiceAt);
            if (wc) setWallClock(wc);
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
            minDate: todayStringInTimezone(selectedTimezone()),
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

    form.querySelectorAll('input[name="reminder_unit"], input[name="next_service_repeat"], input[name="next_service_timezone"]').forEach((input) => {
        input.addEventListener('change', updatePreview);
    });

    document.getElementById('neamee-reminder-presets')?.addEventListener('click', (event) => {
        const btn = event.target.closest('[data-preset]');
        if (!btn) return;
        const tz = selectedTimezone();
        const now = nowInTimezone(tz);
        const map = {
            tomorrow9: (() => {
                const wc = addWallClockOffset(now, 'days', 1);
                wc.hh = 9; wc.mm = 0; wc.ss = 0;
                return wc;
            })(),
            days7: addWallClockOffset(now, 'days', 7),
            days30: addWallClockOffset(now, 'days', 30),
            days90: addWallClockOffset(now, 'days', 90),
        };
        setTab('calendar');
        setWallClock(map[btn.dataset.preset]);
    });

    applyOffsetBtn?.addEventListener('click', () => {
        scheduleModeInput.value = 'offset';
        const tz = selectedTimezone();
        const now = nowInTimezone(tz);
        const amount = parseInt(offsetInput.value, 10) || 1;
        setWallClock(addWallClockOffset(now, selectedUnit(), amount));
        setTab('calendar');
    });

    form.addEventListener('submit', () => {
        if (activeTab !== 'quick' || scheduleModeInput.value !== 'offset') {
            scheduleModeInput.value = 'datetime';
            const wc = getServiceWallClock();
            if (wc) datetimeInput.value = wallClockToString(wc);
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
