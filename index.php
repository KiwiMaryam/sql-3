<?php

abstract class DatabaseModel implements DatabaseWrapper
{
    protected $tableName;
    protected $pdo;

    public function __construct($pdo, $tableName)
    {
        $this->pdo = $pdo;
        $this->tableName = $tableName;
    }

    public function insert(array $tableColumns, array $values): array
    {
        $columns = implode(", ", $tableColumns);
        $placeholders = implode(", ", array_fill(0, count($values), '?'));
        $sql = "INSERT INTO {$this->tableName} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);
        return $this->find($this->pdo->lastInsertId());
    }

   public function update(int $id, array $values): array
{
    $setPart = '';
    $placeholders = [];
    foreach ($values as $key => $value) {
        $setPart .= "{$key} = ?, ";
        $placeholders[] = $value;
    }
    $setPart = rtrim($setPart, ', ');

    $sql = "UPDATE {$this->tableName} SET {$setPart} WHERE id = ?";
    $placeholders[] = $id;

    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($placeholders);

    return $this->find($id);
}

    public function find(int $id): array
    {
        $sql = "SELECT * FROM {$this->tableName} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }

    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->tableName} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([$id]);
    }
}

class Shop extends DatabaseModel
{
    public function __construct($pdo)
    {
        parent::__construct($pdo, 'shop');
    }
}

class Order extends DatabaseModel
{
    public function __construct($pdo)
    {
        parent::__construct($pdo, 'order');
    }
}

class Client extends DatabaseModel
{
    public function __construct($pdo)
    {
        parent::__construct($pdo, 'client');
    }
}

$pdo = new PDO('sqlite:C:/Users/dimaa/Desktop/sql 3/sql.db');

$shop = new Shop($pdo);

// Вставка новой записи в таблицу shop
$newShop = $shop->insert(['name', 'address'], ['Магазин Е', '987 Улица Е']);
print_r($newShop);

// Обновление записи в таблице shop
$updatedShop = $shop->update($newShop['id'], ['name' => 'Магазин']);
print_r($updatedShop);

// Поиск записи в таблице shop
$foundShop = $shop->find($newShop['id']);
print_r($foundShop);

// Удаление записи из таблицы shop
$deleted = $shop->delete($newShop['id']);
echo $deleted ? "Запись успешно удалена." : "Ошибка удаления записи.";

interface DatabaseWrapper
{
    public function insert(array $tableColumns, array $values): array;
    public function update(int $id, array $values): array;
    public function find(int $id): array;
    public function delete(int $id): bool;
}