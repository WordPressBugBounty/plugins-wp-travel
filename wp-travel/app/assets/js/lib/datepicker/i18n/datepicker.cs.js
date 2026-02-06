;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.cs = {
        weekdays: {
            shorthand: ['Ne', 'Po', 'Út', 'St', 'Čt', 'Pá', 'So'],
            longhand: ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota']
        },
        months: {
            shorthand: ['Led', 'Úno', 'Bře', 'Dub', 'Kvě', 'Čvn', 'Čvc', 'Srp', 'Zář', 'Říj', 'Lis', 'Pro'],
            longhand: ['Leden', 'Únor', 'Březen', 'Duben', 'Květen', 'Červen', 'Červenec', 'Srpen', 'Září', 'Říjen', 'Listopad', 'Prosinec']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' – ',
        weekAbbreviation: 'Týd',
        scrollTitle: 'Rolujte pro změnu',
        toggleTitle: 'Klikněte pro přepnutí',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Rok',
        time_24hr: true
    };
})();
