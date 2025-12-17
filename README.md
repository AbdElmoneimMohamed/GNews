
## 🚀 Quick Start

```bash
# 1. Start containers
make build && make up

# 2. Setup database
make db-create && make db-migrate

# 3. sync news
make sync-all

# 4. run unit test
make test

### Code Quality
make code-quality


## 📋 Features

- ✅ **News Ingestion** from GNews API
- ✅ **Data Synchronization** (idempotent, with update detection)
- ✅ **Domain-Driven Design** architecture
- ✅ **REST API** with pagination & filters
- ✅ **CLI Commands** for automation
- ✅ **Code Quality** (PHPStan Level 8, ECS PSR-12)

## 🏗️ Architecture

src/
├── Domain/              # Pure business logic (no framework dependencies)
├── Application/         # Use cases & orchestration
└── Infrastructure/      # Framework implementations (Symfony, Doctrine, HTTP)

## please check the postman collection
