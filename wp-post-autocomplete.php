<?php
/*
Plugin Name:  WP Post Autocomplete
Description:  Яякс поиск статей с автокомплитом. Для отображения формы поиска, используйте шорткод [post-autocomplete-form']
Version: 1.0.0
*/

defined('ABSPATH') or die('No script kiddies please!');

if(class_exists('WpPostAutocomplete') == false):
	class WpPostAutocomplete
	{
		public function __construct()
		{
			add_shortcode('post-autocomplete-form', array($this, 'post_autocomplete_form'));
		}

		//===========================================================
		// SHORTCODE
		//===========================================================

		/**
		 * Отображает форму поиска
		 */
		public function post_autocomplete_form()
		{
			$content  = '<div class="wrap-post-autocomplete">';
			$content .= '<input type="text" class="post-autocomplete-field" placeholder="Введите текст для поиска">';
			$content .= '</div>';

			return $content;
		}
	}

	new WpPostAutocomplete();
endif;