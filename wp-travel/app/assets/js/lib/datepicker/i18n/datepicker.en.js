;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.en = {
        weekdays: {
            shorthand: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],
            longhand: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
        },
        months: {
            shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            longhand: ['January','February','March','April','May','June','July','August','September','October','November','December']
        },
        firstDayOfWeek: 0,
        rangeSeparator: ' to ',
        weekAbbreviation: 'Wk',
        scrollTitle: 'Scroll to increment',
        toggleTitle: 'Click to toggle',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Year',
        time_24hr: false
    };
})();
