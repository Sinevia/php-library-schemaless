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

    /**
     * Creates the storage tables
     */
    public static function createTables() {
        if (static::getTableEntity()->exists() == false) {
            static::getTableEntity()->create(static::$tableEntitySchema);
        }
        if (static::getTableAttribute()->exists() == false) {
            static::getTableAttribute()->create(static::$tableAttributeSchema);
        }
    }

    /**
     * Deletes the storage tables
     */
    public static function deleteTables() {
        if (static::getTableEntity()->exists() == true) {
            static::getTableEntity()->drop();
        }
        if (static::getTableAttribute()->exists() == true) {
            static::getTableAttribute()->drop();
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
        return static::getDatabase()->table(static::$tableEntity);
    }

    /**
     * Returns the table used to store attributes
     * @return type
     */
    public static function getTableAttribute() {
        return static::getDatabase()->table(static::$tableAttribute);
    }

    /**
     * Creates a new entity and returns it
     * @param array $entityData
     * @param array $attributesData
     * @return array|null
     * @throws \RuntimeException
     */
    public static function createEntity($entityData, $attributesData = []) {
        // Set "Id" if not predefined
        if (isset($entityData['Id']) == false) {
            $entityData['Id'] = \Sinevia\Uid::microUid();
        }

        // Set "IsActive" if not predefined
        if (isset($entityData['IsActive']) == false) {
            $entityData['IsActive'] = 'Yes';
        }

        $entityData['CreatedAt'] = date('Y-m-d H:i:s');
        $entityData['UpdatedAt'] = date('Y-m-d H:i:s');

        static::getDatabase()->transactionBegin();

        try {
            $result = static::getTableEntity()->insert($entityData);
            if ($result === false) {
                throw new \RuntimeException('Create entity failed');
            }
            foreach ($attributesData as $key => $value) {
                $result2 = static::setAttribute($entityData['Id'], $key, $value);
                if ($result2 === false) {
                    throw new \RuntimeException('Creating "' . $key . '" attribute failed');
                }
            }
            static::getDatabase()->transactionCommit();
            return static::getEntity($entityData['Id']);
        } catch (\Exception $e) {
            static::getDatabase()->transactionRollBack();
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
        $isActive = trim($options['IsActive'] ?? '');
        $limitFrom = (int) ($options['limitFrom'] ?? 0);
        $limitTo = (int) ($options['limitTo'] ?? 10);
        $wheres = ($options['wheres'] ?? []);
        $withAttributes = (float) ($options['withAttributes'] ?? false);
        $withSoftDeletes = (float) ($options['withSoftDeletes'] ?? true);

        $query = static::getTableEntity();

        if ($type != '') {
            $query->where('Type', '=', $type);
        }

        if ($isActive != '') {
            $query->where('IsActive', '=', $isActive);
        }
        
        if ($withSoftDeletes == false) {
            $query->where(static::$tableEntity . '.DeletedAt', '=', NULL);
        }

        $alliases = [];

        foreach ($wheres as $where) {
            if (is_array($where)) {
                if (substr($where[0], 0, 10) != 'Attribute_') {
                    $query->whereRaw(' AND ' . static::$tableEntity . '.' . $where[0] . ' ' . $where[1] . ' ' . static::getDatabase()->quote($where[2]));
                    continue;
                }

                $alias = 'Alias' . uniqid();
                $query->join(static::$tableAttribute, 'Id', 'EntityId', 'LEFT', $alias);
                $query->whereRaw(' AND (' . $alias . '.Key = ' . static::getDatabase()->quote(substr($where[0], 10)) . ' AND ' . $alias . '.Value' . ' ' . $where[1] . ' ' . static::getDatabase()->quote(static::attributeValueEncode($where[2])) . ' )');
            }
        }

        $query->limit($limitFrom, $limitTo);

        $selectFields = $alliases;
        $entities = $query->select(
                static::$tableEntity . '.Id, ' .
                static::$tableEntity . '.Type, ' .
                static::$tableEntity . '.Title, ' .
                static::$tableEntity . '.CreatedAt, ' .
                static::$tableEntity . '.UpdatedAt, ' .
                static::$tableEntity . '.DeletedAt'
                //static::$tableAttribute.'.*'
        );

        if ($withAttributes == true) {
            foreach ($entities as $index => $entity) {
                $entity['attributes'] = static::getAttributes($entity['Id']);
                $entities[$index] = $entity;
            }
        }

        return $entities;
    }

    /**
     * Retrieves a single entity by ID
     * @param string $entityId
     * @return array
     */
    public static function getEntity($entityId) {
        return static::getTableEntity()
                        ->where('Id', '=', $entityId)
                        ->selectOne();
    }

    public static function getAttributes($entityId) {
        $attributeRows = static::getTableAttribute()
                ->where('EntityId', '=', $entityId)
                ->select();

        $attributes = [];

        foreach ($attributeRows as $attributeRow) {
            $attributes[$attributeRow['Key']] = static::attributeValueDecode($attributeRow['Value']);
        }

        return $attributes;
    }

    public static function getAttribute($entityId, $key) {
        $attribute = static::getTableAttribute()
                ->where('EntityId', '=', $entityId)
                ->where('Key', '=', $key)
                ->selectOne();

        if (is_null($attribute)) {
            return null;
        }

        return static::attributeValueDecode($attribute['Value']);
    }

    public static function setAttribute($entityId, $key, $value) {
        $exists = static::getTableAttribute()
                        ->where('EntityId', '=', $entityId)
                        ->where('Key', '=', $key)
                        ->numRows() > 0 ? true : false;
        if ($exists) {
            $result = static::getTableAttribute()
                    ->where('EntityId', '=', $entityId)
                    ->where('Key', '=', $key)
                    ->update([
                'Value' => static::attributeValueEncode($value),
                'UpdatedAt' => date('Y-m-d'),
            ]);
        } else {
            $result = static::getTableAttribute()->insert([
                'Id' => \Sinevia\Uid::microUid(),
                'EntityId' => $entityId,
                'Key' => $key,
                'Value' => static::attributeValueEncode($value),
                'CreatedAt' => date('Y-m-d'),
                'UpdatedAt' => date('Y-m-d'),
            ]);
        }

        if ($result !== false) {
            return true;
        }

        return false;
    }

    public static function setAttributes($entityId, $attributes) {
        static::getDatabase()->transactionBegin();
        try {
            foreach ($attributes as $key => $value) {
                $result2 = static::setAttribute($entityId, $key, $value);
                if ($result2 === false) {
                    throw new \RuntimeException('Creating attribute "' . $key . '" failed');
                }
            }
            static::getDatabase()->transactionCommit();
            return true;
        } catch (\Exception $e) {
            static::getDatabase()->transactionRollBack();
            //echo $e->getMessage();
            return false;
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
