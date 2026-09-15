/**
 * Vanilla-JS month calendar + time-slot picker + booking form for the
 * turf-installation estimate scheduler. No external calendar library —
 * fetches availability from scheduler/availability.php and submits to
 * scheduler/book.php (or scheduler/reschedule.php in reschedule mode).
 */
(function () {
    'use strict';

    var root = document.querySelector('[data-scheduler]');
    if (!root) {
        return;
    }

    var mode = root.dataset.mode || 'book';
    var token = root.dataset.token || '';
    var endpoint = mode === 'reschedule' ? '/scheduler/reschedule.php' : '/scheduler/book.php';

    var calendarEl = root.querySelector('[data-scheduler-calendar]');
    var slotsEl = root.querySelector('[data-scheduler-slots]');
    var formEl = root.querySelector('[data-scheduler-form]');
    var confirmEl = root.querySelector('[data-scheduler-confirm]');
    var cancelledEl = root.querySelector('[data-scheduler-cancelled]');
    var errorEl = root.querySelector('[data-scheduler-error]');
    var bookingEl = root.querySelector('[data-scheduler-booking]');
    var slotStartInput = formEl.querySelector('[name="slot_start"]');
    var selectedSlotLabel = root.querySelector('[data-selected-slot-label]');
    var submitBtn = formEl.querySelector('[type="submit"]');
    var submitDefaultLabel = submitBtn.textContent;

    var today = new Date();
    var viewYear = today.getFullYear();
    var viewMonth = today.getMonth();

    var activeDayBtn = null;
    var activeSlotBtn = null;

    function pad(n) { return n < 10 ? '0' + n : '' + n; }
    function monthKey(y, m) { return y + '-' + pad(m + 1); }
    function dateKey(y, m, d) { return y + '-' + pad(m + 1) + '-' + pad(d); }

    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.hidden = false;
    }
    function clearError() {
        errorEl.hidden = true;
        errorEl.textContent = '';
    }

    function renderCalendar(days) {
        calendarEl.innerHTML = '';

        var monthLabel = new Date(viewYear, viewMonth, 1).toLocaleDateString('en-US', { month: 'long', year: 'numeric' });
        var header = document.createElement('div');
        header.className = 'scheduler-cal__header';

        var prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'scheduler-cal__nav';
        prevBtn.setAttribute('aria-label', 'Previous month');
        prevBtn.textContent = '←';

        var monthSpan = document.createElement('span');
        monthSpan.className = 'scheduler-cal__month';
        monthSpan.textContent = monthLabel;

        var nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'scheduler-cal__nav';
        nextBtn.setAttribute('aria-label', 'Next month');
        nextBtn.textContent = '→';

        header.appendChild(prevBtn);
        header.appendChild(monthSpan);
        header.appendChild(nextBtn);
        calendarEl.appendChild(header);

        var grid = document.createElement('div');
        grid.className = 'scheduler-cal__grid';
        ['S', 'M', 'T', 'W', 'T', 'F', 'S'].forEach(function (d) {
            var el = document.createElement('div');
            el.className = 'scheduler-cal__dow';
            el.textContent = d;
            grid.appendChild(el);
        });

        var firstOfMonth = new Date(viewYear, viewMonth, 1);
        var startWeekday = firstOfMonth.getDay();
        var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

        for (var i = 0; i < startWeekday; i++) {
            var blank = document.createElement('div');
            blank.className = 'scheduler-cal__day scheduler-cal__day--blank';
            grid.appendChild(blank);
        }

        for (var d = 1; d <= daysInMonth; d++) {
            var key = dateKey(viewYear, viewMonth, d);
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'scheduler-cal__day';
            btn.textContent = String(d);
            if (days[key]) {
                btn.classList.add('scheduler-cal__day--open');
                btn.addEventListener('click', (function (dateStr, btnEl) {
                    return function () { selectDate(dateStr, btnEl); };
                })(key, btn));
            } else {
                btn.disabled = true;
            }
            grid.appendChild(btn);
        }
        calendarEl.appendChild(grid);

        prevBtn.addEventListener('click', function () {
            viewMonth--;
            if (viewMonth < 0) { viewMonth = 11; viewYear--; }
            loadMonth();
        });
        nextBtn.addEventListener('click', function () {
            viewMonth++;
            if (viewMonth > 11) { viewMonth = 0; viewYear++; }
            loadMonth();
        });
    }

    function loadMonth() {
        clearError();
        slotsEl.hidden = true;
        formEl.hidden = true;
        fetch('/scheduler/availability.php?month=' + monthKey(viewYear, viewMonth))
            .then(function (r) { return r.json(); })
            .then(function (data) { renderCalendar(data.days || {}); })
            .catch(function () {
                showError('Could not load the calendar. Please try again or call us at ' + root.dataset.phone + '.');
            });
    }

    function selectDate(dateStr, btnEl) {
        clearError();
        if (activeDayBtn) { activeDayBtn.classList.remove('scheduler-cal__day--selected'); }
        activeDayBtn = btnEl;
        btnEl.classList.add('scheduler-cal__day--selected');

        slotsEl.hidden = false;
        slotsEl.innerHTML = '<p class="scheduler-slots__loading">Loading times…</p>';
        formEl.hidden = true;

        var url = '/scheduler/availability.php?date=' + dateStr;
        if (token) { url += '&token=' + encodeURIComponent(token); }

        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var slots = data.slots || [];
                slotsEl.innerHTML = '';
                if (!slots.length) {
                    var empty = document.createElement('p');
                    empty.className = 'scheduler-slots__empty';
                    empty.textContent = 'No times left that day — pick another.';
                    slotsEl.appendChild(empty);
                    return;
                }
                var heading = document.createElement('p');
                heading.className = 'scheduler-slots__heading';
                heading.textContent = 'Pick a time:';
                slotsEl.appendChild(heading);

                var wrap = document.createElement('div');
                wrap.className = 'scheduler-slots__grid';
                slots.forEach(function (slot) {
                    var b = document.createElement('button');
                    b.type = 'button';
                    b.className = 'scheduler-slots__btn';
                    b.textContent = slot.label;
                    b.addEventListener('click', function () { selectSlot(slot, b); });
                    wrap.appendChild(b);
                });
                slotsEl.appendChild(wrap);
            })
            .catch(function () { showError('Could not load times for that day. Please try again.'); });
    }

    function selectSlot(slot, btnEl) {
        if (activeSlotBtn) { activeSlotBtn.classList.remove('scheduler-slots__btn--selected'); }
        activeSlotBtn = btnEl;
        btnEl.classList.add('scheduler-slots__btn--selected');
        slotStartInput.value = slot.value;
        if (selectedSlotLabel) { selectedSlotLabel.textContent = slot.label; }
        formEl.hidden = false;
        formEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    var errorMessages = {
        slot_unavailable: 'That time was just booked by someone else — please pick another.',
        missing_fields: 'Please fill in all the required fields.',
        invalid_email: 'Please enter a valid email address.',
        already_cancelled: 'This appointment has already been cancelled.',
        not_found: 'We could not find that appointment.'
    };

    formEl.addEventListener('submit', function (e) {
        e.preventDefault();
        clearError();
        if (!slotStartInput.value) {
            showError('Please pick a date and time first.');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.textContent = 'Booking…';

        var fd = new FormData(formEl);
        if (token) { fd.set('token', token); }

        fetch(endpoint, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    showError(errorMessages[data.error] || 'Something went wrong. Please try again or call us.');
                    submitBtn.disabled = false;
                    submitBtn.textContent = submitDefaultLabel;
                    return;
                }
                bookingEl.hidden = true;
                confirmEl.hidden = false;
                var whenEl = confirmEl.querySelector('[data-confirm-when]');
                if (whenEl) { whenEl.textContent = data.when || ''; }
            })
            .catch(function () {
                showError('Something went wrong submitting that. Please try again or call us.');
                submitBtn.disabled = false;
                submitBtn.textContent = submitDefaultLabel;
            });
    });

    var cancelBtn = root.querySelector('[data-scheduler-cancel]');
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            if (!window.confirm('Cancel this appointment?')) { return; }
            clearError();
            var fd = new FormData();
            fd.set('token', token);
            fd.set('action', 'cancel');
            fetch(endpoint, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        bookingEl.hidden = true;
                        if (cancelledEl) { cancelledEl.hidden = false; }
                    } else {
                        showError(errorMessages[data.error] || 'Could not cancel — please call us instead.');
                    }
                })
                .catch(function () { showError('Could not cancel — please call us instead.'); });
        });
    }

    loadMonth();
})();
