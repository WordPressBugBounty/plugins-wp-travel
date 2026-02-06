;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.no = {
        weekdays: {
            shorthand: ['Sø', 'Ma', 'Ti', 'On', 'To', 'Fr', 'Lø'],
            longhand: ['Søndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag']
        },
        months: {
            shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Des'],
            longhand: ['Januar','Februar','Mars','April','Mai','Juni','Juli','August','September','Oktober','November','Desember']
        },
        firstDayOfWeek: 0,
        rangeSeparator: ' til ',
        weekAbbreviation: 'Uke',
        scrollTitle: 'Rull for å endre',
        toggleTitle: 'Klikk for å bytte',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'År',
        time_24hr: true
    };
})();
