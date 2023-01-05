<?php

namespace SiteSystemSynchronizer;

class Tools {

	public static function initialise(){}

	public static function inner_section_description(){
		return '';
	}

	public static function raw_html( $args ){
		if(empty($args['html'])){
			return;
		}

		echo $args['html'];
		if(!empty($args['desc'])) : ?>
			<p class="description"><?=$args['desc'];?></p>
		<?php endif;
	}

	public static function link_field($args){
		extract($args, EXTR_SKIP);

		$args = wp_parse_args($args, array(
			'classes' => array('button button-secondary'),
			'target' => '_self',
		));

		if(empty($args['id']) || empty($args['page']))
			return;

		?>
		<a id="<?=esc_attr($args['id']);?>" href="<?=esc_attr($link);?>" target="<?=esc_attr($target);?>" class="<?=implode(' ', $args['classes']);?>"><?=esc_attr($value);?></a>
		<?php if ( ! empty( $desc ) ) : ?>
			<p class="description"><?=$desc;?></p>
		<?php endif;
	}

	public static function button_field($args){
		self::_set_name_and_value($args);
		extract($args, EXTR_SKIP);

		$args = wp_parse_args($args, array(
			'classes' => array('button button-secondary'),
		));

		if(empty($args['id']) || empty($args['page']))
			return;

		?>
		<input type="button" id="<?=esc_attr( $args['id'] ); ?>" name="<?=esc_attr( $name ); ?>" value="<?=esc_attr( $value ); ?>" class="<?=implode( ' ', $args['classes'] ); ?>" />
		<?php if ( ! empty( $desc ) ) : ?>
			<p class="description"><?=$desc;?></p>
		<?php endif;
	}

	public static function text_field($args){
		#Helper::_debug($args); exit;
		$attrs = [];
		
		self::_set_name_and_value($args);
		extract($args, EXTR_SKIP);

		$args = wp_parse_args($args, array(
			'classes' => [],
		));

		if(empty($args['id']) || empty($args['page']))
			return;

		if(isset($args['readonly']))
			$attrs[] = 'readonly="readonly"';

		?>
		<input type="text" id="<?=esc_attr($args['id']);?>" name="<?=esc_attr($name);?>" value="<?=esc_attr($value);?>" class="<?=implode(' ', $args['classes']);?>" <?=implode(' ', $attrs);?>/>
		<?php if(!empty($desc)) : ?>
			<p class="description"><?=$desc;?></p>
		<?php endif;
	}

	public static function check_field($args){
		self::_set_name_and_value($args);
		#Helper::_debug($args); exit;
		extract($args, EXTR_SKIP);

		$args = wp_parse_args($args, array(
			'classes' => [],
		));

		if(empty($args['id']) || empty($args['page']))
			return;

		if($value){
			$args['sub_desc'] = date('d.m.Y H:i', $value);
		}
		?>
		<input type="checkbox" id="<?=esc_attr( $args['id'] ); ?>" name="<?=esc_attr($name);?>" value="<?=esc_attr($value);?>" class="<?=implode( ' ', $args['classes'] ); ?>" />
		<?php if(!empty($args['sub_desc'])) echo $args['sub_desc']; ?>
		<?php if(!empty($desc)):?>
			<p class="description"><?=$desc;?></p>
		<?php endif;
	}

	public static function textarea_field( $args ) {
		self::_set_name_and_value( $args );
		extract( $args, EXTR_SKIP );

		$args = wp_parse_args( $args, array(
			'classes' => [],
			'rows'    => 5,
			'cols'    => 50,
		) );

		if ( empty( $args['id'] ) || empty( $args['page'] ) )
			return;

		?>
		<textarea id="<?=esc_attr( $args['id'] ); ?>" name="<?=esc_attr( $name ); ?>" class="<?=implode( ' ', $args['classes'] ); ?>" rows="<?=absint( $args['rows'] ); ?>" cols="<?=absint( $args['cols'] ); ?>"><?=esc_textarea( $value ); ?></textarea>

		<?php if ( ! empty( $desc ) ) : ?>
			<p class="description"><?=$desc; ?></p>
		<?php endif;
	}

	public static function number_field( $args ) {
		self::_set_name_and_value( $args );
		extract( $args, EXTR_SKIP );

		$args = wp_parse_args( $args, array(
			'classes' => [],
			'min' => '1',
			'step' => '1',
			'desc' => '',
		) );
		if ( empty( $args['id'] ) || empty( $args['page'] ) )
			return;

		?>
		<input type="number" id="<?=esc_attr( $args['id'] ); ?>" name="<?=esc_attr( $name ); ?>" value="<?=esc_attr( $value ); ?>" class="<?=implode( ' ', $args['classes'] ); ?>" min="<?=$args['min']; ?>" step="<?=$args['step']; ?>" />
		<?php if ( ! empty( $args['sub_desc'] ) ) echo $args['sub_desc']; ?>
		<?php if ( ! empty( $args['desc'] ) ) : ?>
			<p class="description"><?=$args['desc']; ?></p>
		<?php endif;
	}

