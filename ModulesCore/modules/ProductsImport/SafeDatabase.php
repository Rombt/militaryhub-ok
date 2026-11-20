<?php

namespace ModulesCore\modules\ProductsImport;

/**
 * Compatibility layer for OkayCMS Database::query().
 * Some CMS result()/fetch() implementations release mysqli_result without
 * clearing Database::$res. A subsequent query() then frees it a second time.
 * Keep the CMS unchanged and recover only from that specific condition.
 */
class SafeDatabase extends \Database
{
    private $moduleTablePrefix;

    public function __construct(string $tablePrefix)
    {
        if (!preg_match('/^[A-Za-z0-9_]*$/D', $tablePrefix)) {
            throw new \InvalidArgumentException('Недопустимый префикс таблиц целевой базы.');
        }
        $this->moduleTablePrefix = $tablePrefix;
        parent::__construct();
    }

    public function table(string $name): string
    {
        if (!preg_match('/^[A-Za-z0-9_]+$/D', $name)) {
            throw new \InvalidArgumentException('Недопустимое имя таблицы.');
        }
        return $this->moduleTablePrefix . $name;
    }

    public function query()
    {
        $args = func_get_args();
        // Replace module placeholders BEFORE OkayCMS::placehold() uses the CMS-wide prefix.
        // Only SQL identifiers of the form __table are replaced; parameter placeholders remain intact.
        $args[0] = preg_replace_callback('/(?<![A-Za-z0-9_])__([A-Za-z][A-Za-z0-9_]*)/', function ($matches) {
            return $this->table($matches[1]);
        }, $args[0]);

        try {
            return parent::query(...$args);
        } catch (\Error $e) {
            if (strpos($e->getMessage(), 'mysqli_result object is already closed') === false) {
                throw $e;
            }

            // The failed parent query() did not execute SQL: it failed while
            // releasing the result of the previous query.
            $property = new \ReflectionProperty(\Database::class, 'res');
            $property->setAccessible(true);
            $property->setValue($this, null);

            return parent::query(...$args);
        }
    }
}
