;(function () {
    if (typeof flatpickr === 'undefined') {
        return;
    }

    flatpickr.l10ns.fi = {
        weekdays: {
            shorthand: ['Su', 'Ma', 'Ti', 'Ke', 'To', 'Pe', 'La'],
            longhand: ['Sunnuntai', 'Maanantai', 'Tiistai', 'Keskiviikko', 'Torstai', 'Perjantai', 'Lauantai']
        },
        months: {
            shorthand: ['Tammi', 'Helmi', 'Maalis', 'Huhti', 'Touko', 'Kesä', 'Heinä', 'Elo', 'Syys', 'Loka', 'Marras', 'Joulu'],
            longhand: ['Tammikuu','Helmikuu','Maaliskuu','Huhtikuu','Toukokuu','Kesäkuu','Heinäkuu','Elokuu','Syyskuu','Lokakuu','Marraskuu','Joulukuu']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' – ',
        weekAbbreviation: 'Vk',
        scrollTitle: 'Selaa muuttaaksesi',
        toggleTitle: 'Klikkaa vaihtaaksesi',
        amPM: ['AM', 'PM'],
        yearAriaLabel: 'Vuosi',
        time_24hr: true
    };
})();
