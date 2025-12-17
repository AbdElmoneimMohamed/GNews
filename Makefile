
## Docker Commands
build: ## Build Docker images
	docker compose build --pull --no-cache

up: ## Start containers
	docker compose up -d --wait

down: ## Stop containers
	docker compose down --remove-orphans

stop: ## Stop containers
	docker compose stop

start: ## Start containers
	docker compose up -d

restart: ## Restart containers
	$(MAKE) down
	$(MAKE) up

logs: ## View container logs (use: make logs, or make logs-f for follow mode)
	docker compose logs --tail=100

## Application Commands
sync: ## Sync news articles (use: make sync ARGS="--keyword=technology")
	docker compose exec php bin/console app:news:sync $(ARGS)

sync-tech: ## Sync technology news (default 10 articles)
	docker compose exec php bin/console app:news:sync --keyword="technology" --language=en --max=10

sync-ai: ## Sync AI news (default 10 articles)
	docker compose exec php bin/console app:news:sync --keyword="artificial intelligence" --language=en --max=10

sync-crypto: ## Sync cryptocurrency news (default 10 articles)
	docker compose exec php bin/console app:news:sync --keyword="cryptocurrency" --language=en --max=10

sync-business: ## Sync business news (default 10 articles)
	docker compose exec php bin/console app:news:sync --keyword="business" --language=en --max=10

sync-all: ## Sync multiple topics (tech, AI, crypto, business)
	$(make sync-tech)
	$(make sync-ai)
	$(make sync-crypto)
	$(make sync-business)

ssh: ## Access PHP container with bash (if available)
	docker compose exec php bash

console: ## Access Symfony console (use: make console CMD="list")
	docker compose exec php bin/console $(CMD)

cc: ## Clear Symfony cache
	docker compose exec php bin/console cache:clear

## Testing Commands
test: ## Run all tests
	docker compose exec php vendor/bin/phpunit

## Development Commands

install: ## Initial setup (build, up, composer install)
	$(MAKE) build
	$(MAKE) up
	$(MAKE) composer-install
	@echo "${GREEN}✓ Project installed successfully!${RESET}"
	@echo "${YELLOW}Access your application at: https://localhost${RESET}"

fresh: ## Fresh install (down, build, up, install dependencies)
	$(MAKE) down
	$(MAKE) build
	$(MAKE) up
	$(MAKE) composer-install
	@echo "${GREEN}✓ Fresh installation completed!${RESET}"

clean: ## Clean up (stop containers, remove volumes)
	docker compose down -v --remove-orphans
	@echo "${GREEN}✓ Cleanup completed!${RESET}"

reset: ## Reset everything (clean, fresh install)
	$(MAKE) clean
	$(MAKE) fresh

lint: ## Lint Twig templates
	docker compose exec php bin/console lint:twig templates/

lint-yaml: ## Lint YAML files
	docker compose exec php bin/console lint:yaml config/

lint-container: ## Lint container
	docker compose exec php bin/console lint:container

stan: ## Run PHPStan static analysis at level 8
	docker compose exec php vendor/bin/phpstan analyse --memory-limit=256M

ecs: ## Check code style with Easy Coding Standard
	docker compose exec php vendor/bin/ecs check

ecs-fix: ## Fix code style issues with Easy Coding Standard
	docker compose exec php vendor/bin/ecs check --fix

code-quality: ## Run all code quality checks (PHPStan + ECS + PHPMD)
	$(MAKE) stan
	$(MAKE) ecs


