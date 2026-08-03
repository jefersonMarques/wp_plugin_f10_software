from __future__ import annotations

import hashlib
import re
import shutil
import subprocess
import sys
import zipfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
BUILD_DIRECTORY = ROOT / "build"
PACKAGE_NAME = "f10-captura-de-leads"
PACKAGE_VERSION = "1.3.9"
PACKAGE_DIRECTORY = BUILD_DIRECTORY / PACKAGE_NAME
PACKAGE_FILE = BUILD_DIRECTORY / f"{PACKAGE_NAME}-v{PACKAGE_VERSION}-wordpress-org.zip"
CHECKSUM_FILE = BUILD_DIRECTORY / "SHA256SUMS.txt"

TEXT_EXTENSIONS = {
    ".php",
    ".js",
    ".css",
    ".txt",
    ".md",
    ".json",
    ".xml",
    ".yml",
    ".yaml",
}

IGNORED_NAMES = {
    ".git",
    ".github",
    ".gitignore",
    "build",
    "tools",
    "README.md",
}

PROTECTED_VALUES = {
    "f10-captura-de-leads": "__F10LECA_TEXT_DOMAIN__",
    "wp_plugin_f10_software": "__F10LECA_GITHUB_REPOSITORY__",
}

IDENTIFIER_REPLACEMENTS = (
    ("F10_Lead_Capture_", "F10LECA_"),
    ("F10_Lead_Capture", "F10LECA"),
    ("F10_LEAD_CAPTURE_", "F10LECA_"),
    ("F10_LEAD_CAPTURE", "F10LECA"),
    ("F10Lead", "F10LECA"),
    ("f10_lead_capture_", "f10leca_"),
    ("f10_lead_capture", "f10leca"),
    ("f10-lead-capture", "f10leca"),
    ("f10_", "f10leca_"),
    ("f10-", "f10leca-"),
)

FORBIDDEN_PATTERNS = {
    "legacy class prefix": re.compile(r"\bF10_Lead_Capture"),
    "legacy constant prefix": re.compile(r"\bF10_LEAD_CAPTURE"),
    "legacy JavaScript prefix": re.compile(r"\bF10Lead"),
    "short underscore prefix": re.compile(r"\bf10_"),
    "short dash prefix": re.compile(r"\bf10-(?!captura-de-leads\b)"),
}


def replace_identifiers(value: str, protect_values: bool = True) -> str:
    if protect_values:
        for original, placeholder in PROTECTED_VALUES.items():
            value = value.replace(original, placeholder)

    for original, replacement in IDENTIFIER_REPLACEMENTS:
        value = value.replace(original, replacement)

    if protect_values:
        for original, placeholder in PROTECTED_VALUES.items():
            value = value.replace(placeholder, original)

    return value


def copy_plugin_source() -> None:
    if BUILD_DIRECTORY.exists():
        shutil.rmtree(BUILD_DIRECTORY)

    PACKAGE_DIRECTORY.parent.mkdir(parents=True, exist_ok=True)

    def ignore(directory: str, names: list[str]) -> set[str]:
        return {name for name in names if name in IGNORED_NAMES}

    shutil.copytree(ROOT, PACKAGE_DIRECTORY, ignore=ignore)


def update_text_files() -> None:
    for path in PACKAGE_DIRECTORY.rglob("*"):
        if not path.is_file() or path.suffix.lower() not in TEXT_EXTENSIONS:
            continue

        original = path.read_text(encoding="utf-8")
        updated = replace_identifiers(original)

        updated = re.sub(
            r"(?m)^(\s*\*\s*Version:\s*)1\.3\.8\s*$",
            rf"\g<1>{PACKAGE_VERSION}",
            updated,
        )
        updated = updated.replace(
            "define('F10LECA_VERSION', '1.3.8');",
            f"define('F10LECA_VERSION', '{PACKAGE_VERSION}');",
        )
        updated = re.sub(
            r"(?m)^(Stable tag:\s*)1\.3\.8\s*$",
            rf"\g<1>{PACKAGE_VERSION}",
            updated,
        )

        if path.name == "readme.txt" and f"= {PACKAGE_VERSION} =" not in updated:
            marker = "== Changelog ==\n\n"
            changelog = (
                f"= {PACKAGE_VERSION} =\n\n"
                "* Replaces short global identifiers with the unique f10leca/F10LECA prefix.\n"
                "* Updates classes, constants, hooks, AJAX actions, shortcodes, options, transients, menus, database identifiers and asset handles.\n"
                "* Keeps the assigned f10-captura-de-leads text domain and plugin slug unchanged.\n\n"
            )
            if marker not in updated:
                raise RuntimeError("The readme changelog marker was not found.")
            updated = updated.replace(marker, marker + changelog, 1)

        if updated != original:
            path.write_text(updated, encoding="utf-8", newline="\n")


def rename_paths() -> None:
    paths = sorted(
        PACKAGE_DIRECTORY.rglob("*"),
        key=lambda path: (len(path.parts), len(str(path))),
        reverse=True,
    )

    for path in paths:
        if not path.exists():
            continue

        new_name = replace_identifiers(path.name, protect_values=True)
        if new_name == path.name:
            continue

        destination = path.with_name(new_name)
        if destination.exists():
            raise RuntimeError(
                f"Cannot rename {path.relative_to(PACKAGE_DIRECTORY)} to "
                f"{destination.relative_to(PACKAGE_DIRECTORY)} because the destination exists."
            )
        path.rename(destination)


