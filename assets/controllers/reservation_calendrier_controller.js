import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';
import { French } from 'flatpickr/dist/l10n/fr.js';
import 'flatpickr/dist/flatpickr.min.css';

export default class extends Controller {
    static targets = ['debut', 'fin'];
    static values = { disabled: Array };

    connect() {
        const options = {
            locale: French,
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            disable: this.disabledValue,
            minDate: 'today',
        };

        this.finPicker = flatpickr(this.finTarget, options);
        this.debutPicker = flatpickr(this.debutTarget, {
            ...options,
            onChange: (selectedDates) => {
                if (selectedDates[0]) {
                    this.finPicker.set('minDate', selectedDates[0]);
                }
            },
        });
    }

    disconnect() {
        this.debutPicker?.destroy();
        this.finPicker?.destroy();
    }
}
