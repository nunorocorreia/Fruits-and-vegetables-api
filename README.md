# 🍎🥕 Fruits and Vegetables

## 🎯 Goal
We want to build a service which will take a `request.json` and:
* Process the file and create two separate collections for `Fruits` and `Vegetables`
* Each collection has methods like `add()`, `remove()`, `list()`;
* Units have to be stored as grams;
* Store the collections in a storage engine of your choice. (e.g. Database, In-memory)
* Provide an API endpoint to query the collections. As a bonus, this endpoint can accept filters to be applied to the returning collection.
* Provide another API endpoint to add new items to the collections (i.e., your storage engine).
* As a bonus you might:
  * consider giving an option to decide which units are returned (kilograms/grams);
  * how to implement `search()` method collections;
  * use latest version of Symfony's to embed your logic 

### ✔️ How can I check if my code is working?
You have two ways of moving on:
* You call the Service from PHPUnit test like it's done in dummy test (just run `bin/phpunit` from the console)

or

* You create a Controller which will be calling the service with a json payload

## 💡 Hints before you start working on it
* Keep KISS, DRY, YAGNI, SOLID principles in mind
* We value a clean domain model, without unnecessary code duplication or complexity
* Think about how you will handle input validation
* Follow generally-accepted good practices, such as no logic in controllers, information hiding (see the first hint).
* Timebox your work - we expect that you would spend between 3 and 4 hours.
* Your code should be tested
* We don't care how you handle data persistence, no bonus points for having a complex method

## When you are finished
* Please upload your code to a public git repository (i.e. GitHub, Gitlab)

## 🐳 Docker image
Optional. Just here if you want to run it isolated.

### 📥 Pulling image
```bash
docker pull tturkowski/fruits-and-vegetables
```

### 🧱 Building image
```bash
docker build -t tturkowski/fruits-and-vegetables -f docker/Dockerfile .
```

### 🏃‍♂️ Running container
```bash
docker run -it -w/app -v$(pwd):/app tturkowski/fruits-and-vegetables sh 
```

### 🛂 Running tests
```bash
docker run -it -w/app -v$(pwd):/app tturkowski/fruits-and-vegetables bin/phpunit
```

### ⌨️ Run development server
```bash
docker run -it -w/app -v$(pwd):/app -p8080:8080 tturkowski/fruits-and-vegetables php -S 0.0.0.0:8080 -t /app/public
# Open http://127.0.0.1:8080 in your browser
```

## 🚀 Features

- ✅ **Fruits & Vegetables Management**: Separate collections for fruits and vegetables
- ✅ **CRUD Operations**: Add, remove, list, and search items
- ✅ **Unit Conversion**: Automatic conversion between grams (g) and kilograms (kg)
- ✅ **Database Persistence**: SQLite database with Doctrine ORM
- ✅ **RESTful API**: Clean API endpoints with proper HTTP methods
- ✅ **Search Functionality**: Partial name matching with case-insensitive search
- ✅ **Advanced Sorting**: Reusable SortableTrait with configurable sorting options
- ✅ **Date Tracking**: Automatic date_add and date_upd timestamps
- ✅ **Input Validation**: Comprehensive validation with error handling
- ✅ **Testing**: PHPUnit tests for all services

## 🛠️ Setup

### Prerequisites
- PHP 8.1+
- Composer
- SQLite (or Docker)

### Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd FruitsAndVegetables
```

2. **Install dependencies**
```bash
composer install
```

3. **Setup database**
```bash
# Create database and run migrations
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

4. **Process initial data**
```bash
# Process the request.json file
php bin/console app:process-json
```

## 📡 API Endpoints

### Fruits API

#### List Fruits
```http
GET /api/fruits
```

**Query Parameters:**
- `unit` (optional): Display unit - `g` or `kg` (default: `g`)
- `filter[type]` (optional): Filter by type
- `filter[name]` (optional): Filter by exact name match
- `sort[field]` (optional): Sort by field

**Example:**
```bash
# Basic list
curl "http://localhost:8000/api/fruits?unit=kg"

# List with sorting by quantity descending
curl "http://localhost:8000/api/fruits?sort[quantity]=desc"

# List with multiple sorts (type ASC, then name ASC)
curl "http://localhost:8000/api/fruits?sort[type]=asc&sort[name]=asc"

# List with filtering and sorting
curl "http://localhost:8000/api/fruits?filter[type]=fruit&sort[quantity]=desc"
```

#### Add Fruit
```http
POST /api/fruits
```

**Request Body:**
```json
{
    "name": "Apple",
    "type": "fruit",
    "quantity": 1.5,
    "unit": "kg"
}
```

**Example:**
```bash
curl -X POST "http://localhost:8000/api/fruits" \
  -H "Content-Type: application/json" \
  -d '{"name": "Apple", "type": "fruit", "quantity": 1.5, "unit": "kg"}'
```

#### Search Fruits
```http
GET /api/fruits/search
```

**Query Parameters:**
- `name` (required): Search term for partial name matching
- `unit` (optional): Display unit - `g` or `kg` (default: `g`)
- `sort[field]` (optional): Sort by field - `asc` or `desc`

