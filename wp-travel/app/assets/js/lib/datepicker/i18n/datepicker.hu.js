;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.hu = {
        weekdays: {
            shorthand: ['V', 'H', 'K', 'Sz', 'Cs', 'P', 'Sz'],
            longhand: ['Vasárnap', 'Hétfő', 'Kedd', 'Szerda', 'Csütörtök', 'Péntek', 'Szombat']
        },
        months: {
            shorthand: ['Jan', 'Feb', 'Már', 'Ápr', 'Máj', 'Jún', 'Júl', 'Aug', 'Szep', 'Okt', 'Nov', 'Dec'],
            longhand: ['Január', 'Február', 'Március', 'Április', 'Május', 'Június', 'Július', 'Augusztus', 'Szeptember', 'Október', 'November', 'December']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' – ',
        weekAbbreviation: 'Hét',
        scrollTitle: 'Görgessen a változtatáshoz',
        toggleTitle: 'Kattintson a váltáshoz',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Év',
        time_24hr: true
    };
})();
