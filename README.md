# Perigeebar (Laravel) — Deploy & Run Guide (Docker)


## 1) Установка Docker и Docker Compose


### 1.2 Установить Docker (официально)

```bash
sudo apt-get update
sudo apt-get install -y ca-certificates curl gnupg

sudo install -m 0755 -d /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
| sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt-get update
sudo apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
```
### 1.2 Проверка

```bash
docker version
docker compose version
```
---

## 2) Клонирование проекта

### 2.1 Создать директорию и выдать права

```bash
mkdir -p /var/www/perigee-lounge.example/public_html
cd /var/www/perigee-lounge.example/public_html
```
### 2.2 Клонировать репозиторий
```bash
git clone <repository-url> .
```
---

## 3) Подготовка `.env`

### 3.1 Скопировать `.env`

```bash
cp .env.example .env
```

### 3.2 Рекомендуемые значения `.env` для Docker

Открой `.env` и поставь (минимум):

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=perigee
DB_USERNAME=perigee
DB_PASSWORD=perigee_pass //поменять тут и в докере
```
---

## 5) Первый запуск (с нуля)

### 5.1 Поднять контейнеры и собрать образы

```bash
docker compose up -d --build
```

### 5.1 Опустить контейнеры

```bash
docker compose down -v
```

### 5.2 Собрать фронт (Vite)

```bash
docker compose run --rm node sh -lc "npm ci --include=dev && npm run build"

```

### 5.3 Проверить статус контейнеров

```bash
docker compose ps
```

### 5.4 Открыть сайт

* Если серверный IP:
  `http://SERVER_IP:8080`

---



## 7) Команды для очистки кешей (Laravel)

### Полная чистка кешей (рекомендуется после правок `.env`, конфигов, роутов)

```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

### Сборка кешей (ускорение)

```bash
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```
---

## 8) Логи Laravel (500 ошибки, mail log, etc.)

### 8.1 Смотреть логи внутри контейнера

```bash
docker logs perigee_app
```

### 8.2 Смотреть логи внутри контейнера в реальном времени

```bash
docker logs -f perigee_app

```

---

## 9) Команды для пересборки / обновления проекта

### 9.1 Обновить код из Git

```bash
git pull
```

### 9.2 Пересобрать образы и перезапустить

```bash
docker compose down
docker compose up -d --build
```

### 9.3 Переустановить PHP зависимости (composer)

```bash
docker compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader
```

### 9.4 Обновить фронт (Vite)

```bash
docker compose run --rm node sh -lc "npm run build"
```

### 9.5 Прогнать миграции (если появились новые)

```bash
docker compose exec app php artisan migrate --force
```

### 9.6 Очистить кеши после деплоя

```bash
docker compose exec app php artisan config:clear
docker compose exec app php artisan cache:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear
```

---

## 10) Полный RESET (всё с нуля, включая базу)

⚠️ Это удалит базу данных и все данные (volumes).

```bash
docker compose down -v
docker compose up -d --build
docker compose run --rm node
```

---

## 11) Быстрые проверки и диагностика

### 11.1 Проверить nginx конфиг

```bash
docker compose exec nginx nginx -t
```

### 11.2 Проверить, что php-fpm слушает 9000

```bash
docker compose exec app sh -lc "ss -lntp | grep 9000 || true"
```

### 11.3 Проверить доступность сайта с сервера

```bash
curl -I http://127.0.0.1:8080
```

---

## 12) Команды уборки Docker (если забился диск)

### Удалить неиспользуемые образы/контейнеры/сети

```bash
docker system prune -af
```

### Удалить неиспользуемые volumes (ОСТОРОЖНО — может удалить нужные)

```bash
docker volume prune -f
```

---

## 13) “Daily” команда обслуживания (типовой деплой чеклист)

```bash
cd /var/www/perigee-lounge.example/public_html \
&& git pull \
&& docker compose up -d --build \
&& docker compose run --rm node \
&& docker compose exec app composer install --no-interaction --prefer-dist --optimize-autoloader \
&& docker compose exec app php artisan migrate --force \
&& docker compose exec app php artisan config:clear \
&& docker compose exec app php artisan cache:clear \
&& docker compose exec app php artisan view:clear \
&& docker compose exec app php artisan route:clear
```

---

## 14) Где меняется порт

Если хочешь другой порт (например 80), поменяй в `docker-compose.yml` у nginx:

```yaml
ports:
  - "80:80"
```
