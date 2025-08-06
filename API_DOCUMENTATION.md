# MQTT API Documentation

## Overview

This API provides a RESTful interface for managing MQTT topics and sending messages to MQTT brokers. It's built with Laravel and integrates with Mosquitto MQTT broker.

**Base URL:** `http://10.102.0.21:8000/api`

## Authentication

Currently, the API doesn't require authentication for basic operations. All endpoints are publicly accessible.

## Endpoints

### 1. Create Topic

Creates a new MQTT topic in the system.

**Endpoint:** `POST /mqtt/topics`

**Request Body:**
```json
{
    "name": "string",
    "description": "string (optional)"
}
```

**Parameters:**
- `name` (required): Topic name (must be unique)
- `description` (optional): Topic description

**Response (201 Created):**
```json
{
    "success": true,
    "message": "Tópico criado com sucesso",
    "data": {
        "id": 1,
        "name": "pmmg/1bpm/doc1",
        "description": "Test topic",
        "is_active": true,
        "created_at": "2025-08-05T21:30:00.000000Z",
        "updated_at": "2025-08-05T21:30:00.000000Z"
    }
}
```

**Example:**
```bash
curl -X POST http://10.102.0.21:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{
    "name": "pmmg/1bpm/doc1",
    "description": "Test topic for device control"
  }'
```

---

### 2. List All Topics

Retrieves all active topics from the system.

**Endpoint:** `GET /mqtt/topics`

**Response (200 OK):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "pmmg/1bpm/doc1",
            "description": "Test topic",
            "is_active": true,
            "created_at": "2025-08-05T21:30:00.000000Z",
            "updated_at": "2025-08-05T21:30:00.000000Z"
        }
    ]
}
```

**Example:**
```bash
curl -X GET http://10.102.0.21:8000/api/mqtt/topics
```

---

### 3. Get Specific Topic

Retrieves a specific topic by ID.

**Endpoint:** `GET /mqtt/topics/{id}`

**Parameters:**
- `id` (path): Topic ID

**Response (200 OK):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "pmmg/1bpm/doc1",
        "description": "Test topic",
        "is_active": true,
        "created_at": "2025-08-05T21:30:00.000000Z",
        "updated_at": "2025-08-05T21:30:00.000000Z"
    }
}
```

**Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Tópico não encontrado"
}
```

**Example:**
```bash
curl -X GET http://10.102.0.21:8000/api/mqtt/topics/1
```

---

### 4. Deactivate Topic

Deactivates a specific topic (soft delete).

**Endpoint:** `PATCH /mqtt/topics/{id}/deactivate`

**Parameters:**
- `id` (path): Topic ID

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Tópico desativado com sucesso"
}
```

**Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Tópico não encontrado"
}
```

**Example:**
```bash
curl -X PATCH http://10.102.0.21:8000/api/mqtt/topics/1/deactivate
```

---

### 5. Send Message

Sends a message to a specific MQTT topic.

**Endpoint:** `POST /mqtt/send-message`

**Request Body:**
```json
{
    "topico": "string",
    "mensagem": "string"
}
```

**Parameters:**
- `topico` (required): Topic name to send message to
- `mensagem` (required): Message content

**Response (200 OK):**
```json
{
    "success": true,
    "message": "Mensagem enviada com sucesso para o tópico: pmmg/1bpm/doc1",
    "data": {
        "topic": "pmmg/1bpm/doc1",
        "message": "ligar"
    }
}
```

**Response (404 Not Found):**
```json
{
    "success": false,
    "message": "Tópico não existe ou está inativo"
}
```

**Response (500 Internal Server Error):**
```json
{
    "success": false,
    "message": "Erro ao enviar mensagem MQTT: [error details]"
}
```

**Example:**
```bash
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{
    "topico": "pmmg/1bpm/doc1",
    "mensagem": "ligar"
  }'
```

## Error Responses

All endpoints may return the following error responses:

### 400 Bad Request
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field_name": ["Validation error message"]
    }
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### 500 Internal Server Error
```json
{
    "success": false,
    "message": "Internal server error"
}
```

## Data Models

### Topic Model

```php
{
    "id": "integer",
    "name": "string",
    "description": "string|null",
    "is_active": "boolean",
    "created_at": "datetime",
    "updated_at": "datetime"
}
```

## MQTT Configuration

The API connects to the MQTT broker with the following configuration:

- **Host:** `localhost` (configurable via `MQTT_HOST` env variable)
- **Port:** `1883` (configurable via `MQTT_PORT` env variable)
- **Client ID:** `laravel_mqtt_client` (configurable via `MQTT_CLIENT_ID` env variable)

## Usage Examples

### Complete Workflow

1. **Create a topic:**
```bash
curl -X POST http://10.102.0.21:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{
    "name": "device/led/control",
    "description": "LED control for IoT device"
  }'
```

2. **List all topics:**
```bash
curl -X GET http://10.102.0.21:8000/api/mqtt/topics
```

3. **Send a message:**
```bash
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{
    "topico": "device/led/control",
    "mensagem": "ligar"
  }'
```

4. **Deactivate a topic:**
```bash
curl -X PATCH http://10.102.0.21:8000/api/mqtt/topics/1/deactivate
```

## Testing

### Test Script

You can use the provided test script to verify MQTT message delivery:

```bash
python3 teste_mqtt.py
```

### Manual Testing

1. Start the Laravel server:
```bash
./start_server.sh
```

2. Send a test message:
```bash
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "pmmg/1bpm/doc1", "mensagem": "test"}'
```

## Notes

- All topics must be created before sending messages to them
- Only active topics can receive messages
- The API automatically connects to the MQTT broker for each message send operation
- Messages are sent with QoS level 0 (at most once delivery)
- The API is designed for simple IoT device control scenarios

## Support

For issues or questions, check the Laravel logs:
```bash
tail -f storage/logs/laravel.log
``` 