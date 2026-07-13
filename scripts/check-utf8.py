from pathlib import Path

TEXT_EXTENSIONS = {
    ".php", ".css", ".scss", ".js", ".json", ".html", ".xml",
    ".po", ".pot", ".md", ".yml", ".yaml", ".txt",
}
SUSPICIOUS = tuple(
    bytes.fromhex(value).decode("utf-8")
    for value in (
        "c383", "c382", "c3a2e282ace284a2", "c3a2e282acc593",
        "c3a2e282ac", "c3afc2bbc2bf", "efbfbd",
    )
)
IGNORED_PARTS = {".git", "node_modules", "vendor"}
errors = []

for path in Path(".").rglob("*"):
    if not path.is_file() or path.suffix.lower() not in TEXT_EXTENSIONS:
        continue
    if any(part in IGNORED_PARTS for part in path.parts):
        continue
    raw = path.read_bytes()
    if raw.startswith(b"\xef\xbb\xbf"):
        errors.append(f"{path}: UTF-8 BOM is not allowed")
    try:
        text = raw.decode("utf-8", errors="strict")
    except UnicodeDecodeError as exc:
        errors.append(f"{path}: invalid UTF-8: {exc}")
        continue
    for pattern in SUSPICIOUS:
        if pattern in text:
            errors.append(f"{path}: suspicious mojibake pattern {pattern!r}")

if errors:
    print("\n".join(errors))
    raise SystemExit(1)

print("UTF-8 validation passed")
