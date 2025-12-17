#!/bin/bash

# News Aggregation Service - API Test Script
# This script tests all API endpoints

BASE_URL="http://localhost"
API_URL="${BASE_URL}/api/news"

echo "=========================================="
echo "News Aggregation Service - API Tests"
echo "=========================================="
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Test 1: Ingest articles
echo -e "${BLUE}Test 1: Ingesting articles from GNews API${NC}"
echo "POST ${API_URL}/ingest"
echo ""

INGEST_RESPONSE=$(curl -s -X POST "${API_URL}/ingest" \
  -H "Content-Type: application/json" \
  -d '{
    "keyword": "technology",
    "language": "en",
    "max": 10
  }')

echo "$INGEST_RESPONSE" | jq '.'
echo ""

# Check if ingestion was successful
if echo "$INGEST_RESPONSE" | jq -e '.stats.saved' > /dev/null 2>&1; then
    SAVED_COUNT=$(echo "$INGEST_RESPONSE" | jq -r '.stats.saved')
    echo -e "${GREEN}✓ Successfully ingested ${SAVED_COUNT} articles${NC}"
else
    echo -e "${RED}✗ Failed to ingest articles${NC}"
fi
echo ""
echo "=========================================="
echo ""

# Test 2: Get all articles (paginated)
echo -e "${BLUE}Test 2: Get all articles (first page)${NC}"
echo "GET ${API_URL}?page=1&limit=5"
echo ""

GET_ALL_RESPONSE=$(curl -s "${API_URL}?page=1&limit=5")
echo "$GET_ALL_RESPONSE" | jq '.'
echo ""

if echo "$GET_ALL_RESPONSE" | jq -e '.data' > /dev/null 2>&1; then
    TOTAL=$(echo "$GET_ALL_RESPONSE" | jq -r '.meta.total')
    echo -e "${GREEN}✓ Successfully retrieved articles. Total: ${TOTAL}${NC}"
else
    echo -e "${RED}✗ Failed to retrieve articles${NC}"
fi
echo ""
echo "=========================================="
echo ""

# Test 3: Search articles by keyword
echo -e "${BLUE}Test 3: Search articles by keyword${NC}"
echo "GET ${API_URL}?keyword=technology&limit=3"
echo ""

SEARCH_RESPONSE=$(curl -s "${API_URL}?keyword=technology&limit=3")
echo "$SEARCH_RESPONSE" | jq '.'
echo ""

if echo "$SEARCH_RESPONSE" | jq -e '.data' > /dev/null 2>&1; then
    FOUND=$(echo "$SEARCH_RESPONSE" | jq -r '.meta.total')
    echo -e "${GREEN}✓ Found ${FOUND} articles matching 'technology'${NC}"
else
    echo -e "${RED}✗ Search failed${NC}"
fi
echo ""
echo "=========================================="
echo ""

# Test 4: Get single article
echo -e "${BLUE}Test 4: Get single article by ID${NC}"
echo "GET ${API_URL}/1"
echo ""

SINGLE_RESPONSE=$(curl -s "${API_URL}/1")
echo "$SINGLE_RESPONSE" | jq '.'
echo ""

if echo "$SINGLE_RESPONSE" | jq -e '.id' > /dev/null 2>&1; then
    TITLE=$(echo "$SINGLE_RESPONSE" | jq -r '.title')
    echo -e "${GREEN}✓ Successfully retrieved article: ${TITLE}${NC}"
else
    echo -e "${RED}✗ Failed to retrieve article${NC}"
fi
echo ""
echo "=========================================="
echo ""

# Test 5: Date range filter
echo -e "${BLUE}Test 5: Filter by date range${NC}"
FROM_DATE="2024-12-01T00:00:00Z"
TO_DATE="2024-12-16T23:59:59Z"
echo "GET ${API_URL}?from=${FROM_DATE}&to=${TO_DATE}&limit=3"
echo ""

DATE_RESPONSE=$(curl -s "${API_URL}?from=${FROM_DATE}&to=${TO_DATE}&limit=3")
echo "$DATE_RESPONSE" | jq '.'
echo ""

if echo "$DATE_RESPONSE" | jq -e '.data' > /dev/null 2>&1; then
    FOUND=$(echo "$DATE_RESPONSE" | jq -r '.meta.total')
    echo -e "${GREEN}✓ Found ${FOUND} articles in date range${NC}"
else
    echo -e "${RED}✗ Date filter failed${NC}"
fi
echo ""
echo "=========================================="
echo ""

# Test 6: Pagination
echo -e "${BLUE}Test 6: Test pagination${NC}"
echo "GET ${API_URL}?page=1&limit=2"
echo ""

PAGE1_RESPONSE=$(curl -s "${API_URL}?page=1&limit=2")
echo "$PAGE1_RESPONSE" | jq '.'
echo ""

if echo "$PAGE1_RESPONSE" | jq -e '.meta.total_pages' > /dev/null 2>&1; then
    TOTAL_PAGES=$(echo "$PAGE1_RESPONSE" | jq -r '.meta.total_pages')
    echo -e "${GREEN}✓ Pagination working. Total pages: ${TOTAL_PAGES}${NC}"
else
    echo -e "${RED}✗ Pagination test failed${NC}"
fi
echo ""
echo "=========================================="
echo ""

# Test 7: Invalid endpoint (404)
echo -e "${BLUE}Test 7: Test error handling (404)${NC}"
echo "GET ${API_URL}/99999"
echo ""

ERROR_RESPONSE=$(curl -s -w "\nHTTP_STATUS:%{http_code}" "${API_URL}/99999")
HTTP_STATUS=$(echo "$ERROR_RESPONSE" | grep "HTTP_STATUS:" | cut -d':' -f2)
RESPONSE_BODY=$(echo "$ERROR_RESPONSE" | sed '$d')

echo "$RESPONSE_BODY" | jq '.'
echo ""

if [ "$HTTP_STATUS" = "404" ]; then
    echo -e "${GREEN}✓ Error handling working correctly (404)${NC}"
else
    echo -e "${RED}✗ Expected 404, got ${HTTP_STATUS}${NC}"
fi
echo ""
echo "=========================================="
echo ""

# Summary
echo -e "${BLUE}Test Summary${NC}"
echo "All API endpoints have been tested!"
echo ""
echo "Available endpoints:"
echo "  POST ${API_URL}/ingest - Ingest articles from GNews"
echo "  GET  ${API_URL}        - List articles with filters"
echo "  GET  ${API_URL}/{id}   - Get single article"
echo ""
echo "=========================================="

