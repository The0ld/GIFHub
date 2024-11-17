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

- **Unique Constraint on Favorite GIFs**: The `favorite_gifs` table enforces a
    **unique constraint** on the combination of `user_id` and `gif_id`.
    This ensures that a user cannot save the same GIF more than once,
    maintaining data consistency.

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
graph LR
    %% Node styles
    classDef actorStyle fill:#474943,stroke:#000,stroke-width:1px
    classDef useCaseStyle fill:#024A86,stroke:#000,stroke-width:1px,rx:10,ry:10
    classDef errorStyle fill:#C82A54,stroke:#000,stroke-width:1px,rx:5,ry:5

    %% Actors
    User[User]
    System[System]
    class User,System actorStyle

    %% Use Cases
    subgraph "Use Cases"
        Authenticate(Authenticate via API)
        SearchGIFs(Search for GIFs)
        GetGIFDetails(Get GIF Details)
        SaveGIF(Save GIF as Favorite)
        LogInteractions(Log Service Interactions)
    end
    class Authenticate,SearchGIFs,GetGIFDetails,SaveGIF,LogInteractions useCaseStyle

    %% Errors
    subgraph "Error Handling"
        ValidationError(Validation Error)
        UnauthenticatedError(Unauthenticated Action)
        UnauthorizedError(Unauthorized Action)
        NotFoundError(GIF Not Found)
        DuplicateEntryError(Duplicate Entry Error)
    end
    class ValidationError,UnauthenticatedError,UnauthorizedError,NotFoundError,DuplicateEntryError errorStyle

    %% User interactions
    User -->|Initiates authentication| Authenticate
    User -->|Searches for content| SearchGIFs
    User -->|Requests GIF details| GetGIFDetails
    User -->|Saves GIF as favorite| SaveGIF

    %% System interactions
    System -->|Performs| LogInteractions

    %% Use cases trigger logging
    Authenticate -->|Triggers logging| System
    SearchGIFs -->|Triggers logging| System
    GetGIFDetails -->|Triggers logging| System
    SaveGIF -->|Triggers logging| System

    %% Error handling for Authenticate
    Authenticate -->|Handles 422 Validation Error| ValidationError
    Authenticate -->|Handles 401 Unauthenticated Error| UnauthenticatedError

    %% Error handling for SearchGIFs
    SearchGIFs -->|Handles 422 Validation Error| ValidationError
    SearchGIFs -->|Handles 401 Unauthenticated Error| UnauthenticatedError

    %% Error handling for GetGIFDetails
    GetGIFDetails -->|Handles 422 Validation Error| ValidationError
    GetGIFDetails -->|Handles 401 Unauthenticated Error| UnauthenticatedError
    GetGIFDetails -->|Handles 404 Not Found Error| NotFoundError

    %% Error handling for SaveGIF
    SaveGIF -->|Handles 422 Validation Error| ValidationError
    SaveGIF -->|Handles 401 Unauthenticated Error| UnauthenticatedError
    SaveGIF -->|Handles 403 Unauthorized Error| UnauthorizedError
    SaveGIF -->|Handles 409 Duplicate Entry| DuplicateEntryError

    %% Errors trigger logging
    ValidationError -->|Triggers logging| System
    UnauthenticatedError -->|Triggers logging| System
    UnauthorizedError -->|Triggers logging| System
    NotFoundError -->|Triggers logging| System
    DuplicateEntryError -->|Triggers logging| System
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

These diagrams outlines the flow for user authentication and token generation:

#### **1.1 Authenticate via API - Validation Fails**

**Description:** This diagram illustrates the workflow when the login request validation fails due to invalid input data (e.g., missing email or password).

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant Middleware as LogServiceInteraction
    participant Controller as AuthController
    participant Request as LoginRequest
    participant DB as Database

    User->>API: POST /api/auth/login
    API->>Middleware: Passes request
    Middleware->>Controller: Calls AuthController@login
    Controller->>Request: Validates LoginRequest
    Request-->>Controller: ValidationException (422)
    Controller->>Middleware: Prepares Error Response
    Middleware->>DB: Logs service interaction
    Middleware->>User: Returns 422 Validation Error
