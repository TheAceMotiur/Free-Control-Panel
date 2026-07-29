#!/usr/bin/env bash
set -uo pipefail

# FreeNetly Control Panel - Powerful Auto-Fixing Installer
# Usage: curl -fsSL https://raw.githubusercontent.com/TheAceMotiur/Free-Control-Panel/refs/heads/main/install.sh | bash

REPO_URL="${REPO_URL:-https://github.com/TheAceMotiur/Free-Control-Panel.git}"
APP_DIR="${APP_DIR:-/var/www/freenetly}"
DOMAIN="${DOMAIN:-localhost}"
DB_ROOT_PASS="${DB_ROOT_PASS:-$(openssl rand -base64 24)}"
DB_NAME="${DB_NAME:-freenetly}"
DB_USER="${DB_USER:-freenetly}"
DB_PASS="${DB_PASS:-freenetly}"
ADMIN_EMAIL="${ADMIN_EMAIL:-TheAceMotiur}"
ADMIN_PASS="${ADMIN_PASS:-AmiMotiur27@}"

log() { echo "[$(date '+%H:%M:%S')] $*"; }
error() { echo "[ERROR] $*" >&2; }
success() { echo "[SUCCESS] $*"; }

if [[ $(id -u) -ne 0 ]]; then
  error "Please run this script as root."
  exit 1
fi

if [[ ! -f /etc/os-release ]]; then
  error "Cannot detect OS. /etc/os-release not found."
  exit 1
fi

source /etc/os-release
os_id="${ID:-}"
os_like="${ID_LIKE:-}"
version_id="${VERSION_ID:-}"

case "$os_id:$os_like" in
  almalinux:*|rocky:*|rhel:*|centos:*) ;;
  *almalinux*|*rocky*|*rhel*|*centos*) ;;
  *)
    error "This installer supports AlmaLinux, Rocky, RHEL, or CentOS 8/9."
    exit 1
    ;;
esac

case "$version_id" in
  8|8.*|9|9.*) ;;
  *)
    error "This installer expects version 8 or 9."
    exit 1
    ;;
esac

log "Starting FreeNetly Control Panel installation..."

# [1/7] Update system
log "[1/7] Updating system packages..."
dnf update -y 2>&1 | grep -v "^Last metadata" || true

# [2/7] Install packages
log "[2/7] Installing Apache, PHP, MariaDB, phpMyAdmin..."
dnf install -y epel-release git curl unzip policycoreutils-python-utils 2>&1 | grep -v "already installed" | grep -v "Nothing to do" || true
dnf install -y https://rpms.remirepo.net/enterprise/remi-release-9.rpm 2>&1 | grep -v "already installed" || true
dnf module reset php -y 2>&1 | grep -v "^Nothing" || true
dnf module enable php:remi-8.2 -y 2>&1 | grep -v "^Nothing" || true
dnf install -y httpd mariadb-server mariadb php php-cli php-mysqlnd php-xml php-gd php-mbstring php-json php-curl php-zip php-fpm php-opcache php-ldap php-pear php-process php-soap php-pecl-xmlrpc php-bcmath php-intl php-pecl-redis6 php-pecl-imagick-im7 phpMyAdmin 2>&1 | grep -v "already installed" | grep -v "Nothing to do" || true

# Auto-fix: Enable and start services
systemctl enable --now httpd mariadb 2>&1 | grep -v "Created symlink" || true
for svc in httpd mariadb; do
  if ! systemctl is-active --quiet $svc; then
    log "Auto-fix: Starting $svc..."
    systemctl start $svc || { systemctl restart $svc; sleep 2; }
  fi
done

# [3/7] Configure MariaDB with auto-troubleshooting
log "[3/7] Configuring MariaDB (with auto-fix)..."

