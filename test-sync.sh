#!/bin/bash

# Test News Synchronization Command
# This script demonstrates the data sync functionality

echo "=================================="
echo "News Sync Test Script"
echo "=================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${BLUE}Test 1: Basic Sync with Keyword${NC}"
echo "Command: app:news:sync --keyword=\"technology\" --max=5"
echo ""
docker compose exec php bin/console app:news:sync --keyword="technology" --max=5
echo ""
echo "---"
echo ""

echo -e "${BLUE}Test 2: Sync with Language Filter${NC}"
echo "Command: app:news:sync --keyword=\"AI\" --language=en --max=3"
echo ""
docker compose exec php bin/console app:news:sync --keyword="AI" --language=en --max=3
echo ""
echo "---"
echo ""

echo -e "${BLUE}Test 3: Idempotency Test (Same Query Again)${NC}"
echo "Running the same sync again - should show 'Skipped' for unchanged articles"
echo "Command: app:news:sync --keyword=\"AI\" --language=en --max=3"
echo ""
docker compose exec php bin/console app:news:sync --keyword="AI" --language=en --max=3
echo ""
echo "---"
echo ""

echo -e "${BLUE}Test 4: View Stored Articles${NC}"
echo "Command: doctrine:query:sql"
echo ""
docker compose exec php bin/console doctrine:query:sql \
  "SELECT id, title, language, created_at FROM news_articles LIMIT 5"
echo ""
echo "---"
echo ""

echo -e "${GREEN}✅ Sync tests completed!${NC}"
echo ""
echo -e "${YELLOW}Available Sync Commands:${NC}"
echo ""
echo "  # Via Makefile"
echo "  make sync ARGS=\"--keyword=technology\""
echo ""
echo "  # Direct command"
echo "  docker compose exec php bin/console app:news:sync --keyword=\"blockchain\" --max=10"
echo ""
echo "  # With date range"
echo "  docker compose exec php bin/console app:news:sync \\"
echo "    --keyword=\"crypto\" \\"
echo "    --from=\"2024-12-01\" \\"
echo "    --to=\"2024-12-16\" \\"
echo "    --max=20"
echo ""

