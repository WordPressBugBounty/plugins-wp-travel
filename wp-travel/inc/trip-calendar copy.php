<?php

if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if( is_plugin_active('wp-travel-pro/wp-travel-pro.php') ){

function wptravel_wp_trip_calendar_shortcode() {
    global $wpdb;

    $today = date('Y-m-d');

    // 1) Get future trip dates
    $results = $wpdb->get_results(
        $wpdb->prepare("
            SELECT d.pricing_ids, d.trip_id, d.start_date, p.post_title
            FROM {$wpdb->prefix}wt_dates d
            INNER JOIN {$wpdb->prefix}posts p ON p.ID = d.trip_id
            WHERE d.start_date >= %s
        ", $today)
    );

    $events = [];

    foreach ( (array) $results as $trip ) {
        $pax_text    = '';
        $price_html  = '';
        $pricing_ids = array_filter(array_map('trim', explode(',', (string) $trip->pricing_ids)));

        // 2) Try to get inventory + price from the first valid pricing_id that returns data
        foreach ( $pricing_ids as $pid ) {
            $pid = (int) $pid;
            if ( $pid <= 0 ) { continue; }

            // Inventory (WP Travel Pro)
            if ( class_exists('WP_Travel_Pro') && class_exists('WP_Travel_Helpers_Inventory') ) {
                $args = [
                    'trip_id'       => (int) $trip->trip_id,
                    'pricing_id'    => $pid,
                    'selected_date' => $trip->start_date,
                    'times'         => '',
                ];
                $inventory_data = WP_Travel_Helpers_Inventory::get_inventory( $args );

                if ( ! is_wp_error( $inventory_data ) && ! empty( $inventory_data['inventory'][0] ) ) {
                    $inv = $inventory_data['inventory'][0];
                    if ( isset($inv['booked_pax'], $inv['pax_limit']) ) {
                        $pax_text = (int) $inv['booked_pax'] . '/' . (int) $inv['pax_limit'] . __( ' ( Pax )', 'wp-travel' );
                    }
                }
            }

            // Price
            $row = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT regular_price FROM {$wpdb->prefix}wt_price_category_relation WHERE pricing_id = %d",
                    $pid
                ),
                ARRAY_A
            );

            if ( $row && isset($row['regular_price']) ) {
                $price = $row['regular_price'];

                // Convert/format using WP Travel helpers if available
                if ( class_exists('WpTravel_Helpers_Trip_Pricing_Categories') ) {
                    $price = WpTravel_Helpers_Trip_Pricing_Categories::get_converted_price( $price );
                }
                if ( function_exists('wptravel_get_formated_price_currency') ) {
                    // Note: function name in WP Travel is spelled "formated" (one 't')
                    $price_html = wptravel_get_formated_price_currency( $price );
                } else {
                    // Fallback plain number
                    $price_html = esc_html( $price );
                }
            }

            // If we got something meaningful, we can stop at first usable pricing_id
            if ( $pax_text !== '' || $price_html !== '' ) {
                break;
            }
        }

        $events[] = [
            'title' => $trip->post_title,
            'start' => $trip->start_date,
            'extendedProps' => [
                'tripId'  => (int) $trip->trip_id,
                'tripUrl' => get_permalink( (int) $trip->trip_id ),
                'pax'     => $pax_text,
                // price may contain HTML (currency symbol + formatting)
                'price'   => $price_html,
				'rating'  => (float) get_post_meta( $trip->trip_id, '_wpt_average_rating' )[0],
                'image'   => get_the_post_thumbnail_url( (int) $trip->trip_id, 'medium' ) ?: ''
            ]
        ];
    }

    wp_enqueue_script(
		'fullcalendar-js',
		untrailingslashit( plugin_dir_url( WP_TRAVEL_PLUGIN_FILE ) ) . '/app/assets/js/lib/full-calendar/full-calendar.js' ,
		array(), // Dependencies
		null,    // Version
		true     // Load in footer
	);


    // 4) Inline JS (pass events safely)
    wp_add_inline_script(
        'fullcalendar-js',
        'document.addEventListener("DOMContentLoaded",function(){' .
            'const events=' . wp_json_encode($events) . ';' .
            'const el=document.getElementById("wp-trip-calendar"); if(!el) return;' .
            'const cal=new FullCalendar.Calendar(el,{' .
                'initialView:"dayGridMonth",' .
                'headerToolbar:{left:"prev,next today",center:"title",right:"dayGridMonth,timeGridWeek,listWeek"},' .
                'events:events,' .
                'eventClick:function(info){' .
                    'info.jsEvent.preventDefault();' .
                    'const trip=info.event.extendedProps;' .
                    'const m=document.getElementById("wp-trip-modal"); if(!m) return;' .

                    'var t=m.querySelector(".trip-title"); if(t) t.textContent=info.event.title;' .
                    'var px=m.querySelector(".trip-pax"); if(px) px.textContent=trip.pax||"";' .
                    'var pr=m.querySelector(".trip-price"); if(pr) pr.innerHTML=trip.price||"";' .
                    'var a=m.querySelector(".trip-link"); if(a) a.href=trip.tripUrl||"#";' .
					'var rating = parseInt(trip.rating) || 0;
					var stars = "";
					for (var i = 1; i <= 5; i++) {
						stars += i <= rating ? "★" : "☆";
					}
					document.querySelector("#wp-trip-modal .trip-rating").innerHTML = stars;'.

                    'var img=m.querySelector(".trip-image");' .
                    'if(img && trip.image){ img.src=trip.image; img.style.display="block"; } else if(img){ img.style.display="none"; }' .

                    'm.style.display="block";' .
                '}' .
            '});' .
            'cal.render();' .
        '});'
    );

    // 5) HTML output (added .trip-price)
    ob_start();
?>
<div id="wp-trip-calendar" style="max-width:100%; margin-bottom:20px;"></div>
<div id="wp-trip-modal" style="z-index: 9999; display:none; position:fixed; top:20%; left:50%; transform:translateX(-50%);
    background:#fff; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.3); z-index:9999; max-width:420px; width:92%;">
  <img class="trip-image" src="" alt="" style="width:100%; display:none;">
  <h2 class="trip-title" style="font-size: 25px; margin: 10px 0 0;"></h2>
  <div class="trip-rating"></div>
  <p class="trip-pax" style="margin:0 0 6px;"></p>
  <p class="trip-price" style="margin:0 0 12px;"></p>
  <a class="trip-link" href="#" target="_blank" style="text-decoration:none;">
    <button style="padding:8px 16px; background:#007bff; color:#fff; border:none; cursor:pointer; border-radius:6px;">
      <?php echo esc_html__('View More', 'wp-travel'); ?>
    </button>
  </a>
  <button onclick="document.getElementById('wp-trip-modal').style.display='none'"
          style="margin-left:10px; padding:8px 16px; background:#ccc; border:none; cursor:pointer; border-radius:6px;">
    <?php echo esc_html__('Close', 'wp-travel'); ?>
  </button>
</div>
<style>
    #wp-trip-calendar thead table {
        margin-bottom: 0px;
    }
    
    #wp-trip-calendar .fc-header-toolbar.fc-toolbar .fc-toolbar-chunk:last-child{
        display: none;
    }

    #wp-trip-calendar.fc .fc-daygrid-day-top {
        display: flex;
        flex-direction: row;
    }

    #wp-trip-calendar .fc-header-toolbar .fc-button-group{
        gap: 5px;
    }

    #wp-trip-calendar th {
        padding: 0;
        background-color: var(--fc-button-bg-color);
        height: 50px;
        vertical-align: middle;   /* Ensures table-cell vertical alignment */
        line-height: 50px;        /* Ensures text is centered even if vertical-align fails */
        text-align: center;       /* Optional: centers text horizontally */
    }

    #wp-trip-calendar th a{
        text-decoration: none;
        color: #fff;
    }

    #wp-trip-calendar tbody .fc-daygrid-day-top a{
        text-decoration: none;
        font-size: 20px;
    }

    .fc-daygrid-event-harness{
        margin: 2px;
    }

    #wp-trip-modal,
    #wp-trip-modal img{
        border-radius: 6px;
    }

    #wp-trip-modal .trip-rating{
        color: #ff9200; 
    }

</style>
<?php
return ob_get_clean();
}
add_shortcode('wp_trip_calendar_view', 'wptravel_wp_trip_calendar_shortcode');
}