# Company Employee Invitation System API

## Overview
This API provides a complete employee invitation system where company admins can invite employees, manage them, and automatically handle account creation for new users.

## Features
- Send invitations to both existing and non-existing users
- Auto-create accounts with generated passwords for new users
- Email invitations with login details
- Accept invitations and join companies
- Remove employees (auto-delete accounts for auto-created users)
- Get company data with employee information

## Authentication
All protected endpoints require an `Authorization` header with a valid token obtained from the login endpoint.

## Endpoints

### 1. Send Company Invitation
**POST** `/api/company/invite`

**Headers:**
```
Authorization: {your_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "email": "employee@example.com"
}
```

**Response:**
```json
{
  "message": "Invitation sent successfully",
  "user_type": "new" // or "existing"
}
```

**Description:**
- If user exists: Sends invitation to existing user
- If user doesn't exist: Creates account with generated password and sends invitation with login details

---

### 2. Accept Invitation
**GET** `/api/company-invitation/accept/{token}`

**Response:**
```json
{
  "message": "Invitation accepted successfully. You are now an employee of Company Name",
  "company_name": "Company Name",
  "login_details": {
    "email": "employee@example.com",
    "password": "generated_password",
    "note": "Please change your password after logging in"
  }
}
```

**Description:**
- Accepts the invitation and adds user as company employee
- Returns login details if password was auto-generated

---

### 3. Remove Employee
**DELETE** `/api/company/employee`

**Headers:**
```
Authorization: {your_token}
Content-Type: application/json
```

**Request Body:**
```json
{
  "employee_id": 123
}
```

**Response:**
```json
{
  "message": "Employee removed and account deleted successfully"
}
// or
{
  "message": "Employee removed from company successfully"
}
```

**Description:**
- Removes employee from company
- If employee was auto-created, deletes their account completely
- If employee had existing account, just removes from company

---

### 4. Get Company Data
**GET** `/api/company/data`

**Headers:**
```
Authorization: {your_token}
```

**Response:**
```json
{
  "company": {
    "id": 1,
    "name": "Company Name",
    "owner": {
      "id": 1,
      "name": "Owner Name",
      "email": "owner@example.com"
    },
    "employee_count": 5,
    "employees": [
      {
        "id": 2,
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "is_auto_created": false,
        "created_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "pending_invitations_count": 2,
    "pending_invitations": [
      {
        "id": 1,
        "email": "pending@example.com",
        "created_at": "2024-01-01T00:00:00.000000Z"
      }
    ]
  }
}
```

**Description:**
- Returns complete company information
- Lists all employees with their details
- Shows pending invitations
- Indicates which employees were auto-created

## Email Templates

The system sends different email templates based on user type:

### For New Users (Auto-created accounts):
- Includes login credentials (email + generated password)
- Instructions to change password after first login
- Accept invitation button

### For Existing Users:
- Simple invitation to join company
- Accept invitation button
- No login credentials (they already have accounts)

## Security Features

1. **Auto-generated passwords**: Strong random passwords for new users
2. **Account cleanup**: Auto-created accounts are deleted when user is removed
3. **Token-based authentication**: Secure API access
4. **Email verification**: Invited users are auto-verified
5. **Duplicate prevention**: Cannot invite same email twice

## Database Schema Changes

### Users Table
- Added `is_auto_created` field to track auto-created accounts

### Company Invitations Table
- Added `generated_password` field to store passwords for new users
- Added `user_id` field to link invitation to created user

## Error Handling

The API returns appropriate HTTP status codes:
- `200`: Success
- `400`: Bad Request (validation errors, duplicates)
- `401`: Unauthorized (invalid/missing token)
- `403`: Forbidden (no company access)
- `404`: Not Found (invalid invitation token)
- `500`: Server Error

## Testing

You can test the APIs using tools like Postman or curl:

```bash
# Send invitation
curl -X POST http://your-domain/api/company/invite \
  -H "Authorization: your_token" \
  -H "Content-Type: application/json" \
  -d '{"email": "newemployee@example.com"}'

# Get company data
curl -X GET http://your-domain/api/company/data \
  -H "Authorization: your_token"

# Remove employee
curl -X DELETE http://your-domain/api/company/employee \
  -H "Authorization: your_token" \
  -H "Content-Type: application/json" \
  -d '{"employee_id": 123}'
```
