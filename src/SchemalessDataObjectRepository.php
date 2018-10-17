<?php

namespace Sinevia;

class SchemalessDataObjectRepository {

    public static function saveObject($object) {
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
        return Sinevia\Schemaless::updateEntity($object->getId(), $entityData, $attributeDate);
    }
    
    public static hydrateObject($object, array $data){
        $data = self::flattenArrayWithDashes($data);
        $object->data = $data;
        return $card;
    }
    
    protected static function flattenArrayWithDashes(array $array) {
        $ritit = new RecursiveIteratorIterator(new RecursiveArrayIterator($array));
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
