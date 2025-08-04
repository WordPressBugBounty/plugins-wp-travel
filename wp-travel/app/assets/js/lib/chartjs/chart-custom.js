
jQuery(document).ready(function ($) {

	let bookingChart;
    let originalTotalRevenue = $('.main-chart .total-sales div big').html();
	function renderChart(chartData) {
        
        const chartDataa = {
            stat_label: chartData.stat_label,
            data: [
                {
                    label: 'Bookings',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    pointBackgroundColor: 'rgb(192, 75, 95)',
                    pointBorderColor: 'rgb(192, 75, 95)',
                    fill: true,
                    tension: 0.3,
                    data: chartData.data
                }
            ]
        };

		const ctx = document.getElementById("wp-travel-booking-canvas").getContext("2d");

		// If chart exists, destroy it first
		if (bookingChart) {
			bookingChart.destroy();
		}

		const config = {
			type: 'line',
			data: {
				labels: chartDataa.stat_label,
				datasets: chartDataa.data
			},
			options: {
				responsive: true,
				title: {
					display: true,
					text: wp_travel_chart_data.chart_title
				},
				tooltips: {
					mode: 'index',
					intersect: false,
				},
				hover: {
					mode: 'nearest',
					intersect: true
				},
				scales: {
					xAxes: [{
						display: true,
						scaleLabel: {
							display: false
						}
					}],
					yAxes: [{
						display: true,
						scaleLabel: {
							display: false
						}
					}]
				}
			}
		};

		bookingChart = new Chart(ctx, config);
	}

	// Submit handler
	$('.stat-toolbar-form').on('submit', function (e) {
		e.preventDefault();
        $('.main-chart .data-wrapper').hide();
        $('.main-chart .loader-wrapper').css( 'display', 'flex' );
       

		const fromDate = $('input[name="booking_stat_from"]').val();
		const toDate = $('input[name="booking_stat_to"]').val();
		const interval = $('select[name="booking_intervals"]').val();
		const nonce = $('input[name="_nonce"]').val();

		$.ajax({
			type: 'POST',
			url: wp_travel_chart_data.ajax_url,
			data: {
				action: 'wptravel_get_booking_data_stat',
				booking_stat_from: fromDate,
				booking_stat_to: toDate,
				booking_intervals: interval,
                _nonce: wp_travel_chart_data._nonce
			},
			success: function (response) {
				if (response.success) {
					renderChart(response.data.stat_data);
                    if( interval !== 'all-time' ){
                        $('.main-chart .total-sales div big').html(response.data.total_revenue);
                    }else{
                        $('.main-chart .total-sales div big').html(originalTotalRevenue);
                    }

                    $('.main-chart .total-bookings big').text(response.data.max_bookings);
                    $('.main-chart .top-destination strong').html(response.data.top_destination);
                    $('.main-chart .top-trip strong').html(response.data.top_trip);
                    $('.top-trips .top-bottom tbody').html(response.data.top_10_trips);
                    $('.top-trips .bottom-top tbody').html(response.data.low_10_trips);
                    $('.top-destinations .top-bottom tbody').html(response.data.top_10_destinations);
                    $('.top-destinations .bottom-top tbody').html(response.data.low_10_destinations);

                    $('.main-chart .loader-wrapper').css( 'display', 'none' );
                    $('.main-chart .data-wrapper').show();
				} else {
					alert('Failed to load chart data');
				}
			},
			error: function (xhr) {
				alert('AJAX error');
				console.error(xhr.responseText);
			}
		});
	});

	// 🟢 Optionally trigger chart on initial load:
	$('.stat-toolbar-form').submit();
});



// var config = {
//     type: 'line',
//     data: {
//         labels: JSON.parse(wp_travel_chart_data.labels),
//         datasets: JSON.parse(wp_travel_chart_data.datasets)
//     },
//     options: {
//         responsive: true,
//         title: {
//             display: true,
//             text: wp_travel_chart_data.chart_title
//         },
//         tooltips: {
//             mode: 'index',
//             intersect: false,
//         },
//         hover: {
//             mode: 'nearest',
//             intersect: true
//         },
//         scales: {
//             xAxes: [{
//                 display: true,
//                 scaleLabel: {
//                     display: false,
//                     labelString: 'Year'
//                 }
//             }],
//             yAxes: [{
//                 display: true,
//                 scaleLabel: {
//                     display: false,
//                     labelString: 'Value'
//                 }
//             }]
//         }
//     }
// };

