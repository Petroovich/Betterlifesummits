<?php

/**
 * https://docs.wpbeaverbuilder.com/beaver-builder/developer/custom-modules/
 *
 * @class FLCMEventTextSnippets
 */
class FLCMEventTextSnippets extends FLBuilderModule{

	/**
	 * @method __construct
	 */
	public function __construct(){
		parent::__construct([
			'name' => __('Event Text Snippets', 'fl-builder'),
			'description' => __('Shortcodes list', 'fl-builder'),
			'category' => __('Custom', 'fl-builder'),
			'group' => __('Custom Modules', 'fl-builder'),
			'partial_refresh' => true,
			'icon' => 'list.svg',
		]);
	}

	/**
	 * Ensure backwards compatibility with old settings.
	 *
	 * @param object $settings A module settings object.
	 * @param object $helper A settings compatibility helper.
	 * @return object
	 * @since 2.2
	 */
	public function filter_settings($settings, $helper){

		// Handle old responsive button align.
		if(isset($settings->mobile_align)){
			$settings->align_responsive = $settings->mobile_align;
			unset($settings->mobile_align);
		}

		// Handle old font size setting.
		if(isset($settings->font_size)){
			$settings->typography = [];
			$settings->typography['font_size'] = [
				'length' => $settings->font_size,
				'unit' => isset($settings->font_size_unit) ? $settings->font_size_unit : 'px',
			];
			$settings->typography['line_height'] = [
				'length' => $settings->font_size,
				'unit' => isset($settings->font_size_unit) ? $settings->font_size_unit : 'px',
			];
			unset($settings->font_size);
			unset($settings->font_size_unit);
		}

		// Handle old padding setting.
		if(isset($settings->padding) && is_numeric($settings->padding)){
			$settings->padding_top = $settings->padding;
			$settings->padding_bottom = $settings->padding;
			$settings->padding_left = $settings->padding * 2;
			$settings->padding_right = $settings->padding * 2;
			unset($settings->padding);
		}

		// Handle old gradient style setting.
		if(isset($settings->three_d) && $settings->three_d){
			$settings->style = 'gradient';
		}

		// Handle old border settings.
		if(!empty($settings->bg_color) && (!isset($settings->border) || empty($settings->border))){
			$settings->border = [];

			// Border style, color, and width
			if(isset($settings->border_size) && isset($settings->style) && 'transparent' === $settings->style){
				$settings->border['style'] = 'solid';
				$settings->border['color'] = FLBuilderColor::adjust_brightness($settings->bg_color, 12, 'darken');
				$settings->border['width'] = [
					'top' => $settings->border_size,
					'right' => $settings->border_size,
					'bottom' => $settings->border_size,
					'left' => $settings->border_size,
				];
				unset($settings->border_size);
				if(!empty($settings->bg_hover_color)){
					$settings->border_hover_color = FLBuilderColor::adjust_brightness($settings->bg_hover_color, 12, 'darken');
				}
			}

			// Border radius
			if(isset($settings->border_radius)){
				$settings->border['radius'] = [
					'top_left' => $settings->border_radius,
					'top_right' => $settings->border_radius,
					'bottom_left' => $settings->border_radius,
					'bottom_right' => $settings->border_radius,
				];
				unset($settings->border_radius);
			}
		}

		// Handle old transparent background style.
		if(isset($settings->style) && 'transparent' === $settings->style){
			$settings->style = 'flat';
			$helper->handle_opacity_inputs($settings, 'bg_opacity', 'bg_color');
			$helper->handle_opacity_inputs($settings, 'bg_hover_opacity', 'bg_hover_color');
		}

		// Return the filtered settings.
		return $settings;
	}

	/**
	 * @method enqueue_scripts
	 */
	public function enqueue_scripts(){
		if($this->settings && 'lightbox' == $this->settings->click_action){
			$this->add_js('jquery-magnificpopup');
			$this->add_css('font-awesome-5');
			$this->add_css('jquery-magnificpopup');
		}
	}

	/**
	 * @method update
	 */
	public function update($settings){
		// Remove the old three_d setting.
		if(isset($settings->three_d)){
			unset($settings->three_d);
		}

		return $settings;
	}

}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLCMEventTextSnippets', [
	'general' => [
		'title' => __('General', 'fl-builder'),
		'sections' => [
			'general' => [
				'title' => '',
				'fields' => [
					'text_summitname' => [
						'type' => 'shortcode',
						'label' => __('Summit name', 'fl-builder'),
						'description' => __('Return site name to retrieve summit name', 'fl-builder'),
						'default' => __('[summitname]', 'fl-builder'),
						'props' => [
							'readonly' => 'readonly'
						]
					],
					'text_dayssincesummitstart' => [
						'type' => 'shortcode',
						'label' => __('Days since summit start', 'fl-builder'),
						'description' => __('Shows current day of summit', 'fl-builder'),
						'default' => __('[dayssincesummitstart]', 'fl-builder'),
					],
					'text_timeddollarsavingstext' => [
						'type' => 'shortcode',
						'label' => __('Timed dollar savings text', 'fl-builder'),
						'description' => __('Returns the dollars saved depending on when the visitor opens page. Use on welcome and early bird pages.', 'fl-builder'),
						'default' => __('[timeddollarsavingstext]', 'fl-builder'),
					],
					'text_timedpercentsavingstext' => [
						'type' => 'shortcode',
						'label' => __('Timed percent savings text', 'fl-builder'),
						'description' => __('Returns the percentage saved depending on when the visitor opens page. Use on welcome and early bird pages.', 'fl-builder'),
						'default' => __('[timedpercentsavingstext]', 'fl-builder'),
					],
					'text_summit_package_value' => [
						'type' => 'shortcode',
						'label' => __('Summit package value', 'fl-builder'),
						'description' => __('Use on welcome and early bird pages.', 'fl-builder'),
						'default' => __('[summit_package_value]', 'fl-builder'),
					],
					'text_timedpricetext' => [
						'type' => 'shortcode',
						'label' => __('Timed price text', 'fl-builder'),
						'description' => __('Returns the price depending on when the visitor opens page. Use on welcome and early bird pages.', 'fl-builder'),
						'default' => __('[timedpricetext]', 'fl-builder'),
					],
					'text_numberofspeakers' => [
						'type' => 'shortcode',
						'label' => __('Number of speakers', 'fl-builder'),
						'description' => __('Returns the number of speakers on the summit by counting how many posts have been published.', 'fl-builder'),
						'default' => __('[numberofspeakers]', 'fl-builder'),
					],
					'text_daily_theme' => [
						'type' => 'shortcode',
						'label' => __('Daily theme', 'fl-builder'),
						'description' => __('Enter the day so that the shortcode looks like this [daily_theme event_day=1].', 'fl-builder'),
						'default' => __('[daily_theme event_day=X]', 'fl-builder'),
						'readonly' => ''
					],
				],
			],
		],
	],
]);
