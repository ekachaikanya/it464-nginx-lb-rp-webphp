# ═══════════════════════════════════════════════════════════
# PHP-FPM + Nginx Web Server in ONE container
# Supervisor จัดการทั้ง 2 processes
# ═══════════════════════════════════════════════════════════
FROM php:8.2-fpm-alpine

# ── ติดตั้ง nginx และ supervisor ────────────────────────
RUN apk add --no-cache nginx supervisor

# ── Nginx web server config (→ fastcgi pass to php-fpm) ─
COPY nginx.conf /etc/nginx/http.d/default.conf

# ── Supervisor config ────────────────────────────────────
COPY supervisord.conf /etc/supervisord.conf

# ── PHP application ──────────────────────────────────────
COPY www/ /var/www/html/

# ── Permissions & dirs ───────────────────────────────────
RUN chown -R www-data:www-data /var/www/html \
 && mkdir -p /run/nginx /var/log/supervisor

EXPOSE 80

# Supervisor boots nginx + php-fpm together
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
