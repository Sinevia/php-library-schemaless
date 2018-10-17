if (class_exists('\Sinevia\DataObject')) {

  class SchemalessDataObject extends Sinevia\DataObject {

      function getId() {
          return $this->get('Id');
      }

      function getType() {
          return $this->get('Type');
      }

      function getParentId() {
          return $this->get('ParentId');
      }

      function getSequence() {
          return $this->get('Sequence');
      }

      function getDescription() {
          return $this->get('Description');
      }

      function getCreatedAt() {
          return $this->get('CreatedAt');
      }

      function getDeletedAt() {
          return $this->get('DeletedAt');
      }

      function getUpdatedAt() {
          return $this->get('UpdatedAt');
      }

      function setType($type) {
          return $this->set('Type', $type);
      }

      function setParentId($parentId) {
          return $this->set('ParentId', $parentId);
      }

      function setSequence($sequence) {
          return $this->set('Sequence', $sequence);
      }

      function setDescription($description) {
          return $this->set('Description', $description);
      }

      function setCreatedAt($createdAt) {
          return $this->set('CreatedAt', $createdAt);
      }

      function setDeletedAt($deletedAt) {
          return $this->set('DeletedAt', $deletedAt);
      }

      function setUpdatedAt($updatedAt) {
          return $this->set('UpdatedAt', $updatedAt);
      }

      protected function getAttribute($key) {
          return $this->get('Attribute_' . $key);
      }

      protected function setAttribute($key, $value) {
          return $this->set('Attribute_' . $key, $value);
      }
  }
}
