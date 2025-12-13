# Dynamic Classified Ads API

A RESTful API built with Laravel for a classified ads platform featuring dynamic, category-specific fields.

## Features

- **Dynamic Category Fields**: Each category has its own set of custom fields (e.g., Cars have mileage, fuel type; Apartments have bedrooms, area)
- **Laravel Sanctum Authentication**: Secure API token-based authentication
- **Dynamic Validation**: Validation rules are automatically built based on category field definitions
- **API Resources**: Clean, transformed JSON responses using Laravel API Resources
- **Caching**: External API responses are cached for 24 hours

## Requirements

- PHP 8.2+
- Composer
- MySQL

## Installation

```bash
# Clone the repository
git clone <repository-url>
cd dynamic-classified-api

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed categories and fields from OLX API
php artisan db:seed --class=CategoriesSeeder
```

## API Endpoints

### Authentication

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/auth/register` | Register new user | No |
| POST | `/api/v1/auth/login` | Login and get token | No |


### Ads

| Method | Endpoint | Description | Auth Required |
|--------|----------|-------------|---------------|
| POST | `/api/v1/ads` | Create a new ad | Yes |
| GET | `/api/v1/my-ads` | List user's ads (paginated) | Yes |
| GET | `/api/v1/ads/{id}` | View a specific ad | No |

## Usage Examples

### Register a User

```bash
 POST http://localhost:8000/api/v1/auth/register 
  "Content-Type: application/json" 
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
  }
```

### Login

```bash
 POST http://localhost:8000/api/v1/auth/login 
  "Content-Type: application/json" 
  {
    "email": "john@example.com",
    "password": "password123"
  }
```

### Create an Ad

```bash
 POST http://localhost:8000/api/v1/ads 
  "Content-Type: application/json" 
  "Authorization: Bearer {your-token}" 
   {
    "category_id": 1,
    "title": "BMW 320i 2020 Model",
    "description": "Well maintained car, single owner, full service history.",
    "price": 25000,
    "fields": {
      "mileage": 50000,
      "fuel_type": "petrol",
      "transmission": "automatic"
    }
  }
```

### Get My Ads

```bash
GET "http://localhost:8000/api/v1/my-ads?per_page=10" 
 "Authorization: Bearer {your-token}"
```

### View a Specific Ad

```bash
GET http://localhost:8000/api/v1/ads/1
```

## Response Format

### Success Response (Create Ad)

```json
{
  "success": true,
  "message": "Ad created successfully",
  "data": {
    "id": 1,
    "title": "BMW 320i 2020 Model",
    "description": "Well maintained car, single owner, full service history.",
    "price": 25000.00,
    "price_formatted": "25,000.00 USD",
    "category": {
      "id": 1,
      "external_id": 23,
      "name": "Cars for Sale",
      "slug": "cars-for-sale"
    },
    "dynamic_fields": [
      {
        "id": 1,
        "field_name": "Mileage",
        "field_attribute": "mileage",
        "field_type": "range",
        "value": "50000",
        "display_value": "50000"
      },
      {
        "id": 2,
        "field_name": "Fuel Type",
        "field_attribute": "fuel_type",
        "field_type": "single_choice",
        "value": "petrol",
        "display_value": "Petrol"
      }
    ],
    "created_at": "2025-12-11T10:30:00+00:00",
    "updated_at": "2025-12-11T10:30:00+00:00"
  }
}
```

### Paginated Response (My Ads)

```json
{
  "data": [
    {
      "id": 1,
      "title": "BMW 320i 2020 Model",
      "description": "Well maintained car...",
      "price": 25000.00,
      "category": {...},
      "dynamic_fields": [...]
    }
  ],
  "success": true,
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 67,
    "from": 1,
    "to": 15
  },
  "links": {
    "first": "http://localhost:8000/api/v1/my-ads?page=1",
    "last": "http://localhost:8000/api/v1/my-ads?page=5",
    "prev": null,
    "next": "http://localhost:8000/api/v1/my-ads?page=2"
  }
}
```

### Validation Error Response (HTTP 422)

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "title": ["The title field is required."],
    "fields.mileage": ["The Mileage field is required."]
  }
}
```

### Authentication Error Response (HTTP 401)

```json
{
  "success": false,
  "message": "Unauthenticated.Please login to access this feature."
}
```



## Running Tests

```bash
php artisan test

# Or run specific test files
php artisan test --filter=PostAdTest
```

## Dynamic Validation

The `StoreAdRequest` dynamically builds validation rules based on the selected category's fields:

- **Required fields**: Validated based on `is_mandatory` flag in `category_fields` table
- **Field types**: Validated based on `value_type` (integer, string, decimal, boolean)
- **Choice fields**: Validated against allowed options in `category_field_options` table

## Caching

External API calls to fetch categories and category fields are cached for 24 hours to prevent excessive requests. Cache can be cleared using:

```bash
php artisan cache:clear
```
