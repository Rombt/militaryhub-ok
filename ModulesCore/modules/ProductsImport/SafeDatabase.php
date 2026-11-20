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

    /**
     * Prepared statements for rollback's arbitrary row values. OkayCMS's
     * legacy placehold parser can reject long lists of positional parameters.
     * Uses the SAME mysqli connection as the CMS, so transactions are shared.
     */
    public function queryPrepared(string $sql, array $params = [])
    {
        $sql = preg_replace_callback('/(?<![A-Za-z0-9_])__([A-Za-z][A-Za-z0-9_]*)/',
            function ($m) { return $this->table($m[1]); }, $sql);
        if (trim($sql) === '') {
            throw new \RuntimeException('Prepared SQL is empty.');
        }
        $mysqli = $this->mysqliConnection();
        $statement = $mysqli->prepare($sql);
        if (!$statement) {
            throw new \RuntimeException('Prepared SQL failed: ' . $mysqli->error);
        }
        try {
            if ($params) {
                if (substr_count($sql, '?') !== count($params)) {
                    throw new \RuntimeException('SQL placeholder count mismatch.');
                }
                $values = array_values($params);
                $types = str_repeat('s', count($values));
                $refs = [];
                foreach ($values as $i => &$value) {
                    if ($value !== null && !is_scalar($value)) {
                        throw new \InvalidArgumentException('Unsupported prepared SQL parameter.');
                    }
                    $refs[$i] = &$value;
                }
                unset($value);
                if (!$statement->bind_param($types, ...$refs)) {
                    throw new \RuntimeException('Cannot bind prepared SQL parameters.');
                }
            }
            if (!$statement->execute()) {
                throw new \RuntimeException('Prepared SQL execution failed: ' . $statement->error);
            }
            if ($statement->field_count > 0) {
                $result = $statement->get_result();
                if (!$result) {
                    throw new \RuntimeException('mysqli mysqlnd get_result() is required.');
                }
                return $result;
            }
            return true;
        } finally {
            $statement->close();
        }
    }

    private function mysqliConnection(): \mysqli
    {
        $reflection = new \ReflectionObject($this);
        do {
            foreach ($reflection->getProperties() as $property) {
                if ($property->isStatic()) continue;
                $property->setAccessible(true);
                $value = $property->getValue($this);
                if ($value instanceof \mysqli) return $value;
            }
            $reflection = $reflection->getParentClass();
        } while ($reflection);
        throw new \RuntimeException(
            'Не найдено соединение mysqli в Database; откат остановлен без выполнения SQL.'
        );
    }

    public function query()
    {
        $args = func_get_args();
        // Replace module placeholders BEFORE OkayCMS::placehold() uses the CMS-wide prefix.
        // Only SQL identifiers of the form __table are replaced; parameter placeholders remain intact.
        $args[0] = preg_replace_callback('/(?<![A-Za-z0-9_])__([A-Za-z][A-Za-z0-9_]*)/', function ($matches) {
            return $this->table($matches[1]);
        }, $args[0]);

        // Database::query() blindly calls free() on its previous $res.
        // That result may already have been freed by another consumer.
        // Drop the stale reference BEFORE entering the parent method.
        // The parent will assign its fresh result to $res as usual, so
        // Database::result(), results() and num_rows() remain compatible.
        $property = new \ReflectionProperty(\Database::class, 'res');
        $property->setAccessible(true);
        $property->setValue($this, null);

        return parent::query(...$args);
    }
}
