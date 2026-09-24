<?php

namespace ModulesCore;

class ModuleLoader
{
    protected $modulesPath;

    protected $modulesWebPath;

    protected static $modulesPath_static;
    protected static array $modulesName = [];
    protected static array $menuItems = [];
    protected static array $BackendTranslationsItems = [];


    public function __construct( $modulesPath )
    {
        $this->modulesPath = self::$modulesPath_static = rtrim($modulesPath, '/');
        if (! is_dir($this->modulesPath) ) {
            return;
        }
        $documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));
        $realModulesPath = str_replace('\\', '/', realpath($this->modulesPath));
        $this->modulesWebPath = ltrim(str_replace($documentRoot, '', $realModulesPath), '/');


        //!!????
        include_once 'ModulesCore/Helpers_glob.php';
    }

    public function loadModules()
    {
        $folders = scandir($this->modulesPath);
        $isBackend = strpos($_SERVER['REQUEST_URI'], '/backend') !== false;

        foreach ( $folders as $moduleFolder ) {
            if ($moduleFolder === '.' || $moduleFolder === '..' ) {
                continue;
            }

            $modulePath = $this->modulesPath . '/' . $moduleFolder;
            $mainFile = $modulePath . '/' . $moduleFolder . '.php';
            $backendFile = $modulePath . '/' . $moduleFolder . '-backend.php';

            // подключение хелперов
            $this->loadModuleHelpers($modulePath);


            // Определяем, какой файл подключать
            $fileToLoad = ( $isBackend && is_file($backendFile) ) ? $backendFile : $mainFile;


            if (is_file($fileToLoad) ) {
                include_once $fileToLoad;

                list( $className, $is_Namespace ) = $this->resolveClassName($moduleFolder, $isBackend);

                // сохраняем имя модуля
                self::$modulesName[] = array(
                    'moduleFolder' => $moduleFolder,
                    'is_Namespace' => $is_Namespace,
                );

                
                if ($className ) {
                    $refClass = new \ReflectionClass($className);
                    
                    if ($refClass->hasMethod('__construct') ) {
                        new $className();
                        $this->loadAssets($moduleFolder);
                    } elseif ($refClass->hasMethod('init') && $refClass->getMethod('init')->isStatic() ) {
                        call_user_func([ $className, 'init' ]);
                        $this->loadAssets($moduleFolder);
                    }
                }
            }
        }
    }

    private function resolveClassName($moduleFolder, $isBackend)
    {
        $suffix = $isBackend ? 'Backend' : '';
        $classBase = $moduleFolder . $suffix;

        $withNamespace = '\\' . $moduleFolder . '\\' . $classBase;
        $withoutNamespace = $classBase;

        if (class_exists($withNamespace)) {
            return [$withNamespace, $moduleFolder];
        }
        if (class_exists($withoutNamespace)) {
            return [$withoutNamespace, false];
        }



        return [false, false];
    }

    protected function isForCurrentContext( $basename, $isBackend )
    {
        if ($isBackend ) {
            return strpos($basename, 'backend') !== false;
        }
        return strpos($basename, 'backend') === false;
    }

    protected function loadAssets( $moduleName )
    {
        $isBackend = strpos($_SERVER['REQUEST_URI'], '/backend') !== false;
        $basePath = $this->modulesPath . '/' . $moduleName . '/' . 'design' . '/' . 'assets';
        $webBasePath = $this->modulesWebPath . '/' . $moduleName . '/' . 'design' . '/' . 'assets';
        $webBase = ltrim($webBasePath, '/');

        if ($isBackend ) {
            $webBase = '..' . '/' . $webBase;
        }
        $webBase = ltrim($webBase, '/');

        $cssPath = $basePath . '/' . 'css';
        $jsPath = $basePath . '/' . 'js';

        if (is_dir($cssPath) ) {
            $webCssPath = $webBase . '/' . 'css' . '/';
            foreach ( glob($cssPath . '/*.css') as $file ) {
                $basename = basename($file);
                if ($this->isForCurrentContext($basename, $isBackend) ) {
                    AssetsManager::registerCss($webCssPath . $basename);
                }
            }
        }

        if (is_dir($jsPath) ) {
            $webJsPath = $webBase . '/' . 'js' . '/';
            foreach ( glob($jsPath . '/*.js') as $file ) {
                $basename = basename($file);
                if ($this->isForCurrentContext($basename, $isBackend) ) {
                    AssetsManager::registerJs($webJsPath . $basename);
                }
            }
        }
    }

    /**
     * подключает контроллеры всех модулей
     */
    public static function checkController( $module )
    {

        $controllers = [];
        // foreach ( self::$modulesName as $moduleName ) {
        foreach ( self::$modulesName as $_module ) {

            $moduleName = $_module['moduleFolder'];
            // $is_Namespace = $module['is_Namespace'];

            $fullPath = self::$modulesPath_static . '/' . $moduleName . '/' . 'controllers' . '/*.php';
            $controllers = glob($fullPath);
            foreach ( $controllers as $controller ) {

                $fileName = basename($controller);

                if ($fileName === $module . '.php' ) {

                    if ($_module['is_Namespace'] ) {
                        return [ $controller, $_module['is_Namespace'] ];
                    }
                    return [ $controller ];
                    // return $module;
                }
            }
        }

        return false;
    }

    /**
     * подключает хелперы всех модулей
     */
    private function loadModuleHelpers( string $modulePath ): void
    {
        $helpersPath = $modulePath . '/helpers';

        if (is_dir($helpersPath) ) {
            foreach ( scandir($helpersPath) as $helperFile ) {
                if ($helperFile === '.' || $helperFile === '..' ) {
                    continue;
                }

                $fullHelperPath = $helpersPath . '/' . $helperFile;

                if (is_file($fullHelperPath) && pathinfo($fullHelperPath, PATHINFO_EXTENSION) === 'php' ) {
                    include_once $fullHelperPath;
                }
            }
        }
    }

    public static function addMenu(array $items): void
    {
        self::$menuItems = array_merge_recursive(
            self::$menuItems,
            $items
        );
    }

    public static function addBackendTranslations(array $items): void
    {

        self::$BackendTranslationsItems = array_merge(
            self::$BackendTranslationsItems,
            $items
        );

    }

    public static function getMenu(): array
    {
        return self::$menuItems;
    }

    public static function getBackendTranslations(): array
    {
        return self::$BackendTranslationsItems;
    }


}