def find_plugin_file() -> Path:
    plugin_files = []
    for path in PACKAGE_DIRECTORY.glob("*.php"):
        content = path.read_text(encoding="utf-8")
        if "Plugin Name: F10 Lead Capture" in content:
            plugin_files.append(path)

    if len(plugin_files) != 1:
        raise RuntimeError(
            f"Expected exactly one root plugin file, found {len(plugin_files)}."
        )

    return plugin_files[0]


def validate_plugin_structure(plugin_file: Path) -> None:
    plugin_content = plugin_file.read_text(encoding="utf-8")
    required_values = (
        f"Version: {PACKAGE_VERSION}",
        "Text Domain: f10-captura-de-leads",
        f"define('F10LECA_VERSION', '{PACKAGE_VERSION}');",
    )

    for required_value in required_values:
        if required_value not in plugin_content:
            raise RuntimeError(f"Missing expected plugin value: {required_value}")

    required_paths = re.findall(
        r"require_once\s+F10LECA_PATH\s*\.\s*'([^']+)'",
        plugin_content,
    )
    missing_paths = [
        relative_path
        for relative_path in required_paths
        if not (PACKAGE_DIRECTORY / relative_path).is_file()
    ]
    if missing_paths:
        raise RuntimeError(f"Missing required files after renaming: {missing_paths}")

    if "https://github.com/jefersonMarques/wp_plugin_f10_software" not in plugin_content:
        raise RuntimeError("The official GitHub repository URL was changed unexpectedly.")


def validate_prefixes() -> None:
    violations: list[str] = []

    for path in PACKAGE_DIRECTORY.rglob("*"):
        if not path.is_file() or path.suffix.lower() not in TEXT_EXTENSIONS:
            continue

        content = path.read_text(encoding="utf-8")
        for label, pattern in FORBIDDEN_PATTERNS.items():
            for match in pattern.finditer(content):
                line_number = content.count("\n", 0, match.start()) + 1
                violations.append(
                    f"{path.relative_to(PACKAGE_DIRECTORY)}:{line_number}: "
                    f"{label}: {match.group(0)}"
                )

    for path in PACKAGE_DIRECTORY.rglob("*"):
        if path.name == PACKAGE_NAME:
            continue

        for label, pattern in FORBIDDEN_PATTERNS.items():
            match = pattern.search(path.name)
            if match:
                violations.append(
                    f"{path.relative_to(PACKAGE_DIRECTORY)}: filename {label}: "
                    f"{match.group(0)}"
                )

    if violations:
        raise RuntimeError("Prefix validation failed:\n" + "\n".join(violations))


def validate_php_syntax() -> None:
    php_files = sorted(PACKAGE_DIRECTORY.rglob("*.php"))
    if not php_files:
        raise RuntimeError("No PHP files were found in the generated package.")

    for php_file in php_files:
        result = subprocess.run(
            ["php", "-l", str(php_file)],
            check=False,
            capture_output=True,
            text=True,
        )
        if result.returncode != 0:
            raise RuntimeError(
                f"PHP syntax validation failed for "
                f"{php_file.relative_to(PACKAGE_DIRECTORY)}:\n"
                f"{result.stdout}\n{result.stderr}"
            )


def create_package() -> str:
    if PACKAGE_FILE.exists():
        PACKAGE_FILE.unlink()

    with zipfile.ZipFile(PACKAGE_FILE, "w", compression=zipfile.ZIP_DEFLATED) as archive:
        for path in sorted(PACKAGE_DIRECTORY.rglob("*")):
            if not path.is_file():
                continue

            archive_name = Path(PACKAGE_NAME) / path.relative_to(PACKAGE_DIRECTORY)
            archive.write(path, archive_name.as_posix())

    with zipfile.ZipFile(PACKAGE_FILE, "r") as archive:
        corrupt_file = archive.testzip()
        if corrupt_file is not None:
            raise RuntimeError(f"The generated ZIP is corrupt at: {corrupt_file}")

    digest = hashlib.sha256(PACKAGE_FILE.read_bytes()).hexdigest()
    CHECKSUM_FILE.write_text(
        f"{digest}  {PACKAGE_FILE.name}\n",
        encoding="utf-8",
        newline="\n",
    )
    return digest


def main() -> int:
    copy_plugin_source()
    update_text_files()
    rename_paths()

    plugin_file = find_plugin_file()
    validate_plugin_structure(plugin_file)
    validate_prefixes()
    validate_php_syntax()
    digest = create_package()

    print(f"Plugin entry point: {plugin_file.relative_to(PACKAGE_DIRECTORY)}")
    print(f"Package: {PACKAGE_FILE}")
    print(f"SHA-256: {digest}")
    return 0


if __name__ == "__main__":
    try:
        raise SystemExit(main())
    except Exception as error:
        print(f"Build failed: {error}", file=sys.stderr)
        raise
