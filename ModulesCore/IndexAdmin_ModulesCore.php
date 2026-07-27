<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 09.02.2022
 * Time: 11:41:07
 */

require_once( 'api/Okay.php' );

class IndexAdmin extends Okay {

	/*Массив с меню сайта (из него автоматически формируется главное меню админки)*/
	private $left_menu = array(
		'left_catalog' => array(
			'left_products_title' => array( 'ProductsAdmin', 'ProductAdmin' ),
			// 'left_types_title'          => array('ProductsTypesAdmin', 'ProductsTypeAdmin'),
			'left_categories_title' => array( 'CategoriesAdmin', 'CategoryAdmin' ),
			'left_brands_title' => array( 'BrandsAdmin', 'BrandAdmin' ),
			'left_features_title' => array( 'FeaturesAdmin', 'FeatureAdmin' ),
			/* rozetka */
			'left_rozetka_title' => array( 'RozetkaCategoriesAdmin' ),
			/* rozetka */
			/* epicentrk */
			// 'left_epicentrk_title'      => array('EpicentrKCategoriesAdmin'),
			/* epicentrk */
		),
		'left_orders' => array(
			'left_orders_title' => array( 'OrdersAdmin', 'OrderAdmin' ),
			'left_orders_settings_title' => array( 'OrderSettingsAdmin' ),
		),
		'left_users' => array(
			'left_users_title' => array( 'UsersAdmin', 'UserAdmin' ),
			'left_groups_title' => array( 'UserGroupsAdmin', 'UserGroupAdmin' ),
			'left_coupons_title' => array( 'CouponsAdmin' ),
			'left_subscribe_title' => array( 'SubscribeMailingAdmin' ),
			'left_referrals_title' => array( 'ReferralsAdmin', 'ReferralAdmin' ),
		),
		'left_pages' => array(
			'left_pages_title' => array( 'PagesAdmin', 'PageAdmin' ),
			'left_menus_title' => array( 'MenusAdmin', 'MenuAdmin' ),
		),
		'left_blog' => array(
			'left_blog_title' => array( 'BlogAdmin', 'PostAdmin' ),
		),
		'left_comments' => array(
			'left_comments_title' => array( 'CommentsAdmin' ),
			'left_feedbacks_title' => array( 'FeedbacksAdmin' ),
			'left_callbacks_title' => array( 'CallbacksAdmin' ),
		),
		'left_auto' => array(
			'left_import_title' => array( 'ImportAdmin' ),
			'left_export_title' => array( 'ExportAdmin' ),
			'left_log_title' => array( 'ImportLogAdmin' ),
		),
		'left_stats' => array(
			'left_stats_title' => array( 'StatsAdmin' ),
			'left_products_stat_title' => array( 'ReportStatsAdmin' ),
			'left_categories_stat_title' => array( 'CategoryStatsAdmin' ),
		),
		'left_seo' => array(
			'left_robots_title' => array( 'RobotsAdmin' ),
			'left_setting_counter_title' => array( 'SettingsCounterAdmin' ),
			'left_seo_patterns_title' => array( 'SeoPatternsAdmin' ),
			'left_seo_filter_patterns_title' => array( 'SeoFilterPatternsAdmin' ),
			'left_feature_aliases_title' => array( 'FeaturesAliasesAdmin' ),
		),
		'left_design' => array(
			'left_theme_title' => array( 'ThemeAdmin' ),
			'left_template_title' => array( 'TemplatesAdmin' ),
			'left_style_title' => array( 'StylesAdmin' ),
			'left_script_title' => array( 'ScriptsAdmin' ),
			'left_images_title' => array( 'ImagesAdmin' ),
			'left_translations_title' => array( 'TranslationsAdmin', 'TranslationAdmin' ),
		),
		'left_banners' => array(
			'left_banners_title' => array( 'BannersAdmin', 'BannerAdmin' ),
			'left_banners_images_title' => array( 'BannersImagesAdmin', 'BannersImageAdmin' ),
		),
		'left_settings' => array(
			'left_setting_general_title' => array( 'SettingsGeneralAdmin' ),
			'left_setting_notify_title' => array( 'SettingsNotifyAdmin' ),
			'left_setting_catalog_title' => array( 'SettingsCatalogAdmin' ),
			'left_setting_feed_title' => array( 'SettingsFeedAdmin' ),
			'left_currency_title' => array( 'CurrencyAdmin' ),
			'left_delivery_title' => array( 'DeliveriesAdmin', 'DeliveryAdmin' ),
			'left_payment_title' => array( 'PaymentMethodsAdmin', 'PaymentMethodAdmin' ),
			'left_managers_title' => array( 'ManagersAdmin', 'ManagerAdmin' ),
			'left_languages_title' => array( 'LanguagesAdmin', 'LanguageAdmin' ),
			'left_system_title' => array( 'SystemAdmin' ),
		),
		/* stores */
		'left_stores' => array(
			'left_stores_title' => array( 'StoresAdmin', 'StoreAdmin' ),
		),
		/* stores */

		'left_metachanges' => array(
			'left_metachanges_title' => array( 'MetaChangesAdmin', 'MetaChangeAdmin' ),
		),

		'left_seocomments' => array(
			'left_seocomments_title' => array( 'SeoCommentsAdmin', 'SeoCommentAdmin' ),
		),

		'left_fastfilters' => array(
			'left_fastfilters_title' => array( 'FastFiltersAdmin', 'FastFilterAdmin' ),
		),

		'left_apis' => array(
			/* turbosms */
			'left_turbosms_title' => array( 'SettingsTurboSMSAdmin' ),
			/* turbosms */
			/* esputnik */
			'left_setting_esputnik_title' => array( 'SettingsESputnikAdmin' ),
			/* esputnik */
			/* novaposhta */
			'left_novaposhta_title' => array( 'SettingsNovaPoshtaAdmin' ),
			/* novaposhta */
			/* justin */
			'left_justin_title' => array( 'SettingsJustInAdmin' ),
			/* justin */
			/* autorize */
			'left_autorize_title' => array( 'SettingsAutorizeAdmin' ),
			/* autorize */
		),
	);

