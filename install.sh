#!/usr/bin/env bash
set -euo pipefail

# Main installer entrypoint for AlmaLinux 9 VPS
# Usage:
#   curl -fsSL https://raw.githubusercontent.com/TheAceMotiur/Free-Control-Panel/refs/heads/main/install.sh | bash
# or:
#   bash install.sh

if [[ $(id -u) -ne 0 ]]; then
  echo "Please run this script as root." >&2
  exit 1
fi

if [[ -f /etc/os-release ]]; then
  # shellcheck disable=SC1091
  source /etc/os-release
fi

if [[ "${ID:-}" == "almalinux" ]] || [[ "${ID_LIKE:-}" == *"almalinux"* ]] || [[ "${ID:-}" == "rocky" ]] || [[ "${ID_LIKE:-}" == *"rocky"* ]]; then
  curl -fsSL https://raw.githubusercontent.com/TheAceMotiur/Free-Control-Panel/refs/heads/main/install-almalinux9.sh | bash
else
  echo "This installer currently supports AlmaLinux or Rocky Linux." >&2
  exit 1
fi
