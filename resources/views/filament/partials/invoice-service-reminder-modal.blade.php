<div id="neamee-reminder-modal" class="neamee-reminder-modal" hidden aria-hidden="true">
    <div class="neamee-reminder-modal__backdrop" data-reminder-close>
        <span class="neamee-reminder-orb neamee-reminder-orb--1"></span>
        <span class="neamee-reminder-orb neamee-reminder-orb--2"></span>
        <span class="neamee-reminder-orb neamee-reminder-orb--3"></span>
    </div>

    <div class="neamee-reminder-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="neamee-reminder-title">
        <div class="neamee-reminder-modal__glow"></div>
        <div class="neamee-reminder-modal__shine"></div>

        <div class="neamee-reminder-modal__header">
            <div class="neamee-reminder-modal__header-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div class="neamee-reminder-modal__header-text">
                <p class="neamee-reminder-modal__eyebrow">Service reminder scheduler</p>
                <h2 id="neamee-reminder-title" class="neamee-reminder-modal__title">Invoice</h2>
                <p class="neamee-reminder-modal__meta" id="neamee-reminder-meta"></p>
            </div>
            <button type="button" class="neamee-reminder-modal__close" data-reminder-close aria-label="Close">&times;</button>
        </div>

        <div class="neamee-reminder-modal__status" id="neamee-reminder-status" hidden></div>

        <form id="neamee-reminder-form" method="post" action="#">
            @csrf
            <input type="hidden" name="schedule_mode" id="neamee-reminder-schedule-mode" value="datetime">

            <div class="neamee-reminder-modal__section">
                <label class="neamee-reminder-modal__label">Alert timing</label>
                <div class="neamee-reminder-unit-pills">
                    <label class="neamee-reminder-unit-pill">
                        <input type="radio" name="reminder_unit" value="days" checked>
                        <span class="neamee-reminder-unit-pill__body">
                            <strong>Days</strong>
                            <small>5 days before + service day</small>
                        </span>
                    </label>
                    <label class="neamee-reminder-unit-pill">
                        <input type="radio" name="reminder_unit" value="hours">
                        <span class="neamee-reminder-unit-pill__body">
                            <strong>Hours</strong>
                            <small>1 hour before</small>
                        </span>
                    </label>
                    <label class="neamee-reminder-unit-pill">
                        <input type="radio" name="reminder_unit" value="minutes">
                        <span class="neamee-reminder-unit-pill__body">
                            <strong>Minutes</strong>
                            <small>5 minutes before</small>
                        </span>
                    </label>
                </div>
            </div>

            <div class="neamee-reminder-modal__section">
                <label class="neamee-reminder-modal__label">Repeat</label>
                <div class="neamee-reminder-repeat-grid">
                    <label class="neamee-reminder-repeat-chip">
                        <input type="radio" name="next_service_repeat" value="none" checked>
                        <span>Once</span>
                    </label>
                    <label class="neamee-reminder-repeat-chip">
                        <input type="radio" name="next_service_repeat" value="weekly">
                        <span>Weekly</span>
                    </label>
                    <label class="neamee-reminder-repeat-chip">
                        <input type="radio" name="next_service_repeat" value="biweekly">
                        <span>Every 2 weeks</span>
                    </label>
                    <label class="neamee-reminder-repeat-chip">
                        <input type="radio" name="next_service_repeat" value="monthly">
                        <span>Monthly</span>
                    </label>
                    <label class="neamee-reminder-repeat-chip">
                        <input type="radio" name="next_service_repeat" value="quarterly">
                        <span>Quarterly</span>
                    </label>
                    <label class="neamee-reminder-repeat-chip">
                        <input type="radio" name="next_service_repeat" value="yearly">
                        <span>Yearly</span>
                    </label>
                </div>
            </div>

            <div class="neamee-reminder-modal__tabs">
                <button type="button" class="neamee-reminder-tab is-active" data-tab="calendar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    Smart calendar
                </button>
                <button type="button" class="neamee-reminder-tab" data-tab="quick">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" width="16" height="16"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Quick pick
                </button>
            </div>

            <div class="neamee-reminder-tab-panel is-active" data-panel="calendar">
                <label class="neamee-reminder-modal__label" for="neamee-reminder-datetime">Service date &amp; time</label>
                <div class="neamee-reminder-datetime-wrap">
                    <input type="text" id="neamee-reminder-datetime" name="next_service_at" placeholder="Tap to open calendar…" autocomplete="off" readonly>
                </div>
            </div>

            <div class="neamee-reminder-tab-panel" data-panel="quick">
                <div class="neamee-reminder-presets" id="neamee-reminder-presets">
                    <button type="button" data-preset="tomorrow9">Tomorrow<br><small>9:00 AM</small></button>
                    <button type="button" data-preset="days7">In 7 days</button>
                    <button type="button" data-preset="days30">In 30 days</button>
                    <button type="button" data-preset="days90">In 3 months</button>
                </div>
                <div class="neamee-reminder-offset-row">
                    <input type="number" id="neamee-reminder-offset" name="offset_amount" min="1" value="7" class="neamee-reminder-offset-input">
                    <span id="neamee-reminder-offset-unit-label">days</span>
                    <span class="neamee-reminder-offset-from">from now</span>
                    <button type="button" class="neamee-reminder-apply-offset" id="neamee-reminder-apply-offset">Apply</button>
                </div>
            </div>

            <div class="neamee-reminder-preview" id="neamee-reminder-preview">
                <div class="neamee-reminder-preview__head">
                    <span class="neamee-reminder-preview__pulse"></span>
                    <strong>Live preview</strong>
                </div>
                <p id="neamee-reminder-preview-text">Choose a date to preview reminder times.</p>
                <div class="neamee-reminder-timeline" id="neamee-reminder-timeline" hidden>
                    <div class="neamee-reminder-timeline__item" id="neamee-timeline-early">
                        <span class="neamee-reminder-timeline__dot"></span>
                        <div><em>Early alert</em><b id="neamee-timeline-early-time">—</b></div>
                    </div>
                    <div class="neamee-reminder-timeline__item" id="neamee-timeline-service">
                        <span class="neamee-reminder-timeline__dot neamee-reminder-timeline__dot--main"></span>
                        <div><em>Service</em><b id="neamee-timeline-service-time">—</b></div>
                    </div>
                    <div class="neamee-reminder-timeline__item" id="neamee-timeline-repeat" hidden>
                        <span class="neamee-reminder-timeline__dot neamee-reminder-timeline__dot--repeat"></span>
                        <div><em>Then repeats</em><b id="neamee-timeline-repeat-label">—</b></div>
                    </div>
                </div>
            </div>

            <div class="neamee-reminder-modal__section">
                <label class="neamee-reminder-modal__label" for="neamee-reminder-notes">Customer notes (optional)</label>
                <textarea id="neamee-reminder-notes" name="next_service_notes" rows="2" placeholder="Oil change, tire rotation, inspection…"></textarea>
            </div>

            <div class="neamee-reminder-modal__footer">
                <button type="button" class="neamee-reminder-btn neamee-reminder-btn--danger" id="neamee-reminder-clear-btn" hidden>Clear</button>
                <button type="button" class="neamee-reminder-btn neamee-reminder-btn--secondary" id="neamee-reminder-send-btn" hidden>Send now</button>
                <div class="neamee-reminder-modal__footer-spacer"></div>
                <button type="button" class="neamee-reminder-btn neamee-reminder-btn--ghost" data-reminder-close>Cancel</button>
                <button type="submit" class="neamee-reminder-btn neamee-reminder-btn--primary neamee-reminder-btn--shine">Save reminder</button>
            </div>
        </form>

        <form id="neamee-reminder-send-form" method="post" action="#" hidden>@csrf</form>
        <form id="neamee-reminder-clear-form" method="post" action="#" hidden>@csrf @method('DELETE')</form>
    </div>
</div>
