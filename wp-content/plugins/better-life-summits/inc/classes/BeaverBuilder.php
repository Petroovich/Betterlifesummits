<?php
/**
 * Beaver Builder Plugin Hooks & Filters
 * https://hooks.wpbeaverbuilder.com/bb-plugin/
 */

namespace BetterLifeSummits;

class BeaverBuilder{

	public static function initialise(){
		$self = new self();

		#add_action('fl_builder_before_ui_bar_title', [$self, 'fl_builder_before_ui_bar_title']);
		#add_action('fl_builder_after_ui_bar_title', [$self, 'fl_builder_after_ui_bar_title']);
		add_filter('fl_builder_ui_bar_buttons', [$self, 'fl_builder_ui_bar_buttons'], 10, 1);
		add_action('fl_builder_after_render_content', [$self, 'fl_builder_after_render_content']);
	}

	public static function fl_builder_after_render_content(){
		$shortcodes_list = include B3_PLUGIN_DIR.'/inc/shortcodes/list.php';
		#Helper::_debug($shortcodes_list[7]);
		include_once B3_VIEWS_PATH.'/builder/shortcodes-panel.php';
	}

	public static function fl_builder_ui_bar_buttons($buttons){
		$buttons['shortcodes'] = [
			'label' => __('Shortcodes', 'fl-builder'),
			'id' => 'js_shortcodes_panel_toggle',
		];

		return $buttons;
	}

}
