<?php

/**
 * All speakers - shortcode :
 *
 * @class FLCMAllSpeakers
 */
class FLCMAllSpeakers extends FLBuilderModule{

	/**
	 * @method __construct
	 */
	public function __construct(){
		parent::__construct([
			'name' => __('All speakers', 'fl-builder'),
			'description' => __('All speakers.', 'fl-builder'),
			'category' => __('Custom', 'fl-builder'),
			'group' => __('Custom Modules', 'fl-builder'),
			'partial_refresh' => true,
			#'icon' => 'edit-page.svg',
		]);
	}

	/**
	 * @param object $settings A module settings object.
	 * @param object $helper A settings compatibility helper.
	 *
	 * @return object
	 */
	public function filter_settings($settings, $helper){
		return $settings;
	}

	/**
	 * @method update
	 */
	public function update($settings){
		return $settings;
	}
}

/**
 * Register the module and its form settings.
 */
FLBuilder::register_module('FLCMAllSpeakers', [
	'general' => [
		'title' => __('General', 'fl-builder'),
		'sections' => [
			'general' => [
				'title' => '',
				'fields' => [
					'page_type' => [
						'type' => 'select',
						'label' => __('Page type', 'fl-builder'),
						'default' => 'sales',
						'options' => [
							'sales' => __('Sales page', 'fl-builder'),
							'registration' => __('Registration page', 'fl-builder'),
						],
					],
					'posts_per_page' => [
						'type' => 'unit',
						'label' => __('Posts per page', 'fl-builder'),
						'description' => __('-1 = all posts', 'fl-builder'),
						'default' => 50,
						'slider' => [
							'min' => -1,
							'max' => 100,
							'step' => 1,
						],
					],
					'orderby' => [
						'type' => 'select',
						'label' => __('Order by', 'fl-builder'),
						'default' => 'meta_value',
						'options' => [
							'ID' => 'ID',
							'name' => 'Name',
							'date' => 'Date',
							'meta_value' => 'Meta value',
						],
						'toggle' => [
							'meta_value' => [
								'fields' => ['meta_key'],
							],
						],
					],
					'meta_key' => [
						'type' => 'select',
						'label' => __('Meta key', 'fl-builder'),
						'default' => 'speaker_slot_of_the_event',
						'options' => [
							'speaker_slot_of_the_day' => 'Daily order',
							'speaker_slot_of_the_event' => 'Presentation order',
						],
						'connections' => ['string'],
					],
					'order' => [
						'type' => 'select',
						'label' => __('Order direction', 'fl-builder'),
						'default' => 'ASC',
						'options' => [
							'ASC' => 'ASC',
							'DESC' => 'DESC',
						],
					],
				],
			],
		],
	],
]);
