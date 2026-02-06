;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.pt = {
        weekdays: {
            shorthand: ['Do', 'Se', 'Te', 'Qa', 'Qi', 'Sx', 'Sa'],
            longhand: ['Domingo', 'Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado']
        },
        months: {
            shorthand: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
            longhand: ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' até ',
        weekAbbreviation: 'Sem',
        scrollTitle: 'Role para alterar',
        toggleTitle: 'Clique para alternar',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Ano',
        time_24hr: true
    };
})();
