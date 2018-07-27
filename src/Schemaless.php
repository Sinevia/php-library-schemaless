<?php

namespace Sinevia;

class Schemaless {

    /**
     * The table name for entities
     * @var string
     */
    public static $tableEntity = 'snv_schemaless_entity';
    
    /**
     * The table schema for entities
     * @var array
     */
    public static $tableEntitySchema = array(
        array("Id", "STRING", "NOT NULL PRIMARY KEY"),
        array("IsActive", "STRING"),
        array("Type", "STRING", "NOT NULL"),
        array("Title", "STRING"),
        array("ParentId", "STRING"),
        array("Sequence", "STRING"),
        array("Description", "TEXT"),
        array("CreatedAt", "STRING"),
        array("UpdatedAt", "STRING"),
        array("DeletedAt", "STRING"),
    );
    
    /**
     * The table name for attributes
     * @var string
     */
    public static $tableAttribute = 'snv_schemaless_attribute';
    
    /**
     * The table schema for attributes
     * @var array
     */
    public static $tableAttributeSchema = array(
        array("Id", "STRING", "NOT NULL PRIMARY KEY"),
        array("EntityId", "STRING", "NOT NULL"),
        array("Key", "STRING"),
        array("Value", "TEXT"),
        array("CreatedAt", "STRING"),
        array("UpdatedAt", "STRING"),
        array("DeletedAt", "STRING"),
    );

    public static function createTables() {
        if (self::getTableEntity()->exists() == false) {
            self::getTableEntity()->create(self::$tableEntitySchema);
        }
        if (self::getTableAttribute()->exists() == false) {
            self::getTableAttribute()->create(self::$tableAttributeSchema);
        }
    }

    /**
     * Returns the database to be used for storage
     * @return \Sinevia\SqlDb
     */
    public static function getDatabase() {
        return db();
    }

    /**
     * Returns the table used to store entities
     * @return type
     */
    public static function getTableEntity() {
        return self::getDatabase()->table(self::$tableEntity);
    }

    /**
     * Returns the table used to store attributes
     * @return type
     */
    public static function getTableAttribute() {
        return self::getDatabase()->table(self::$tableAttribute);
    }

    /**
     * Creates a new entity and returns it
     * @param array $entityData
     * @param array $attributesData
     * @return array|null
     * @throws \RuntimeException
     */
    public static function createEntity($entityData, $attributesData = []) {
        if (isset($entityData['Id']) == false) {
            $entityData['Id'] = \Sinevia\Uid::microUid();
        }
        if (isset($entityData['IsActive']) == false) {
            $entityData['IsActive'] = 'No';
        }
        $entityData['CreatedAt'] = date('Y-m-d H:i:s');
        $entityData['UpdatedAt'] = date('Y-m-d H:i:s');
        self::getDatabase()->transactionBegin();
        try {
            $result = self::getTableEntity()->insert($entityData);
            if ($result === false) {
                throw new \RuntimeException('Create entity failed');
            }
            foreach ($attributesData as $key => $value) {
                $result2 = self::setAttribute($entityData['Id'], $key, $value);
                if ($result2 === false) {
                    throw new \RuntimeException('Create attribute failed');
                }
            }
            self::getDatabase()->transactionCommit();
            return self::getEntity($entityData['Id']);
        } catch (\Exception $e) {
            self::getDatabase()->transactionRollBack();
            echo $e->getMessage();
            return null;
        }
    }

    /**
     * Retrieves entities
     * @param array $options
     * @return array
     */
    public static function getEntities($options = []) {
        $type = trim($options['Type'] ?? '');
        $query = self::getTableEntity();
        if ($type != '') {
            $query->where('Type', '=', $type);
        }
        $entities = $query->select();
        return $entities;
    }

    /**
     * Retrieves a single entity by ID
     * @param string $entityId
     * @return array
     */
    public static function getEntity($entityId) {
        return self::getTableEntity()
                        ->where('Id', '=', $entityId)
                        ->select();
    }

    public static function getAttributes($entityId) {
        return self::getTableAttribute()
                        ->where('EntityId', '=', $entityId)
                        ->select();
    }

    public static function getAttribute($entityId, $key) {
        $attribute = self::getTableAttribute()
                ->where('EntityId', '=', $entityId)
                ->where('Key', '=', $key)
                ->selectOne();

        if (is_null($attribute)) {
            return null;
        }

        return static::attributeValueDecode($attribute['Value']);
    }

    public static function setAttribute($entityId, $key, $value) {
        $exists = self::getTableAttribute()
                        ->where('EntityId', '=', $entityId)
                        ->where('Key', '=', $key)
                        ->numRows() > 0 ? true : false;
        if ($exists) {
            return self::getTableAttribute()
                            ->where('EntityId', '=', $entityId)
                            ->where('Key', '=', $key)
                            ->update([
                                'Value' => static::attributeValueEncode($value),
                                'UpdatedAt' => date('Y-m-d'),
            ]);
        } else {
            return self::getTableAttribute()->insert([
                        'Id' => \Sinevia\Uid::microUid(),
                        'EntityId' => $entityId,
                        'Key' => $key,
                        'Value' => static::attributeValueEncode($value),
                        'CreatedAt' => date('Y-m-d'),
                        'UpdatedAt' => date('Y-m-d'),
            ]);
        }
    }

    /**
     * Returns the value
     * @param type $key
     * @return string
     */
    protected static function attributeValueDecode($value) {
        return json_decode($value, true);
    }

    /**
     * Saves the value
     * @param object $value
     * @return boolean
     */
    protected static function attributeValueEncode($value) {
        return json_encode($value);
    }

}
