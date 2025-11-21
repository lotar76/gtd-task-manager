# Примеры использования API

## Базовые примеры с cURL

### 1. Регистрация пользователя

```bash
curl -X POST https://api.local.test/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "SecurePassword123!",
    "password_confirmation": "SecurePassword123!"
  }'
```

**Ответ:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2024-01-01T12:00:00.000000Z",
      "updated_at": "2024-01-01T12:00:00.000000Z"
    },
    "token": "1|abcdefghijklmnopqrstuvwxyz1234567890"
  }
}
```

### 2. Вход пользователя

```bash
curl -X POST https://api.local.test/api/v1/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "SecurePassword123!"
  }'
```

### 3. Получение профиля

```bash
TOKEN="1|abcdefghijklmnopqrstuvwxyz1234567890"

curl -X GET https://api.local.test/api/v1/me \
  -H "Authorization: Bearer $TOKEN"
```

**Ответ:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "roles": ["user"],
    "permissions": [
      "files.upload",
      "files.view",
      "files.download"
    ]
  }
}
```

### 4. Загрузка файла

```bash
TOKEN="1|abcdefghijklmnopqrstuvwxyz1234567890"

curl -X POST https://api.local.test/api/v1/files/upload \
  -H "Authorization: Bearer $TOKEN" \
  -F "file=@/path/to/your/image.jpg" \
  -F "directory=user-uploads"
```

**Ответ:**
```json
{
  "success": true,
  "message": "File uploaded successfully",
  "data": {
    "path": "user-uploads/550e8400-e29b-41d4-a716-446655440000.jpg",
    "url": "https://your-bucket.s3.amazonaws.com/user-uploads/550e8400-e29b-41d4-a716-446655440000.jpg",
    "filename": "550e8400-e29b-41d4-a716-446655440000.jpg",
    "original_name": "image.jpg",
    "mime_type": "image/jpeg",
    "size": 204800
  }
}
```

### 5. Получение информации о файле

```bash
TOKEN="1|abcdefghijklmnopqrstuvwxyz1234567890"
FILE_PATH="user-uploads/550e8400-e29b-41d4-a716-446655440000.jpg"

curl -X GET "https://api.local.test/api/v1/files/show?path=$FILE_PATH" \
  -H "Authorization: Bearer $TOKEN"
```

### 6. Получение временной ссылки для скачивания

```bash
TOKEN="1|abcdefghijklmnopqrstuvwxyz1234567890"
FILE_PATH="user-uploads/550e8400-e29b-41d4-a716-446655440000.jpg"

curl -X GET "https://api.local.test/api/v1/files/download?path=$FILE_PATH&minutes=30" \
  -H "Authorization: Bearer $TOKEN"
```

**Ответ:**
```json
{
  "success": true,
  "message": "Success",
  "data": {
    "download_url": "https://your-bucket.s3.amazonaws.com/user-uploads/550e8400...?X-Amz-Expires=1800",
    "expires_in_minutes": 30
  }
}
```

### 7. Удаление файла

```bash
TOKEN="1|abcdefghijklmnopqrstuvwxyz1234567890"

curl -X DELETE https://api.local.test/api/v1/files/delete \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "path": "user-uploads/550e8400-e29b-41d4-a716-446655440000.jpg"
  }'
```

### 8. Выход

```bash
TOKEN="1|abcdefghijklmnopqrstuvwxyz1234567890"

curl -X POST https://api.local.test/api/v1/logout \
  -H "Authorization: Bearer $TOKEN"
```

## Примеры с JavaScript (fetch)

### Регистрация

```javascript
const register = async () => {
  const response = await fetch('https://api.local.test/api/v1/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      name: 'John Doe',
      email: 'john@example.com',
      password: 'SecurePassword123!',
      password_confirmation: 'SecurePassword123!',
    }),
  });

  const data = await response.json();
  console.log(data);
  
  // Сохранение токена
  localStorage.setItem('token', data.data.token);
};
```

### Вход

```javascript
const login = async (email, password) => {
  const response = await fetch('https://api.local.test/api/v1/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });

  const data = await response.json();
  
  if (data.success) {
    localStorage.setItem('token', data.data.token);
    return data.data;
  } else {
    throw new Error(data.message);
  }
};
```

### Загрузка файла

```javascript
const uploadFile = async (file, directory = 'uploads') => {
  const token = localStorage.getItem('token');
  const formData = new FormData();
  formData.append('file', file);
  formData.append('directory', directory);

  const response = await fetch('https://api.local.test/api/v1/files/upload', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
    },
    body: formData,
  });

  const data = await response.json();
  return data;
};

// Использование
const fileInput = document.querySelector('input[type="file"]');
fileInput.addEventListener('change', async (e) => {
  const file = e.target.files[0];
  const result = await uploadFile(file, 'user-uploads');
  console.log('File uploaded:', result.data.url);
});
```

