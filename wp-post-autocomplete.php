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
			add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
			
			/** Ajax */
			add_action('wp_ajax_autocomplete', array($this, 'ajax_autocomplete'));
			add_action('wp_ajax_nopriv_autocomplete', array($this, 'ajax_autocomplete'));
		}

		public function wp_enqueue_scripts()
		{
			wp_enqueue_style('wp-post-autocomplete', plugin_dir_url(__FILE__).'assets/style.css');
			wp_enqueue_style('wp-post-autocomplete-jquery-ui', plugin_dir_url(__FILE__).'assets/jquery-ui/jquery-ui.min.css');

			wp_enqueue_script('wp-post-autocomplete', plugin_dir_url(__FILE__).'assets/script.js', array('jquery', 'jquery-ui-widget', 'jquery-ui-autocomplete'), null, true);

			wp_localize_script('wp-post-autocomplete', 'WpPostAutocomplete', array(
				'ajax' => admin_url('admin-ajax.php'),
				'action' => 'autocomplete',
				'security' => wp_create_nonce('autocomplete_security'),
			));
		}
		
		//===========================================================
		// AJAX
		//===========================================================
		
		/**
		 * Test: curl -d "action=autocomplete&text=это" -X POST http://wordpress.l/wp-admin/admin-ajax.php | json_pp
		 */
		public function ajax_autocomplete()
		{
	//		check_ajax_referer('autocomplete_security', 'security');
			
			try
			{
				$Posts = get_posts([
					'posts_per_page' => 10,
					's' => esc_sql(filter_input(INPUT_POST, 'text', FILTER_SANITIZE_STRING)),
				]);
				
				$array = [];
				foreach($Posts as $Post)
				{
					$array[] = [
						'title' => $Post -> post_title,
						'link' => get_permalink($Post -> ID),
					];
				}

				wp_send_json_success($array);			
			}
			catch (Exception $ex)
			{
				wp_send_json_error(array(
					'message' => 'Не удалось обработать запрос',
				));
			}
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