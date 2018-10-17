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

}