fix_mariadb_auth() {
  log "Auto-fix: Resetting MariaDB root authentication..."
  systemctl stop mariadb
  mkdir -p /var/run/mariadb
  chown mysql:mysql /var/run/mariadb
  
  cat > /tmp/mariadb_reset.sql <<EOF
FLUSH PRIVILEGES;
ALTER USER 'root'@'localhost' IDENTIFIED BY '${DB_ROOT_PASS}';
FLUSH PRIVILEGES;
EOF
  
  mysqld_safe --skip-grant-tables --skip-networking &
  SAFE_PID=$!
  sleep 5
  
  mysql -uroot < /tmp/mariadb_reset.sql 2>/dev/null || true
  rm -f /tmp/mariadb_reset.sql
  
  pkill -9 mysqld_safe 2>/dev/null || true
  pkill -9 mysqld 2>/dev/null || true
  sleep 2
  
  systemctl start mariadb
  sleep 3
  
  echo "${DB_ROOT_PASS}" > /root/.freenetly_mysql_root_pass
  chmod 600 /root/.freenetly_mysql_root_pass
}

MYSQL_ROOT_AUTH=""
MYSQL_ROOT_CMD="mysql"

if mysql -uroot -e "SELECT 1" >/dev/null 2>&1; then
  MYSQL_ROOT_AUTH="-uroot"
elif sudo mysql -uroot -e "SELECT 1" >/dev/null 2>&1; then
  MYSQL_ROOT_AUTH="-uroot"
  MYSQL_ROOT_CMD="sudo mysql"
elif [[ -f /root/.freenetly_mysql_root_pass ]] && mysql -uroot -p"$(cat /root/.freenetly_mysql_root_pass)" -e "SELECT 1" >/dev/null 2>&1; then
  DB_ROOT_PASS=$(cat /root/.freenetly_mysql_root_pass)
  MYSQL_ROOT_AUTH="-uroot -p${DB_ROOT_PASS}"
elif [[ -n "${MYSQL_ROOT_PASSWORD:-}" ]] && mysql -uroot -p"${MYSQL_ROOT_PASSWORD}" -e "SELECT 1" >/dev/null 2>&1; then
  DB_ROOT_PASS="${MYSQL_ROOT_PASSWORD}"
  MYSQL_ROOT_AUTH="-uroot -p${DB_ROOT_PASS}"
  echo "${DB_ROOT_PASS}" > /root/.freenetly_mysql_root_pass
  chmod 600 /root/.freenetly_mysql_root_pass
else
  fix_mariadb_auth
  MYSQL_ROOT_AUTH="-uroot -p${DB_ROOT_PASS}"
fi

${MYSQL_ROOT_CMD} ${MYSQL_ROOT_AUTH} <<MYSQL
CREATE DATABASE IF NOT EXISTS ${DB_NAME};
CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';
FLUSH PRIVILEGES;
MYSQL

${MYSQL_ROOT_CMD} ${MYSQL_ROOT_AUTH} "${DB_NAME}" <<MYSQL
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(160) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL UNIQUE,
  status VARCHAR(20) NOT NULL DEFAULT 'running',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_databases (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL UNIQUE,
  db_name VARCHAR(64) NOT NULL,
  db_user VARCHAR(64) NOT NULL,
  db_password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO services (name, status) SELECT 'Apache', 'running' WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'Apache');
INSERT INTO services (name, status) SELECT 'PHP', 'running' WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'PHP');
INSERT INTO services (name, status) SELECT 'MySQL', 'running' WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'MySQL');
INSERT INTO services (name, status) SELECT 'phpMyAdmin', 'running' WHERE NOT EXISTS (SELECT 1 FROM services WHERE name = 'phpMyAdmin');
MYSQL

ADMIN_HASH=$(php -r 'echo password_hash($argv[1], PASSWORD_DEFAULT);' "${ADMIN_PASS}")
${MYSQL_ROOT_CMD} ${MYSQL_ROOT_AUTH} "${DB_NAME}" <<MYSQL
INSERT INTO users (full_name, email, password_hash, role)
VALUES ('${ADMIN_EMAIL}', '${ADMIN_EMAIL}', '${ADMIN_HASH}', 'admin')
ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash), role = VALUES(role);
MYSQL

success "MariaDB configured successfully"

# [4/7] Deploy application
log "[4/7] Deploying application..."
mkdir -p /var/www
if [[ -d "$PWD/.git" ]] && [[ -f "$PWD/installer.php" ]]; then
  rm -rf "$APP_DIR"
  mkdir -p "$APP_DIR"
  rsync -a --exclude='.git' "$PWD/" "$APP_DIR/" 2>/dev/null || cp -r "$PWD/." "$APP_DIR/"
