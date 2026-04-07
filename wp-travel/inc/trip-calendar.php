<?php
if ( ! function_exists( 'is_plugin_active' ) ) {
    require_once ABSPATH . '/wp-admin/includes/plugin.php';
}

if( is_plugin_active('wp-travel-pro/wp-travel-pro.php') ){

function wptravel_custom_calendar_shortcode($atts) {

    $atts = shortcode_atts([
        'layout' => 'one',
    ], $atts);

    $layout = sanitize_text_field($atts['layout']);

    if( $layout == 'one' ){

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
    }elseif( $layout == 'two' ){
        global $wpdb;

        $today = date('Y-m-d');

        $results = $wpdb->get_results(
            $wpdb->prepare("
                SELECT d.pricing_ids, d.trip_id, d.start_date, p.post_title
                FROM {$wpdb->prefix}wt_dates d
                INNER JOIN {$wpdb->prefix}posts p ON p.ID = d.trip_id
                WHERE d.start_date >= %s
            ", $today)
        );

        $events_by_date = [];

        foreach ( (array) $results as $trip ) {

            $price_html = '';
            $pricing_ids = array_filter(array_map('trim', explode(',', (string) $trip->pricing_ids)));

            foreach ( $pricing_ids as $pid ) {
                $pid = (int) $pid;

                $row = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT regular_price FROM {$wpdb->prefix}wt_price_category_relation WHERE pricing_id = %d",
                        $pid
                    ),
                    ARRAY_A
                );

                if ( $row && isset($row['regular_price']) ) {
                    $price = $row['regular_price'];

                    if ( function_exists('wptravel_get_formated_price_currency') ) {
                        $price_html = wptravel_get_formated_price_currency( $price );
                    } else {
                        $price_html = esc_html( $price );
                    }
                    break;
                }
            }

            $rating = get_post_meta( (int) $trip->trip_id, '_wpt_average_rating', true );
            $rating = $rating ? floatval($rating) : 0;

            $date = $trip->start_date;

            $events_by_date[$date][] = [
                'title' => esc_html($trip->post_title),
                'image' => get_the_post_thumbnail_url( (int) $trip->trip_id, 'medium' ) ?: '',
                'price' => $price_html,
                'rating' => $rating,
                'url'   => get_permalink( (int) $trip->trip_id ),
            ];
        }

        ob_start();
    ?>
    <div class="wp-travel-cal-layout">

        <div class="cal-sidebar">
            <div class="year-nav">
                <button id="prevYear">‹</button>
                <span id="year"></span>
                <button id="nextYear">›</button>
            </div>
            <ul id="monthList"></ul>
        </div>

        <div class="cal-main">
            <h2 id="monthTitle"></h2>
            <div class="cal-grid" id="calendar"></div>
        </div>

        <div class="cal-trips">
            <h3 id="tripDate">Select a date</h3>
            <div id="tripList"></div>
        </div>

    </div>

    <style>
    .wp-travel-cal-layout {
        display: grid;
        grid-template-columns: 220px 1fr 350px;
        height: 100%;
        font-family: sans-serif;
    }

    .wp-travel-cal-layout #monthTitle{
        text-align: center;
        margin-top: 0px;
        font-size: 30px;
    }

    .wp-travel-cal-layout .cal-trips #tripDate{
        margin-top: 0px;
        font-size: 30px;
    }

    .wp-travel-cal-layout .cal-sidebar {
        background: #6b8e23;
        color: #fff;
        padding: 20px;
    }

    .wp-travel-cal-layout .cal-sidebar #year{
        font-size: 30px;
        padding: 0px 18px;
    }

    .wp-travel-cal-layout .cal-sidebar button{
    font-size: 30px;
        padding: 0px 10px;
        padding-top: 0px;
        padding-bottom: 5px; 
        border-radius: 6px;
    }

    .wp-travel-cal-layout .cal-sidebar ul {
        list-style: none;
        padding: 0;
    }

    .wp-travel-cal-layout .cal-sidebar li {
        padding: 10px;
        cursor: pointer;
    }

    .wp-travel-cal-layout .cal-sidebar li.active {
        background: #f4c542;
        color: #000;
        border-radius: 6px;
    }

    .wp-travel-cal-layout .cal-main {
        padding: 20px;
        background: #fffcfc;
    }

    .wp-travel-cal-layout .cal-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
    }

    .wp-travel-cal-layout .day-name {
        font-weight: bold;
        text-align: center;
        padding: 10px 0;
        background: #dff3f3;
        border-radius: 6px;
    }

    .wp-travel-cal-layout .day {
        padding: 12px;
        text-align: center;
        cursor: pointer;
        border-radius: 10px;
        font-size: 30px;
    }

    .wp-travel-cal-layout .day.today {
        border: 2px solid red;
        background: #ffe6e6;
    }

    .wp-travel-cal-layout .day.active {
        border: 2px solid orange;
    }

    .wp-travel-cal-layout .dot {
        width: 6px;
        height: 6px;
        background: green;
        border-radius: 50%;
        margin: 5px auto 0;
    }



    .wp-travel-cal-layout .cal-trips {
        background: #f7f7f7;
        padding: 20px;
        
    }

    .wp-travel-cal-layout .cal-trips #tripList{
        height: 570px;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 10px;
    }

    .wp-travel-cal-layout .cal-trips .event-card .trip-title{
        text-decoration: none;
        
    }

    .wp-travel-cal-layout .cal-trips .event-card .trip-title h4{
        margin-top: 0px;
        margin-bottom: 0px;
        font-size: 18px;
    }

    .wp-travel-cal-layout .cal-trips .event-card .trip-price{
        font-weight: 600;
        font-size: 16px;
    }

    .wp-travel-cal-layout .event-card {
        display: flex;
        gap: 10px;
        background: #fff;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .wp-travel-cal-layout .event-card img {
        width: 80px;
        height: 80px;
        border-radius: 10px;
        object-fit: cover;
    }

    .wp-travel-cal-layout .rating {
        font-size: 14px;
        margin-bottom: -5px;
        color: #f4c542;
    }

    /* ===== RESPONSIVE CALENDAR ===== */
    @media (max-width: 1200px) {
        .wp-travel-cal-layout {
            grid-template-columns: 200px 1fr 300px;
        }

        .wp-travel-cal-layout .cal-sidebar, .wp-travel-cal-layout .cal-main, .wp-travel-cal-layout .cal-trips {
            padding: 15px;
        }

        .wp-travel-cal-layout #monthTitle, .wp-travel-cal-layout #year, .wp-travel-cal-layout #tripDate {
            font-size: 24px;
        }

        .wp-travel-cal-layout .day {
            font-size: 24px;
            padding: 10px;
        }

        .wp-travel-cal-layout .event-card img {
            width: 60px;
            height: 60px;
        }
    }

    @media (max-width: 900px) {
        .wp-travel-cal-layout {
            grid-template-columns: 1fr;
            grid-template-rows: auto auto auto;
        }

        .wp-travel-cal-layout .cal-sidebar, .wp-travel-cal-layout .cal-main, .wp-travel-cal-layout .cal-trips {
            width: 100%;
            padding: 10px;
        }

        .wp-travel-cal-layout .cal-grid {
            gap: 5px;
        }

        .wp-travel-cal-layout .day-name {
            font-size: 14px;
            padding: 6px 0;
        }

        .wp-travel-cal-layout .day {
            font-size: 20px;
            padding: 8px;
        }

        .wp-travel-cal-layout .event-card img {
            width: 50px;
            height: 50px;
        }

        .wp-travel-cal-layout #monthTitle, .wp-travel-cal-layout #year, .wp-travel-cal-layout #tripDate {
            font-size: 20px;
        }

        .wp-travel-cal-layout .cal-trips #tripList {
            height: 400px; 
        }
    }

    @media (max-width: 600px) {
        .wp-travel-cal-layout .cal-grid {
            gap: 3px;
        }

        .wp-travel-cal-layout .day-name {
            font-size: 12px;
            padding: 4px 0;
        }

        .wp-travel-cal-layout .day {
            font-size: 16px;
            padding: 6px;
        }

        .wp-travel-cal-layout .event-card img {
            width: 40px;
            height: 40px;
        }

        .wp-travel-cal-layout .trip-price {
            font-size: 14px;
        }

        .wp-travel-cal-layout .rating {
            font-size: 12px;
        }

        .wp-travel-cal-layout .cal-trips #tripList {
            height: 300px;
        }
    }
    </style>

    <script>
    const EVENTS = <?php echo wp_json_encode($events_by_date); ?>;

    document.addEventListener("DOMContentLoaded", function () {

        let current = new Date();
        let selectedDate = null;

        const months = [
            "January","February","March","April","May","June",
            "July","August","September","October","November","December"
        ];

        function renderStars(rating) {
            let stars = '';
            for (let i = 1; i <= 5; i++) {
                stars += (i <= rating) ? '★' : '☆';
            }
            return stars;
        }

        function renderMonths() {
            let html = "";
            months.forEach((m, i) => {
                html += `<li data-month="${i}" class="${i === current.getMonth() ? 'active' : ''}">${m}</li>`;
            });
            document.getElementById("monthList").innerHTML = html;
        }

        function renderCalendar() {
            const year = current.getFullYear();
            const month = current.getMonth();

            document.getElementById("year").textContent = year;
            document.getElementById("monthTitle").textContent = months[month];

            const firstDay = new Date(year, month, 1).getDay();
            const days = new Date(year, month + 1, 0).getDate();

            let html = "";

            // ✅ Day Names Header
            const dayNames = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
            dayNames.forEach(day => {
                html += `<div class="day-name">${day}</div>`;
            });

            for (let i = 0; i < firstDay; i++) {
                html += `<div></div>`;
            }

            for (let d = 1; d <= days; d++) {
                const date = `${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                const hasEvent = EVENTS[date];

                // ✅ Check if this date is today
                const today = new Date();
                const todayStr = `${today.getFullYear()}-${String(today.getMonth()+1).padStart(2,'0')}-${String(today.getDate()).padStart(2,'0')}`;
                const isToday = date === todayStr ? 'today' : '';

                html += `
                    <div class="day ${isToday}" data-date="${date}">
                        ${d}
                        ${hasEvent ? '<div class="dot"></div>' : ''}
                    </div>
                `;
            }

            document.getElementById("calendar").innerHTML = html;
        }

        function renderEvents(date) {
            selectedDate = date;

            const selected = new Date(date);
            const options = { month: 'long', day: 'numeric' };
            const formattedDate = selected.toLocaleDateString(undefined, options);

            document.getElementById("tripDate").textContent = formattedDate;

            const list = EVENTS[date] || [];
            let html = "";

            list.forEach(e => {
                html += `
                    <div  class="event-card">
                        <a href="${e.url}" target="_blank"><img src="${e.image}"></a>
                        <div>
                            <a href="${e.url}" class="trip-title" target="_blank"><h4>${e.title}</h4></a>
                            <div class="rating">${renderStars(e.rating)}</div>
                            <div class="trip-price">${e.price}</div>
                        </div>
                    </div>
                `;
            });

            document.getElementById("tripList").innerHTML = html || "No Trips.";
        }

        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("day")) {
                document.querySelectorAll(".day").forEach(d => d.classList.remove("active"));
                e.target.classList.add("active");

                renderEvents(e.target.dataset.date);
            }

            if (e.target.tagName === "LI") {
                current.setMonth(parseInt(e.target.dataset.month));
                renderCalendar();

                document.querySelectorAll(".cal-sidebar li").forEach(li => li.classList.remove("active"));
                e.target.classList.add("active");
            }
        });

        document.getElementById("prevYear").onclick = () => {
            current.setFullYear(current.getFullYear() - 1);
            renderCalendar();
        };

        document.getElementById("nextYear").onclick = () => {
            current.setFullYear(current.getFullYear() + 1);
            renderCalendar();
        };

        renderMonths();
        renderCalendar();
    });
    </script>

    <?php
    return ob_get_clean();
    }

    
}

add_shortcode('wp_trip_calendar_view', 'wptravel_custom_calendar_shortcode');
}