**Examples:**
```bash
# Basic search
curl "http://localhost:8000/api/fruits/search?name=apple"

# Search with unit conversion
curl "http://localhost:8000/api/fruits/search?name=apple&unit=kg"

# Search with sorting by quantity descending
curl "http://localhost:8000/api/fruits/search?name=apple&sort[quantity]=desc"

# Search with multiple sorts (name ASC, then quantity DESC)
curl "http://localhost:8000/api/fruits/search?name=apple&sort[name]=asc&sort[quantity]=desc"
```

#### Delete Fruit
```http
DELETE /api/fruits/{id}
```

**Example:**
```bash
curl -X DELETE "http://localhost:8000/api/fruits/1"
```

### Vegetables API

#### List Vegetables
```http
GET /api/vegetables
```

**Query Parameters:**
- `unit` (optional): Display unit - `g` or `kg` (default: `g`)
- `filter[type]` (optional): Filter by type
- `filter[name]` (optional): Filter by exact name match
- `sort[field]` (optional): Sort by field - `asc` or `desc`

**Examples:**
```bash
# Basic list
curl "http://localhost:8000/api/vegetables"

# List with sorting by quantity descending
curl "http://localhost:8000/api/vegetables?sort[quantity]=desc"

# List with multiple sorts (type ASC, then name ASC)
curl "http://localhost:8000/api/vegetables?sort[type]=asc&sort[name]=asc"

# List with filtering and sorting
curl "http://localhost:8000/api/vegetables?filter[type]=vegetable&sort[quantity]=desc"
```

#### Add Vegetable
```http
POST /api/vegetables
```

**Request Body:**
```json
{
    "name": "Carrot",
    "type": "vegetable",
    "quantity": 500,
    "unit": "g"
}
```

#### Search Vegetables
```http
GET /api/vegetables/search
```

**Query Parameters:**
- `name` (required): Search term for partial name matching
- `unit` (optional): Display unit - `g` or `kg` (default: `g`)
- `sort[field]` (optional): Sort by field - `asc` or `desc`

**Examples:**
```bash
# Basic search
curl "http://localhost:8000/api/vegetables/search?name=carrot"

# Search with unit conversion
curl "http://localhost:8000/api/vegetables/search?name=carrot&unit=kg"

# Search with sorting by quantity descending
curl "http://localhost:8000/api/vegetables/search?name=carrot&sort[quantity]=desc"

# Search with multiple sorts (name ASC, then quantity DESC)
curl "http://localhost:8000/api/vegetables/search?name=carrot&sort[name]=asc&sort[quantity]=desc"
```

#### Delete Vegetable
```http
DELETE /api/vegetables/{id}
```

## 📊 Response Format

All API endpoints return JSON responses in the following format:

### Success Response
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Apple",
            "type": "fruit",
            "quantity": 1500,
            "unit": "g",
            "date_add": "2024-01-15 10:30:00",
            "date_upd": "2024-01-15 14:45:00"
        }
    ]
}
```

### Error Response
```json
{
    "success": false,
    "error": "Error message"
}
```

## 🔍 Search Functionality

The search feature uses **case-insensitive partial matching**:

- **Pattern**: `LIKE '%query%'`
- **Example**: Searching for `apple` will find:
  - "Apple"
  - "Pineapple"
  - "Apple Juice"
- **Parameter**: Use `name` parameter (e.g., `?name=apple`)

## 🔄 Sorting Functionality

The application includes a **reusable SortableTrait** that provides advanced sorting capabilities across all services.

### SortableTrait Features

**Core Methods:**
- `applySorting()` - Applies sorting to Doctrine QueryBuilder
- `getAllowedSortFields()` - Returns allowed fields for sorting
- `getDefaultSortField()` - Returns default sort field

**Supported Sort Fields:**
- `id` - Sort by ID (default)
- `name` - Sort by name
- `type` - Sort by type
- `quantity` - Sort by quantity
- `date_add` - Sort by creation date
- `date_upd` - Sort by last update date

**Sort Directions:**
- `asc` - Ascending order
- `desc` - Descending order

### Usage Examples

**Single Sort:**
```bash
# Sort by quantity descending
GET /api/fruits?sort[quantity]=desc

# Sort by name ascending
GET /api/fruits?sort[name]=asc
```

**Multiple Sorts:**
```bash
# Sort by type ASC, then name ASC
GET /api/fruits?sort[type]=asc&sort[name]=asc

# Sort by quantity DESC, then name ASC
GET /api/fruits?sort[quantity]=desc&sort[name]=asc
```

**Search with Sorting:**
```bash
# Search for "apple" and sort by quantity descending
GET /api/fruits/search?name=apple&sort[quantity]=desc

# Sort by creation date (newest first)
GET /api/fruits?sort[date_add]=desc

# Sort by last update date (most recently updated first)
GET /api/fruits?sort[date_upd]=desc

# Sort by creation date then by name
GET /api/fruits?sort[date_add]=desc&sort[name]=asc
```

## 🧪 Testing

### Run Tests
```bash
bin/phpunit
```
