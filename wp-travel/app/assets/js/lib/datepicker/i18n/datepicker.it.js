;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.it = {
        weekdays: {
            shorthand: ['Do', 'Lu', 'Ma', 'Me', 'Gi', 'Ve', 'Sa'],
            longhand: ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato']
        },
        months: {
            shorthand: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
            longhand: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' a ',
        weekAbbreviation: 'Sett',
        scrollTitle: 'Scorri per modificare',
        toggleTitle: 'Clicca per alternare',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Anno',
        time_24hr: false
    };
})();
