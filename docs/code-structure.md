# Code structure

## Doctrine

We use [DoctrineExtensions](https://symfony.com/bundles/StofDoctrineExtensionsBundle/current/index.html) on a lot of
entity fields. It introduces more functionality that allows to use Doctrine more efficiently. We use:

1. [Timestampable](https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/timestampable.md) to
   automatically update date fields on create and update

```php
/**
 * @Gedmo\Timestampable(on="create")
 * @ORM\Column(type="datetime", name="created_on", options={"default": "CURRENT_TIMESTAMP"})
 */
private DateTimeInterface $createdOn;

/**
 * @Gedmo\Timestampable(on="update")
 * @ORM\Column(type="datetime", name="edited_on", options={"default": "CURRENT_TIMESTAMP"})
 */
private DateTimeInterface $editedOn;
```
 
2. [Softdeleteable](https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/softdeleteable.md) to
   implicitly delete records. It introduces a `deleted_at` column on the entity, and filters doctrine methods like
   `findAll` to ignore deleted records.

3. [Sortable](https://github.com/doctrine-extensions/DoctrineExtensions/blob/main/doc/sortable.md) to make entity
   records sortable. E.g. the `sequence` column used by sortable datagrids. 

```php
/**
 * @Gedmo\SortablePosition
 * @ORM\Column(type="integer", length=11, nullable=true)
 */
private ?int $sequence;

/**
 * @Gedmo\SortableGroup
 * @ORM\Column(type="locale", name="language")
 */
private Locale $locale;
```
