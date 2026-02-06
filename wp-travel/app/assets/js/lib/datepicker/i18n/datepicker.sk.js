;(function () {
    if (typeof flatpickr === 'undefined') return;

    flatpickr.l10ns.sk = {
        weekdays: {
            shorthand: ['Ne', 'Po', 'Ut', 'St', 'Št', 'Pi', 'So'],
            longhand: ['Nedeľa', 'Pondelok', 'Utorok', 'Streda', 'Štvrtok', 'Piatok', 'Sobota']
        },
        months: {
            shorthand: ['Jan', 'Feb', 'Mar', 'Apr', 'Máj', 'Jún', 'Júl', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'],
            longhand: ['Január','Február','Marec','Apríl','Máj','Jún','Júl','August','September','Október','November','December']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' do ',
        weekAbbreviation: 'Týž',
        scrollTitle: 'Prejdite pre zmenu',
        toggleTitle: 'Kliknite pre prepnutie',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Rok',
        time_24hr: true
    };
})();
