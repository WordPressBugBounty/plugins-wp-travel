;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.es = {
        weekdays: {
            shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
            longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado']
        },
        months: {
            shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            longhand: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' a ',
        weekAbbreviation: 'Sem',
        scrollTitle: 'Desplazar para cambiar',
        toggleTitle: 'Haga clic para alternar',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Año',
        time_24hr: false
    };
})();
