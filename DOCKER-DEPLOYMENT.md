# 🐳 eDonation Docker Deployment Guide

## Cross-Platform Deployment: Mac (ARM64) → Windows (AMD64)

This guide explains how to build Docker images on your **Mac (Apple Silicon/ARM64)** that will run on a **Windows Server (Intel/AMD64)** using Docker Desktop with WSL2/Linux Containers.

---

## 🎯 The Architecture Mismatch Problem

| Environment | Architecture | Default Docker Platform |
|------------|--------------|------------------------|
| **Your Mac** (M1/M2/M3) | ARM64 | `linux/arm64` |
| **Windows Server** (Intel/AMD) | AMD64 | `linux/amd64` |

**The Problem:** If you build an image on Mac without specifying the platform, it creates a `linux/arm64` image that **will NOT run** on Windows.

**The Solution:** Force the build to target `linux/amd64` platform.

---

## 🪄 The "Magic" Build Command

### Option 1: Build & Push to Registry (Recommended for Production)

Run this on your **Mac** to build an AMD64-compatible image:

```bash
# Navigate to project directory
cd /Applications/XAMPP/xamppfiles/htdocs/edonation

# Build for AMD64 platform
docker build --platform linux/amd64 -t edonation:latest .

# Tag for your registry (Docker Hub, ACR, ECR, etc.)
docker tag edonation:latest your-registry.com/edonation:latest

# Push to registry
docker push your-registry.com/edonation:latest
```

### Option 2: Build Multi-Platform Image with Buildx

```bash
# Create a new builder (one-time setup)
docker buildx create --name multiarch --driver docker-container --use

# Build and push multi-platform image
docker buildx build \
  --platform linux/amd64 \
  -t your-registry.com/edonation:latest \
  --push \
  .
```

### Option 3: Export as Tar File (Offline Transfer)

```bash
# Build the AMD64 image
docker build --platform linux/amd64 -t edonation:latest .

# Save to tar file
docker save edonation:latest -o edonation-amd64.tar

# Compress for transfer
gzip edonation-amd64.tar
```

Then transfer `edonation-amd64.tar.gz` to Windows and load:

```powershell
# On Windows (PowerShell)
docker load -i edonation-amd64.tar.gz
```

---

## 📁 Files Created

| File | Purpose |
|------|---------|
| `Dockerfile` | Production-ready PHP 8.2 + Apache + MySQL extensions |
| `docker-compose.yml` | Production configuration (AMD64 forced) |
| `docker-compose.dev.yml` | Development configuration with phpMyAdmin |
| `.dockerignore` | Excludes unnecessary files from build |

---

## 🚀 Deployment Steps

### Step 1: Build on Mac

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/edonation

# Build for AMD64
docker build --platform linux/amd64 -t edonation:latest .
```

### Step 2: Transfer to Windows

**Option A: Via Container Registry**
```bash
# Push to Docker Hub
docker tag edonation:latest yourusername/edonation:latest
docker push yourusername/edonation:latest
```

**Option B: Via Tar File**
```bash
docker save edonation:latest -o edonation-amd64.tar
# Transfer file to Windows via SCP, USB, or cloud storage
```

### Step 3: Run on Windows Server

1. Copy `docker-compose.yml` and `database/donation.sql` to Windows
2. Create folder structure:
   ```
   C:\edonation\
   ├── docker-compose.yml
   └── database\
       └── donation.sql
   ```

3. Pull and run:
   ```powershell
   cd C:\edonation
   docker compose up -d
   ```

---

## 🔧 Environment Variables

The following environment variables can be customized in `docker-compose.yml`:

| Variable | Default | Description |
|----------|---------|-------------|
| `DB_HOST` | `db` | MySQL host (service name in Docker) |
| `DB_NAME` | `donation` | Database name |
| `DB_USER` | `edonation` | Database username |
| `DB_PASS` | `edonate@FON` | Database password |
| `APP_ENV` | `production` | Environment mode |
| `APP_DEBUG` | `false` | Enable debug mode |
| `JWT_SECRET` | - | JWT signing key |
| `LINE_TOKEN` | - | LINE Notify token |
| `GMAIL_USER` | - | Gmail SMTP user |
| `GMAIL_PASS` | - | Gmail app password |

---

## 🏗️ How `platform: linux/amd64` Works

In `docker-compose.yml`, we specify:

```yaml
services:
  app:
    platform: linux/amd64  # ← Forces AMD64 architecture
    image: edonation:latest
    ...

  db:
    platform: linux/amd64  # ← Forces AMD64 architecture
    image: mysql:8.0
    ...
