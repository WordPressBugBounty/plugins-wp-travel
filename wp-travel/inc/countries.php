<?php

function wptravel_get_countries() {

	$countries = array(
		'SelCon' => __( 'Select Country', 'wp-travel' ),
		'AF' => __( 'Afghanistan', 'wp-travel' ),
		'AX' => __( 'land Islands', 'wp-travel' ),
		'AL' => __( 'Albania', 'wp-travel' ),
		'DZ' => __( 'Algeria', 'wp-travel' ),
		'AS' => __( 'American Samoa', 'wp-travel' ),
		'AD' => __( 'Andorra', 'wp-travel' ),
		'AO' => __( 'Angola', 'wp-travel' ),
		'AI' => __( 'Anguilla', 'wp-travel' ),
		'AQ' => __( 'Antarctica', 'wp-travel' ),
		'AG' => __( 'Antigua and Barbuda', 'wp-travel' ),
		'AR' => __( 'Argentina', 'wp-travel' ),
		'AM' => __( 'Armenia', 'wp-travel' ),
		'AW' => __( 'Aruba', 'wp-travel' ),
		'AU' => __( 'Australia', 'wp-travel' ),
		'AT' => __( 'Austria', 'wp-travel' ),
		'AZ' => __( 'Azerbaijan', 'wp-travel' ),
		'BS' => __( 'Bahamas', 'wp-travel' ),
		'BH' => __( 'Bahrain', 'wp-travel' ),
		'BD' => __( 'Bangladesh', 'wp-travel' ),
		'BB' => __( 'Barbados', 'wp-travel' ),
		'BY' => __( 'Belarus', 'wp-travel' ),
		'BE' => __( 'Belgium', 'wp-travel' ),
		'PW' => __( 'Belau', 'wp-travel' ),
		'BZ' => __( 'Belize', 'wp-travel' ),
		'BJ' => __( 'Benin', 'wp-travel' ),
		'BM' => __( 'Bermuda', 'wp-travel' ),
		'BT' => __( 'Bhutan', 'wp-travel' ),
		'BO' => __( 'Bolivia', 'wp-travel' ),
		'BQ' => __( 'Bonaire, Saint Eustatius and Saba', 'wp-travel' ),
		'BA' => __( 'Bosnia and Herzegovina', 'wp-travel' ),
		'BW' => __( 'Botswana', 'wp-travel' ),
		'BV' => __( 'Bouvet Island', 'wp-travel' ),
		'BR' => __( 'Brazil', 'wp-travel' ),
		'IO' => __( 'British Indian Ocean Territory', 'wp-travel' ),
		'VG' => __( 'British Virgin Islands', 'wp-travel' ),
		'BN' => __( 'Brunei', 'wp-travel' ),
		'BG' => __( 'Bulgaria', 'wp-travel' ),
		'BF' => __( 'Burkina Faso', 'wp-travel' ),
		'BI' => __( 'Burundi', 'wp-travel' ),
		'KH' => __( 'Cambodia', 'wp-travel' ),
		'CM' => __( 'Cameroon', 'wp-travel' ),
		'CA' => __( 'Canada', 'wp-travel' ),
		'CV' => __( 'Cape Verde', 'wp-travel' ),
		'KY' => __( 'Cayman Islands', 'wp-travel' ),
		'CF' => __( 'Central African Republic', 'wp-travel' ),
		'TD' => __( 'Chad', 'wp-travel' ),
		'CL' => __( 'Chile', 'wp-travel' ),
		'CN' => __( 'China', 'wp-travel' ),
		'CX' => __( 'Christmas Island', 'wp-travel' ),
		'CC' => __( 'Cocos (Keeling) Islands', 'wp-travel' ),
		'CO' => __( 'Colombia', 'wp-travel' ),
		'KM' => __( 'Comoros', 'wp-travel' ),
		'CG' => __( 'Congo (Brazzaville)', 'wp-travel' ),
		'CD' => __( 'Congo (Kinshasa)', 'wp-travel' ),
		'CK' => __( 'Cook Islands', 'wp-travel' ),
		'CR' => __( 'Costa Rica', 'wp-travel' ),
		'HR' => __( 'Croatia', 'wp-travel' ),
		'CU' => __( 'Cuba', 'wp-travel' ),
		'CW' => __( 'Cura&ccedil;ao', 'wp-travel' ),
		'CY' => __( 'Cyprus', 'wp-travel' ),
		'CZ' => __( 'Czech Republic', 'wp-travel' ),
		'DK' => __( 'Denmark', 'wp-travel' ),
		'DJ' => __( 'Djibouti', 'wp-travel' ),
		'DM' => __( 'Dominica', 'wp-travel' ),
		'DO' => __( 'Dominican Republic', 'wp-travel' ),
		'EC' => __( 'Ecuador', 'wp-travel' ),
		'EG' => __( 'Egypt', 'wp-travel' ),
		'SV' => __( 'El Salvador', 'wp-travel' ),
		'GQ' => __( 'Equatorial Guinea', 'wp-travel' ),
		'ER' => __( 'Eritrea', 'wp-travel' ),
		'EE' => __( 'Estonia', 'wp-travel' ),
		'ET' => __( 'Ethiopia', 'wp-travel' ),
		'FK' => __( 'Falkland Islands', 'wp-travel' ),
		'FO' => __( 'Faroe Islands', 'wp-travel' ),
		'FJ' => __( 'Fiji', 'wp-travel' ),
		'FI' => __( 'Finland', 'wp-travel' ),
		'FR' => __( 'France', 'wp-travel' ),
		'GF' => __( 'French Guiana', 'wp-travel' ),
		'PF' => __( 'French Polynesia', 'wp-travel' ),
		'TF' => __( 'French Southern Territories', 'wp-travel' ),
		'GA' => __( 'Gabon', 'wp-travel' ),
		'GM' => __( 'Gambia', 'wp-travel' ),
		'GE' => __( 'Georgia', 'wp-travel' ),
		'DE' => __( 'Germany', 'wp-travel' ),
		'GH' => __( 'Ghana', 'wp-travel' ),
		'GI' => __( 'Gibraltar', 'wp-travel' ),
		'GR' => __( 'Greece', 'wp-travel' ),
		'GL' => __( 'Greenland', 'wp-travel' ),
		'GD' => __( 'Grenada', 'wp-travel' ),
		'GP' => __( 'Guadeloupe', 'wp-travel' ),
		'GU' => __( 'Guam', 'wp-travel' ),
		'GT' => __( 'Guatemala', 'wp-travel' ),
		'GG' => __( 'Guernsey', 'wp-travel' ),
		'GN' => __( 'Guinea', 'wp-travel' ),
		'GW' => __( 'Guinea-Bissau', 'wp-travel' ),
		'GY' => __( 'Guyana', 'wp-travel' ),
		'HT' => __( 'Haiti', 'wp-travel' ),
		'HM' => __( 'Heard Island and McDonald Islands', 'wp-travel' ),
		'HN' => __( 'Honduras', 'wp-travel' ),
		'HK' => __( 'Hong Kong', 'wp-travel' ),
		'HU' => __( 'Hungary', 'wp-travel' ),
		'IS' => __( 'Iceland', 'wp-travel' ),
		'IN' => __( 'India', 'wp-travel' ),
		'ID' => __( 'Indonesia', 'wp-travel' ),
		'IR' => __( 'Iran', 'wp-travel' ),
		'IQ' => __( 'Iraq', 'wp-travel' ),
		'IE' => __( 'Ireland', 'wp-travel' ),
		'IM' => __( 'Isle of Man', 'wp-travel' ),
		'IL' => __( 'Israel', 'wp-travel' ),
		'IT' => __( 'Italy', 'wp-travel' ),
		'CI' => __( 'Ivory Coast', 'wp-travel' ),
		'JM' => __( 'Jamaica', 'wp-travel' ),
		'JP' => __( 'Japan', 'wp-travel' ),
		'JE' => __( 'Jersey', 'wp-travel' ),
		'JO' => __( 'Jordan', 'wp-travel' ),
		'KZ' => __( 'Kazakhstan', 'wp-travel' ),
		'KE' => __( 'Kenya', 'wp-travel' ),
		'KI' => __( 'Kiribati', 'wp-travel' ),
		'KW' => __( 'Kuwait', 'wp-travel' ),
		'KG' => __( 'Kyrgyzstan', 'wp-travel' ),
		'LA' => __( 'Laos', 'wp-travel' ),
		'LV' => __( 'Latvia', 'wp-travel' ),
		'LB' => __( 'Lebanon', 'wp-travel' ),
		'LS' => __( 'Lesotho', 'wp-travel' ),
		'LR' => __( 'Liberia', 'wp-travel' ),
		'LY' => __( 'Libya', 'wp-travel' ),
		'LI' => __( 'Liechtenstein', 'wp-travel' ),
		'LT' => __( 'Lithuania', 'wp-travel' ),
		'LU' => __( 'Luxembourg', 'wp-travel' ),
		'MO' => __( 'Macao S.A.R., China', 'wp-travel' ),
		'MK' => __( 'North Macedonia', 'wp-travel' ),
		'MG' => __( 'Madagascar', 'wp-travel' ),
		'MW' => __( 'Malawi', 'wp-travel' ),
		'MY' => __( 'Malaysia', 'wp-travel' ),
		'MV' => __( 'Maldives', 'wp-travel' ),
		'ML' => __( 'Mali', 'wp-travel' ),
		'MT' => __( 'Malta', 'wp-travel' ),
		'MH' => __( 'Marshall Islands', 'wp-travel' ),
		'MQ' => __( 'Martinique', 'wp-travel' ),
		'MR' => __( 'Mauritania', 'wp-travel' ),
		'MU' => __( 'Mauritius', 'wp-travel' ),
		'YT' => __( 'Mayotte', 'wp-travel' ),
		'MX' => __( 'Mexico', 'wp-travel' ),
		'FM' => __( 'Micronesia', 'wp-travel' ),
		'MD' => __( 'Moldova', 'wp-travel' ),
		'MC' => __( 'Monaco', 'wp-travel' ),
		'MN' => __( 'Mongolia', 'wp-travel' ),
		'ME' => __( 'Montenegro', 'wp-travel' ),
		'MS' => __( 'Montserrat', 'wp-travel' ),
		'MA' => __( 'Morocco', 'wp-travel' ),
		'MZ' => __( 'Mozambique', 'wp-travel' ),
		'MM' => __( 'Myanmar', 'wp-travel' ),
		'NA' => __( 'Namibia', 'wp-travel' ),
		'NR' => __( 'Nauru', 'wp-travel' ),
		'NP' => __( 'Nepal', 'wp-travel' ),
		'NL' => __( 'Netherlands', 'wp-travel' ),
		'NC' => __( 'New Caledonia', 'wp-travel' ),
		'NZ' => __( 'New Zealand', 'wp-travel' ),
		'NI' => __( 'Nicaragua', 'wp-travel' ),
		'NE' => __( 'Niger', 'wp-travel' ),
		'NG' => __( 'Nigeria', 'wp-travel' ),
		'NU' => __( 'Niue', 'wp-travel' ),
		'NF' => __( 'Norfolk Island', 'wp-travel' ),
		'MP' => __( 'Northern Mariana Islands', 'wp-travel' ),
		'KP' => __( 'North Korea', 'wp-travel' ),
		'NO' => __( 'Norway', 'wp-travel' ),
		'OM' => __( 'Oman', 'wp-travel' ),
		'PK' => __( 'Pakistan', 'wp-travel' ),
		'PS' => __( 'Palestinian Territory', 'wp-travel' ),
		'PA' => __( 'Panama', 'wp-travel' ),
		'PG' => __( 'Papua New Guinea', 'wp-travel' ),
		'PY' => __( 'Paraguay', 'wp-travel' ),
		'PE' => __( 'Peru', 'wp-travel' ),
		'PH' => __( 'Philippines', 'wp-travel' ),
		'PN' => __( 'Pitcairn', 'wp-travel' ),
		'PL' => __( 'Poland', 'wp-travel' ),
		'PT' => __( 'Portugal', 'wp-travel' ),
		'PR' => __( 'Puerto Rico', 'wp-travel' ),
		'QA' => __( 'Qatar', 'wp-travel' ),
		'RE' => __( 'Reunion', 'wp-travel' ),
		'RO' => __( 'Romania', 'wp-travel' ),
		'RU' => __( 'Russia', 'wp-travel' ),
		'RW' => __( 'Rwanda', 'wp-travel' ),
		'BL' => __( 'Saint Barth&eacute;lemy', 'wp-travel' ),
		'SH' => __( 'Saint Helena', 'wp-travel' ),
		'KN' => __( 'Saint Kitts and Nevis', 'wp-travel' ),
		'LC' => __( 'Saint Lucia', 'wp-travel' ),
		'MF' => __( 'Saint Martin (French part)', 'wp-travel' ),
		'SX' => __( 'Saint Martin (Dutch part)', 'wp-travel' ),
		'PM' => __( 'Saint Pierre and Miquelon', 'wp-travel' ),
		'VC' => __( 'Saint Vincent and the Grenadines', 'wp-travel' ),
		'SM' => __( 'San Marino', 'wp-travel' ),
		'ST' => __( 'S&atilde;o Tom&eacute; and Pr&iacute;ncipe', 'wp-travel' ),
		'SA' => __( 'Saudi Arabia', 'wp-travel' ),
		'SN' => __( 'Senegal', 'wp-travel' ),
		'RS' => __( 'Serbia', 'wp-travel' ),
		'SC' => __( 'Seychelles', 'wp-travel' ),
		'SL' => __( 'Sierra Leone', 'wp-travel' ),
		'SG' => __( 'Singapore', 'wp-travel' ),
		'SK' => __( 'Slovakia', 'wp-travel' ),
		'SI' => __( 'Slovenia', 'wp-travel' ),
		'SB' => __( 'Solomon Islands', 'wp-travel' ),
		'SO' => __( 'Somalia', 'wp-travel' ),
		'ZA' => __( 'South Africa', 'wp-travel' ),
		'GS' => __( 'South Georgia/Sandwich Islands', 'wp-travel' ),
		'KR' => __( 'South Korea', 'wp-travel' ),
		'SS' => __( 'South Sudan', 'wp-travel' ),
		'ES' => __( 'Spain', 'wp-travel' ),
		'LK' => __( 'Sri Lanka', 'wp-travel' ),
		'SD' => __( 'Sudan', 'wp-travel' ),
		'SR' => __( 'Suriname', 'wp-travel' ),
		'SJ' => __( 'Svalbard and Jan Mayen', 'wp-travel' ),
		'SZ' => __( 'Swaziland', 'wp-travel' ),
		'SE' => __( 'Sweden', 'wp-travel' ),
		'CH' => __( 'Switzerland', 'wp-travel' ),
		'SY' => __( 'Syria', 'wp-travel' ),
		'TW' => __( 'Taiwan', 'wp-travel' ),
		'TJ' => __( 'Tajikistan', 'wp-travel' ),
		'TZ' => __( 'Tanzania', 'wp-travel' ),
		'TH' => __( 'Thailand', 'wp-travel' ),
		'TL' => __( 'Timor-Leste', 'wp-travel' ),
		'TG' => __( 'Togo', 'wp-travel' ),
		'TK' => __( 'Tokelau', 'wp-travel' ),
		'TO' => __( 'Tonga', 'wp-travel' ),
		'TT' => __( 'Trinidad and Tobago', 'wp-travel' ),
		'TN' => __( 'Tunisia', 'wp-travel' ),
		'TR' => __( 'Turkey', 'wp-travel' ),
		'TM' => __( 'Turkmenistan', 'wp-travel' ),
		'TC' => __( 'Turks and Caicos Islands', 'wp-travel' ),
		'TV' => __( 'Tuvalu', 'wp-travel' ),
		'UG' => __( 'Uganda', 'wp-travel' ),
		'UA' => __( 'Ukraine', 'wp-travel' ),
		'AE' => __( 'United Arab Emirates', 'wp-travel' ),
		'GB' => __( 'United Kingdom (UK)', 'wp-travel' ),
		'US' => __( 'United States (US)', 'wp-travel' ),
		'UM' => __( 'United States (US) Minor Outlying Islands', 'wp-travel' ),
		'VI' => __( 'United States (US) Virgin Islands', 'wp-travel' ),
		'UY' => __( 'Uruguay', 'wp-travel' ),
		'UZ' => __( 'Uzbekistan', 'wp-travel' ),
		'VU' => __( 'Vanuatu', 'wp-travel' ),
		'VA' => __( 'Vatican', 'wp-travel' ),
		'VE' => __( 'Venezuela', 'wp-travel' ),
		'VN' => __( 'Vietnam', 'wp-travel' ),
		'WF' => __( 'Wallis and Futuna', 'wp-travel' ),
		'EH' => __( 'Western Sahara', 'wp-travel' ),
		'WS' => __( 'Samoa', 'wp-travel' ),
		'YE' => __( 'Yemen', 'wp-travel' ),
		'ZM' => __( 'Zambia', 'wp-travel' ),
		'ZW' => __( 'Zimbabwe', 'wp-travel' ),
	);

	return apply_filters( 'wp_travel_country_list', $countries );
}

/**
 * Return Country by country code
 *
 * @param Mixed $country_code Array or string.
 * @since 1.0.5
 * @return void
 */
function wptravel_get_country_by_code( $country_code ) {
	if ( ! $country_code ) {
		return;
	}

	$all_countries = wptravel_get_countries();
	if ( ! is_array( $country_code ) ) {
		return $all_countries[ $country_code ];
	} else {
		$countries = array();
		foreach ( $country_code as $code ) {
			$countries[] = $all_countries[ $code ];
		}
		return $countries;
	}

}