// window.onload = function() {
//     var ctx = document.getElementById("wp-travel-booking-canvas").getContext("2d");
//     window.myLine = new Chart(ctx, config);
// };

jQuery(document).ready(function($) {

    if( $('#interval-selection').val() === 'custom' ){
        $('.date-picker').show();
    }

    $('#interval-selection').on('change', function() {
        var selectedValue = $(this).val();

        if (selectedValue === 'custom') {
            $('.date-picker').show();
        } else {
            $('.date-picker').hide();
        }
    });

    $('.form-compare-stat .datepicker-from').val(wp_travel_chart_data.booking_stat_from);
    $('.form-compare-stat .datepicker-to').val(wp_travel_chart_data.booking_stat_to);

    $('.form-compare-stat .datepicker-from').wpt_datepicker({
        language: 'en',
        maxDate: new Date(),
        onSelect: function(dateStr) {
            newMinDate = null;
            newMaxDate = new Date();
            $('.form-compare-stat .datepicker-to').removeAttr('required');
            if ('' !== dateStr) {
                $('.form-compare-stat .datepicker-to').attr('required', 'required');
                new_date_min = new Date(dateStr);
                new_date_max = new Date(dateStr);

                newMinDate = new Date(new_date_min.setDate(new Date(new_date_min.getDate() + 1)));

                maxDate = new Date(new_date_max.setMonth(new Date(new_date_max.getMonth() + 1)));
                if (maxDate < newMaxDate) {
                    newMaxDate = maxDate;
                }
            }
            $('.form-compare-stat .datepicker-to').wpt_datepicker({
                minDate: newMinDate,
                maxDate: newMaxDate,
            });
        }
    }).attr('readonly', 'readonly');

    $('.form-compare-stat .datepicker-to').wpt_datepicker({
        language: 'en',
        maxDate: new Date(),
        onSelect: function(dateStr) {
            newMinDate = new Date();
            newMaxDate = null;
            $('.form-compare-stat .datepicker-from').removeAttr('required');
            if ('' !== dateStr) {
                $('.form-compare-stat .datepicker-from').attr('required', 'required');
                new_date_min = new Date(dateStr);
                new_date_max = new Date(dateStr);

                newMinDate = new Date(new_date_max.setMonth(new Date(new_date_max.getMonth() - 1)));
                newMaxDate = new Date(new_date_min.setDate(new Date(new_date_min.getDate() - 1)));

            }
            $('.form-compare-stat .datepicker-from').wpt_datepicker({
                minDate: newMinDate,
                maxDate: newMaxDate,
            });
        }

    }).attr('readonly', 'readonly');


    $('.stat-toolbar-form .dashicons-calendar-alt, .stat-toolbar-form .field-label').on('click', function() {
        $(this).closest('.field-group').children('.form-control').focus();
    });

    // Show more link on top country
    var showChar = wp_travel_chart_data.show_char;
    var ellipsestext = "..";
    var moretext = wp_travel_chart_data.show_more_text;
    var lesstext = wp_travel_chart_data.show_less_text;
    $('.wp-travel-more').each(function() {
        var content = $(this).html();

        if (content.length > showChar) {

            var c = content.substr(0, showChar);
            var h = content.substr(showChar, content.length - showChar);

            var html = c + '<span class="moreellipses">' + ellipsestext + '&nbsp;</span><span class="morecontent"><span>' + h + '</span>&nbsp;&nbsp;<a href="" class="morelink">' + moretext + '</a></span>';

            $(this).html(html);
        }

    });

    $(".morelink").click(function() {
        if ($(this).hasClass("less")) {
            $(this).removeClass("less");
            $(this).html(moretext);
        } else {
            $(this).addClass("less");
            $(this).html(lesstext);
        }
        $(this).parent().prev().toggle();
        $(this).prev().toggle();
        return false;
    });


})