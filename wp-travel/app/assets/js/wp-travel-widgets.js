function GetConvertedPrice( price ) {
    var conversionRate = 'undefined' !== typeof wp_travel && 'undefined' !== typeof wp_travel.conversion_rate ? wp_travel.conversion_rate : 1;
    var _toFixed       = 'undefined' !== typeof wp_travel && 'undefined' !== typeof wp_travel.number_of_decimals ? wp_travel.number_of_decimals : 2;
    conversionRate     = parseFloat( conversionRate ).toFixed( 2 );
    return parseFloat( price * conversionRate ).toFixed( _toFixed );
}


document.addEventListener('DOMContentLoaded', function () {

    setTimeout(function() {
		document.querySelectorAll('.woocommerce-order-received header, .woocommerce-order-received footer, .woocommerce-order-received #wpadminbar, .woocommerce-order-received main div').forEach(function(el) {
			el.style.visibility = 'visible';
		});
	}, 1000);

	const iframe = document.getElementById('wp-travel-woo-checkout-frame');

	if (!iframe) return;

	// Reusable function to apply styles and logic
	const applyIframeCustomizations = () => {
		try {
			const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
			const iframeWindow = iframe.contentWindow;

			if (!iframeDoc || !iframeDoc.body) {
				console.warn('Iframe content not accessible yet.');
				return;
			}

            const style = iframeDoc.createElement('style');
            style.id = 'custom-iframe-style';
            style.textContent = `
                .woocommerce-checkout.woocommerce-page #wpadminbar,
                .woocommerce-checkout.woocommerce-page footer,
                .woocommerce-checkout.woocommerce-page header,
                .woo-onpage-enable #wpadminbar,
                .woo-onpage-enable footer,
                .woo-onpage-enable header {
                    display: none !important;
                }
                .woocommerce-order-received main div {
                    visibility: hidden !important;
                }
                .woo-onpage-enable #newBookingDetails br {
                    display: none !important;
                }
                .woo-onpage-enable #newBookingDetails td a {
                    white-space: normal;
                    word-break: break-word;
                    display: inline-block;
                }
            `;
            iframeDoc.head.appendChild(style);
			

			const iframeURL = iframeWindow.location.href;

			// If it's the order received page
			if (iframeURL.includes('/order-received/')) {
				const main = iframeDoc.querySelector('main');

				if (main) {
					main.innerHTML = `
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" width="200" height="200" style="shape-rendering: auto; display: block; background: transparent; margin-top: 10%;" xmlns:xlink="http://www.w3.org/1999/xlink">
							<g>
								<circle stroke-dasharray="164.93361431346415 56.97787143782138" r="35" stroke-width="10" stroke="#008600" fill="none" cy="50" cx="50">
									<animateTransform keyTimes="0;1" values="0 50 50;360 50 50" dur="1s" repeatCount="indefinite" type="rotate" attributeName="transform"></animateTransform>
								</circle>
							</g>
						</svg>
					`;

					const match = iframeURL.match(/\/order-received\/(\d+)\//);
					if (match && match[1]) {
						const orderID = parseInt(match[1]);
						const newOrderID = orderID + 1;

						const thankyouUrl = new URL(wp_travel.thankyouPageUrl);
						thankyouUrl.searchParams.set('order_id', newOrderID);

						// Wait 1 second then redirect
						setTimeout(() => {
							window.location.href = thankyouUrl.toString();
						}, 1000);
					}
				}
			}
		} catch (err) {
			console.warn('Iframe access failed:', err);
		}
	};

	// Re-apply logic on every load
	iframe.addEventListener('load', () => {
		// Wait a bit to ensure iframe content is fully loaded
		setTimeout(applyIframeCustomizations, 150);
	});
});



jQuery(function($) {
    $('.g-recaptcha-response').attr('required', true);
    function findGetParameter(parameterName) {
        var result = null,
            tmp = [];
        var items = location.search.substr(1).split("&");
        for (var index = 0; index < items.length; index++) {
            tmp = items[index].split("=");
            if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
        }
        return result;
    }

    $(document).on('click', '.showcoupon', function(e) {
        e.preventDefault();

        const form = $('.woocommerce-form-coupon');
        form.slideToggle(); // Or use `.toggle()` for instant toggle
    });

    $(document).ready(function() {
        var prices = [];
        if( typeof wp_travel.prices === 'object' ) {
          prices = wp_travel.prices.map(function(x) {
              return parseInt(x, 10);
          });
        }
        var min         = 0;
        var max         = 0;
        var filteredMin = 0;
        var filteredMax = 0;
        if ( prices.length > 0 ) {
            min = Math.min.apply(null, prices),
            max = Math.max.apply(null, prices);
            min = parseInt( GetConvertedPrice( min ) );
            max = parseInt( GetConvertedPrice( max ) );
        }

        if (findGetParameter('min_price')) {
            filteredMin = findGetParameter('min_price');
        } else {
            filteredMin = min;
        }
        if (findGetParameter('max_price')) {
            filteredMax = findGetParameter('max_price');
        } else {
            filteredMax = max;
        }

        // Filter Range Slider Widget.
        $(".wp-travel-range-slider").slider({
            range: true,
            min: min,
            max: max,
            values: [filteredMin, filteredMax],
            slide: function(event, ui) {
                $(".price-amount").val(wp_travel.currency_symbol + ui.values[0] + " - " + wp_travel.currency_symbol + ui.values[1]);
                $('.wp-travel-range-slider').siblings('.wp-travel-filter-price-min').val(ui.values[0]);
                $('.wp-travel-range-slider').siblings('.wp-travel-filter-price-max').val(ui.values[1]);
            }
        });
        $(".price-amount").val(wp_travel.currency_symbol + $(".wp-travel-range-slider").slider("values", 0) +
            " - " + wp_travel.currency_symbol + $(".wp-travel-range-slider").slider("values", 1));

        // $(".trip-duration-calender input").wpt_datepicker({
        //     language: wp_travel.locale,
        // });

    });

    $('.wp-travel-filter-submit-shortcode').on('click', function() {
        var view_mode = $(this).siblings('.wp-travel-filter-view-mode').data('mode');
        var pathname = $(this).siblings('.wp-travel-filter-archive-url').val();
        if (!pathname) {
            pathname = window.location.pathname;
        }

        query_string = '';
        if ( window.location.search ) {
            query_string = window.location.search;
        }
        
        var full_url       = new URL( pathname + query_string );
        var search_params  = full_url.searchParams;
        var data_index = $(this).siblings('.wptravel_filter-data-index').data('index');

        $('.wp_travel_search_filters_input' + data_index).each(function() {
            var filterby = $(this).attr('name');
            var filterby_val = $(this).val();
            search_params.set( filterby, filterby_val );
            full_url.search = search_params.toString();
        });

        var new_url     = full_url.toString();
        window.location = new_url;
    });

    $('.wp-travel-filter-search-submit').on('click', function() {
        var view_mode = $(this).siblings('.wp-travel-widget-filter-view-mode').data('mode');
        var pathname = $(this).siblings('.wp-travel-widget-filter-archive-url').val();
        if (!pathname) {
            pathname = window.location.pathname;
        }
        // query_string = '?';
        // var check_query_string = pathname.match(/\?/);
        // if (check_query_string) {
        //     query_string = '&';
        // }
        // var data_index = $(this).siblings('.filter-data-index').data('index');
        // $('.wp_travel_search_widget_filters_input' + data_index).each(function() {
        //     filterby = $(this).attr('name');
        //     filterby_val = $(this).val();
        //     query_string += filterby + '=' + filterby_val + '&';
        // })
        // redirect_url = pathname + query_string;
        // redirect_url = redirect_url.replace(/&+$/, '');

        // redirect_url = redirect_url + '&view_mode=' + view_mode;
        // window.location = redirect_url;

        query_string = '';
        // if ( window.location.search ) {
        //     query_string = window.location.search;
        // }
        var full_url       = new URL( pathname + query_string );
        var search_params  = full_url.searchParams;

        var data_index = $(this).siblings('.filter-data-index').data('index');
        $('.wp_travel_search_widget_filters_input' + data_index).each(function() {
            var filterby = $(this).attr('name');
            var filterby_val = $(this).val();
            // query_string += filterby + '=' + filterby_val + '&';
            search_params.set( filterby, filterby_val );
            full_url.search = search_params.toString();
        })
        var new_url     = full_url.toString();
  
        window.location = new_url;
    });

    // Enquiry Submission.
    var handleEnquirySubmission = function(e) {

        e.preventDefault();

        //Remove any previous errors.
        $('.enquiry-response').remove();
        var formData = $( '#wp-travel-enquiries' ).serializeArray();
        formData.push({name:'nonce',value: wp_travel.nonce});
        var text_processing = $('#wp_travel_label_processing').val();
        var text_submit_enquiry = $('#wp_travel_label_submit_enquiry').val();
        $.ajax({
            type: "POST",
            url: wp_travel.ajaxUrl,
            data: formData,
            beforeSend: function() {
                $('#wp-travel-enquiry-submit').addClass('loading-bar loading-bar-striped active').val(text_processing).attr('disabled', 'disabled');
            },
            success: function(data) {

                if (false == data.success) {
                    var message = '<span class="enquiry-response enquiry-error-msg">' + data.data.message + '</span>';
                    $('#wp-travel-enquiries').append(message);
                } else {
                    if (true == data.success) {

                        var message = '<span class="enquiry-response enquiry-success-msg">' + data.data.message + '</span>';
                        $('#wp-travel-enquiries').append(message);

                        setTimeout(function() {
                            jQuery('#wp-travel-send-enquiries').magnificPopup('close');
                            $('#wp-travel-enquiries .enquiry-response ').hide();
                        }, '3000');

                    }
                }

                $('#wp-travel-enquiry-submit').removeClass('loading-bar loading-bar-striped active').val(text_submit_enquiry).removeAttr('disabled', 'disabled');
                //Reset Form Fields.
                $('#wp-travel-enquiry-name').val('');
                $('#wp-travel-enquiry-email').val('');
                $('#wp-travel-enquiry-query').val('');

                return false;
            }
        });
        $('#wp-travel-enquiries').trigger('reset');
    }
    $('body').off('submit', '#wp-travel-enquiries')
    $('#wp-travel-enquiries').submit(handleEnquirySubmission);
    
    $(document).on( 'click', '.btn-wptravel-filter-by-shortcodes-itinerary', function(){
        var parent = $(this).parent( '.wp-travel-filter-by-heading' );
        if ( parent &&  parent.siblings( '.wp-toolbar-filter-field' ) ) {
            parent.siblings( '.wp-toolbar-filter-field, .wp-travel-filter-button' ).toggleClass( 'show-in-mobile' );

            if ( parent.siblings( '.wp-toolbar-filter-field' ).hasClass( 'show-in-mobile' ) ) {
                $(this).addClass( 'active' );
            } else {
                $(this).removeClass( 'active' );
            }
        }
    } );

    $(document).on( 'click', '.edit-trip a', function(){
        $('.checkout-trip-extras').css( 'display', 'none' );
    } );
    
    $(document).on( 'click', '.edit-pax-selector-qty', function(){

        if( $(this).parent().find( '.wp-trave-pax-selected-frontend-second' ).val() >= $(this).attr( 'data-minpax' ) ){
            $('.wp-travel-book').removeClass( 'btn-disable' );
        }

        var cartInputValue = document.getElementsByClassName('edit-pax-'+$(this).attr( 'data-cart' ));

       
        var cartInputValueTrip = document.getElementsByClassName('wp-trave-pax-selected-frontend-second');

        if( document.getElementsByClassName('wptravel-recurring-dates').length > 0 ){
            cartInputValueTrip = document.querySelectorAll('.wp-travel-active-date .wp-trave-pax-selected-frontend-second');
        }

        if( $(this).attr( 'data-allpricing' ) == 1 ){
            $flag = 1;

            if( cartInputValue.length > 0 ){
                for(var i=0;i<cartInputValue.length;i++){
                    if(parseFloat(cartInputValue[i].value) < $(this).attr( 'data-minpax' ) ){
                        $flag = 0;
                    }
                }

                if( $flag == 0 ){
                    $('.cart-edit-'+$(this).attr( 'data-cart' )).addClass( 'btn-disable' );
                }else{
                    $('.cart-edit-'+$(this).attr( 'data-cart' )).removeClass( 'btn-disable' );
                }
            }
   
            if( cartInputValueTrip.length > 0 ){
                for(var i=0;i<cartInputValueTrip.length;i++){
                    if(parseFloat(cartInputValueTrip[i].value) < $(this).attr( 'data-minpax' ) ){
                        $flag = 0;
                    }
               
                }

       
                if( $flag == 0 ){
                    $('.wp-travel-book').addClass( 'btn-disable' );
                }else{
                    $('.wp-travel-book').removeClass( 'btn-disable' );
                }
            }
           
        }else{
            var totalpax=0;
            if( cartInputValue.length > 0 ){
                for(var i=0;i<cartInputValue.length;i++){
                    if(parseFloat(cartInputValue[i].value))
                    totalpax += parseFloat(cartInputValue[i].value);
                }
            }

            if( cartInputValueTrip.length > 0 ){
                for(var i=0;i<cartInputValueTrip.length;i++){
                    if(parseFloat(cartInputValueTrip[i].value)  ){
                        totalpax += parseFloat(cartInputValueTrip[i].value);
                    }
               
                }
            }

            if( totalpax >= $(this).attr( 'data-minpax' ) ){   
                $('.wp-travel-book').removeClass( 'btn-disable' );             
                $('.cart-edit-'+$(this).attr( 'data-cart' )).removeClass( 'btn-disable' );
            }else{
                $('.wp-travel-book').addClass( 'btn-disable' );   
                $('.cart-edit-'+$(this).attr( 'data-cart' )).addClass( 'btn-disable' );
            }
        }
       
    } );

    

});

jQuery(function($) {

    $('.wp-travel-itinerary-items').keypress((e) => {   
        // Enter key corresponds to number 13 
        if (e.which === 13) { 
            $('#wp-travel-filter-search-submit').click()
        } 
    }) 

    $(document).on('click', '.datepicker--cell-day:not(.-disabled-)', function(event) {
        $('.datepicker').css( 'left', '-100000px' );
        $('.datepicker').removeClass( 'active' );
    });    

    // $(document).on('click', '.wp-travel-datepicker', function(event) {
    //     var childItem = $(this).parent().parent().parent().parent().parent().parent().attr( 'data-child' ) 
    //     $('#datepickers-container .datepicker:nth-child(' +childItem+ ')').css( 'left', '735px' );
    //     $('#datepickers-container .datepicker:nth-child(' +childItem+ ')').addClass( 'active' );
    // });
    
    $(document).on('click', '.open-quick-view-modal', function(event) {
        event.preventDefault();

        $(this).siblings('.wp-travel-quick-view-modal').show();
        $('.modal-overlay').show();
    });

    // Close the modal
    $(document).on('click', '.close-modal', function(event) {
        event.preventDefault();

        $(this).closest('.wp-travel-quick-view-modal').hide();
        $('.modal-overlay').hide();
    });

    $(document).on('click', '.modal-overlay', function(event) {
        event.preventDefault();
        $('.wp-travel-quick-view-modal').hide();
        $('.modal-overlay').hide();
    });

    $('.wp-travel-quick-view #overview').show();
    $( '.wp-travel-quick-view ul.tab-list li' ).addClass( 'resp-tab-active' );
    $('.wp-travel-quick-view .tab-list-content').addClass( 'resp-tab-content-active' );

    $(document).on('click', '.wp-travel-quick-view ul.tab-list .overview', function(event) {
        event.preventDefault();
        $( '.wp-travel-quick-view ul.tab-list li' ).removeClass( 'resp-tab-active' );
        $( this ).addClass( 'resp-tab-active' );
        $('.wp-travel-quick-view .tab-list-content').hide();
        $('.wp-travel-quick-view #overview').show();
    });

    $(document).on('click', '.wp-travel-quick-view ul.tab-list .trip_outline', function(event) {
        event.preventDefault();
        $( '.wp-travel-quick-view ul.tab-list li' ).removeClass( 'resp-tab-active' );
        $( this ).addClass( 'resp-tab-active' );
        $('.wp-travel-quick-view .tab-list-content').hide();
        $('.wp-travel-quick-view #trip_outline').show();
    });

    $(document).on('click', '.wp-travel-quick-view ul.tab-list .trip_includes', function(event) {
        event.preventDefault();
        $( '.wp-travel-quick-view ul.tab-list li' ).removeClass( 'resp-tab-active' );
        $( this ).addClass( 'resp-tab-active' );
        $('.wp-travel-quick-view .tab-list-content').hide();
        $('.wp-travel-quick-view #trip_includes').show();
    });

    $(document).on('click', '.wp-travel-quick-view ul.tab-list .trip_excludes', function(event) {
        event.preventDefault();
        $( '.wp-travel-quick-view ul.tab-list li' ).removeClass( 'resp-tab-active' );
        $( this ).addClass( 'resp-tab-active' );
        $('.wp-travel-quick-view .tab-list-content').hide();
        $('.wp-travel-quick-view #trip_excludes').show();
    });
    
    $(document).on('click', '.wp-travel-quick-view ul.tab-list .gallery', function(event) {
        event.preventDefault();
        $( '.wp-travel-quick-view ul.tab-list li' ).removeClass( 'resp-tab-active' );
        $( this ).addClass( 'resp-tab-active' );
        $('.wp-travel-quick-view .tab-list-content').hide();
        $('.wp-travel-quick-view #gallery').show();
    });

    $(document).on('click', '.custom-link-enable input', function(event) {
        if ($(this).is(':checked')) {
            var url = $(this).data('url');

            $('button.wp-travel-book').attr('data-url', url);
            $('button.wp-travel-book').addClass('custom-booking-link');
            // $('button.wp-travel-book').removeClass('wp-travel-book');
        }
    });

    $(document).on('click', '.wp-travel-book.custom-booking-link', function(event) {
        var url = $(this).data('url');

        if (!url) {
            return; 
        }
    
        window.open(url, '_blank')
    });
});



// PWA
// if ("serviceWorker" in navigator) {
//     window.addEventListener("load", function() {
//       navigator.serviceWorker
//         .register("/sw.js")

//     })
//   }
