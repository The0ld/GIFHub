# GIFHub API

## Project Description

GIFHub is an API designed to manage GIF searches and favorites using
Giphy as an external provider. The API is built for scalability and
security, featuring Passport authentication and Redis-based caching.

---

### Database Design

The application follows a relational database structure with the following key entities:

- **Users**: Represents authenticated users in the system.
- **Favorite GIFs**: Stores GIFs saved by users, allowing aliasing for personalized identification.
- **Service Logs**: Tracks API interactions for monitoring and debugging purposes.

Below is the Entity-Relationship Diagram (ERD) that illustrates these relationships:

```mermaid
erDiagram
    USER {
        int id PK
        string name
        string email
        string password
        datetime email_verified_at
        datetime created_at
        datetime updated_at
        string remember_token
    }
    FAVORITE_GIF {
        int id PK
        int user_id FK
        string gif_id
        string alias
        datetime created_at
        datetime updated_at
        UNIQUE(user_id, gif_id)
    }
    SERVICE_LOG {
        int id PK
        int user_id FK
        string service
        json request_body
        json response_body
        int response_status
        string ip_address
        string duration
        datetime created_at
        datetime updated_at
    }

    USER ||--o{ FAVORITE_GIF : "has many"
    USER ||--o{ SERVICE_LOG : "has many"
```

This diagram demonstrates:

- **One-to-Many Relationships**:
- Users can have multiple favorite GIFs.
- Users can generate multiple service logs.

---

## System Requirements

No special requirements are needed on your host machine, as all dependencies
are containerized using Docker. Just ensure you have:

- **Docker:** Version 20.10 or higher.
- **Docker Compose:** Version 1.29 or higher.
- **Make:** Installed on your machine (optional but recommended).

---

## Deployment Instructions

### Step 1: Clone the Repository

Clone the repository to your local machine:

```bash
git clone <REPOSITORY_URL>
cd gifhub
```

### Step 2: Configure Environment Variables

Copy the `.env.example` file as `.env` to configure your environment:

```bash
cp .env.example .env
```

Ensure you add your Giphy API Key in the .env file:

```env
GIPHY_API_KEY=your_giphy_api_key_here
```

### Step 3: Build and Start Containers

Run the following command to build and start the Docker services:

```bash
make setup
```

This command will:

- Build the required containers.
- Install Laravel dependencies.
- Generate the application key and Passport keys.
- Run database migrations and seed sample data.

### Step 4: Start the Server

To start the application and serve it locally:

```bash
make serve
```

The API will be accessible at:

```bash
http://localhost:8000/api
```

---

## Postman Collection and Environment

We provide a Postman collection and environment file for easy interaction
with the API.

### Download Links

- **Collection:** [GIFHub Postman Collection](https://github.com/The0ld/GIFHub/blob/main/docs/GIFHub%20API.postman_collection.json)
- **Environment:** [GIFHub Postman Environment](https://github.com/The0ld/GIFHub/blob/main/docs/GIFHub.postman_environment.json)

### Instructions

1. Import the collection and environment into Postman.
2. Ensure the `base_url` variable in the environment is set to:

    ```bash
    http://localhost:8000/api
    ```

3. The collection includes:
    - Authentication routes (login).
    - Favorite GIF management routes.
    - GIF search routes.

---

## Useful Commands

Here are some commonly used commands for managing the project:

- **Start the project:**

    ```bash
    make up
    ```

- **Stop the containers:**

    ```bash
    make down
    ```

- **Run tests:**

    ```bash
    make test
    ```

- **View logs for the application container:**

    ```bash
    docker compose logs -f app
    ```

---

## Additional Resources

- **Laravel Documentation:** [https://laravel.com/docs](https://laravel.com/docs)
- **Docker Documentation:** [https://docs.docker.com/](https://docs.docker.com/)
- **Giphy API Documentation:** [https://developers.giphy.com/](https://developers.giphy.com/)

---

## License

This project is licensed under the [MIT License](LICENSE).
