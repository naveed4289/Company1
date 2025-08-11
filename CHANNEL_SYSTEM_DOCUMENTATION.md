# Channel Management System Documentation

## Overview
This documentation describes the channel management system that allows company employees and owners to create and manage channels with proper authorization controls.

## Features
- **Channel Creation**: Both company owners and employees can create channels
- **Channel Types**: Public and Private channels
- **Authorization**: Only channel creators and company owners can manage channels
- **Company Isolation**: Users can only access channels from their own companies
- **Full CRUD Operations**: Create, Read, Update, Delete channels

## Database Schema

### Channels Table
```sql
CREATE TABLE channels (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    type ENUM('public', 'private') DEFAULT 'public',
    company_id BIGINT NOT NULL,
    created_by BIGINT NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX (company_id, type),
    INDEX (created_by)
);
```

## API Endpoints

### Authentication Required
All endpoints require authentication using the `AuthToken` middleware.

### Channel Endpoints

#### 1. Get All Channels
```http
GET /api/channels
```
**Description**: Get all channels for companies where the user is owner or employee

**Query Parameters**:
- `type` (optional): Filter by channel type (`public` or `private`)
- `company_id` (optional): Filter by specific company

**Response**:
```json
{
    "message": "Channels retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "General Discussion",
            "type": "public",
            "company_id": 1,
            "created_by": 2,
            "created_at": "2025-08-11T17:31:31.000000Z",
            "updated_at": "2025-08-11T17:31:31.000000Z",
            "company": {
                "id": 1,
                "name": "Acme Corp",
                "user_id": 1
            },
            "creator": {
                "id": 2,
                "first_name": "John",
                "last_name": "Doe",
                "email": "john@example.com"
            }
        }
    ]
}
```

#### 2. Create Channel
```http
POST /api/channels
```
**Description**: Create a new channel

**Request Body**:
```json
{
    "name": "Development Team",
    "type": "private",
    "company_id": 1
}
```

**Validation Rules**:
- `name`: required, string, max:255
- `type`: required, enum('public', 'private')
- `company_id`: required, exists in companies table

**Response**: Same as single channel format

#### 3. Get Single Channel
```http
GET /api/channels/{id}
```
**Description**: Get details of a specific channel

**Authorization**: User must belong to the same company as the channel

**Response**: Single channel object

#### 4. Update Channel
```http
PATCH /api/channels/{id}
```
**Description**: Update channel details

**Authorization**: Only channel creator or company owner can update

**Request Body** (all fields optional):
```json
{
    "name": "Updated Channel Name",
    "type": "private"
}
```

#### 5. Delete Channel
```http
DELETE /api/channels/{id}
```
**Description**: Delete a channel

**Authorization**: Only channel creator or company owner can delete

**Response**:
```json
{
    "message": "Channel 'Development Team' deleted successfully"
}
```

#### 6. Toggle Channel Type
```http
PUT /api/channels/{id}/toggle-type
```
**Description**: Toggle channel between public and private

**Authorization**: Only channel creator or company owner can toggle

**Response**:
```json
{
    "message": "Channel type changed to private successfully",
    "data": { /* channel object */ }
}
```

#### 7. Get My Channels
```http
GET /api/my-channels
```
**Description**: Get all channels created by the authenticated user

**Response**: Array of channels created by the user

## Authorization Rules

### Channel Access Levels

1. **View Access**: Users can view channels if they belong to the same company
   - Company owner can view all company channels
   - Employees can view all channels in companies they're part of

2. **Management Access**: Users can manage channels if they are:
   - Channel creator (the user who created the channel)
   - Company owner (owner of the company the channel belongs to)

3. **Company Isolation**: Users cannot access channels from other companies

### Middleware Protection

#### ChannelOwnerMiddleware
- Applied to update, delete, and toggle-type operations
- Verifies user can manage the specific channel
- Automatically loads channel data for controller use
- Returns 403 Forbidden for unauthorized access

## Models and Relationships

### Channel Model
```php
// Relationships
channel->company()      // belongsTo Company
channel->creator()      // belongsTo User (created_by)

// Helper Methods
channel->canBeManaged($user)         // Check if user can manage channel
channel->isUserInSameCompany($user)  // Check if user belongs to same company

// Scopes
Channel::public()              // Filter public channels
Channel::private()             // Filter private channels
Channel::byCompany($companyId) // Filter by company
```

### Updated Model Relationships
```php
// Company Model
company->channels()     // hasMany Channel

// User Model  
user->createdChannels() // hasMany Channel (created_by)
```

## Request Validation

### CreateChannelRequest
- Validates channel creation data
- Auto-assigns company_id if user has a company
- Ensures user has access to specified company

### UpdateChannelRequest
- Validates partial updates (all fields optional)
- Uses 'sometimes' validation for conditional rules

## Security Features

1. **Authentication**: All endpoints require valid authentication token
2. **Authorization**: Multi-level authorization checking
3. **Company Isolation**: Users can only access their company's channels
4. **Ownership Verification**: Management operations require ownership
5. **Input Validation**: Comprehensive request validation
6. **SQL Injection Protection**: Uses Eloquent ORM with parameter binding

## Error Handling

### Common Error Responses

#### 401 Unauthorized
```json
{
    "message": "Unauthorized"
}
```

#### 403 Forbidden
```json
{
    "message": "Forbidden: You can only manage channels you created or channels in your company"
}
```

#### 404 Not Found
```json
{
    "message": "Channel not found"
}
```

#### 422 Validation Error
```json
{
    "message": "The name field is required.",
    "errors": {
        "name": ["Channel name is required"]
    }
}
```

## Usage Examples

### Creating a Channel
```javascript
// Employee creating a channel
fetch('/api/channels', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        name: 'Marketing Team',
        type: 'private'
        // company_id auto-assigned if user has company
    })
});
```

### Getting Company Channels
```javascript
// Get all public channels in company
fetch('/api/channels?type=public&company_id=1', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
```

### Managing Channel (Owner/Creator Only)
```javascript
// Toggle channel privacy
fetch('/api/channels/1/toggle-type', {
    method: 'PUT',
    headers: {
        'Authorization': 'Bearer ' + token
    }
});

// Update channel (specify exact channel ID)
fetch('/api/channels/1', {
    method: 'PATCH',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        name: 'Updated Channel Name',
        type: 'public'
    })
});
```

## Implementation Notes

1. **Reusable Code**: All components are modular and reusable
2. **Proper File Organization**: Models, Controllers, Requests, and Middleware in appropriate directories
3. **Laravel Conventions**: Follows Laravel naming and structure conventions
4. **Database Optimization**: Proper indexing for performance
5. **Comprehensive Validation**: Input validation at multiple levels
6. **Error Handling**: Graceful error handling with informative messages

## Installation Steps

1. **Migration**: Run `php artisan migrate` to create the channels table
2. **Middleware**: Middleware is automatically registered in `bootstrap/app.php`
3. **Routes**: API routes are defined in `routes/api.php`
4. **Ready to Use**: The system is ready for use after migration

This channel management system provides a robust, secure, and scalable solution for company-based channel management with proper authorization controls.
