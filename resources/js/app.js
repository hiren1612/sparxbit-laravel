import './bootstrap';

// Custom date picker (port of the original React DatePicker): a popover calendar
// that respects the user's week-start preference and greys out days that aren't
// selectable (a specific weekday and/or before a minimum date). Writes the chosen
// ISO date back to a Livewire property via $wire.set so validation + preview update.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('sparxDatePicker', (opts) => ({
        field: opts.field,
        allowedDow: opts.allowedDow === undefined ? null : opts.allowedDow, // 0=Sun..6=Sat or null
        minISO: opts.minISO || '',
        sundayFirst: !!opts.sundayFirst,
        placeholder: opts.placeholder || 'Pick a date',
        open: false,
        selected: '',
        viewYear: 0,
        viewMonth: 0,
        months: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],

        init() {
            this.selected = this.$wire.get(this.field) || '';
            const d = this.selected ? this.parse(this.selected) : new Date();
            this.viewYear = d.getFullYear();
            this.viewMonth = d.getMonth();
        },
        parse(iso) {
            const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(iso);
            return m ? new Date(+m[1], +m[2] - 1, +m[3]) : new Date();
        },
        toISO(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        },
        display() {
            if (!this.selected) return this.placeholder;
            const m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(this.selected);
            return m ? m[3] + '-' + m[2] + '-' + m[1] : this.selected;
        },
        hasValue() {
            return !!this.selected;
        },
        get dayLabels() {
            return this.sundayFirst ? ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] : ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'];
        },
        get monthLabel() {
            return this.months[this.viewMonth] + ' ' + this.viewYear;
        },
        get leadBlanks() {
            const s = new Date(this.viewYear, this.viewMonth, 1).getDay();
            return this.sundayFirst ? s : (s === 0 ? 6 : s - 1);
        },
        get daysInMonth() {
            return new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
        },
        get days() {
            const out = [];
            for (let day = 1; day <= this.daysInMonth; day++) {
                const dt = new Date(this.viewYear, this.viewMonth, day);
                const iso = this.toISO(dt);
                const disabled = (this.allowedDow !== null && dt.getDay() !== this.allowedDow)
                    || (this.minISO && iso < this.minISO);
                out.push({ day, iso, disabled, selected: iso === this.selected });
            }
            return out;
        },
        pick(d) {
            if (d.disabled) return;
            this.selected = d.iso;
            this.$wire.set(this.field, d.iso);
            this.open = false;
        },
        shift(delta) {
            let m = this.viewMonth + delta, y = this.viewYear;
            if (m < 0) { m = 11; y--; }
            if (m > 11) { m = 0; y++; }
            this.viewMonth = m;
            this.viewYear = y;
        },
        clear() {
            this.selected = '';
            this.$wire.set(this.field, '');
            this.open = false;
        },
        goToday() {
            const t = new Date();
            this.selected = this.toISO(t);
            this.$wire.set(this.field, this.selected);
            this.viewMonth = t.getMonth();
            this.viewYear = t.getFullYear();
            this.open = false;
        },
        get showToday() {
            return this.allowedDow === null;
        },
    }));
});