```

**What this does:**
- Tells Docker to pull/use `linux/amd64` images
- On Mac, this runs via QEMU emulation (slower but compatible)
- On Windows, this runs natively (fast)

---

## 📂 Volume & Path Handling

### ✅ Windows-Compatible Paths (Recommended)

```yaml
volumes:
  # Relative paths work on BOTH Mac and Windows
  - ./database/donation.sql:/docker-entrypoint-initdb.d/01-donation.sql:ro
```

### ❌ Avoid Absolute Paths

```yaml
# DON'T DO THIS - Mac-specific path
volumes:
  - /Users/username/project/database:/data

# DON'T DO THIS - Windows-specific path  
volumes:
  - C:\Users\username\project\database:/data
```

### Named Volumes for Data Persistence

```yaml
volumes:
  mysql_data:
    driver: local

services:
  db:
    volumes:
      - mysql_data:/var/lib/mysql  # ← Persists across restarts
```

---

## 🔌 Database Connection Explained

### Inside Docker Network

```
┌─────────────────────────────────────────────────────┐
│                 Docker Network                       │
│  ┌──────────────┐        ┌──────────────┐           │
│  │     app      │───────→│      db      │           │
│  │  (PHP/Apache)│        │   (MySQL)    │           │
│  │              │        │              │           │
│  │ DB_HOST=db   │        │ Port: 3306   │           │
│  └──────────────┘        └──────────────┘           │
└─────────────────────────────────────────────────────┘
```

- `DB_HOST=db` refers to the container service name
- Docker's internal DNS resolves `db` to the MySQL container's IP
- No need for `localhost` or IP addresses

### Generated `.env` File

The container entrypoint automatically generates `/var/www/html/.env`:

```ini
DB_HOST=db          # ← Points to MySQL container
DB_NAME=donation
DB_USER=edonation
DB_PASS=edonate@FON
```

---

## 🧪 Local Development

For development with live code changes:

```bash
# Start development environment
docker compose -f docker-compose.dev.yml up -d

# Access the app
open http://localhost:8080

# Access phpMyAdmin
open http://localhost:8081

# View logs
docker compose -f docker-compose.dev.yml logs -f app

# Stop
docker compose -f docker-compose.dev.yml down
```

---

## 🔍 Troubleshooting

### Image Won't Run on Windows

**Error:** `exec format error` or similar

**Solution:** Rebuild with explicit platform:
```bash
docker build --platform linux/amd64 -t edonation:latest .
```

### Database Connection Refused

**Cause:** App container starting before DB is ready

**Solution:** Already handled with `depends_on` + `healthcheck`:
```yaml
depends_on:
  db:
    condition: service_healthy
```

### SQL Import Failed

**Check:** Ensure `donation.sql` exists at `./database/donation.sql`

**Verify on Windows:**
```powershell
dir database\donation.sql
```

### Container Logs

```bash
# View all logs
docker compose logs

# View specific service
docker compose logs app
docker compose logs db

# Follow logs
docker compose logs -f
```

---

## 📋 Quick Reference Commands

| Command | Description |
|---------|-------------|
| `docker build --platform linux/amd64 -t edonation:latest .` | Build AMD64 image |
| `docker compose up -d` | Start production stack |
| `docker compose down` | Stop all containers |
| `docker compose down -v` | Stop and remove volumes |
| `docker compose logs -f` | Follow logs |
| `docker exec -it edonation-app bash` | Shell into app container |
| `docker exec -it edonation-db mysql -u root -p` | Access MySQL CLI |

---

## ✅ Checklist Before Deployment

- [ ] Build image with `--platform linux/amd64`
- [ ] Push to registry or export as tar
- [ ] Copy `docker-compose.yml` to Windows
- [ ] Copy `database/donation.sql` to Windows
- [ ] Update secrets (JWT_SECRET, GMAIL_*, LINE_TOKEN)
- [ ] Run `docker compose up -d`
- [ ] Verify app is accessible
- [ ] Check database imported correctly
