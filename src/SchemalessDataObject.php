<?php

namespace Sinevia;

if (class_exists('\Sinevia\DataObject')) {

  class SchemalessDataObject extends \Sinevia\DataObject {

      public function getId() {
          if ($this->has('Id')) {
              return $this->get('Id');
          }
          return null;
      }

      public function getType() {
          return $this->get('Type');
      }

      public function getParentId() {
          return $this->get('ParentId');
      }

      public function getSequence() {
          return $this->get('Sequence');
      }

      public function getDescription() {
          return $this->get('Description');
      }

      public function getCreatedAt() {
          return $this->get('CreatedAt');
      }

      public function getDeletedAt() {
          return $this->get('DeletedAt');
      }

      public function getUpdatedAt() {
          return $this->get('UpdatedAt');
      }

      public function setId($id) {
          return $this->set('Id', $id);
      }

      public function setType($type) {
          return $this->set('Type', $type);
      }

      public function setParentId($parentId) {
          return $this->set('ParentId', $parentId);
      }

      public function setSequence($sequence) {
          return $this->set('Sequence', $sequence);
      }

      public function setDescription($description) {
          return $this->set('Description', $description);
      }

      public function setCreatedAt($createdAt) {
          return $this->set('CreatedAt', $createdAt);
      }

      public function setDeletedAt($deletedAt) {
          return $this->set('DeletedAt', $deletedAt);
      }

      public function setUpdatedAt($updatedAt) {
          return $this->set('UpdatedAt', $updatedAt);
      }

      protected function getAttribute($key) {
          return $this->get('Attributes_' . $key);
      }

      protected function setAttribute($key, $value) {
          return $this->set('Attributes_' . $key, $value);
      }

  }
  
}
