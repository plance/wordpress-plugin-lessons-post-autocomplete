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
		private $QueryPosts;
		private $posts_per_page = 10;

		public function __construct()
		{
			add_shortcode('post-autocomplete-form', array($this, 'shortcode_post_autocomplete_form'));
			add_shortcode('post-autocomplete-posts', array($this, 'shortcode_post_autocomplete_posts'));
			add_shortcode('post-autocomplete-pagination', array($this, 'shortcode_post_autocomplete_pagination'));

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
					's' => filter_input(INPUT_POST, 'text', FILTER_SANITIZE_STRING),
					'orderby' => 'title',
					'order' => 'ASC',
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
		 * Шорткод. Отображает форму поиска
		 */
		public function shortcode_post_autocomplete_form()
		{
			global $post;

			$content = '';
			if(is_a($post, 'WP_Post'))
			{
				$content .= '<form action="' . get_permalink($post -> ID) . '" class="post-autocomplete-form" method="GET">';
				$content .= '<input type="text" name="text" class="post-autocomplete__field" placeholder="Введите текст для поиска" value="'.filter_input(INPUT_GET, 'text', FILTER_SANITIZE_STRING).'">';
				$content .= '</form>';
			}

			return $content;
		}

		/**
		 * Шорткод. Отображает список найденных постов
		 */
		public function shortcode_post_autocomplete_posts()
		{
			//Sets
			$content = '';
			$page = max(1, get_query_var('paged'));
			$text_search = filter_input(INPUT_GET, 'text', FILTER_SANITIZE_STRING);
			$offset = $this -> posts_per_page * ($page - 1);

			if(empty($text_search))
			{
				return '';
			}

			$this -> QueryPosts = new WP_Query();
			$Posts = $this -> QueryPosts -> query([
				'posts_per_page' => $this -> posts_per_page,
				'offset' => $offset,
				's' => $text_search,
				'orderby' => 'title',
				'order' => 'ASC',
			]);

			if($this -> QueryPosts -> have_posts())
			{
				$content .= '<div>Найдено записей: '.$this -> QueryPosts -> found_posts.'</div>';
				$content .= '<ul class="post-autocomplete-posts">';
				foreach($Posts as $Post)
				{
					$content .= '<li>';
					$content .= '<a href="'.get_permalink($Post -> ID).'">';
					$content .= esc_attr($Post -> post_title);
					$content .= '</a>';
					$content .= '</li>';
				}
				$content .= '</ul>';
			}
			else
			{
				$content .= '<div class="post-autocomplete-nofound">';
				$content .= 'Ничего не найдено';
				$content .= '</div>';

				return $content;
			}
			wp_reset_postdata();


			return $content;
		}

		/**
		 * Шорткод. Отображает пагинацию
		 */
		public function shortcode_post_autocomplete_pagination()
		{
			//Sets
			$content = '';

			if(!empty($this -> QueryPosts))
			{
				$big = 999999999;
				$result = paginate_links([
					'base' => str_replace($big, '%#%', get_pagenum_link($big)),
					'format' => '',
					'current' => max(1, get_query_var('paged')),
					'total' => ceil($this -> QueryPosts -> found_posts / $this -> posts_per_page)
				]);

				$content .= str_replace('/page/1/', '', $result);
			}

			return $content;
		}
	}

	new WpPostAutocomplete();
endif;