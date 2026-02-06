;(function () {
    if (typeof flatpickr === 'undefined') return;

    flatpickr.l10ns.zh = {
        weekdays: {
            shorthand: ['日', '一', '二', '三', '四', '五', '六'],
            longhand: ['周日', '周一', '周二', '周三', '周四', '周五', '周六']
        },
        months: {
            shorthand: ['一月', '二月', '三月', '四月', '五月', '六月', '七月', '八月', '九月', '十月', '十一月', '十二月'],
            longhand: ['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月']
        },
        firstDayOfWeek: 1,
        rangeSeparator: ' 至 ',
        weekAbbreviation: '周',
        scrollTitle: '滚动切换',
        toggleTitle: '点击切换',
        amPM: ['AM', 'PM'],
        yearAriaLabel: '年',
        time_24hr: true
    };
})();
