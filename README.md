# Git clone

Database migration and seeding with 10,000 products(you can change the count in [DatabaseSeeder.php](database/seeders/DatabaseSeeder.php))
```console
php artisan migrate --seed
```

# Api routes

### Get all products.

```http
GET /api/v1/products/?search=
```

##### Parameters
| Parameter | Type | Description        |
| :--- | :--- |:-------------------|
| `search` | `string` | For search by name |

##### Responses

```javascript
{
  "id": int,
  "name": string,
  "frequency": int
}
```

### Show product and similar products.

```http
GET /api/v1/products/{product}
```

##### Parameters
| Parameter | Type  | Description |
| :--- |:------|:------------|
| `product` | `int` | Id          |

##### Responses

```javascript
{
  "id": int,
  "name": string,
  "frequency": int,
  "similar_products": [
        {
            "id": int,
            "name": string,
            "frequency": int,
        }
    ]
}
```
