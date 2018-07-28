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

1. Creating new entities

```php
// 1. Create new person
$person = \Sinevia\Schemaless::createEntity([
    'Type' => 'Person',
    'Title' => 'Peter Pan'
]);

// 2. Check if successful
if (is_null($person)) {
    die('Entity failed to be created');
} else {
    echo 'New entity created with Id '.$person['Id']);
    var_dump($person);
}

// 3. Create new person with attributes
$person = \Sinevia\Schemaless::createEntity([
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

// 4. Check if successful
if (is_null($person)) {
    die('Entity failed to be created');
} else {
    echo 'New entity created with Id '.$person['Id']);
    var_dump($person);
}

```
2. Set attributes

```php
// 1. Add attributes individually
\Sinevia\Schemaless::setAttribute($personId, 'AddressLine1', '45 Queens Road');
\Sinevia\Schemaless::setAttribute($personId, 'AddressLine2', 'Foxes Layer');

// 2. Add many attributes at once
\Sinevia\Schemaless::setAttributes($personId, [
    'AddressLine1' => '122 Shepherds Close',
    'AddressLine2' => 'Cows Barn',
]);
```

3. Get entities

```php
// 1. Retrieve entity by Id
$person = \Sinevia\Schemaless::getEntity($personId);
var_dump($person);


// 2. Retrieve entities by search
$people = \Sinevia\Schemaless::getEntities([
    'Type' => 'Person',
    'IsActive' => 'Yes',
    'limitFrom' => 0,
    'limitTo' => 2,
    // Advanced querying
    'wheres' => [
        ['CreatedAt', '>', '2018-07-27 22:24:10'],
        ['CreatedAt', '<', '2018-07-27 23:31:50'],
        ['Attribute_FirstName', '=', 'Peter'],
        ['Attribute_LastName', 'LIKE', '%an%'],
    ],
    // Should returned entities contain also the attributes
    'withAttributes' => true,
]);

var_dump($people);

```

4. Get attributes

```php
// 1. Retrieve and display entity attributes
echo \Sinevia\Schemaless::getAttribute($personId, 'AddressLine1');
echo \Sinevia\Schemaless::getAttribute($personId, 'AddressLine2');

// 2. Get all atributes at once
$attributes = \Sinevia\Schemaless::getAttributes($personId);
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
