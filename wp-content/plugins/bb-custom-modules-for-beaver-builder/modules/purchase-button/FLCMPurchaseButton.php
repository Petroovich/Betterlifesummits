<?php

/**
 * @class FLCMPurchaseButton
 */
class FLCMPurchaseButton extends FLBuilderModule{

	/**
	 * @method __construct
	 */
	public function __construct(){
		parent::__construct([
			'name' => __('Purchase Button', 'fl-builder'),
			'description' => __('A purchase popup button.', 'fl-builder'),
			'category' => __('Custom', 'fl-builder'),
			'group' => __('Custom Modules', 'fl-builder'),
			'partial_refresh' => true,
			'icon' => 'button.svg',
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

	/**
	 * @method get_classname
	 */
	public function get_classname(){
		$classname = 'fl-button-wrap';

		if(!empty($this->settings->width)){
			$classname .= ' fl-button-width-'.$this->settings->width;
		}
		if(!empty($this->settings->align)){
			$classname .= ' fl-button-'.$this->settings->align;
		}
		if(!empty($this->settings->icon)){
			$classname .= ' fl-button-has-icon';
		}

		return $classname;
	}

	/**
	 * Returns button link rel based on settings
	 * @since 1.10.9
	 */
	public function get_rel(){
		$rel = [];
		if('_blank' == $this->settings->link_target){
			$rel[] = 'noopener';
		}
		if(isset($this->settings->link_nofollow) && 'yes' == $this->settings->link_nofollow){
			$rel[] = 'nofollow';
		}
		$rel = implode(' ', $rel);
		if($rel){
			$rel = ' rel="'.$rel.'" ';
		}
		return $rel;
	}

}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLCMPurchaseButton', [
	'general' => [
		'title' => __('General', 'fl-builder'),
		'sections' => [
			'general' => [
				'title' => '',
				'fields' => [
					'text' => [
						'type' => 'text',
						'label' => __('Text', 'fl-builder'),
						'default' => __('BUY NOW', 'fl-builder'),
						'preview' => [
							'type' => 'text',
							'selector' => '.fl-button-text',
						],
						'connections' => ['string'],
					],
					'icon' => [
						'type' => 'icon',
						'label' => __('Icon', 'fl-builder'),
						'show_remove' => true,
						'show' => [
							'fields' => ['icon_position', 'icon_animation'],
						],
						'preview' => [
							'type' => 'none',
						],
					],
					'icon_position' => [
						'type' => 'select',
						'label' => __('Icon Position', 'fl-builder'),
						'default' => 'before',
						'options' => [
							'before' => __('Before Text', 'fl-builder'),
							'after' => __('After Text', 'fl-builder'),
						],
						'preview' => [
							'type' => 'none',
						],
					],
					'icon_animation' => [
						'type' => 'select',
						'label' => __('Icon Visibility', 'fl-builder'),
						'default' => 'disable',
						'options' => [
							'disable' => __('Always Visible', 'fl-builder'),
							'enable' => __('Fade In On Hover', 'fl-builder'),
						],
						'preview' => [
							'type' => 'none',
						],
					],
					'click_action' => [
						'type' => 'select',
						'label' => __('Click Action', 'fl-builder'),
						'default' => 'auto',
						'options' => [
							'auto' => __('Auto by ACF fields logic', 'fl-builder'),
							#'link' => __('Link', 'fl-builder'),
							'shortcode' => __('Shortcode', 'fl-builder'),
						],
						'toggle' => [
							'auto' => [
								'fields' => '',
							],
							'link' => [
								'fields' => ['link'],
							],
							'shortcode' => [
								'fields' => ['shortcode'],
							],
						],
						'preview' => [
							'type' => 'none',
						],
					],
					'link' => [
						'type' => 'link',
						'default' => '#',
						'label' => __('Link', 'fl-builder'),
						'placeholder' => __('http://www.example.com', 'fl-builder'),
						'show_target' => false,
						'show_nofollow' => false,
						'show_download' => false,
						'preview' => [
							'type' => 'none',
						],
						'connections' => ['url'],
					],
					'shortcode' => [
						'type' => 'text',
						'label' => __('Shortcode', 'fl-builder'),
						'placeholder' => __('[custom_shortcode id="123"]', 'fl-builder'),
						'preview' => [
							'type' => 'none',
						],
						'connections' => ['string'],
					],
					'css_class' => [
						'type' => 'text',
						'label' => __('CSS class', 'fl-builder'),
						'connections' => ['string'],
					],
				],
			],
		],
	],
	'style' => [
		'title' => __('Style', 'fl-builder'),
		'sections' => [
			'style' => [
				'title' => '',
				'fields' => [
					'width' => [
						'type' => 'select',
						'label' => __('Width', 'fl-builder'),
						'default' => 'auto',
						'options' => [
							'auto' => _x('Auto', 'Width.', 'fl-builder'),
							'full' => __('Full Width', 'fl-builder'),
							'custom' => __('Custom', 'fl-builder'),
						],
						'toggle' => [
							'auto' => [
								'fields' => ['align'],
							],
							'full' => [],
							'custom' => [
								'fields' => ['align', 'custom_width'],
							],
						],
					],
					'custom_width' => [
						'type' => 'unit',
						'label' => __('Custom Width', 'fl-builder'),
						'default' => '200',
						'slider' => [
							'px' => [
								'min' => 0,
								'max' => 1000,
								'step' => 10,
							],
						],
						'units' => [
							'px',
							'vw',
							'%',
						],
						'preview' => [
							'type' => 'css',
							'selector' => 'a.fl-button',
							'property' => 'width',
						],
					],
					'align' => [
						'type' => 'align',
						'label' => __('Align', 'fl-builder'),
						'default' => 'left',
						'responsive' => true,
						'preview' => [
							'type' => 'css',
							'selector' => '.fl-button-wrap',
							'property' => 'text-align',
						],
					],
					'padding' => [
						'type' => 'dimension',
						'label' => __('Padding', 'fl-builder'),
						'responsive' => true,
						'slider' => true,
						'units' => ['px'],
						'preview' => [
							'type' => 'css',
							'selector' => 'a.fl-button',
							'property' => 'padding',
						],
					],
				],
			],
			'text' => [
				'title' => __('Text', 'fl-builder'),
				'fields' => [
					'text_color' => [
						'type' => 'color',
						'connections' => ['color'],
						'label' => __('Text Color', 'fl-builder'),
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'preview' => [
							'type' => 'css',
							'selector' => 'a.fl-button, a.fl-button *',
							'property' => 'color',
							'important' => true,
						],
					],
					'text_hover_color' => [
						'type' => 'color',
						'connections' => ['color'],
						'label' => __('Text Hover Color', 'fl-builder'),
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'preview' => [
							'type' => 'css',
							'selector' => 'a.fl-button:hover, a.fl-button:hover *, a.fl-button:focus, a.fl-button:focus *',
							'property' => 'color',
							'important' => true,
						],
					],
					'typography' => [
						'type' => 'typography',
						'label' => __('Typography', 'fl-builder'),
						'responsive' => true,
						'preview' => [
							'type' => 'css',
							'selector' => 'a.fl-button',
						],
					],
				],
			],
			'icons' => [
				'title' => __('Icon', 'fl-builder'),
				'fields' => [
					'duo_color1' => [
						'label' => __('DuoTone Icon Primary Color', 'fl-builder'),
						'type' => 'color',
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'preview' => [
							'type' => 'css',
							'selector' => 'i.fl-button-icon.fad:before',
							'property' => 'color',
							'important' => true,
						],
					],
					'duo_color2' => [
						'label' => __('DuoTone Icon Secondary Color', 'fl-builder'),
						'type' => 'color',
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'preview' => [
							'type' => 'css',
							'selector' => 'i.fl-button-icon.fad:after',
							'property' => 'color',
							'important' => true,
						],
					],
				],
			],
			'colors' => [
				'title' => __('Background', 'fl-builder'),
				'fields' => [
					'bg_color' => [
						'type' => 'color',
						'connections' => ['color'],
						'label' => __('Background Color', 'fl-builder'),
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'preview' => [
							'type' => 'none',
						],
					],
					'bg_hover_color' => [
						'type' => 'color',
						'connections' => ['color'],
						'label' => __('Background Hover Color', 'fl-builder'),
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'preview' => [
							'type' => 'none',
						],
					],
					'style' => [
						'type' => 'select',
						'label' => __('Background Style', 'fl-builder'),
						'default' => 'flat',
						'options' => [
							'flat' => __('Flat', 'fl-builder'),
							'gradient' => __('Gradient', 'fl-builder'),
						],
					],
					'button_transition' => [
						'type' => 'select',
						'label' => __('Background Animation', 'fl-builder'),
						'default' => 'disable',
						'options' => [
							'disable' => __('Disabled', 'fl-builder'),
							'enable' => __('Enabled', 'fl-builder'),
						],
						'preview' => [
							'type' => 'none',
						],
					],
				],
			],
			'border' => [
				'title' => __('Border', 'fl-builder'),
				'fields' => [
					'border' => [
						'type' => 'border',
						'label' => __('Border', 'fl-builder'),
						'responsive' => true,
						'preview' => [
							'type' => 'css',
							'selector' => 'a.fl-button',
							'important' => true,
						],
					],
					'border_hover_color' => [
						'type' => 'color',
						'connections' => ['color'],
						'label' => __('Border Hover Color', 'fl-builder'),
						'default' => '',
						'show_reset' => true,
						'show_alpha' => true,
						'preview' => [
							'type' => 'none',
						],
					],
				],
			],
		],
	],
]);
