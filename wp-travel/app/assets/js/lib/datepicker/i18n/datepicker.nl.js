;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.nl = {
        weekdays: {
            shorthand: ['zo', 'ma', 'di', 'wo', 'do', 'vr', 'za'],
            longhand: ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag']
        },
        months: {
            shorthand: ['Jan', 'Feb', 'Mrt', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
            longhand: ['Januari', 'Februari', 'Maart', 'April', 'Mei', 'Juni', 'Juli', 'Augustus', 'September', 'Oktober', 'November', 'December']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' tot ',
        weekAbbreviation: 'Wk',
        scrollTitle: 'Scroll om te wijzigen',
        toggleTitle: 'Klik om te wisselen',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Jaar',
        time_24hr: true
    };
})();
