<?php
return [
	[
		'label' => 'Summit name',
		'shortcode' => '[summitname]',
		'description' => 'Return site name to retrieve summit name',
		'readonly' => 'readonly',
	],
	[
		'label' => 'Days since summit start',
		'shortcode' => '[dayssincesummitstart]',
		'description' => 'Shows current day of summit',
		'readonly' => 'readonly',
	],
	[
		'label' => 'Timed dollar savings text',
		'shortcode' => '[timeddollarsavingstext]',
		'description' => 'Returns the dollars saved depending on when the visitor opens page. Use on welcome and early bird pages.',
		'readonly' => 'readonly',
	],
	[
		'label' => 'Timed percent savings text',
		'shortcode' => '[timedpercentsavingstext]',
		'description' => 'Returns the percentage saved depending on when the visitor opens page. Use on welcome and early bird pages.',
		'readonly' => 'readonly',
	],
	[
		'label' => 'Summit package value',
		'shortcode' => '[summit_package_value]',
		'description' => 'Use on welcome and early bird pages.',
		'readonly' => 'readonly',
	],
	[
		'label' => 'Timed price text',
		'shortcode' => '[timedpricetext]',
		'description' => 'Returns the price depending on when the visitor opens page. Use on welcome and early bird pages.',
		'readonly' => 'readonly',
	],
	[
		'label' => 'Number of speakers',
		'shortcode' => '[numberofspeakers]',
		'description' => 'Returns the number of speakers on the summit by counting how many posts have been published.',
		'readonly' => 'readonly',
	],
	[
		'label' => 'Daily theme',
		'shortcode' => '[daily_theme event_day=X]',
		'description' => 'Enter the day so that the shortcode looks like this [#daily_theme event_day=1#].',
		'readonly' => '',
	],
];