```

---

#### **1.2 Authenticate via API - Authentication Succeeds**

**Description:** This diagram shows the workflow when the user provides valid credentials and successfully authenticates, receiving an access token.

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
    Controller->>ServiceInterface: Calls login with DTO
    ServiceInterface->>Service: Resolved to AuthService (via DI container)
    Service->>RepositoryInterface: Calls authenticate with DTO
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

---

#### **1.3 Authenticate via API - Authentication Fails**

**Description:** This diagram demonstrates the workflow when the user provides valid input, but authentication fails due to incorrect credentials (e.g., wrong password).

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
    participant DB as Database

    User->>API: POST /api/auth/login
    API->>Middleware: Passes request
    Middleware->>Controller: Calls AuthController@login
    Controller->>Request: Validates LoginRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to CredentialsDTO
    DTO->>Controller: Returns DTO
    Controller->>ServiceInterface: Calls login with DTO
    ServiceInterface->>Service: Resolved to AuthService (via DI container)
    Service->>RepositoryInterface: Calls authenticate with DTO
    RepositoryInterface->>Repository: Resolved to AuthRepository (via DI container)
    Repository->>DB: Queries user by email
    DB->>Repository: Returns user record
    Repository-->>Service: Returns null (user not found or password mismatch)
    Service-->>Controller: Throws AuthenticationException (401)
    Controller->>Middleware: Prepares Error Response
    Middleware->>DB: Logs service interaction
    Middleware->>User: Returns 401 Unauthorized Error
```

---

#### **1.4 Authenticate via API - Internal Server Error**

**Description:** This diagram illustrates the workflow when an unexpected error occurs during the authentication process (e.g., database connection failure).

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
    participant DB as Database

    User->>API: POST /api/auth/login
    API->>Middleware: Passes request
    Middleware->>Controller: Calls AuthController@login
    Controller->>Request: Validates LoginRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to CredentialsDTO
    DTO->>Controller: Returns DTO
    Controller->>ServiceInterface: Calls login with DTO
    ServiceInterface->>Service: Resolved to AuthService (via DI container)
    Service->>RepositoryInterface: Calls authenticate with DTO
    RepositoryInterface->>Repository: Resolved to AuthRepository (via DI container)
    Repository->>DB: Queries user by email
    DB-->>Repository: Throws DatabaseException
    Repository-->>Service: Passes exception
    Service-->>Controller: Passes exception
    Controller->>Middleware: Prepares Error Response
    Middleware->>DB: Logs service interaction
    Middleware->>User: Returns 500 Internal Server Error
```

#### **2. Search for GIFs**

These diagrams demonstrates the workflow for querying GIFs from the Giphy API:

#### **2.1 Search for GIFs - User Not Authenticated**

**Description:** This diagram illustrates the workflow when the user is not authenticated while trying to search for GIFs.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware

    User->>API: GET /api/v1/gifs?q=&limit=&offset=
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>User: Returns 401 Unauthenticated Error
```

---

#### **2.2 Search for GIFs - Validation Fails**

**Description:** This diagram shows the workflow when the request validation fails due to invalid query parameters.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware
    participant LogMiddleware as LogServiceInteraction
    participant Controller as GifController@index
    participant Request as GifFilterRequest
    participant DB as Database

    User->>API: GET /api/v1/gifs?q=&limit=&offset=
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@index
    Controller->>Request: Validates GifFilterRequest
    Request-->>Controller: ValidationException (422)
    Controller->>LogMiddleware: Prepares error response
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Error
    AuthMiddleware->>User: Returns 422 Validation Error
