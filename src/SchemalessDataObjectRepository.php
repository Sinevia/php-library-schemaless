<?php

namespace Sinevia;

class SchemalessDataObjectRepository {

    /**
     * Creates or updates an object
     * @param SchemalessDataObject $object
     * @return boolean true if successful, false otherwise
     */
    public static function saveObject(&$object) {
        $saveArray = $object->data_changed;
        $entityData = [];
        $attributeDate = [];
        foreach ($saveArray as $key => $value) {
            $isAttribute = false;
            if (substr($key, 0, 11) == 'Attributes_') {
                $isAttribute = true;
                $key = substr($key, 11);
            }
            if ($isAttribute == true) {
                $attributeDate[$key] = $value;
            }
            if ($isAttribute == false) {
                $entityData[$key] = $value;
            }
        }

        if (isset($entityData['Id']) == false) {
            $result = \Sinevia\Schemaless::createEntity($entityData, $attributeDate);
            if (is_array($result)) {
                $object->setId($result['Id']);
                return true;
            }
            return false;
        }

        return \Sinevia\Schemaless::updateEntity($object->getId(), $entityData, $attributeDate);
    }

    /**
     * Hydrates an object with data
     * @param type $object
     * @param array $data
     * @return type
     */
    public static function hydrateObject($object, array $data) {
        $dataFlattened = self::flattenArrayWithDashes($data);
        $object->data = $dataFlattened;
        return $object;
    }

    /**
     * Recursively flattens an array 
     * @param array $array
     * @return \App\Plugins\RecursiveIteratorIterator
     */
    protected static function flattenArrayWithDashes(array $array) {
        $ritit = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($array));
        $result = array();
        foreach ($ritit as $leafValue) {
            $keys = array();
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $keys[] = $ritit->getSubIterator($depth)->key();
            }
            $result[join('_', $keys)] = $leafValue;
        }
        return $result;
    }

}
