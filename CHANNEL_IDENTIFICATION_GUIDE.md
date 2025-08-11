# Channel Identification & Update Guide

## Problem Ko Samjhna
Jab company mein multiple employees ne apne channels banaye hote hain, toh update karte waqt ye confusion hoti hai ke konsa specific channel update karna hai.

## Solution - Channel Ko Identify Karne Ke Tareeke

### 1. **Sabse Channels List Karna**
```http
GET /api/channels
```

**Response Example:**
```json
{
    "message": "Channels retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Marketing Team",
            "type": "private",
            "company_id": 1,
            "created_by": 2,
            "created_at": "2025-08-11T18:30:00.000000Z",
            "company": {
                "id": 1,
                "name": "Tech Solutions",
                "user_id": 1
            },
            "creator": {
                "id": 2,
                "first_name": "Ahmed",
                "last_name": "Ali",
                "email": "ahmed@company.com"
            }
        },
        {
            "id": 2,
            "name": "Development Team",
            "type": "public",
            "company_id": 1,
            "created_by": 3,
            "created_at": "2025-08-11T19:00:00.000000Z",
            "company": {
                "id": 1,
                "name": "Tech Solutions",
                "user_id": 1
            },
            "creator": {
                "id": 3,
                "first_name": "Sara",
                "last_name": "Khan",
                "email": "sara@company.com"
            }
        }
    ]
}
```

### 2. **Creator Ke Hisab Se Channels Dekhna**
```http
GET /api/channels-by-creator
```

**Response Example:**
```json
{
    "message": "Channels grouped by creator retrieved successfully",
    "data": [
        {
            "creator": {
                "id": 2,
                "name": "Ahmed Ali",
                "email": "ahmed@company.com"
            },
            "channels": [
                {
                    "id": 1,
                    "name": "Marketing Team",
                    "type": "private",
                    "company": "Tech Solutions",
                    "created_at": "2025-08-11T18:30:00.000000Z",
                    "can_manage": true
                },
                {
                    "id": 5,
                    "name": "Sales Team",
                    "type": "public",
                    "company": "Tech Solutions",
                    "created_at": "2025-08-11T20:00:00.000000Z",
                    "can_manage": true
                }
            ]
        },
        {
            "creator": {
                "id": 3,
                "name": "Sara Khan",
                "email": "sara@company.com"
            },
            "channels": [
                {
                    "id": 2,
                    "name": "Development Team",
                    "type": "public",
                    "company": "Tech Solutions",
                    "created_at": "2025-08-11T19:00:00.000000Z",
                    "can_manage": false
                }
            ]
        }
    ]
}
```

### 3. **Channel Name Se Search Karna**
```http
GET /api/search-channels?search=Marketing
```

**Response Example:**
```json
{
    "message": "Search results retrieved successfully",
    "data": [
        {
            "id": 1,
            "name": "Marketing Team",
            "type": "private",
            "company": "Tech Solutions",
            "creator": {
                "id": 2,
                "name": "Ahmed Ali",
                "email": "ahmed@company.com"
            },
            "created_at": "2025-08-11T18:30:00.000000Z",
            "can_manage": true
        }
    ],
    "search_term": "Marketing"
}
```

### 4. **Apne Banaye Gaye Channels Dekhna**
```http
GET /api/my-channels
```

## Channel Update Karne Ka Proper Tareeka

### Step 1: Channel ID Pata Karna
Upar ke methods mein se koi bhi use karke specific channel ka **ID** pata karna.

### Step 2: Channel Update Karna
Channel ID use karke update karna:

```http
PATCH /api/channels/{channel_id}
```

**Example:**
```javascript
// Channel ID 1 ko update karna
fetch('/api/channels/1', {
    method: 'PATCH',
    headers: {
        'Content-Type': 'application/json',
        'Authorization': 'Bearer ' + token
    },
    body: JSON.stringify({
        name: 'Updated Marketing Team',
        type: 'public'
    })
});
```

## Practical Examples

### Example 1: Multiple Channels Mein Se Specific Channel Update Karna

```javascript
// Step 1: Saare channels dekhna
const response = await fetch('/api/channels', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
const channelsData = await response.json();

// Step 2: Manual ya programmatically specific channel choose karna
const targetChannel = channelsData.data.find(channel => 
    channel.name === 'Marketing Team' && 
    channel.creator.email === 'ahmed@company.com'
);

// Step 3: Us channel ko update karna
if (targetChannel) {
    const updateResponse = await fetch(`/api/channels/${targetChannel.id}`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        },
        body: JSON.stringify({
            name: 'Updated Marketing Team',
            type: 'private'
        })
    });
}
```

### Example 2: Creator Ke Naam Se Channel Update Karna

```javascript
// Creator ke channels dekhna
const creatorChannels = await fetch('/api/channels-by-creator', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
const creatorData = await creatorChannels.json();

// Ahmed Ali ke channels mein se "Marketing Team" update karna
const ahmedData = creatorData.data.find(creator => 
    creator.creator.email === 'ahmed@company.com'
);

if (ahmedData) {
    const marketingChannel = ahmedData.channels.find(channel => 
        channel.name === 'Marketing Team'
    );
    
    if (marketingChannel && marketingChannel.can_manage) {
        await fetch(`/api/channels/${marketingChannel.id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                name: 'Digital Marketing Team'
            })
        });
    }
}
```

### Example 3: Search Se Channel Update Karna

```javascript
// Channel search karna
const searchResponse = await fetch('/api/search-channels?search=Development', {
    headers: {
        'Authorization': 'Bearer ' + token
    }
});
const searchData = await searchResponse.json();

// Search results mein se first channel update karna (agar manage kar sakte hain)
if (searchData.data.length > 0) {
    const channel = searchData.data[0];
    
    if (channel.can_manage) {
        await fetch(`/api/channels/${channel.id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token
            },
            body: JSON.stringify({
                type: 'private'
            })
        });
    }
}
```

## Key Points

1. **Channel ID** har channel ka unique identifier hai
2. **can_manage** field batata hai ke aap us channel ko manage kar sakte hain ya nahi
3. **creator** information se pata chalta hai ke channel kisne banaya hai
4. Company mein multiple channels hone par **ID** use karke specific channel identify karna
5. Search functionality se aasaani se channel find kar sakte hain

## Available API Endpoints

```http
GET /api/channels                    # Saare channels list
GET /api/channels/{id}               # Specific channel details
GET /api/my-channels                 # Apne banaye gaye channels
GET /api/channels-by-creator         # Creator wise grouped channels
GET /api/search-channels?search=term # Channel search by name

PATCH /api/channels/{id}             # Channel update (owner/creator only)
DELETE /api/channels/{id}            # Channel delete (owner/creator only)
PUT /api/channels/{id}/toggle-type   # Public/Private toggle (owner/creator only)
```

Is tarah se aap easily identify kar sakte hain ke konsa channel update karna hai, company mein kitne bhi channels hों!