```

---

#### **2.3 Search for GIFs - Cache Hit**

**Description:** This diagram demonstrates the successful retrieval of GIFs from the Redis cache when the user is authenticated, and the data is already cached.

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
    participant GifListDTO
    participant Resource as GifListResource
    participant DB as Database

    User->>API: GET /api/v1/gifs?q=&limit=&offset=
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@index
    Controller->>Request: Validates GifFilterRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to GifFilterDTO
    DTO->>Controller: Returns DTO
    Controller->>ServiceInterface: Calls filterGifs with DTO
    ServiceInterface->>Service: Resolved to GifService (via DI)
    Service->>ClientInterface: Calls filterGifs with DTO
    ClientInterface->>Client: Resolved to GiphyClient (via DI)
    Client->>Redis: Checks Redis cache for key
    Redis->>Client: Returns cached GifListDTO
    Client->>Service: Returns cached GifListDTO
    Service->>Controller: Returns GifListDTO
    Controller->>Resource: Formats response with ApiResponse
    Resource->>Controller: Returns formatted JSON response
    Controller->>LogMiddleware: Response ready
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Response
    AuthMiddleware->>User: Returns JSON response with GIF list data and pagination
```

---

#### **2.4 Search for GIFs - Cache Miss and Giphy API Error**

**Description:** This diagram illustrates the workflow when the user is authenticated, the data is not in the cache, and the Giphy API returns an error.

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
    participant DB as Database

    User->>API: GET /api/v1/gifs?q=&limit=&offset=
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@index
    Controller->>Request: Validates GifFilterRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to GifFilterDTO
    DTO->>Controller: Returns DTO
    Controller->>ServiceInterface: Calls filterGifs with DTO
    ServiceInterface->>Service: Resolved to GifService (via DI)
    Service->>ClientInterface: Calls filterGifs with DTO
    ClientInterface->>Client: Resolved to GiphyClient (via DI)
    Client->>Redis: Checks Redis cache for key
    Redis-->>Client: Cache Miss
    Client->>Giphy: Queries Giphy API with filters
    Giphy-->>Client: Returns error response (e.g., 401, 403, 422, 500)
    Client-->>Service: Throws GiphyClientException
    Service-->>Controller: Passes exception
    Controller->>LogMiddleware: Prepares error response
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Error
    AuthMiddleware->>User: Returns JSON error response with appropriate code
```

---

#### **2.5 Search for GIFs - Cache Miss and Giphy API Success**

**Description:** This diagram shows the workflow when the user is authenticated, the data is not in the cache, and the Giphy API successfully returns the GIFs.

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

    User->>API: GET /api/v1/gifs?q=&limit=&offset=
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@index
    Controller->>Request: Validates GifFilterRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to GifFilterDTO
    DTO->>Controller: Returns DTO
    Controller->>ServiceInterface: Calls filterGifs with DTO
    ServiceInterface->>Service: Resolved to GifService (via DI)
    Service->>ClientInterface: Calls filterGifs with DTO
    ClientInterface->>Client: Resolved to GiphyClient (via DI)
    Client->>Redis: Checks Redis cache for key
    Redis-->>Client: Cache Miss
    Client->>Giphy: Queries Giphy API with filters
    Giphy->>Client: Returns raw GIF data
    Client->>Redis: Stores GIF data in cache
    Client->>GifListDTO: Constructs GifListDTO
    GifListDTO->>GifClientDTO: Maps each GIF to GifClientDTO
    GifListDTO->>PaginationDTO: Builds PaginationDTO for pagination details
    GifClientDTO->>GifListDTO: Returns processed GIF DTOs
    PaginationDTO->>GifListDTO: Returns pagination DTO
    GifListDTO->>Client: Returns structured DTO list
    Client->>Service: Returns structured DTO list
    Service->>Controller: Returns GifListDTO
    Controller->>Resource: Formats response with ApiResponse
    Resource->>Controller: Returns formatted JSON response
    Controller->>LogMiddleware: Response ready
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Response
    AuthMiddleware->>User: Returns JSON response with GIF list data and pagination
```

