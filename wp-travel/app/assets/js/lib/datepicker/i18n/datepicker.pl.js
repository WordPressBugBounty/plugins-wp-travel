;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.pl = {
        weekdays: {
            shorthand: ['Nd', 'Pn', 'Wt', 'Śr', 'Czw', 'Pt', 'So'],
            longhand: ['Niedziela', 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota']
        },
        months: {
            shorthand: ['Sty', 'Lut', 'Mar', 'Kwi', 'Maj', 'Cze', 'Lip', 'Sie', 'Wrz', 'Paź', 'Lis', 'Gru'],
            longhand: ['Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec','Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' do ',
        weekAbbreviation: 'Tydz',
        scrollTitle: 'Przewiń aby zmienić',
        toggleTitle: 'Kliknij aby przełączyć',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Rok',
        time_24hr: true
    };
})();
