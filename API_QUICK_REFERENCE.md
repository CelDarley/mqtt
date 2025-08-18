# MQTT API Quick Reference

## Base URL
`http://10.102.0.21:8000/api`

## Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/mqtt/topics` | Create new topic |
| `GET` | `/mqtt/topics` | List all topics |
| `GET` | `/mqtt/topics/{id}` | Get specific topic |
| `PATCH` | `/mqtt/topics/{id}/deactivate` | Deactivate topic |
| `POST` | `/mqtt/send-message` | Send message to topic |

## Quick Examples

### Create Topic
```bash
curl -X POST http://10.102.0.21:8000/api/mqtt/topics \
  -H "Content-Type: application/json" \
  -d '{"name": "device/control", "description": "Device control"}'
```

### List Topics
```bash
curl -X GET http://10.102.0.21:8000/api/mqtt/topics
```

### Send Message
```bash
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "device/control", "mensagem": "ligar"}'
```

### Deactivate Topic
```bash
curl -X PATCH http://10.102.0.21:8000/api/mqtt/topics/1/deactivate
```

## Response Format

### Success Response
```json
{
    "success": true,
    "message": "Operation successful",
    "data": { ... }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description"
}
```

## Common Commands

### Test API Connection
```bash
curl -X GET http://10.102.0.21:8000/api/mqtt/topics
```

### Test Message Sending
```bash
curl -X POST http://10.102.0.21:8000/api/mqtt/send-message \
  -H "Content-Type: application/json" \
  -d '{"topico": "pmmg/1bpm/doc1", "mensagem": "test"}'
```

### Check Server Status
```bash
curl -I http://10.102.0.21:8000/api/mqtt/topics
```

## MQTT Configuration

- **Broker:** `10.102.0.21:1883`
- **QoS:** 0 (at most once)
- **Client ID:** `laravel_mqtt_client`

## Notes

- Topics must be created before sending messages
- Only active topics can receive messages
- Messages are sent with QoS 0
- No authentication required for basic operations 