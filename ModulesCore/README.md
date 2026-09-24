# Система модулей для OkayCMS 2.4.1

**ModulesCore** — это система автоподключения пользовательских модулей в OkayCMS 2.4.1 с минимальным вмешательством в основную структуру проекта. 
Все модули изолированы и могут работать как на фронтенде, так и в админ-панели. 
В OkayCMS 2.4.1 отсутствует система хуков по этому вмешательство модулей в код ядра CMS неизбежно.
  Самым оптимальным в данной ситуации считаю статические методы которые вызываются в нужном месте кода ядра чем то вроде этого:
    class_exists( PromotionsOrder::class) && PromotionsOrder::promotions_update_orders( $result, $this->db );

## Подключения ModulesCore 
  заменить файл IndexAdmin.php файлом IndexAdmin_ModulesCore.php
  
---

## Файловая структура
```
ModulesCore/
├── AssetsManager.php
├── autoload.php
├── ModuleLoader.php
├── modules_permissions.php             # права для пунктов меню админки для модулей
├── modules_template_dir.php            # папка шаблонов модулей для админки
└── modules/
    └── ExampleModule/
        ├── ExampleModule-backend.php           # Главный PHP-класс для backend 
        ├── ExampleModule.php                   # Главный PHP-класс для frontend
        ├── controllers/                             
        ├── design/                              
        |   ├── html/                           # Шаблоны .tpl
        |       ├── template1.tpl  
        |       └── template2.tpl  
        |   ├── assets/
        |   │   ├── css/                            # Стили модуля
        |   │   |   ├── style.css                   # Будут выводится в head   
        |   │   |   ├── footer-style.css           
        |   │   |   ├── footer-style-backend.css    # Будут выводится на backend в footer        
        |   │   |   └── style-backend.css           
        |   │   ├── js/                             # Скрипты модуля
        |   │   │   ├── script.js                   # Будут выводится в footer   
        |   │   │   ├── footer-script.js           
        |   │   │   └── script-backend.js        
        |   │   └── images/                         # Изображения, иконки
        ├── langs/                               # Локализация (опционально)
        ├── helpers/                             # Файлы из этой папки будут подключатся как на фронт так и на бэк в ModuleLoader.php
        └── config/                              # Настройки (JSON, YAML, PHP) (опционально)
        └── migrations/           
            ├── install.sql                      # создание таблиц
```

## Назначение основных файлов

- `AssetsManager.php` — класс для подключения CSS и JS модулей в нужные места шаблона (head или footer, фронт и админка).
- `autoload.php` — автозагрузка всех необходимых файлов модулей.
- `ModuleLoader.php` — основной загрузчик всех пользовательских модулей из папки `modules/`.

## Назначение подкаталогов модуля
- `views/` — шаблоны `.tpl` и `.php`, используемые модулем.
- `assets/css/` — стили модуля.
  - `style.css` и `footer-style.css` подключаются на фронтенде.
  - `style-backend.css` и `footer-style-backend.css` — только на страницах админки.
- `assets/js/` — скрипты модуля.
  - `script.js`, `footer-script.js` — фронтенд.
  - `script-backend.js` — админка.
- `assets/images/` — изображения, иконки и пр.
- `lang/` — файлы локализации необходимо для отображения в главном меню админки.
- `config/` — конфигурации (опционально).



## Подключения ModulesCore 
  добавить в backend/index.php обязательно перед require_once 'backend/core/IndexAdmin.php';
    require_once( 'ModulesCore/autoload.php' );
  добавить в index.php
    require_once( 'ModulesCore/autoload.php' );
  заменить файл IndexAdmin.php файлом IndexAdmin_ModulesCore.php

  для  v.2.8.5 обязательно добавить метод BackendTranslations::__set()
    public function __set($var, $value)
    {
        if (!isset($this->lang_translations)) {
            $this->init_translations();
        }

        $this->lang_translations->$var = $value;
    }
      
## Подключение стилей и скриптов в шаблонах `.tpl`
В нужных местах шаблона (обычно в `index.tpl`, `head.tpl`, `footer.tpl` и аналогичных), вывод подключённых модулем стилей и скриптов осуществляется следующим образом:

```html
	{$modules_head_css nofilter}
	{$modules_head_js nofilter}

    ...

    {$modules_footer_css nofilter}
    {$modules_footer_js nofilter}
```

Эти переменные должны быть предварительно присвоены в front-контроллере и backend-контроллере. Например, в `IndexView.php` или аналогичном в конце метода fetch() перед return:

```php
if (class_exists('\\ModulesCore\\AssetsManager')) {
    \$this->design->assign('modules_head_css', \ModulesCore\AssetsManager::renderCss('head'));
    \$this->design->assign('modules_footer_css', \ModulesCore\AssetsManager::renderCss('footer'));

    \$this->design->assign('modules_head_js', \ModulesCore\AssetsManager::renderJs('head'));
    \$this->design->assign('modules_footer_js', \ModulesCore\AssetsManager::renderJs('footer'));
}
```

## Как добавить модуль
1. Поместите папку нового модуля в директорию `ModulesCore/modules/`.
2. Убедитесь, что в модуле есть главный класс с таким же именем, как и папка модуля.
3. При необходимости создайте отдельный backend-файл `ExampleModule-backend.php` для логики в админке.
4. Используйте папки `assets/`, `views/`, `lang/`, `config/` по необходимости.
5. Для того что бы пункты меню в админке появились:
  - добавить контроллеры пунктов меню в modules_permissions.php
  - этот код в файле YourModule-backend.php 
      class YouModuleBackend extends IndexAdmin {

          ....

          $newMenuItems = [ 
            'left_your_menu_item_name' => array(
              'left_your_menu_item_name' => array( 'your_controller_name' ),
            ),
          ];
          $this->addToLeftMenu( $newMenuItems );

          .....

          parent::__construct();
      }
      при этом
        если в массиве $newMenuItems один элемент твой пункт меню будет без подпунктов:
          $newMenuItems = [ 
            'left_your_menu_item_name' => array(
              'left_your_menu_item_name' => array( 'your_controller_name' ),
            ),
          ];
        для того что бы получить под пункты нужно добавить элементы в массив
          $newMenuItems = [ 
            'left_your_menu_item_name' => array(
              'left_your_menu_item_sub_item_name_1' => array( 'your_controller_name' ),
              'left_your_menu_item_sub_item_name_2' => array( 'your_controller_name' ),
            ),
          ];
  - из метода fetch() контроллера возвращать контент!
      return $this->design->fetch( 'page_of_your_module.tpl' );
6. Для того что бы пункты меню в админке имели надписи:  
  - добавить перевод пунктов меню в modules\ModuleName\langs\ru.php
7. Для страниц пунктов меню в админке 
  - добавить папку шаблонов модуля в modules_template_dir.php
8. Пункты меню могут хранится в базе данных __managers.menu 
    для полного удаления модуля из меню нужно удалить их от туда
## Планируемые расширения
- Поддержка зависимостей между модулями.
- Интерфейс для управления модулями из админки.
- Автоматическое кеширование и минификация подключаемых стилей и скриптов.

---

Это базовое описание архитектуры модульной системы для OkayCMS. 
Проект может расширяться по мере надобности. 
Вся логика модулей должна быть изолирована и независима от основной логики CMS.
