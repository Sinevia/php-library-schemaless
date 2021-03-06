# Schemaless

[![Latest Stable Version](https://poser.pugx.org/sinevia/php-library-schemaless/v/stable)](https://packagist.org/packages/sinevia/php-library-schemaless)
[![Total Downloads](https://poser.pugx.org/sinevia/php-library-schemaless/downloads)](https://packagist.org/packages/sinevia/php-library-schemaless)
[![Latest Unstable Version](https://poser.pugx.org/sinevia/php-library-schemaless/v/unstable)](https://packagist.org/packages/sinevia/php-library-schemaless)
[![License](https://poser.pugx.org/sinevia/php-library-schemaless/license)](https://packagist.org/packages/sinevia/php-library-schemaless)
[![Monthly Downloads](https://poser.pugx.org/sinevia/php-library-schemaless/d/monthly)](https://packagist.org/packages/sinevia/php-library-schemaless)
[![Daily Downloads](https://poser.pugx.org/sinevia/php-library-schemaless/d/daily)](https://packagist.org/packages/sinevia/php-library-schemaless)
[![composer.lock](https://poser.pugx.org/sinevia/php-library-schemaless/composerlock)](https://packagist.org/packages/sinevia/php-library-schemaless)

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

// 1. Retrieve entity by Id with Attributes
$person = \Sinevia\Schemaless::getEntity($personId, ['withAttributes' => true]);
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
5. Update entity

```php
// 1. Update entity by Id
\Sinevia\Schemaless::updateEntity($personId,[
    'Title' => 'Updated'
]);

// 2. Update entity by Id with Attributes
\Sinevia\Schemaless::updateEntity($personId,[
    'Title' => 'Updated'
],[
    'Postcode' => 'New Postcode';
]);

```

6. Update attribute

```php
$isDeleted = \Sinevia\Schemaless::setAttribute($personId,'AddressLine1','Updated Address 1');
```


7. Delete entity

```php
$isDeleted = \Sinevia\Schemaless::deleteEntity($personId);
```

8. Delete attribute

```php
$isDeleted = \Sinevia\Schemaless::deleteAttribute($personId,'AddressLine1');
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

## Extend

One can easily extend the default schemaless class, and create his own version. See the example below which custome table names.

```php
class MySchemaless extends Sinevia\Schemaless {
    public static $tableEntity = 'my_schemaless_entity';
    public static $tableAttribute = 'my_schemaless_attribute';

}

$page = MySchemaless::createEntity([
    'Type'=>'Page',
    'Title'=>'Home Page'
]);
```


## Working with Objects ##

Schemaless provides options to work with Objects instead of with Entities and Attributes. This is achieved with two helper classes - SchemalessDataObject and SchemalessDataObjectRepository.

Note that being an abstraction it will be a bit slower as it will have to hydrate the data objects with data.

### 1. Creating a Data Object Class ###

```
class Person extends SchemalessDataObject {

    const TYPE = 'Person';

    function __construct() {
        $this->setType(self::TYPE);
    }

    public function getEmail() {
        return $this->getAttribute('Email');
    }

    public function setEmail($email) {
        $this->setAttribute("Email", $email);
    }

}
```

### 2. Creating a Data Object Reository ###

```
class PersonRepository extends SchemalessDataObjectRepository {

    public static function findPersonByEmail($email) {
        $entities = \Sinevia\Schemaless::getEntities([
                    'Type' => Person::TYPE,
                    'limitFrom' => 0,
                    'limitTo' => 1,
                    'wheres' => [
                        ['Attribute_Email', '=', $email]
                    ],
                    'withAttributes' => true,
        ]);

        if (count($entities) < 1) {
            return null;
        }

        $person = new Person;
        self::hydrateObject($user, $entities[0]);
        return $person;
    }
    
}
```

### 3. How to Use ###

```
// Creating a new object

$person = new Person;
$person->setEmail('sam@example.com');
$isSaved = Person::saveObject($person);
if($isSaved){
    echo $person->getId();
}

```

```
// Finding an object

$person = Person::findByEmail('sam@example.com');
if(is_null($person)){
    echo $person->getEmail();
}

```