	// Соответсвие модулей и названий соответствующих прав
	private $modules_permissions = array(
		'ProductsAdmin' => 'products',
		'ProductAdmin' => 'products',
		'CategoriesAdmin' => 'categories',
		'CategoryAdmin' => 'categories',
		'BrandsAdmin' => 'brands',
		'BrandAdmin' => 'brands',
		'FeaturesAdmin' => 'features',
		'FeatureAdmin' => 'features',
		'OrdersAdmin' => 'orders',
		'OrderAdmin' => 'orders',
		'UsersAdmin' => 'users',
		'UserAdmin' => 'users',
		'ExportUsersAdmin' => 'users',
		'UserGroupsAdmin' => 'groups',
		'UserGroupAdmin' => 'groups',
		'CouponsAdmin' => 'coupons',
		'PagesAdmin' => 'pages',
		'PageAdmin' => 'pages',
		'MenusAdmin' => 'menu',
		'MenuAdmin' => 'menu',
		'BlogAdmin' => 'blog',
		'PostAdmin' => 'blog',
		'CommentsAdmin' => 'comments',
		'FeedbacksAdmin' => 'feedbacks',
		'ImportAdmin' => 'import',
		'ExportAdmin' => 'export',
		'ImportLogAdmin' => 'import',
		'StatsAdmin' => 'stats',
		'ThemeAdmin' => 'design',
		'StylesAdmin' => 'design',
		'TemplatesAdmin' => 'design',
		'ImagesAdmin' => 'design',
		'ScriptsAdmin' => 'design',
		'SettingsGeneralAdmin' => 'settings',
		'SettingsNotifyAdmin' => 'settings',
		'SettingsCatalogAdmin' => 'settings',
		'SettingsCounterAdmin' => 'settings_counter',
		'SettingsFeedAdmin' => 'settings',
		'SystemAdmin' => 'settings',
		'CurrencyAdmin' => 'currency',
		'DeliveriesAdmin' => 'delivery',
		'DeliveryAdmin' => 'delivery',
		'PaymentMethodAdmin' => 'payment',
		'PaymentMethodsAdmin' => 'payment',
		'ManagersAdmin' => 'managers',
		'ManagerAdmin' => 'managers',
		'SubscribeMailingAdmin' => 'subscribes',
		'BannersAdmin' => 'banners',
		'BannerAdmin' => 'banners',
		'BannersImagesAdmin' => 'banners',
		'BannersImageAdmin' => 'banners',
		'CallbacksAdmin' => 'callbacks',

		/* Мультиязычность start */
		'LanguageAdmin' => 'languages',
		'LanguagesAdmin' => 'languages',
		'TranslationAdmin' => 'languages',
		'TranslationsAdmin' => 'languages',
		/* Мультиязычность end */
		/*statistic*/
		'ReportStatsAdmin' => 'stats',
		'CategoryStatsAdmin' => 'stats',
		/*statistic*/
		'RobotsAdmin' => 'robots',
		'OrderSettingsAdmin' => 'order_settings',
		'SeoPatternsAdmin' => 'seo_patterns',
		'SeoFilterPatternsAdmin' => 'seo_filter_patterns',
		'FeaturesAliasesAdmin' => 'features_aliases',

		/* turbosms */
		'SettingsTurboSMSAdmin' => 'settings',
		/* turbosms */
		/* esputnik */
		'SettingsESputnikAdmin' => 'settings',
		/* esputnik */
		/* novaposhta */
		'SettingsNovaPoshtaAdmin' => 'settings',
		/* novaposhta */
		/* justin */
		'SettingsJustInAdmin' => 'settings',
		/* justin */
		/* autorize */
		'SettingsAutorizeAdmin' => 'settings',
		/* autorize */
		/* products_types */
		'ProductsTypeAdmin' => 'products',
		'ProductsTypesAdmin' => 'products',
		/* products_types */
		/* stores */
		'StoreAdmin' => 'settings',
		'StoresAdmin' => 'settings',
		/* stores */
		/* referrals */
		'ReferralAdmin' => 'referrals',
		'ReferralsAdmin' => 'referrals',
		/* referrals */

		'MetaChangesAdmin' => 'metachanges',
		'MetaChangeAdmin' => 'metachanges',

		'SeoCommentsAdmin' => 'seocomments',
		'SeoCommentAdmin' => 'seocomments',

		'FastFiltersAdmin' => 'fastfilters',
		'FastFilterAdmin' => 'fastfilters',

		/* rozetka */
		'RozetkaCategoriesAdmin' => 'features',
		/* rozetka */
		/* epicentrk */
		// 'EpicentrKCategoriesAdmin'  => 'features',
		/* epicentrk */

	);


