# Schemaless

Note. Work in progress. Subject to change

A streamlined entity-attribute (EA) implementation for PHP. This package is designed for quick plug and play "schemaless" prototyping. To achieve this the package uses only two database tables unlike the standard EAV (which uses at least three tables).

## Install

Using composer

```php
composer require sinevia/php-library-schemaless
```

Create storage tables

```php
\Sinevia\Schemaless::createTables();
```

## Uninstall

Delete storage tables

```php
\Sinevia\Schemaless::deleteTables();
```

## How to Use?

```php
// 1. Create entity with attributes
$entity = \Sinevia\Schemaless::createEntity([
    'Type' => 'Person',
    'Title' => 'Peter Pan',
        ], [
    'FirstName' => 'Peter',
    'LastName' => 'Pan',
    'AddressLine1' => '23 Kings Way',
    'AddressLine2' => 'Wolves Den',
    'Postcode' => 'WWW 123 NET',
    'Country' => 'WW',    
]);

// 2. Check if successful
if (is_null($entity)) {
    die('Entity failed to be created');
}

// 3. Retrieve and display entity
var_dump(\Sinevia\Schemaless::getEntity($entity['Id']));

// 4. Retrieve and display entity attributes
echo \Sinevia\Schemaless::getAttribute($entity['Id'],'AddressLine1');
echo \Sinevia\Schemaless::getAttribute($entity['Id'],'AddressLine2');

// 5. Update entity attributes
\Sinevia\Schemaless::setAttribute($entity['Id'], 'AddressLine1', '45 Queens Road');
\Sinevia\Schemaless::setAttribute($entity['Id'], 'AddressLine2', 'Foxes Layer');

// 6. Retrieve and display entity attributes
echo \Sinevia\Schemaless::getAttribute($entity['Id'],'AddressLine1');
echo \Sinevia\Schemaless::getAttribute($entity['Id'],'AddressLine2');
   
```     

## Table Schema ##

The following schema is used for the database.

| Entity    |                  |
|-----------|------------------|
| Id        | String, UniqueId |
| Status    | String           |
| Type      | String           |
| ParentId  | String, UniqueId |
| Sequence  | Integer          |
| Name      | String           |
| CreatedAt | DateTime         |
| DeletedAt | DateTime         |
| Udated At | DateTime         |

| Attribute |                  |
|-----------|------------------|
| Id        | String, UniqueId |
| EntityId  | String, UniqueId |
| Key       | String           |
| Value     | JSON Text (Long) |
| CreatedAt | DateTime         |
| DeletedAt | DateTime         |
| UpdatedAt | DateTime         |