	public static function select_field( $args ) {
		self::_set_name_and_value($args);
		extract( $args, EXTR_SKIP );

		if(empty($options) || empty($id) || empty($name)){
			return;
		}

		if(!isset($size)){
			$size = 1;
		}

		?>
		<select id="<?=esc_attr( $id ); ?>" name="<?=esc_attr($name); ?>" class="<?=esc_attr($class)?>" size="<?=esc_attr($size)?>">
			<?php foreach ( $options as $name => $label ) : ?>
				<option value="<?=esc_attr( $name ); ?>" <?php selected( $name, (string) $value ); ?>>
					<?=esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php if ( ! empty( $desc ) ) : ?>
			<p class="description"><?=$desc; ?></p>
		<?php endif; ?>
		<?php
	}

	public static function yesno_field( $args ) {
		self::_set_name_and_value( $args );
		extract( $args, EXTR_SKIP );

		?>
		<label class="tix-yes-no description"><input type="radio" name="<?=esc_attr( $name ); ?>" value="1" <?php checked( $value, true ); ?>> <?php _e( 'Yes', PINLOADER_TEXT_DOMAIN ); ?></label>
		<label class="tix-yes-no description"><input type="radio" name="<?=esc_attr( $name ); ?>" value="0" <?php checked( $value, false ); ?>> <?php _e( 'No', PINLOADER_TEXT_DOMAIN ); ?></label>

		<?php if ( isset( $args['description'] ) ) : ?>
			<p class="description"><?=$args['description']; ?></p>
		<?php endif; ?>
		<?php
	}

	public static function yesno2_field( $args ) {
		self::_set_name_and_value( $args );
		extract( $args, EXTR_SKIP );

		?>
		<label class="tix-yes-no description"><input type="radio" name="<?=esc_attr( $name ); ?>" value="1" <?php checked( $value, true ); ?>> <?php _e( 'Forced', PINLOADER_TEXT_DOMAIN ); ?></label>
		<label class="tix-yes-no description"><input type="radio" name="<?=esc_attr( $name ); ?>" value="0" <?php checked( $value, false ); ?>> <?php _e( 'Automatically', PINLOADER_TEXT_DOMAIN ); ?></label>

		<?php if ( isset( $args['description'] ) ) : ?>
			<p class="description"><?=$args['description']; ?></p>
		<?php endif; ?>
		<?php
	}

	private static function _set_name_and_value(&$args){
		if(!isset($args['name'])){
			$args['name'] = sprintf('%s[%s]', esc_attr($args['page']), esc_attr($args['id']));
		}

		if(!isset($args['value'])){
			$args['value'] = Core::get_option($args['id']);
		}
	}
	
	/** FORM FIELDS **/
	
	public static function form_input($args){
		$default = array(
			'type' => 'text',
			'placeholder' => '',
			'class' => array('form-control'),
			'name' => 'text_'.md5('test'.time()),
			'id' => '',
			'value' => (isset($_REQUEST[$args['name']]) ? $_REQUEST[$args['name']] : false),
			'mobile' => false,
		);
		
		$args = wp_parse_args($args, $default);
		
		$html = sprintf('<input type="%s" name="%s" value="%s" id="%s" class="%s" placeholder="%s" data-mobile="%s">',
			$args['type'],
			$args['name'],
			$args['value'],
			$args['id'],
			implode(' ', $args['class']),
			$args['placeholder'],
			$args['mobile']
		);
		
		return $html;
	}
	
	public static function form_select($args, $options){
		$default = array(
			'title' => '',
			'class' => array('selectpicker'),
			'name' => md5('test'.time()),
			'id' => '',
			'value' => (isset($_REQUEST[$args['name']]) ? $_REQUEST[$args['name']] : false),
			'mobile' => false,
		);
		
		$args = wp_parse_args($args, $default);
		
		$def_class = array('selectpicker');
		$args['class'] = wp_parse_args($args['class'], $def_class);
		
		//self::_debug($args);
		
		$_options = [];
		foreach($options as $k => $v){
			$selected = ($k == $args['value']) ? 'selected="selected"' : '';
			$_options[] = sprintf('<option value="%d" %s>%s</option>', $k, $selected, $v);
		}
		
		$html = sprintf('<select name="%s" id="%s" class="%s" data-title="%s" data-mobile="%s">%s</select>',
			$args['name'],
			$args['id'],
			implode(' ', $args['class']),
			$args['title'],
			$args['mobile'],
			implode('', $_options)
		);
		
		return $html;
	}
	
	public static function form_month_select($args){
		$default = array(
			'title' => 'Month',
			'class' => array('selectpicker'),
			'name' => 'month',
			'id' => '',
			'value' => (isset($_REQUEST[$args['name']]) ? $_REQUEST[$args['name']] : false),
			'mobile' => false,
		);
		
		$args = wp_parse_args($args, $default);
		
		if(false == $args['mobile']){
			self::$months[0] = "Month";
			ksort(self::$months);
		}
		
		return self::form_select($args, self::$months);
	}
	
