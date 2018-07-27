<?php

namespace App;

class Schemaless {

    public static $tableEntity = 'snv_schemaless_entity';
    
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
    
    public static $tableAttribute = 'snv_schemaless_attribute';
    
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
     * @return \Sinevia\SqlDb
     */
    public static function getDatabase() {
        return db();
    }

    public static function getTableEntity() {
        return self::getDatabase()->table(self::$tableEntity);
    }

    public static function getTableAttribute() {
        return self::getDatabase()->table(self::$tableAttribute);
    }

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
            return false;
        }
    }

    public static function getEntities($options = []) {
        $type = trim($options['Type'] ?? '');
        $query = self::getTableEntity();
        if ($type != '') {
            $query->where('Type', '=', $type);
        }
        $entities = $query->select();
        return $entities;
    }

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
        return self::getTableAttribute()
                        ->where('EntityId', '=', $entityId)
                        ->where('Key', '=', $key)
                        ->selectOne();
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
                                'Value' => $value,
                                'UpdatedAt' => date('Y-m-d'),
            ]);
        } else {
            return self::getTableAttribute()->insert([
                        'Id' => \Sinevia\Uid::microUid(),
                        'EntityId' => $entityId,
                        'Key' => $key,
                        'Value' => $value,
                        'CreatedAt' => date('Y-m-d'),
                        'UpdatedAt' => date('Y-m-d'),
            ]);
        }
    }

}
