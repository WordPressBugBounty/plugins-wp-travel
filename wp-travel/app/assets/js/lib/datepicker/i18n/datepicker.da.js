;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.da = {
        weekdays: {
            shorthand: ['Sø', 'Ma', 'Ti', 'On', 'To', 'Fr', 'Lø'],
            longhand: ['Søndag', 'Mandag', 'Tirsdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lørdag']
        },
        months: {
            shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
            longhand: ['Januar','Februar','Marts','April','Maj','Juni','Juli','August','September','Oktober','November','December']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' til ',
        weekAbbreviation: 'Uge',
        scrollTitle: 'Scroll for at ændre',
        toggleTitle: 'Klik for at skifte',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'År',
        time_24hr: true
    };
})();
