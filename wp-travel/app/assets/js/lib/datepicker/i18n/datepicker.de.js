;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.de = {
        weekdays: {
            shorthand: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            longhand: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag']
        },
        months: {
            shorthand: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
            longhand: ['Januar','Februar','März','April','Mai','Juni','Juli','August','September','Oktober','November','Dezember']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' bis ',
        weekAbbreviation: 'KW',
        scrollTitle: 'Scrollen zum Ändern',
        toggleTitle: 'Klicken zum Umschalten',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Jahr',
        time_24hr: true
    };
})();
