# Ledger Hash Contract (crypto-ledger)

**Feature**: LedgerHashChain  
**Status**: Draft  

**Methods**:
- `hash(string $canonicalPayload, ?string $previousHash = null): string`
  - Computes SHA-256 of `$previousHash . '|' . $canonicalPayload`
  - `previousHash` null => empty string for genesis

- `verifyChain(array $entries): bool`
  - Validates hash chain integrity across entries
  - Returns `true` if all hashes match; `false` if tampered

**Constraints**:
- Use `hash('sha256', ...)` function
- Treat `null` previousHash as empty string
- Detect tampering when any entry's hash doesn't match

**Acceptance**:
- Deterministic hash output
- Chain verification passes
- Tampering breaks chain
- Genesis record handled correctly