	public static function form_year_select($args){
		$default = array(
			'title' => 'Year',
			'class' => array('selectpicker'),
			'name' => 'year',
			'id' => '',
			'value' => (isset($_REQUEST[$args['name']]) ? $_REQUEST[$args['name']] : false),
			'mobile' => false,
		);
		
		$args = wp_parse_args($args, $default);
		
		if(false == $args['mobile']){
			$options = array(0 => $args['title']);
		}else{
			$options = [];
		}
		for($i = date('Y'); $i <= date('Y')+10; $i++){
			$options[$i] = $i;
		}
		
		return self::form_select($args, $options);
	}
	
	public static function form_cats_select($args){
		$default = array(
			'title' => 'Categories',
			'class' => array('selectpicker'),
			'name' => 'categories',
			'taxonomy' => 'category',
			'id' => '',
			'value' => (isset($_REQUEST[$args['name']]) ? $_REQUEST[$args['name']] : false),
			'mobile' => false,
		);
		
		$args = wp_parse_args($args, $default);
		
		$terms = get_terms(array('taxonomy' => $args['taxonomy'], 'fields' => 'id=>name'));
		
		if(false == $args['mobile']){
			$terms[0] = "Category";
			ksort($terms);
		}
		
		return self::form_select($args, $terms);
	}
	
	public static function form_course_select($args){
		$default = array(
			'title' => 'Date',
			'class' => array('selectpicker'),
			'name' => 'course_date',
			'id' => '',
			'value' => (isset($_REQUEST[$args['name']]) ? $_REQUEST[$args['name']] : false),
			'mobile' => false,
			'options' => [],
		);
		
		$args = wp_parse_args($args, $default);
		
		$options = array(0 => $args['title']);
		if(!empty($args['options'])){
			foreach($args['options'] as $time){
				$options[] = date('H:i', strtotime($time['time']));
			}
		}
		
		
		return self::form_select($args, $options);
	}
	
	public static function form_course_dropdown($args){
		$default = array(
			'title' => 'Date',
			'class' => [],
			'name' => 'course_date',
			'id' => '',
			'options' => [],
		);
		
		$args = wp_parse_args($args, $default);
		
		$options_html = '
			<li data-original-index="%d">
				<a tabindex="0" class="%s" role="option" aria-disabled="false" aria-selected="false">
					<span class="text">%s</span>
				</a>
			</li>
		';
		$options = [];
		if(!empty($args['options'])){
			foreach($args['options'] as $k => $time){
				$options[] = sprintf($options_html, ($k+1), '', date('H:i', strtotime($time['time'])));
			}
		}
		
		
		$html = '
			<div class="btn-group '.implode(' ', $args['class']).'">
				<button type="button" class="btn dropdown-toggle btn-default" data-toggle="dropdown" role="button" data-id="" title="'.$args['title'].'" aria-expanded="false">
					<span class="filter-option pull-left">'.$args['title'].'</span>&nbsp;<span class="bs-caret"><span class="caret"></span></span>
				</button>
				<div class="dropdown-menu" role="combobox">
					<ul class="dropdown-menu inner" role="listbox" aria-expanded="false">
					'.implode('', $options).'
					</ul>
				</div>
			</div>
		';
		
		
		return $html;
	}
	
	public static function form_course_panel($args){
		$default = array(
			'z_index' => 1,
			'label' => 'Date',
			'wrap_class' => array('panel', 'toggle-panel', 'dropdown'),
			'title_class' => array('title', 'collapsed', 'small-arrow'),
			'id' => '',
			'options' => [],
		);
		
		$args['wrap_class'] = wp_parse_args($args['wrap_class'], $default['wrap_class']);
		$args['title_class'] = wp_parse_args($args['title_class'], $default['title_class']);
		$args = wp_parse_args($args, $default);
		
		$options_html = '<li data-original-index="%d"><a class="link">%s</a></li>';
		$options = [];
		if(!empty($args['options'])){
			foreach($args['options'] as $k => $time){
				$options[] = sprintf($options_html, ($k+1), date('H:i', strtotime($time['time'])));
			}
		}
		
		
		$html = '
			<div class="'.implode(' ', $args['wrap_class']).'" style="z-index:'.$args['z_index'].';">
				<a class="'.implode(' ', $args['title_class']).'" role="button" data-toggle="collapse" data-parent="#'.$args['id'].'" href="#'.$args['id'].'" aria-expanded="true" aria-controls="collapse">
					'.$args['label'].'
				</a>
				<div id="'.$args['id'].'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading">
					<ul class="links">
						'.implode('', $options).'
					</ul>
				</div>
			</div>
		';
		
		
		return $html;
	}

}

