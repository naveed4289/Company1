# Chat System API Documentation

## Overview
This API provides a comprehensive chat system for companies with support for both public and private channels. Public channels are accessible to all company members, while private channels require explicit membership.

## Authentication
All endpoints require authentication using the `AuthToken` middleware. Include the user token in the request headers.

## Endpoints

### 1. Get All Channels
**GET** `/api/channels`

Get all channels that the authenticated user has access to (public channels from their company + private channels they're members of).

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "General Discussion",
      "type": "public",
      "company_id": 1,
      "created_by": 1,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z",
      "creator": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe"
      }
    },
    {
      "id": 2,
      "name": "Private Team Chat",
      "type": "private",
      "company_id": 1,
      "created_by": 1,
      "created_at": "2025-01-01T00:00:00.000000Z",
      "updated_at": "2025-01-01T00:00:00.000000Z",
      "creator": {
        "id": 1,
        "first_name": "John",
        "last_name": "Doe"
      },
      "members": [
        {
          "id": 1,
          "first_name": "John",
          "last_name": "Doe"
        },
        {
          "id": 2,
          "first_name": "Jane",
          "last_name": "Smith"
        }
      ]
    }
  ]
}
```

### 2. Create Channel
**POST** `/api/channels`

Create a new channel in the company.

**Request Body:**
```json
{
  "name": "New Channel",
  "type": "public", // or "private"
  "company_id": 1
}
```

**Response:**
```json
{
  "message": "Channel created successfully",
  "data": {
    "id": 3,
    "name": "New Channel",
    "type": "public",
    "company_id": 1,
    "created_by": 1,
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z",
    "creator": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe"
    },
    "company": {
      "id": 1,
      "name": "Company Name"
    }
  }
}
```

### 3. Update Channel
**PATCH** `/api/channels/{id}`

Update channel details. Only channel creator or company owner can update.

**Request Body:**
```json
{
  "name": "Updated Channel Name",
  "type": "private"
}
```

### 4. Delete Channel
**DELETE** `/api/channels/{id}`

Delete a channel. Only channel creator or company owner can delete.

**Response:**
```json
{
  "message": "Channel 'Channel Name' deleted successfully"
}
```

### 5. Get Channel Messages
**GET** `/api/channels/{channelId}/messages`

Get messages from a specific channel with pagination.

**Query Parameters:**
- `per_page` (optional): Number of messages per page (default: 50)
- `page` (optional): Page number

**Response:**
```json
{
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "channel_id": 1,
        "user_id": 1,
        "content": "Hello everyone!",
        "created_at": "2025-01-01T00:00:00.000000Z",
        "updated_at": "2025-01-01T00:00:00.000000Z",
        "user": {
          "id": 1,
          "first_name": "John",
          "last_name": "Doe",
          "email": "john@example.com"
        }
      }
    ],
    "first_page_url": "http://localhost/api/channels/1/messages?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost/api/channels/1/messages?page=1",
    "links": [...],
    "next_page_url": null,
    "path": "http://localhost/api/channels/1/messages",
    "per_page": 50,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

### 6. Send Message
**POST** `/api/channels/{channelId}/messages`

Send a message to a channel.

**Request Body:**
```json
{
  "content": "Hello, this is my message!"
}
```

**Response:**
```json
{
  "message": "Message sent successfully",
  "data": {
    "id": 2,
    "channel_id": 1,
    "user_id": 1,
    "content": "Hello, this is my message!",
    "created_at": "2025-01-01T00:00:00.000000Z",
    "updated_at": "2025-01-01T00:00:00.000000Z",
    "user": {
      "id": 1,
      "first_name": "John",
      "last_name": "Doe",
      "email": "john@example.com"
    }
  }
}
```

### 7. Delete Message
**DELETE** `/api/messages/{messageId}`

Delete a message. Only the message sender, channel creator, or company owner can delete messages.

**Response:**
```json
{
  "message": "Message deleted successfully"
}
```

### 8. Add Member to Private Channel
**POST** `/api/channels/{channelId}/members`

Add a member to a private channel. Only channel creator or company owner can add members.

**Request Body:**
```json
{
  "user_id": 2
}
```

**Response:**
```json
{
  "message": "Member added to channel successfully"
}
```

### 9. Remove Member from Private Channel
**DELETE** `/api/channels/{channelId}/members`

Remove a member from a private channel. Only channel creator or company owner can remove members.

**Request Body:**
```json
{
  "user_id": 2
}
```

**Response:**
```json
{
  "message": "Member removed from channel successfully"
}
```

## Permission Logic

### Public Channels
- **Access**: All company members (owner + employees) can access
- **Read Messages**: All company members can read
- **Send Messages**: All company members can send
- **Delete Messages**: Message sender, channel creator, or company owner

### Private Channels
- **Access**: Only explicit members can access
- **Read Messages**: Only channel members can read
- **Send Messages**: Only channel members can send
- **Delete Messages**: Message sender, channel creator, or company owner
- **Add/Remove Members**: Only channel creator or company owner

### Channel Management
- **Create**: Any company member can create channels
- **Update**: Only channel creator or company owner
- **Delete**: Only channel creator or company owner

## Error Responses

### 403 Forbidden
```json
{
  "message": "You do not have access to this channel"
}
```

### 404 Not Found
```json
{
  "message": "Channel not found"
}
```

### 422 Validation Error
```json
{
  "message": "Validation failed",
  "errors": {
    "content": ["The content field is required."]
  }
}
```

### 500 Server Error
```json
{
  "message": "Error sending message",
  "error": "Detailed error message"
}
```

## Usage Examples

### Creating a Public Channel
```bash
curl -X POST http://localhost/api/channels \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "General Discussion",
    "type": "public",
    "company_id": 1
  }'
```

### Creating a Private Channel
```bash
curl -X POST http://localhost/api/channels \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "Private Team",
    "type": "private",
    "company_id": 1
  }'
```

### Sending a Message
```bash
curl -X POST http://localhost/api/channels/1/messages \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "content": "Hello everyone!"
  }'
```

### Getting Channel Messages
```bash
curl -X GET "http://localhost/api/channels/1/messages?per_page=20&page=1" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Notes

1. **Automatic Membership**: When creating a private channel, the creator is automatically added as a member.

2. **Company Association**: Users can only create channels and access channels within their associated company.

3. **Message Limits**: Messages are limited to 1000 characters.

4. **Pagination**: Message history is paginated with 50 messages per page by default.

5. **Real-time Updates**: This API provides the foundation for real-time chat. For live updates, you would need to implement WebSocket connections or polling.

6. **Database Relationships**: The system maintains proper foreign key relationships with cascade deletes to ensure data integrity.
