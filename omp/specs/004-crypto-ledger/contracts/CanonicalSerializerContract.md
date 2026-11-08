# Canonical Serializer Contract (crypto-ledger)

**Feature**: CanonicalSerializer  
**Status**: Draft  

**Methods**:
- `serialize(array $payload): string`
  - Sorts payload keys alphabetically
  - Converts numeric values to fixed-decimal strings
  - Converts date-like strings to ISO-8601 format
  - Returns compact JSON without extra whitespace

**Constraints**:
- Output must be deterministic (same input => same output)
- Numeric values must not use floating-point representation
- Dates must be normalized to ISO-8601 format

**Acceptance**:
- Identical payloads produce identical serialized output
- Numeric values are represented as fixed-decimal strings
- Dates are converted to ISO-8601 format
- JSON output is compact (no extra whitespace)