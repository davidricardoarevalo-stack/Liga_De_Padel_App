<?php

namespace App\Models;

class Model
{
    protected $table;
    protected $fillable = [];
    protected $attributes = [];
    public $id;
    
    private static $pdo = null;
    
    public function __construct($attributes = [])
    {
        $this->attributes = $attributes;
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }
    }
    
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
        $this->$name = $value;
    }
    
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }
    
    private static function getPdo()
    {
        if (self::$pdo === null) {
            $host = $_ENV['DB_HOST'] ?? 'db';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $dbname = $_ENV['DB_DATABASE'] ?? 'liga';
            $username = $_ENV['DB_USERNAME'] ?? 'liga';
            $password = $_ENV['DB_PASSWORD'] ?? 'secret';
            
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            self::$pdo = new \PDO($dsn, $username, $password);
            self::$pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            // Set UTF-8 encoding for the connection
            self::$pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
        return self::$pdo;
    }
    
    public static function all()
    {
        $instance = new static();
        $pdo = self::getPdo();
        $stmt = $pdo->query("SELECT * FROM " . $instance->table);
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = new static($row);
        }
        return $results;
    }
    
    public static function find($id)
    {
        $instance = new static();
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM " . $instance->table . " WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row ? new static($row) : null;
    }
    
    public static function where($column, $value)
    {
        $instance = new static();
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("SELECT * FROM " . $instance->table . " WHERE $column = ?");
        $stmt->execute([$value]);
        $results = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $results[] = new static($row);
        }
        return new Collection($results);
    }
    
    public function save()
    {
        $pdo = self::getPdo();
        
        if (isset($this->id) && $this->id) {
            // Update - always include all fillable fields
            $sets = [];
            $values = [];
            foreach ($this->fillable as $field) {
                $sets[] = "$field = ?";
                // Get value from attributes array which is updated by __set()
                $value = $this->attributes[$field] ?? null;
                $values[] = $value;
            }
            if (!empty($sets)) {
                $values[] = $this->id;
                $sql = "UPDATE " . $this->table . " SET " . implode(', ', $sets) . " WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute($values);
            }
        } else {
            // Insert
            $fields = [];
            $placeholders = [];
            $values = [];
            foreach ($this->fillable as $field) {
                if (isset($this->$field)) {
                    $fields[] = $field;
                    $placeholders[] = '?';
                    $values[] = $this->$field;
                }
            }
            $sql = "INSERT INTO " . $this->table . " (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($values);
            $this->id = $pdo->lastInsertId();
        }
        return $this;
    }
    
    public function delete()
    {
        if (!$this->id) return false;
        $pdo = self::getPdo();
        $stmt = $pdo->prepare("DELETE FROM " . $this->table . " WHERE id = ?");
        return $stmt->execute([$this->id]);
    }
}

class Collection implements \JsonSerializable
{
    private $items = [];
    
    public function __construct($items = [])
    {
        $this->items = $items;
    }
    
    public function first()
    {
        return isset($this->items[0]) ? $this->items[0] : null;
    }
    
    public function exists()
    {
        return count($this->items) > 0;
    }
    
    public function toArray()
    {
        return $this->items;
    }
    
    public function jsonSerialize(): mixed
    {
        return $this->items;
    }
}