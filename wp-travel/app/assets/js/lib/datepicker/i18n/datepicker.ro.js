;(function () {
    if (typeof flatpickr === 'undefined') return;

    flatpickr.l10ns.ro = {
        weekdays: {
            shorthand: ['D', 'L', 'Ma', 'Mi', 'J', 'V', 'S'],
            longhand: ['Duminică', 'Luni', 'Marţi', 'Miercuri', 'Joi', 'Vineri', 'Sâmbătă']
        },
        months: {
            shorthand: ['Ian', 'Feb', 'Mar', 'Apr', 'Mai', 'Iun', 'Iul', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec'],
            longhand: ['Ianuarie','Februarie','Martie','Aprilie','Mai','Iunie','Iulie','August','Septembrie','Octombrie','Noiembrie','Decembrie']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' până la ',
        weekAbbreviation: 'Săpt',
        scrollTitle: 'Derulează pentru a schimba',
        toggleTitle: 'Click pentru a comuta',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'An',
        time_24hr: true
    };
})();
