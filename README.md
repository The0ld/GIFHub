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
        %% UNIQUE constraint: user_id + gif_id must be unique
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

### Notes on Database Design

- **Unique Constraint on Favorite GIFs**: The `favorite_gifs` table enforces a **unique constraint** on the combination of `user_id` and `gif_id`. This ensures that a user cannot save the same GIF more than once, maintaining data consistency.

---

## Use Case Diagram

The following diagram illustrates the main use cases of the application:

- **Actors**:

  - **User**: Represents the API user.
  - **System**: Handles interaction logging.

- **Key Use Cases**:
    1. **Authenticate via API**: User authentication and token generation.
    2. **Search for GIFs**: Query GIFs based on a keyword.
    3. **Get GIF details**: Fetch detailed information about a specific GIF.
    4. **Save GIF as favorite**: Add a GIF to the user's list of favorites.
    5. **Log service interactions**: Automatically log all interactions.

```mermaid
%% Use Case Diagram
flowchart LR
    User["User"] -->|Initiates authentication| Authenticate["Authenticate via API"]
    User -->|Searches for content| SearchGIFs["Search for GIFs"]
    User -->|Requests specific GIF data| GetGIFDetails["Get GIF details"]
    User -->|Marks a GIF as a favorite| SaveGIF["Save GIF as favorite"]

    subgraph System["System"]
        LogInteractions["Log service interactions"]
    end

    Authenticate -->|Logs authentication events| LogInteractions
    SearchGIFs -->|Logs search queries| LogInteractions
    GetGIFDetails -->|Logs GIF detail requests| LogInteractions
    SaveGIF -->|Logs favorite save actions| LogInteractions
```

### Usage

- Users can explore the API endpoints defined in the project.
- All interactions are logged automatically for auditing and debugging purposes.

---

### Workflow Diagrams

This section contains detailed sequence diagrams to demonstrate the flow of each
key use case within the system. These diagrams illustrate how requests are
processed through various layers of the application, including controllers,
middlewares, services, repositories, and external APIs.

#### **1. Authenticate via API**

This diagram outlines the flow for user authentication and token generation:

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant Middleware as LogServiceInteraction
    participant Controller as AuthController
    participant Request as LoginRequest
    participant DTO as CredentialsDTO
    participant ServiceInterface as AuthServiceInterface
    participant Service as AuthService
    participant RepositoryInterface as AuthRepositoryInterface
    participant Repository as AuthRepository
    participant Resource as LoginResource
    participant DB as Database

    User->>API: POST /api/auth/login
    API->>Middleware: Passes request
    Middleware->>Controller: Calls AuthController@login
    Controller->>Request: Validates LoginRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to CredentialsDTO
    DTO->>Controller: Returns DTO
    Controller->>ServiceInterface: Calls AuthServiceInterface@login with DTO
    ServiceInterface->>Service: Resolved to AuthService (via DI container)
    Service->>RepositoryInterface: Calls AuthRepositoryInterface@authenticate with DTO
    RepositoryInterface->>Repository: Resolved to AuthRepository (via DI container)
    Repository->>DB: Queries user by email
    DB->>Repository: Returns user record
    Repository->>Service: Verifies credentials and returns User
    Service->>Controller: Returns token result
    Controller->>Resource: Wraps token result into LoginResource
    Resource->>Controller: Returns formatted JSON response
    Controller->>Middleware: Response ready
    Middleware->>DB: Logs service interaction
    Middleware->>User: Returns JSON response with access token
```

#### **2. Search for GIFs**

This diagram demonstrates the workflow for querying GIFs from the Giphy API:

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware
    participant LogMiddleware as LogServiceInteraction
    participant Controller as GifController@index
    participant Request as GifFilterRequest
    participant DTO as GifFilterDTO
    participant ServiceInterface as GifServiceInterface
    participant Service as GifService
    participant ClientInterface as GifClientInterface
    participant Client as GiphyClient
    participant Redis as Redis Cache
    participant Giphy as Giphy API
    participant GifListDTO
    participant GifClientDTO
    participant PaginationDTO
    participant Resource as GifListResource
    participant DB as Database

    User->>API: GET /api/v1/gifs?q=<query>&limit=<limit>&offset=<offset>
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@index
    Controller->>Request: Validates GifFilterRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to GifFilterDTO
    DTO->>Controller: Returns DTO
    Controller->>ServiceInterface: Calls GifServiceInterface@filterGifs with DTO
    ServiceInterface->>Service: Resolved to GifService (via DI container)
    Service->>ClientInterface: Calls GifClientInterface@filterGifs with DTO
    ClientInterface->>Client: Resolved to GiphyClient (via DI container)
    Client->>Redis: Checks Redis cache for key
    alt Cache Hit
        Redis->>Client: Returns cached GIF list
    else Cache Miss
        Client->>Giphy: Queries Giphy API with filters
        Giphy->>Client: Returns raw GIF data
        Client->>Redis: Stores GIF data in cache
    end
    Client->>GifListDTO: Constructs GifListDTO
    GifListDTO->>GifClientDTO: Maps each GIF to GifClientDTO
    GifListDTO->>PaginationDTO: Builds PaginationDTO for pagination details
    GifClientDTO->>GifListDTO: Returns processed GIF DTOs
    PaginationDTO->>GifListDTO: Returns pagination DTO
    GifListDTO->>Service: Returns structured DTO list
    Service->>Controller: Returns GifListDTO
    Controller->>Resource: Formats response with ApiResponse
    Resource->>Controller: Returns formatted JSON response
    Controller->>LogMiddleware: Response ready
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>User: Returns JSON response with GIF list data and pagination
```

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
