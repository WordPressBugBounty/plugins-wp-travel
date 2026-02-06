;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.fr = {
        weekdays: {
            shorthand: ['Di', 'Lu', 'Ma', 'Me', 'Je', 'Ve', 'Sa'],
            longhand: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi']
        },
        months: {
            shorthand: ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'],
            longhand: ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' à ',
        weekAbbreviation: 'Sem',
        scrollTitle: 'Faire défiler pour modifier',
        toggleTitle: 'Cliquer pour basculer',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Année',
        time_24hr: true
    };
})();