	// Конструктор
	public function __construct() {
		// Вызываем конструктор базового класса
		parent::__construct();

		// Берем название модуля из get-запроса
		$module = $this->request->get( 'module', 'string' );
		$module = preg_replace( "/[^A-Za-z0-9]+/", "", $module );

		// Администратор
		$this->manager = $this->managers->get_manager();
		$this->design->assign( 'manager', $this->manager );
		if ( ! $this->manager && $module != 'AuthAdmin' ) {
			$_SESSION['before_auth_url'] = $this->config->protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
			header( 'location: ' . $this->config->root_url . '/backend/index.php?module=AuthAdmin' );
			exit();
		} elseif ( $this->manager && $module == 'AuthAdmin' ) {
			header( 'location: ' . $this->config->root_url . '/backend/index.php' );
			exit();
		}

		// Перевод админки
		$backend_translations = $this->backend_translations;
		$file = "backend/lang/" . $this->manager->lang . ".php";
		if ( ! file_exists( $file ) ) {
			foreach ( glob( "backend/lang/??.php" ) as $f ) {
				$file = "backend/lang/" . pathinfo( $f, PATHINFO_FILENAME ) . ".php";
				break;
			}
		}

		// ModulesCore //!! 
		// Расширение прав доступа через внешний файл
		$modules_permissions = 'ModulesCore/modules_permissions.php';
		if ( file_exists( $modules_permissions ) ) {
			$custom_permissions = include $modules_permissions;
			if ( is_array( $custom_permissions ) ) {
				$this->modules_permissions = array_merge( $this->modules_permissions, $custom_permissions );
			}
		}

		$i = 0;
		while ( file_exists( $file ) ) {
			require_once( $file );
			$file = "backend/lang/" . $this->manager->lang . "-" . ++$i . ".php";
		}


		if ( isset( $_SESSION['message_success'] ) ) {
			$this->design->assign( 'message_success', $_SESSION['message_success'] );
			unset( $_SESSION['message_success'] );
		}

		$this->design->set_templates_dir( 'backend/design/html' );
		// $this->design->smarty->addTemplateDir( __DIR__ . '/../../ModulesCore/modules/Promotions/design/html' );
		// ModulesCore //!! 
		// Установка директорий шаблонов модулей через внешний файл
		$modules_template_dir = 'ModulesCore/modules_template_dir.php';

		if ( file_exists( $modules_template_dir ) ) {

			$arr_template_dir = include $modules_template_dir;
			if ( is_array( $arr_template_dir ) ) {
				$this->design->smarty->addTemplateDir( $arr_template_dir );
			}
		}


		$this->design->set_compiled_dir( 'backend/design/compiled' );

		$this->design->assign( 'settings', $this->settings );
		$this->design->assign( 'config', $this->config );

		$is_mobile = $this->design->is_mobile();
		$is_tablet = $this->design->is_tablet();
		$this->design->assign( 'is_mobile', $is_mobile );
		$this->design->assign( 'is_tablet', $is_tablet );

		// Язык
		$languages = $this->languages->get_languages();
		$this->design->assign( 'languages', $languages );

		if ( count( $languages ) ) {
			$post_lang_id = $this->request->post( 'lang_id', 'integer' );
			$admin_lang_id = ( $post_lang_id ? $post_lang_id : $this->request->get( 'lang_id', 'integer' ) );
			if ( $admin_lang_id ) {
				$_SESSION['admin_lang_id'] = $admin_lang_id;
			}
			if ( ! isset( $_SESSION['admin_lang_id'] ) || ! isset( $languages[ $_SESSION['admin_lang_id'] ] ) ) {
				$l = $this->languages->get_first_language();
				$_SESSION['admin_lang_id'] = $l->id;
			}
			$this->design->assign( 'current_language', $languages[ $_SESSION['admin_lang_id'] ] );
			$this->languages->set_lang_id( $_SESSION['admin_lang_id'] );
		}

		$lang_id = $this->languages->lang_id();
		$this->design->assign( 'lang_id', $lang_id );

		$main_lang = $this->languages->get_first_language();
		$this->design->assign( 'main_lang_id', $main_lang->id );

		$this->design->assign( 'lang_link', $this->languages->get_lang_link() );

		/*Формирование меню*/
		if ( $module != "AuthAdmin" ) {
			$menu_selected = '';
			foreach ( $this->left_menu as $section => &$items ) {
				foreach ( $items as $title => &$modules ) {
					if ( in_array( $module, $modules ) ) {
						$menu_selected = $title;
					}
					$modules = reset( $modules );
					if ( ! in_array( $this->modules_permissions[ $modules ], $this->manager->permissions ) ) {
						unset( $this->left_menu[ $section ][ $title ] );
						unset( $this->manager->menu[ $section ][ $title ] );
					} else {
						$this->manager->menu[ $section ][ $title ] = $modules;
					}
				}
				if ( count( $this->left_menu[ $section ] ) == 0 ) {
					unset( $this->left_menu[ $section ] );
				}
				if ( count( $this->manager->menu[ $section ] ) == 0 ) {
					unset( $this->manager->menu[ $section ] );
				}
				unset( $modules );
			}
			unset( $items );

			// Если не запросили модуль - используем модуль первый из разрешенных
			if ( empty( $module ) || ! is_file( 'backend/core/' . $module . '.php' ) ) {

				foreach ( $this->manager->menu as $section => $items ) {
					foreach ( $items as $title => $modules ) {
						if ( empty( $module ) || ! is_file( 'backend/core/' . $module . '.php' ) ) {

							// ModulesCore //!! 
							if ( class_exists( '\ModulesCore\ModuleLoader' ) ) {
								if ( $rmbt_arr_controller = \ModulesCore\ModuleLoader::checkController( $module ) ) {
									$rmbt_controller = $rmbt_arr_controller[0];
									if ( isset( $rmbt_arr_controller[1] ) ) {
										$rmbt_namespace = $rmbt_arr_controller[1];
									}
									break 2;
								}
							}

							if ( $this->managers->access( $this->modules_permissions[ $modules ] ) ) {
								$module = $modules;
								$menu_selected = $title;
								break 2;
							}
						}
					}
				}
				unset( $modules );
			}

			$this->design->assign( 'left_menu', $this->manager->menu );
			$this->design->assign( 'menu_selected', $menu_selected );
			$this->design->assign( 'translit_pairs', $this->translit_pairs );

		}

		// Подключаем файл с необходимым модулем
		// require_once( 'backend/core/' . $module . '.php' );
		// ModulesCore  //!!
		// заменить на:
		if ( file_exists( $file = 'backend/core/' . $module . '.php' ) ) {
			require_once $file;
		} else {
			if ( isset( $rmbt_controller ) ) {
				require_once $rmbt_controller;
			}
		}

		$this->design->assign( 'btr', $backend_translations );

		if ( empty( $module ) ) {
			$module = 'ProductsAdmin';
		}


		//!!	
		if ( $rmbt_namespace != '' ) {
			$module = $rmbt_namespace . '\\' . $module;
		}

		//!!
		if ( ! function_exists( 'modifier_basename' ) ) {
			function modifier_basename( $path ) {
				// для ссылки пункта left_menu если в контроллере используются Namespace 
				$basename = preg_replace( '/^.*\\\\/', '', $path );
				return $basename;
			}
			$this->design->smarty->registerPlugin( 'modifier', 'basename', 'modifier_basename' );
		}

		// Создаем соответствующий модуль
		if ( class_exists( '\\' . $module ) ) {
			$this->module = new $module();
		} else {
			die( "Error creating $module class" );
		}
	}

