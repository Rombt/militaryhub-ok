<?php

require_once 'View.php';

class IndexView extends View
{
    public $modules_dir = 'view/';

    public function __construct()
    {
        parent::__construct();

        if (isset($_SESSION['message_success'])) {
            $this->design->assign('message_success', $_SESSION['message_success']);
            unset($_SESSION['message_success']);
        }

        if (isset($_SESSION['request_register'])) {
            $this->design->assign('login_form_sent', $_SESSION['request_register']);
            unset($_SESSION['request_register']);
        }
    }

    public function fetch()
    {
        if ($referral_url = $this->request->get('ref', 'string')) {
            $referral = $this->ref->get_item($referral_url, array('valid' => 1));
            if (!empty($referral)) {
                setcookie('userRefererId', base64_encode($referral->id), time() + 60 * 60 * 24 * 1, '/', '', false, false);
                if (empty($this->user)) {
                    $_SESSION['request_register'] = 'login';
                }
            }
            header("Location: " . $this->config->root_url . '/' . $this->lang_link);
            exit();
        }

        // Авторизация
        if ($this->request->method('post') && $this->request->post('login')) {
            $phone = $this->request->post('phone');
            if (!$this->validate->is_phone($phone)) {
                $phone = '';
                $this->design->assign('error', 'phone');
            }
            $this->design->assign('login_phone', $phone);

            $password     = $this->request->post('password');
            $login_save   = $this->request->post('pk', 'integer');
            $captcha_code = $this->request->post('captcha_code', 'string');

            if ($this->settings->captcha_login && !$this->validate->verify_captcha('captcha_login', $captcha_code)) {
                $this->design->assign('error', 'captcha');
            } elseif ($user_id = $this->users->check_password($phone, $password)) {
                if ($login_save) {
                    @ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 30);
                    @ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30);
                }

                $_SESSION['user_id'] = $user_id;
                $this->users->update_user($user_id, array('last_ip' => $_SERVER['REMOTE_ADDR']));

                // Перенаправляем пользователя по адресу
                if (!empty($_SERVER['REQUEST_URI'])) {
                    header('Location: ' . $this->config->root_url . $_SERVER['REQUEST_URI']);
                } else {
                    header('Location: ' . $this->config->root_url . '/' . $this->lang_link . 'user');
                }
                exit();
            } else {
                $this->design->assign('error', 'login_incorrect');
            }