#### **3. Search GIF By ID**

These diagrams demonstrates the workflow for get an specific GIF from the Giphy API:

#### **3.1 Search GIF By ID - User Not Authenticated**

**Description:** This diagram illustrates the workflow when the user is not authenticated while trying to retrieve a GIF by ID.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware

    User->>API: GET /api/v1/gifs/{id}
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>User: Returns 401 Unauthenticated Error
```

---

#### **3.2 Search GIF By ID - Cache Hit**

**Description:** This diagram demonstrates the successful retrieval of a GIF from the Redis cache when the user is authenticated, and the GIF data is already cached.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware
    participant LogMiddleware as LogServiceInteraction
    participant Controller as GifController@show
    participant ServiceInterface as GifServiceInterface
    participant Service as GifService
    participant ClientInterface as GifClientInterface
    participant Client as GiphyClient
    participant Redis as Redis Cache
    participant GifClientDTO
    participant Resource as GifResource
    participant DB as Database

    User->>API: GET /api/v1/gifs/{id}
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@show
    Controller->>ServiceInterface: Calls getGifById with ID
    ServiceInterface->>Service: Resolved to GifService (via DI)
    Service->>ClientInterface: Calls getGifById with ID
    ClientInterface->>Client: Resolved to GiphyClient (via DI)
    Client->>Redis: Checks Redis cache for key
    Redis->>Client: Returns cached GifClientDTO
    Client->>Service: Returns GifClientDTO
    Service->>Controller: Returns GifClientDTO
    Controller->>Resource: Formats response with ApiResponse
    Resource->>Controller: Returns formatted JSON response
    Controller->>LogMiddleware: Response ready
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Response
    AuthMiddleware->>User: Returns JSON response with GIF data
```

---

#### **3.3 Search GIF By ID - Cache Miss and Giphy API Success**

**Description:** This diagram shows the workflow when the user is authenticated, the GIF data is not in the cache, and the Giphy API successfully returns the GIF data.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware
    participant LogMiddleware as LogServiceInteraction
    participant Controller as GifController@show
    participant ServiceInterface as GifServiceInterface
    participant Service as GifService
    participant ClientInterface as GifClientInterface
    participant Client as GiphyClient
    participant Redis as Redis Cache
    participant Giphy as Giphy API
    participant GifClientDTO
    participant Resource as GifResource
    participant DB as Database

    User->>API: GET /api/v1/gifs/{id}
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@show
    Controller->>ServiceInterface: Calls getGifById with ID
    ServiceInterface->>Service: Resolved to GifService (via DI)
    Service->>ClientInterface: Calls getGifById with ID
    ClientInterface->>Client: Resolved to GiphyClient (via DI)
    Client->>Redis: Checks Redis cache for key
    Redis-->>Client: Cache Miss
    Client->>Giphy: Queries Giphy API with ID
    Giphy->>Client: Returns GIF data
    Client->>Redis: Stores GIF data in cache
    Client->>GifClientDTO: Constructs GifClientDTO
    GifClientDTO->>Client: Returns GifClientDTO
    Client->>Service: Returns GifClientDTO
    Service->>Controller: Returns GifClientDTO
    Controller->>Resource: Formats response with ApiResponse
    Resource->>Controller: Returns formatted JSON response
    Controller->>LogMiddleware: Response ready
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Response
    AuthMiddleware->>User: Returns JSON response with GIF data