else
  if [[ -d "$APP_DIR/.git" ]]; then
    cd "$APP_DIR" && git pull 2>/dev/null || rm -rf "$APP_DIR"
  fi
  if [[ ! -d "$APP_DIR" ]]; then
    git clone "$REPO_URL" "$APP_DIR" || error "Failed to clone repository"
  fi
fi

mkdir -p "$APP_DIR/includes"
cat > "$APP_DIR/includes/config.php" <<EOF
<?php
// Generated by install.sh
define('APP_NAME', 'FreeNetly Control Panel');
define('APP_URL', 'http://${DOMAIN}');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', '${DB_NAME}');
define('DB_USER', '${DB_USER}');
define('DB_PASS', '${DB_PASS}');
define('INSTALL_LOCK', '1');
EOF

chown -R apache:apache "$APP_DIR" 2>/dev/null || chown -R httpd:httpd "$APP_DIR"
chmod -R 755 "$APP_DIR"

# [5/7] Configure Apache
log "[5/7] Configuring Apache..."
cat > /etc/httpd/conf.d/freenetly.conf <<EOF
<VirtualHost *:80>
    ServerName ${DOMAIN}
    DocumentRoot ${APP_DIR}
    <Directory ${APP_DIR}>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog /var/log/httpd/freenetly_error.log
    CustomLog /var/log/httpd/freenetly_access.log combined
</VirtualHost>
EOF

cat > /etc/httpd/conf.d/phpMyAdmin.conf <<EOF
Alias /phpmyadmin /usr/share/phpMyAdmin
<Directory /usr/share/phpMyAdmin/>
    Require all granted
</Directory>
EOF

if command -v semanage >/dev/null 2>&1; then
  semanage fcontext -a -t httpd_sys_rw_content_t "${APP_DIR}(/.*)?" 2>/dev/null || true
  restorecon -Rv "$APP_DIR" 2>/dev/null || true
fi

cat > /etc/php.d/99-freenetly.ini <<EOF
upload_max_filesize = 100M
post_max_size = 100M
memory_limit = 256M
max_execution_time = 300
EOF

systemctl enable php-fpm 2>/dev/null || true
for svc in httpd php-fpm mariadb; do
  systemctl restart $svc 2>/dev/null || systemctl start $svc 2>/dev/null || true
done

# [6/7] Configure firewall
log "[6/7] Configuring firewall..."
if command -v firewall-cmd >/dev/null 2>&1 && systemctl is-active --quiet firewalld; then
  firewall-cmd --permanent --add-service=http 2>/dev/null || true
  firewall-cmd --permanent --add-service=https 2>/dev/null || true
  firewall-cmd --reload 2>/dev/null || true
fi

# [7/7] Verify installation
log "[7/7] Verifying services..."
FAILED=0
for svc in httpd mariadb; do
  if systemctl is-active --quiet $svc; then
    success "✓ $svc is running"
  else
    error "✗ $svc failed"
    FAILED=1
  fi
done

if [[ $FAILED -eq 1 ]]; then
  error "Some services failed. Attempting auto-recovery..."
  for svc in httpd mariadb php-fpm; do
    systemctl restart $svc 2>/dev/null || true
  done
  sleep 3
fi

echo
echo "═══════════════════════════════════════════════════════════"
success "Installation complete!"
echo "═══════════════════════════════════════════════════════════"
echo
printf 'Web app: http://%s/\n' "$DOMAIN"
printf 'phpMyAdmin: http://%s/phpmyadmin/\n' "$DOMAIN"
printf 'Admin login: http://%s/auth/login.php\n' "$DOMAIN"
echo
printf 'Admin username: %s\n' "$ADMIN_EMAIL"
printf 'Admin password: %s\n' "$ADMIN_PASS"
echo
printf 'Database: %s\n' "$DB_NAME"
printf 'DB User: %s\n' "$DB_USER"
printf 'DB Root Password (saved): %s\n' "$DB_ROOT_PASS"
echo "═══════════════════════════════════════════════════════════"