            $this->design->assign('login_form_sent', 'login');

        }

        if (!isset($_COOKIE['registration_user']) && isset($_SESSION['registration_user'])) {
            unset($_SESSION['registration_user']);
        }

        // Якщо в URL є параметр source=insta_promo, запам'ятовуємо це в сесії
        if ($this->request->get('source') == 'insta_promo') {
            $_SESSION['source_promo'] = 'insta_promo';
        }

        // Якщо користувач прийшов з промо та ще не авторизований,
        // один раз показуємо попап входу/реєстрації
        if (!empty($_SESSION['source_promo']) && empty($this->user) && empty($_SESSION['source_promo_shown'])) {
            $this->design->assign('show_promo_register_popup', true);
            $_SESSION['source_promo_shown'] = 1;
        }

        // Регистрация
        if ($this->request->method('post') && $this->request->post('register')) {
            $user = new stdClass();


            if (isset($_SESSION['registration_user'])) {
                $user = (object)$_SESSION['registration_user'];
            } else {
                $user->name  = $this->request->post('name');
                $user->phone = preg_replace("~[^\d]~", "", $this->request->post('phone'));;
                $user->email    = $this->request->post('email');
                $user->password = $this->request->post('password');
            }
            //            $user->group_id = intval($this->settings->registration_group);

            // ЛОГІКА ВИБОРУ ГРУПИ:
            // Якщо в сесії є мітка промо, беремо нову настройку, інакше — стандартну
            if (isset($_SESSION['source_promo']) && $_SESSION['source_promo'] == 'insta_promo' && $this->settings->registration_promo_group) {
                $user->group_id = intval($this->settings->registration_promo_group);
            } else {
                $user->group_id = intval($this->settings->registration_group);
            }
            $user->last_ip = $_SERVER['REMOTE_ADDR'];

            $captcha_code    = $this->request->post('captcha_code', 'string');
            $validation_code = $this->request->post('code', 'integer');

            $this->design->assign('registration_name', $user->name);
            $this->design->assign('registration_phone', $user->phone);
            $this->design->assign('registration_email', $user->email);

            if ($this->settings->captcha_register && !$this->validate->verify_captcha('captcha_register', $captcha_code)) {
                $this->design->assign('error', 'captcha');
            } elseif (isset($_SESSION['registration_user']) && $validation_code !== $user->validation_code) {
                $this->design->assign('error', 'empty_code');
            } elseif (!$this->validate->is_name($user->name, true)) {
                $this->design->assign('error', 'empty_name');
            } elseif (!$this->validate->is_phone($user->phone, true)) {
                $this->design->assign('error', 'empty_phone');
            } elseif (!$this->validate->is_email($user->email)) {
                $this->design->assign('error', 'empty_email');
            } elseif (empty($user->password)) {
                $this->design->assign('error', 'empty_password');
            } elseif (($cnt = $this->users->count_users(array('from_date' => date('Y-m-d'), 'to_date' => date('Y-m-d', time() + (60 * 60 * 24)), 'ip' => $user->last_ip))) && ($cnt >= 3)) {
                $this->design->assign('error', 'user_must_ip');
            } elseif (($cnt = $this->users->count_users(array('user_exist' => 1, 'email' => $user->email, 'phone' => $user->phone))) && ($cnt > 0)) {
                $this->design->assign('error', 'user_exists');
            } elseif (!isset($_SESSION['registration_user'])) {
                $user->validation_code = rand(1000000, 9999999);
                setcookie('registration_user', 1, time() + (60 * 10), '/', $this->config->root_host, true, true);
                $this->sms->registration_user($user);
                $_SESSION['registration_user'] = $user;

                // Перенаправляем пользователя по адресу
                header('Location: ' . $this->config->root_url . $_SERVER['REQUEST_URI']);
                exit();
            } elseif ($user_id = $this->users->add_user($user)) {
                $_SESSION['user_id']         = $user_id;
                $_SESSION['message_success'] = 'registration';
                unset($_SESSION['registration_user']);
                unset($_SESSION['source_promo']);// Видаляємо мітку джерела після успіху

                // Перенаправляем пользователя по адресу
                if (!empty($_SERVER['REQUEST_URI'])) {
                    header('Location: ' . $this->config->root_url . $_SERVER['REQUEST_URI']);
                } else {
                    header('Location: ' . $this->config->root_url . '/' . $this->lang_link . 'user');
                }
                exit();
            } else {
                $this->design->assign('error', 'unknown error');
            }

            $this->design->assign('login_form_sent', 'register');

        }

        /*Принимаем данные с формы заказа обратного звонка*/
        if ($this->request->method('post') && $this->request->post('callback')) {
            $callback          = new stdClass();
            $callback->phone   = $this->request->post('phone');
            $callback->name    = $this->request->post('name');
            $callback->url     = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $callback->message = $this->request->post('message');
            $callback->type    = $this->request->post('type', 'string');

            $captcha_code = $this->request->post('captcha_code', 'string');

            $prefix = !empty($callback->type) ? $callback->type . '_' : '';
            $this->design->assign("{$prefix}callname", $callback->name);
            $this->design->assign("{$prefix}callphone", $callback->phone);
            $this->design->assign("{$prefix}callmessage", $callback->message);

            /*Валидация данных клиента*/
            if (!$this->validate->is_name($callback->name, true)) {
                $this->design->assign("{$prefix}call_error", 'empty_name');
            } elseif (!$this->validate->is_phone($callback->phone, true)) {
                $this->design->assign("{$prefix}call_error", 'empty_phone');
            } elseif (!$this->validate->is_comment($callback->message)) {
                $this->design->assign("{$prefix}call_error", 'empty_comment');
            } elseif ($this->settings->captcha_callback && !$this->validate->verify_captcha('captcha_callback', $captcha_code)) {
                $this->design->assign("{$prefix}call_error", 'captcha');
            } elseif ($callback_id = $this->callbacks->add_callback($callback)) {
                $this->design->assign("{$prefix}call_sent", true);
                // Отправляем email
                // $this->notify->email_callback_admin($callback_id);
            } else {
                $this->design->assign("{$prefix}call_error", 'unknown error');
            }
        }

        /*E-mail подписка*/
        if ($this->request->post('subscribe')) {
            $email = $this->request->post('subscribe_email');
            $this->db->query("select count(id) as cnt from __subscribe_mailing where email=?", $email);
            $cnt = $this->db->result('cnt');
            if (!$this->validate->is_email($email, true)) {
                $this->design->assign('subscribe_error', 'empty_email');
            } elseif ($cnt > 0) {
                $this->design->assign('subscribe_error', 'email_exist');
            } else {
                $this->db->query("insert into __subscribe_mailing set email=?", $email);
                $this->design->assign('subscribe_success', '1');
            }
        }

        // Менюшки
        $menus = $this->menu->get_menus(array('visible' => 1));
        if (!empty($menus)) {
            foreach ($menus as $menu) {
                $this->design->assign("menu", $menu);
                $all_menu_items = $this->menu->get_menu_items();
                $this->count_visible($this->menu->get_menu_items_tree((int)$menu->id), $all_menu_items, 'submenus');
                $this->design->assign("menu_items", $this->menu->get_menu_items_tree((int)$menu->id));
                $this->design->assign(Menu::MENU_VAR_PREFIX . $menu->group_id, $this->design->fetch("menu.tpl"));
            }
        }

        if ($_SESSION['admin'] && ($manager = $this->managers->get_manager())) {
            // Перевод админки
            $backend_translations = $this->backend_translations;
            $file                 = "backend/lang/" . $manager->lang . ".php";
            if (!file_exists($file)) {
                foreach (glob("backend/lang/??.php") as $f) {
                    $file = "backend/lang/" . pathinfo($f, PATHINFO_FILENAME) . ".php";
                    break;
                }
            }
            include_once $file;
            $this->design->assign('btr', $backend_translations);
            $this->design->assign('admintooltip', $this->design->fetch($this->config->root_dir . 'backend/design/html/admintooltip.tpl'));
        }

        // Пользовательские скриты из админки
        $counters = array();
        foreach ($this->settings->counters as $c) {
            $counters[$c->position][] = $c;
        }
        $this->design->assign('counters', $counters);

        // Содержимое корзины
        $this->checkout = $this->cart->get_cart($_SESSION['shopping_cart']);
        $this->design->assign('cart', $this->checkout);

        // Избранное
        $time = time();
        if (!empty($_COOKIE['wished_products'])) {
            $wished = (array)explode(',', $_COOKIE['wished_products']);
        } else {
            $wished = array();
        }
        if (isset($_SESSION['user_id']) && !isset($_COOKIE['wished_last_modify'])) {
            $w            = $this->wishlist->get(intval($_SESSION['user_id']));
            $count_wished = count(explode(',', $w->products_ids));
            if (!empty($w)) {
                @$usr_wished = (array)explode(',', $w->products_ids);
                foreach ($usr_wished as $product_id) {
                    if (!empty($product_id) && !in_array($product_id, $wished)) {
                        array_push($wished, $product_id);
                    }
                }
            }
            $wp_ids = implode(',', $wished);
            $this->wishlist->update(intval($_SESSION['user_id']), $wp_ids);
            // setcookie('wished_last_modify', $time, $time + $this->wishlist->timeout, '/', $this->config->root_host, false, true);
        }

        $wished = array_splice($wished, 0, $this->wishlist->limit);
        if (!is_array($wished)) {
            $wished = array();
        }
        $wp_ids = implode(',', $wished);
        // if (isset($_COOKIE['admin_ip'])) {
        //     // print_r($usr_wished);
        //     print_r($wp_ids);
        //     die;
        // }
        setcookie('wished_products', $wp_ids, $time + $this->wishlist->timeout, '/', $this->config->root_host, false, true);
        $this->design->assign('wished_products', ($wished[0] > 0) ? $wished : array());

        // Сравнение
        $this->design->assign('comparison', $this->comparison->get_comparison());

        // Категории товаров
        $all_categories = $this->categories->get_categories();
        $this->count_visible($this->categories->get_categories_tree(), $all_categories);
        $this->design->assign('categories', $this->categories->get_categories_tree());

        // Страницы
        $pages = $this->pages->get_pages(array('visible' => 1));
        $this->design->assign('pages', $pages);

        $is_mobile = $this->design->is_mobile();
        $is_tablet = $this->design->is_tablet();
        $this->design->assign('is_mobile', $is_mobile);
        $this->design->assign('is_tablet', $is_tablet);

        // Текущий модуль (для отображения центрального блока)
        $module = $this->request->get('module', 'string');
        $module = preg_replace("/[^A-Za-z0-9]+/", "", $module);

        // Если не задан - берем из настроек
        if (empty($module)) {
            return false;
        }

        // Создаем соответствующий класс
        if (is_file($this->modules_dir . "$module.php")) {
            include_once $this->modules_dir . "$module.php";
            if (class_exists($module)) {
                $this->main = new $module($this);
            } else {
                return false;
            }
        } else {
            return false;
        }

        // Создаем основной блок страницы
        if (!$content = $this->main->fetch()) {
            return false;
        }

        // Передаем основной блок в шаблон
        $this->design->assign('content', $content);

        // Передаем название модуля в шаблон, это может пригодиться
        $this->design->assign('module', $module);

        // Создаем текущую обертку сайта (обычно index.tpl)
        $wrapper = $this->design->get_var('wrapper');
        if (is_null($wrapper)) {
            $wrapper = 'index.tpl';
        }

        if (empty($_SESSION['admin'])) {
            if ($this->settings->site_work == "off") {
                header('HTTP/1.0 503 Service Temporarily Unavailable');
                header('Status: 503 Service Temporarily Unavailable');
                header('Retry-After: 300');//300 seconds
                return $this->design->fetch('tech.tpl');
            }
        }


        //!! стили и скрипты модулей из ModulesCore
        if (class_exists('\ModulesCore\AssetsManager')) {
            $this->design->assign('modules_head_css', \ModulesCore\AssetsManager::renderCss('head'));
            $this->design->assign('modules_footer_css', \ModulesCore\AssetsManager::renderCss('footer'));

            $this->design->assign('modules_head_js', \ModulesCore\AssetsManager::renderJs('head'));
            $this->design->assign('modules_footer_js', \ModulesCore\AssetsManager::renderJs('footer'));
        }


        if (!empty($wrapper)) {
            return $this->body = $this->design->fetch($wrapper);
        } else {
            return $this->body = $content;
        }
    }

    /* Подсчет количества видимых дочерних элементов */
    private function count_visible($items = array(), &$all_items, $subitems_name = 'subcategories')
    {
        foreach ($items as $item) {

            $parent_id = $item->parent_id;

            // если родитель существует — убеждаемся, что это объект
            if ($parent_id && isset($all_items[$parent_id])) {
                if (!is_object($all_items[$parent_id])) {
                    $all_items[$parent_id] = (object)[
                    'count_children_visible' => 0
                    ];
                }

                if (!isset($all_items[$parent_id]->count_children_visible)) {
                    $all_items[$parent_id]->count_children_visible = 0;
                }

                if (!empty($item->visible)) {
                    $all_items[$parent_id]->count_children_visible++;
                }
            }

            // рекурсия по подкатегориям
            if (!empty($item->{$subitems_name}) && is_array($item->{$subitems_name})) {
                $this->count_visible($item->{$subitems_name}, $all_items, $subitems_name);
            }
        }
    }

}
