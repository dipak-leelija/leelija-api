### Prerequisites

- [PHP](https://www.php.net/downloads.php)
- [MySQL](https://www.mysql.com/downloads/)
- [Composer](http://getcomposer.org/)
- [Postman](https://www.postman.com/downloads/)

## Getting Started

Clone this project with the following commands:

```bash
git clone https://github.com/dipak-leelija/leelija-api.git
cd leelija-api
```

### Configure the application

Copy `.env.example` to `.env` file and enter your database deatils.

```bash
cp .env.example .env
```

## Development

Install the project dependencies and start the PHP server:

```bash
composer install
```

```bash
php -S localhost:8000 -t api
```

## Your APIs

| API                   |    CRUD    |                                Description |
| :-------------------- | :--------: | -----------------------------------------: |
| GET /customer         |  **READ**  |        Get all the Posts from `post` table |
| GET /customer/{id}    |  **READ**  |        Get a single Post from `post` table |
| POST /customer        | **CREATE** | Create a Post and insert into `post` table |
| PUT /customer/{id}    | **UPDATE** |            Update the Post in `post` table |
| DELETE /customer/{id} | **DELETE** |            Delete a Post from `post` table |

Test the API endpoints using [Postman](https://www.postman.com/).

## License

See [License](./LICENSE)