```

---

#### **3.4 Search GIF By ID - Cache Miss and Giphy API Error**

**Description:** This diagram illustrates the workflow when the user is authenticated, the GIF data is not in the cache, and the Giphy API returns an error.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware
    participant LogMiddleware as LogServiceInteraction
    participant Controller as GifController@show
    participant ServiceInterface as GifServiceInterface
    participant Service as GifService
    participant ClientInterface as GifClientInterface
    participant Client as GiphyClient
    participant Redis as Redis Cache
    participant Giphy as Giphy API
    participant DB as Database

    User->>API: GET /api/v1/gifs/{id}
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@show
    Controller->>ServiceInterface: Calls getGifById with ID
    ServiceInterface->>Service: Resolved to GifService (via DI)
    Service->>ClientInterface: Calls getGifById with ID
    ClientInterface->>Client: Resolved to GiphyClient (via DI)
    Client->>Redis: Checks Redis cache for key
    Redis-->>Client: Cache Miss
    Client->>Giphy: Queries Giphy API with ID
    Giphy-->>Client: Returns error response (e.g., 401, 403, 422, 500)
    Client-->>Service: Throws GiphyClientException
    Service-->>Controller: Passes exception
    Controller->>LogMiddleware: Prepares error response
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Error
    AuthMiddleware->>User: Returns JSON error response with appropriate code
```

#### **4. Save Favorite GIF**

These diagrams demonstrates the workflow for saving favorite gif:

#### **4.1 Save Favorite GIF - Successful Case**

**Description:** This diagram demonstrates the successful workflow when a user saves a favorite GIF successfully.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware
    participant LogMiddleware as LogServiceInteraction
    participant Controller as GifController@store
    participant Request as SaveFavoriteGifRequest
    participant DTO as FavoriteGifDTO
    participant Policy as FavoriteGifPolicy@save
    participant ServiceInterface as GifServiceInterface
    participant Service as GifService
    participant RepositoryInterface as GifRepositoryInterface
    participant Repository as GifRepository
    participant DB as Database

    User->>API: POST /api/v1/gifs
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@store
    Controller->>Request: Validates SaveFavoriteGifRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to FavoriteGifDTO
    DTO->>Controller: Returns DTO
    Controller->>Policy: Validates User Authorization
    Policy->>Controller: User Authorized
    Controller->>ServiceInterface: Calls saveFavoriteGif with DTO
    ServiceInterface->>Service: Resolved to GifService (via DI container)
    Service->>RepositoryInterface: Calls saveFavoriteGif with DTO
    RepositoryInterface->>Repository: Resolved to GifRepository (via DI container)
    Repository->>DB: Save favorite GIF in DB
    DB->>Repository: Favorite GIF Saved
    Repository->>Service: Returns success
    Service->>Controller: Returns success
    Controller->>LogMiddleware: Response ready
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Response
    AuthMiddleware->>User: Returns 201 Created
```

---

#### **4.2 Save Favorite GIF - User Not Authenticated**

**Description:** This diagram shows the workflow when the user is not authenticated.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware

    User->>API: POST /api/v1/gifs
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>User: Returns 401 Unauthenticated Error
```

---

#### **4.3 Save Favorite GIF - Validation Fails**

**Description:** This diagram illustrates the workflow when the request validation fails.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware
    participant LogMiddleware as LogServiceInteraction
    participant Controller as GifController@store
    participant Request as SaveFavoriteGifRequest

    User->>API: POST /api/v1/gifs
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@store
    Controller->>Request: Validates SaveFavoriteGifRequest
    Request-->>Controller: ValidationException (422)
    Controller->>LogMiddleware: Prepares error response
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Error
    AuthMiddleware->>User: Returns 422 Validation Error
```

---

#### **4.4 Save Favorite GIF - Authorization Fails**

**Description:** This diagram shows the workflow when user authorization fails.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware
    participant LogMiddleware as LogServiceInteraction
    participant Controller as GifController@store
    participant Request as SaveFavoriteGifRequest
    participant DTO as FavoriteGifDTO
    participant Policy as FavoriteGifPolicy@save

    User->>API: POST /api/v1/gifs
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@store
    Controller->>Request: Validates SaveFavoriteGifRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to FavoriteGifDTO
    DTO->>Controller: Returns DTO
    Controller->>Policy: Validates User Authorization
    Policy-->>Controller: AuthorizationException (403)
    Controller->>LogMiddleware: Prepares error response
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Error
    AuthMiddleware->>User: Returns 403 Authorization Error
```

