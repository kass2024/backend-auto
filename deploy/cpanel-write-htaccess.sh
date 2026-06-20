#!/bin/bash
# Writes .htaccess files directly on the server (no deploy/ folder needed).
# Run: cd ~/api.neamee-autotechsolutions.com && bash deploy/cpanel-write-htaccess.sh

set -e
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Writing .htaccess in: $ROOT"

# ── Root .htaccess (when document root = Laravel project folder) ──
cat > .htaccess << 'HTACCESS'
<IfModule mod_rewrite.c>
    Options +FollowSymLinks -MultiViews
    RewriteEngine On
    RewriteBase /

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteRule ^public/ - [L]

    RewriteCond %{DOCUMENT_ROOT}/public/$1 -f [OR]
    RewriteCond %{DOCUMENT_ROOT}/public/$1 -d
    RewriteRule ^(.*)$ public/$1 [L]

    RewriteCond %{REQUEST_FILENAME} -f [OR]
    RewriteCond %{REQUEST_FILENAME} -d
    RewriteRule ^ - [L]

    RewriteRule ^ public/index.php [L,QSA]
</IfModule>

<IfModule LiteSpeed>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ public/index.php [L,QSA]
</IfModule>

<IfModule !mod_rewrite.c>
    FallbackResource /public/index.php
</IfModule>
HTACCESS

chmod 644 .htaccess
echo "Created: $ROOT/.htaccess"

# ── public/.htaccess (when document root = public/ folder) ──
mkdir -p public
cat > public/.htaccess << 'HTACCESS'
<IfModule mod_rewrite.c>
    Options -MultiViews -Indexes +FollowSymLinks
    RewriteEngine On
    RewriteBase /

    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L,QSA]
</IfModule>

<IfModule LiteSpeed>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L,QSA]
</IfModule>

<IfModule !mod_rewrite.c>
    FallbackResource /index.php
</IfModule>
HTACCESS

chmod 644 public/.htaccess
echo "Created: $ROOT/public/.htaccess"

# ── Laravel index.php ──
if [ ! -f index.php ]; then
  echo "Note: root index.php not required (root .htaccess uses public/index.php)"
fi

if [ -f public/index.cpanel.php ] && [ ! -f public/index.php ]; then
  cp public/index.cpanel.php public/index.php
  echo "Created public/index.php from index.cpanel.php"
fi

echo ""
echo "==> Verify files exist:"
ls -la .htaccess index.php public/.htaccess public/index.php 2>&1

echo ""
echo "==> IMPORTANT — set cPanel document root to public folder:"
echo "    cPanel → Domains → api.neamee-autotechsolutions.com"
echo "    Document Root: $ROOT/public"
echo ""
echo "Then test:"
echo "  curl -s https://api.neamee-autotechsolutions.com/api/public/health"