### Получение профиля с обработкой ошибок

```javascript
const getProfile = async () => {
  const token = localStorage.getItem('token');

  try {
    const response = await fetch('https://api.local.test/api/v1/me', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
    });

    if (!response.ok) {
      if (response.status === 401) {
        // Токен недействителен, перенаправить на страницу входа
        localStorage.removeItem('token');
        window.location.href = '/login';
        return;
      }
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data.data;
  } catch (error) {
    console.error('Error fetching profile:', error);
    throw error;
  }
};
```

## Примеры с Axios

### Настройка Axios

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'https://api.local.test/api/v1',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor для добавления токена
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Interceptor для обработки ошибок
api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
    }
    return Promise.reject(error.response?.data || error.message);
  }
);

export default api;
```

### Использование

```javascript
import api from './api';

// Регистрация
const register = async (userData) => {
  const response = await api.post('/register', userData);
  localStorage.setItem('token', response.data.token);
  return response.data;
};

// Вход
const login = async (email, password) => {
  const response = await api.post('/login', { email, password });
  localStorage.setItem('token', response.data.token);
  return response.data;
};

// Загрузка файла
const uploadFile = async (file, directory) => {
  const formData = new FormData();
  formData.append('file', file);
  formData.append('directory', directory);

  const response = await api.post('/files/upload', formData, {
    headers: {
      'Content-Type': 'multipart/form-data',
    },
  });
  return response.data;
};

// Получение профиля
const getProfile = async () => {
  const response = await api.get('/me');
  return response.data;
};

// Выход
const logout = async () => {
  await api.post('/logout');
  localStorage.removeItem('token');
};
```

## Примеры с Python (requests)

```python
import requests

BASE_URL = 'https://api.local.test/api/v1'

class APIClient:
    def __init__(self):
        self.token = None
        self.session = requests.Session()

    def register(self, name, email, password):
        response = self.session.post(
            f'{BASE_URL}/register',
            json={
                'name': name,
                'email': email,
                'password': password,
                'password_confirmation': password,
            }
        )
        data = response.json()
        if data['success']:
            self.token = data['data']['token']
            self.session.headers.update({
                'Authorization': f'Bearer {self.token}'
            })
        return data

    def login(self, email, password):
        response = self.session.post(
            f'{BASE_URL}/login',
            json={'email': email, 'password': password}
        )
        data = response.json()
        if data['success']:
            self.token = data['data']['token']
            self.session.headers.update({
                'Authorization': f'Bearer {self.token}'
            })
        return data

    def upload_file(self, file_path, directory='uploads'):
        with open(file_path, 'rb') as f:
            files = {'file': f}
            data = {'directory': directory}
            response = self.session.post(
                f'{BASE_URL}/files/upload',
                files=files,
                data=data
            )
        return response.json()

    def get_profile(self):
        response = self.session.get(f'{BASE_URL}/me')
        return response.json()

    def logout(self):
        response = self.session.post(f'{BASE_URL}/logout')
        self.token = None
        return response.json()

# Использование
client = APIClient()

# Регистрация
result = client.register('John Doe', 'john@example.com', 'password123')
print(result)

# Загрузка файла
upload_result = client.upload_file('/path/to/file.jpg', 'user-uploads')
print(upload_result)

# Получение профиля
profile = client.get_profile()
print(profile)
```

## Обработка ошибок

### Типичные коды ошибок

- **400** - Bad Request (неверные параметры)
- **401** - Unauthorized (не авторизован)
- **403** - Forbidden (нет прав доступа)
- **404** - Not Found (ресурс не найден)
- **422** - Validation Error (ошибка валидации)
- **429** - Too Many Requests (превышен лимит запросов)
- **500** - Internal Server Error (внутренняя ошибка сервера)

### Пример обработки ошибок валидации

**Запрос:**
```bash
curl -X POST https://api.local.test/api/v1/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "",
    "email": "invalid-email",
    "password": "123"
  }'
```

**Ответ (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "name": ["The name field is required."],
    "email": ["The email field must be a valid email address."],
    "password": [
      "The password field must be at least 8 characters.",
      "The password field confirmation does not match."
    ]
  }
}
```

## Rate Limiting

При превышении лимита запросов вы получите ответ **429**:

```json
{
  "success": false,
  "message": "Too Many Requests"
}
```

**Заголовки ответа:**
- `X-RateLimit-Limit` - максимальное количество запросов
- `X-RateLimit-Remaining` - оставшиеся запросы
- `Retry-After` - через сколько секунд можно повторить запрос

