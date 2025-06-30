.PHONY: reset-db help

help: ## Show this help message
	@echo "Available commands:"
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

reset-db: ## Reset database: delete, create, migrate, and seed
	@echo "🗑️  Deleting existing database..."
	docker exec -it fruits-and-vegetables rm -f database/app.db
	@echo "📊 Creating new database..."
	docker exec -it fruits-and-vegetables php bin/console doctrine:database:create
	@echo "🔄 Running migrations..."
	docker exec -it fruits-and-vegetables php bin/console doctrine:migrations:migrate --no-interaction
	@echo "🌱 Seeding database with data..."
	docker exec -it fruits-and-vegetables php bin/console app:json-processor
	@echo "✅ Database reset complete!" 