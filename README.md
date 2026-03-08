# 🔄 Nginx + PHP Demo — Web Server · Reverse Proxy · Load Balancer

> สาธิต 3 บทบาทของ Nginx แบบแยก container ชัดเจนบน Docker Compose

## 📐 Architecture

```
Browser :80   → [nginx-lb]  (Load Balancer)  → app1, app2, app3
Browser :8080 → [nginx-rp]  (Reverse Proxy)  → app1

app1 = Nginx Web Server + PHP-FPM  (internal, no host port)
app2 = Nginx Web Server + PHP-FPM  (internal, no host port)
app3 = Nginx Web Server + PHP-FPM  (internal, no host port)
```

## 📁 File Structure

```
nginx-php-demo/
├── docker-compose.yml
├── nginx-lb/
│   └── nginx.conf        ← Load Balancer (least_conn)
├── nginx-rp/
│   └── nginx.conf        ← Reverse Proxy (+headers)
└── app/
    ├── Dockerfile         ← php:8.2-fpm-alpine + nginx + supervisor
    ├── nginx.conf         ← Nginx web server → fastcgi → php-fpm
    ├── supervisord.conf   ← Run nginx + php-fpm together
    └── www/
        └── index.php      ← PHP page แสดงชื่อ server + headers
```

## 🚀 Quick Start

```bash
docker compose up --build -d
```

## 🧪 Test Cases

### Test 1: Load Balancing (port 80)
```bash
# Browser: เปิด http://localhost แล้วกด F5 ซ้ำๆ
# จะเห็น: Web Server 1 → Web Server 2 → Web Server 3 → วนซ้ำ

# หรือใช้ curl:
for i in {1..9}; do
  curl -s http://localhost | grep -o 'Web Server [0-9]'
done
```

### Test 2: Reverse Proxy (port 8080)
```bash
# Browser: เปิด http://localhost:8080 แล้วกด F5 กี่ครั้งก็ได้
# จะเห็น: Web Server 1 เสมอ (nginx-rp → app1 เท่านั้น)

# ดู response header
curl -I http://localhost:8080
# X-Served-Via: nginx-rp
```

### Test 3: Failover
```bash
# หยุด app2
docker compose stop app2

# ทดสอบ LB — ควรยังทำงานได้ (แค่ app1 กับ app3)
for i in {1..6}; do curl -s http://localhost | grep -o 'Web Server [0-9]'; done

# เริ่ม app2 ใหม่
docker compose start app2
```

### Test 4: PHP Headers
```bash
# ดูว่า PHP อ่าน header X-Real-IP ได้
curl -s http://localhost | grep -o 'X-Forwarded-By.*nginx-lb'
curl -s http://localhost:8080 | grep -o 'X-Proxy-By.*nginx-rp'
```

## ⚙️ Change LB Algorithm

แก้ `nginx-lb/nginx.conf`:

```nginx
upstream backend {
    # เลือก 1 อย่าง:
    least_conn;           # ส่งไป server ว่างสุด
    # ip_hash;            # session sticky
    # (ไม่ใส่ = round_robin)
}
```

จากนั้น reload:
```bash
docker compose exec nginx-lb nginx -s reload
```

## 📊 Monitoring

```bash
# LB stats
curl http://localhost/lb-status

# RP stats
curl http://localhost:8080/rp-status

# App health check
curl http://localhost/health

# Logs
docker compose logs -f nginx-lb
docker compose logs -f nginx-rp
docker compose logs -f app1
```

## 🛑 Stop

```bash
docker compose down
```
