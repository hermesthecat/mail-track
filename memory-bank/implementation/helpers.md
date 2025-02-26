# Helper Modules Implementation Details

## Environment Management (env.php)
### Purpose
Manages environment variables and configuration loading.

### Key Components
1. `loadEnv()` function
   - Reads .env file from project root
   - Parses key-value pairs
   - Loads variables into $_ENV array
   - Skips comments and empty lines
   - Auto-executes on include

2. `env()` function
   - Retrieves environment variables
   - Supports default values
   - Simple accessor pattern

### Error Handling
- Dies with message if .env file is missing
- Uses null coalescing operator for safe variable access

## Database Connection (db.php)
### Purpose
Establishes and manages database connection.

### Implementation
- Uses PDO for database abstraction
- Configurable through environment variables:
  - DB_HOST
  - DB_NAME
  - DB_USER
  - DB_PASS
- Sets PDO to throw exceptions on errors
- Single connection pattern

### Error Handling
- Catches PDOException
- Dies with user-friendly error message
- Exception mode enabled for detailed error reporting

## Telegram Integration (telegram.php)
### Purpose
Handles Telegram bot messaging functionality.

### Components
1. Configuration
   - Uses environment variables:
     - TELEGRAM_BOT_TOKEN
     - TELEGRAM_CHAT_ID
   - Defines constants for reuse

2. `sendTelegramMessage()` function
   - Sends HTML-formatted messages
   - Uses Telegram Bot API
   - Handles HTTP POST requests
   - Supports HTML parsing mode

### Error Handling
- Silent failure with error logging
- Uses error_log for failed notifications
- Suppresses file_get_contents warnings

## Geolocation Service (geolocation.php)
### Purpose
Provides IP-based geolocation functionality.

### Implementation
1. Configuration
   - Uses GEOIP_API_KEY from environment
   - Integrates with ipapi.com service

2. `getGeoLocation()` function
   - Takes IP address as input
   - Makes API request to ipapi.com
   - Returns JSON-decoded location data
   - Suppresses potential API errors

### Error Handling
- Silent failure on API errors
- Returns false on failed requests

## Common Patterns
1. Environment Configuration
   - All modules rely on env.php
   - Consistent use of environment variables
   - Configuration through .env file

2. Error Handling
   - Mix of fatal errors (env, db)
   - Silent failures with logging (telegram, geolocation)
   - User-friendly error messages

3. Code Organization
   - Single responsibility principle
   - Clear function documentation
   - Consistent error handling patterns
   - Author attribution

4. Integration Patterns
   - External service abstraction
   - Configuration through environment
   - Simple, focused APIs

## Dependencies
```mermaid
graph TD
    A[env.php] --> B[db.php]
    A --> C[telegram.php]
    A --> D[geolocation.php]
    
    E[External: MySQL] --> B
    F[External: Telegram API] --> C
    G[External: ipapi.com] --> D