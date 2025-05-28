# Chinook API

A RESTful API built with PHP for accessing and managing the Chinook music database. Easily manage artists, albums, tracks, playlists, and more via HTTP requests.

---

## Table of Contents

- [Getting Started](#getting-started)
- [Prerequisites](#prerequisites)
- [Installation](#installation)
- [API Endpoints](#api-endpoints)
    - [Artists](#artists)
    - [Albums](#albums)
    - [Tracks](#tracks)
    - [Playlists](#playlists)
    - [Genres](#genres)
    - [Media Types](#media-types)
- [Testing the API](#testing-the-api)
- [Database Structure](#database-structure)
- [Deployment](#deployment)
- [License](#license)
- [Environment Configuration](#environment-configuration)

---

## Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL database
- Web server (Apache, Nginx)

### Installation

1. **Clone the repository:**
     ```bash
     git clone https://github.com/yourusername/chinook-api.git
     cd chinook-api
     ```

2. **Import the database:**
     - Import the Chinook database schema into your MySQL server. [Chinook Database](https://github.com/lerocha/chinook-database)

3. **Set up URL rewriting:**
     - For Apache, the included `.htaccess` enables URL rewriting:
         ```
         RewriteEngine On
         RewriteCond %{REQUEST_FILENAME} !-f
         RewriteCond %{REQUEST_FILENAME} !-d
         RewriteRule ^(.*)$ index.php [QSA,L]
         ```

4. **Test the installation:**
     - Access the API in your browser or API client:
         ```
         http://localhost/chinook-api/artists
         ```

---

## API Endpoints

### Artists

| Method | Endpoint                  | Description                |
|--------|--------------------------|----------------------------|
| GET    | `/artists`               | Get all artists            |
| GET    | `/artists?s={search}`    | Search artists by name     |
| GET    | `/artists/{id}`          | Get artist by ID           |
| GET    | `/artists/{id}/albums`   | Get all albums by artist   |
| POST   | `/artists`               | Create a new artist        |
| PUT    | `/artists/{id}`          | Update an artist           |
| DELETE | `/artists/{id}`          | Delete an artist           |

**Example POST/PUT body:**
```json
{
    "name": "New Artist Name"
}
```

---

### Albums

| Method | Endpoint                    | Description                |
|--------|-----------------------------|----------------------------|
| GET    | `/albums`                   | Get all albums             |
| GET    | `/albums?s={search}`        | Search albums by title     |
| GET    | `/albums/{id}`              | Get album by ID            |
| GET    | `/albums/{id}/tracks`       | Get all tracks in album    |
| POST   | `/albums`                   | Create a new album         |
| PUT    | `/albums/{id}`              | Update an album            |
| DELETE | `/albums/{id}`              | Delete an album            |

**Example POST/PUT body:**
```json
{
    "title": "New Album Title",
    "artist_id": 1
}
```

---

### Tracks

| Method | Endpoint                          | Description                |
|--------|-----------------------------------|----------------------------|
| GET    | `/tracks`                         | Get all tracks             |
| GET    | `/tracks?s={search}`              | Search tracks by name      |
| GET    | `/tracks?composer={name}`         | Get tracks by composer     |
| GET    | `/tracks/{id}`                    | Get track by ID            |
| POST   | `/tracks`                         | Create a new track         |
| PUT    | `/tracks/{id}`                    | Update a track             |
| DELETE | `/tracks/{id}`                    | Delete a track             |

**Example POST/PUT body:**
```json
{
    "name": "New Track Name",
    "album_id": 1,
    "media_type_id": 1,
    "genre_id": 1,
    "composer": "Composer Name",
    "milliseconds": 200000,
    "bytes": 5000000,
    "unit_price": 0.99
}
```

---

### Playlists

| Method | Endpoint                                 | Description                      |
|--------|------------------------------------------|----------------------------------|
| GET    | `/playlists`                             | Get all playlists                |
| GET    | `/playlists?s={search}`                  | Search playlists by name         |
| GET    | `/playlists/{id}`                        | Get playlist by ID with tracks   |
| POST   | `/playlists`                             | Create a new playlist            |
| POST   | `/playlists/{id}/tracks`                 | Add track to playlist            |
| DELETE | `/playlists/{id}/tracks/{trackId}`       | Remove track from playlist       |
| DELETE | `/playlists/{id}`                        | Delete a playlist                |

**Example for creating playlist:**
```json
{
    "name": "New Playlist Name"
}
```
**Example for adding track to playlist:**
```json
{
    "track_id": 1
}
```

---

### Genres

| Method | Endpoint      | Description        |
|--------|--------------|--------------------|
| GET    | `/genres`    | Get all genres     |

---

### Media Types

| Method | Endpoint         | Description         |
|--------|-----------------|---------------------|
| GET    | `/mediatypes`   | Get all media types |

---

## Testing the API

### Built-in Test Page

- Navigate to `http://yourdomain.com/chinook-api/test.html`
- Select HTTP method and endpoint
- Fill in parameters and request body as needed
- Click "Send Request" to view the response

### Swagger UI

- Visit `http://yourdomain.com/chinook-api/swagger.html` for interactive API docs

### Postman

- Use Postman or similar tools to test endpoints

---

## Database Structure

The Chinook database models the iTunes database and includes:

- **Artist**: Music artists
- **Album**: Albums by artists
- **Track**: Individual songs
- **Playlist**: Custom playlists
- **Genre**: Music genres
- **MediaType**: Media formats

---

## Deployment

### Local Development

- Place the project in your `htdocs` folder (XAMPP)
- Configure database connection
- Access via `http://localhost/chinook-api`

### Production

- **Shared Hosting**: Upload files, create MySQL DB, update config, access via your domain
- **InfinityFree**: Create account, upload files, create DB, update config, access via subdomain

**Testing Deployment:**

- Use `test.html` to verify endpoints
- Check database connection and URL rewriting

---

## License

This project is based on the Chinook Database, licensed under the MIT License.