	/*Отображение запрашиваемого модуля*/
	public function fetch() {
		$currency = $this->money->get_currency();
		$this->design->assign( "currency", $currency );

		// Проверка прав доступа к модулю

		if ( get_class( $this->module ) == 'AuthAdmin' || isset( $this->modules_permissions[ get_class( $this->module ) ] )
			&& $this->managers->access( $this->modules_permissions[ get_class( $this->module ) ] ) ) {
			$content = $this->module->fetch();
			$this->design->assign( "content", $content );
		} else {
			$this->design->assign( "content", false );
		}

		$all_status = $this->orderstatus->get_status();
		if ( $all_status ) {
			$first_status = reset( $all_status );
		}

		/*Счетчики для верхнего меню*/
		$new_orders_counter = $this->orders->count_orders( array( 'status' => $first_status->id ) );
		$this->design->assign( "new_orders_counter", $new_orders_counter );

		$new_comments_counter = $this->comments->count_comments( array( 'approved' => 0 ) );
		$this->design->assign( "new_comments_counter", $new_comments_counter );

		$new_feedbacks_counter = $this->feedbacks->count_feedbacks( array( 'processed' => 0 ) );
		$this->design->assign( "new_feedbacks_counter", $new_feedbacks_counter );

		$new_callbacks_counter = $this->callbacks->count_callbacks( array( 'processed' => 0 ) );
		$this->design->assign( "new_callbacks_counter", $new_callbacks_counter );

		$this->design->assign( "all_counter", $new_orders_counter + $new_comments_counter + $new_feedbacks_counter + $new_callbacks_counter );


		// ModulesCore //!! 
		if ( class_exists( '\ModulesCore\AssetsManager' ) ) {
			$this->design->assign( 'modules_head_css', \ModulesCore\AssetsManager::renderCss( 'head' ) );
			$this->design->assign( 'modules_footer_css', \ModulesCore\AssetsManager::renderCss( 'footer' ) );

			$this->design->assign( 'modules_head_js', \ModulesCore\AssetsManager::renderJs( 'head' ) );
			$this->design->assign( 'modules_footer_js', \ModulesCore\AssetsManager::renderJs( 'footer' ) );
		}

		// Создаем текущую обертку сайта (обычно index.tpl)
		$wrapper = $this->design->smarty->getTemplateVars( 'wrapper' );
		if ( is_null( $wrapper ) ) {
			$wrapper = 'index.tpl';
		}

		if ( ! empty( $wrapper ) ) {
			return $this->body = $this->design->fetch( $wrapper );
		} else {
			return $this->body = $content;
		}
	}

	// ModulesCore //!! 

	protected function addToLeftMenu( array $items ) {
		foreach ( $items as $key => $value ) {
			$this->left_menu[ $key ] = $value;
		}
	}

	protected function getLeftMenu() {
		return $this->left_menu;
	}


}