---

#### **4.5 Save Favorite GIF - Duplicate Favorite GIF Error**

**Description:** This diagram demonstrates the workflow when the user tries to save a GIF that's already a favorite, resulting in a duplicate error.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware
    participant LogMiddleware as LogServiceInteraction
    participant Controller as GifController@store
    participant Request as SaveFavoriteGifRequest
    participant DTO as FavoriteGifDTO
    participant Policy as FavoriteGifPolicy@save
    participant ServiceInterface as GifServiceInterface
    participant Service as GifService
    participant RepositoryInterface as GifRepositoryInterface
    participant Repository as GifRepository
    participant DB as Database

    User->>API: POST /api/v1/gifs
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@store
    Controller->>Request: Validates SaveFavoriteGifRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to FavoriteGifDTO
    DTO->>Controller: Returns DTO
    Controller->>Policy: Validates User Authorization
    Policy->>Controller: User Authorized
    Controller->>ServiceInterface: Calls saveFavoriteGif with DTO
    ServiceInterface->>Service: Resolved to GifService (via DI container)
    Service->>RepositoryInterface: Calls saveFavoriteGif with DTO
    RepositoryInterface->>Repository: Resolved to GifRepository (via DI container)
    Repository->>DB: Save favorite GIF in DB
    DB-->>Repository: Throws QueryException (Code 23000)
    Repository-->>Service: Throws DuplicateFavoriteGifException
    Service-->>Controller: Passes Exception
    Controller->>LogMiddleware: Prepares error response
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Error
    AuthMiddleware->>User: Returns 409 Conflict Error
```

---

#### **4.6 Save Favorite GIF - General Database Error**

**Description:** This diagram illustrates the workflow when a general database error occurs during the save operation.

```mermaid
sequenceDiagram
    participant User
    participant API as Laravel API
    participant AuthMiddleware as Auth:api Middleware
    participant LogMiddleware as LogServiceInteraction
    participant Controller as GifController@store
    participant Request as SaveFavoriteGifRequest
    participant DTO as FavoriteGifDTO
    participant Policy as FavoriteGifPolicy@save
    participant ServiceInterface as GifServiceInterface
    participant Service as GifService
    participant RepositoryInterface as GifRepositoryInterface
    participant Repository as GifRepository
    participant DB as Database

    User->>API: POST /api/v1/gifs
    API->>AuthMiddleware: Validates token
    AuthMiddleware->>LogMiddleware: Passes validated request
    LogMiddleware->>Controller: Calls GifController@store
    Controller->>Request: Validates SaveFavoriteGifRequest
    Request->>Controller: Returns validated request
    Controller->>DTO: Converts request to FavoriteGifDTO
    DTO->>Controller: Returns DTO
    Controller->>Policy: Validates User Authorization
    Policy->>Controller: User Authorized
    Controller->>ServiceInterface: Calls saveFavoriteGif with DTO
    ServiceInterface->>Service: Resolved to GifService (via DI container)
    Service->>RepositoryInterface: Calls saveFavoriteGif with DTO
    RepositoryInterface->>Repository: Resolved to GifRepository (via DI container)
    Repository->>DB: Save favorite GIF in DB
    DB-->>Repository: Throws QueryException (Other Exception)
    Repository-->>Service: Passes Exception
    Service-->>Controller: Passes Exception
    Controller->>LogMiddleware: Prepares error response
    LogMiddleware->>DB: Logs service interaction
    LogMiddleware->>AuthMiddleware: Passes Error
    AuthMiddleware->>User: Returns 500 Internal Server Error
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
