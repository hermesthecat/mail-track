# API Implementation Documentation

## Overview
The application provides a REST-like API interface for managing campaigns, tracking emails, and retrieving statistics. All endpoints are prefixed with `?api=` and require authentication.

## Authentication
- Session-based authentication required for all endpoints
- Role-based access control (RBAC) implementation
- Minimum 'editor' role required for most operations

## Base URL
All API endpoints are relative to the application root URL.

## Endpoints

### 1. Campaign Management
Base path: `?api=campaigns`

#### GET /campaigns
Lists all campaigns with pagination support.

Parameters:
- draw (int): DataTables draw counter
- start (int): Start position
- length (int): Number of records to fetch
- search[value] (string): Search term

Response:
```json
{
    "draw": 1,
    "recordsTotal": 100,
    "recordsFiltered": 10,
    "data": [
        ["name", "description", "tracking_prefix", "stats", "creator", "actions"]
    ]
}
```

#### GET /campaigns&id={id}
Retrieves a specific campaign.

Parameters:
- id (int): Campaign ID

Response:
```json
{
    "id": 1,
    "name": "Campaign Name",
    "description": "Description",
    "tracking_prefix": "prefix",
    "total_sent": 0,
    "total_opened": 0
}
```

#### POST /campaigns
Creates a new campaign.

Required Role: editor

Body:
```json
{
    "name": "Campaign Name",
    "description": "Campaign Description"
}
```

Response:
```json
{
    "success": true,
    "id": 1
}
```

#### PUT /campaigns&id={id}
Updates an existing campaign.

Required Role: editor

Parameters:
- id (int): Campaign ID

Body:
```json
{
    "id": 1,
    "name": "Updated Name",
    "description": "Updated Description"
}
```

Response:
```json
{
    "success": true
}
```

#### DELETE /campaigns&id={id}
Deletes a campaign.

Required Role: editor

Parameters:
- id (int): Campaign ID

Response:
```json
{
    "success": true
}
```

### 2. Email Tracking
Base path: `?track={tracking_id}`

#### GET /track
Records email open event.

Parameters:
- tracking_id (string): Unique tracking identifier

Actions:
1. Records open event in email_logs
2. Updates campaign statistics
3. Stores geolocation data
4. Sends Telegram notification
5. Returns 1x1 transparent GIF

Response:
- Content-Type: image/gif
- Binary GIF image data

### 3. Statistics
Base path: `?api=stats`

#### GET /stats
Retrieves system-wide statistics.

Required Role: editor

Response:
```json
{
    "total_opens": 1000,
    "unique_ips": 500,
    "today_opens": 50,
    "countries": [
        {
            "country": "Country Name",
            "count": 100
        }
    ]
}
```

### 4. Email Logs
Base path: `?api=logs`

#### GET /logs
Retrieves email open logs with pagination.

Required Role: editor

Parameters:
- draw (int): DataTables draw counter
- start (int): Start position
- length (int): Number of records
- search[value] (string): Search term

Response:
```json
{
    "draw": 1,
    "recordsTotal": 1000,
    "recordsFiltered": 100,
    "data": [
        ["tracking_id", "campaign", "ip", "location", "user_agent", "timestamp"]
    ]
}
```

### 5. Password Management
Base path: `?api=change_password`

#### POST /change_password
Changes user password.

Body:
```json
{
    "current_password": "current",
    "new_password": "new"
}
```

Response:
```json
{
    "success": true
}
```

Error Response:
```json
{
    "error": "Error message"
}
```

## Error Handling

### Common Error Responses
```json
{
    "error": "Error message"
}
```

HTTP Status Codes:
- 200: Success
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 405: Method Not Allowed

## Security Measures

1. Authentication
   - Session-based authentication
   - Role-based access control
   - Permission checking before operations

2. Input Validation
   - Parameter validation
   - Content-Type verification
   - Data sanitization

3. Error Handling
   - Generic error messages
   - No sensitive information in errors
   - Proper error status codes

## Rate Limiting
Currently not implemented. Recommended future enhancement.

## Recommendations

1. API Security
   - Implement API rate limiting
   - Add request signing
   - Enable CORS protection
   - Add API versioning

2. Response Enhancement
   - Standardize error responses
   - Add request/response logging
   - Implement proper HTTP status codes
   - Add response caching

3. Authentication
   - Add API key support
   - Implement token-based auth
   - Add request expiration
   - Enable 2FA for sensitive operations