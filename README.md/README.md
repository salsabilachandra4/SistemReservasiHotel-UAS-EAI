# Final Project IAE - Hotel Booking Microservices

Project ini dibuat untuk memenuhi tugas besar mata kuliah Integrasi Aplikasi Enterprise (IAE). Sistem yang dikembangkan adalah backend sistem pemesanan hotel berbasis microservices.

Project ini menggunakan beberapa service yang berjalan secara terpisah, yaitu Customer Service, Room Service, Booking Service, Payment Service, dan RabbitMQ sebagai message broker. Setiap service memiliki tanggung jawab masing-masing dan berkomunikasi menggunakan RESTful API serta message broker.

## Arsitektur Sistem

Sistem menggunakan arsitektur microservices, di mana setiap layanan berjalan secara independen dan memiliki database masing-masing.

Service yang digunakan:

| Service | Fungsi |
|---|---|
| Customer Service | Mengelola data customer atau pelanggan |
| Room Service | Mengelola data kamar hotel dan status ketersediaan kamar |
| Booking Service | Mengelola data pemesanan kamar |
| Payment Service | Mengelola data pembayaran booking |
| RabbitMQ | Message broker untuk komunikasi asynchronous |
| Nginx | Web server / reverse proxy untuk service Laravel |

## Struktur Project

```bash
IAE-tugas/
├── booking-service/
├── customer-service/
├── payment-service/
├── room-service-main/
├── rabbitmq/
└── nginx/

## Teknologi yang Digunakan

| Teknologi | Fungsi |
|---|---|
| Laravel | Backend framework untuk setiap service |
| PHP | Bahasa pemrograman backend |
| MySQL | Database setiap service |
| Docker | Containerisasi service |
| Docker Compose | Menjalankan container dengan konfigurasi |
| RabbitMQ | Message broker untuk komunikasi asynchronous |
| RESTful API | Komunikasi antar service menggunakan HTTP |
| GraphQL | Komunikasi data fleksibel |
| Hasura | GraphQL engine untuk akses database |

## Endpoint RESTful API

### Customer Service

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | /api/customers | Menampilkan semua customer |
| GET | /api/customers/{id} | Menampilkan detail customer |
| POST | /api/customers | Menambahkan customer |
| PUT | /api/customers/{id} | Mengubah data customer |
| DELETE | /api/customers/{id} | Menghapus customer |
| GET | /api/customers/{id}/bookings | Menampilkan booking milik customer |

### Room Service

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | /api/rooms | Menampilkan semua kamar |
| GET | /api/rooms/{id} | Menampilkan detail kamar |
| POST | /api/rooms | Menambahkan kamar |
| PUT | /api/rooms/{id} | Mengubah data kamar |
| DELETE | /api/rooms/{id} | Menghapus kamar |
| GET | /api/rooms/available | Menampilkan kamar yang tersedia |
| POST | /api/rooms/{id}/reserve | Mengubah status kamar menjadi reserved |
| POST | /api/rooms/{id}/release | Mengembalikan status kamar menjadi available |
| GET | /api/rooms/{id}/bookings | Menampilkan booking berdasarkan kamar |

### Booking Service

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | /api/bookings | Menampilkan semua booking |
| GET | /api/bookings/{id} | Menampilkan detail booking |
| POST | /api/bookings | Membuat booking baru |
| PUT | /api/bookings/{id} | Mengubah data booking |
| DELETE | /api/bookings/{id} | Menghapus booking |
| GET | /api/bookings/{id}/room | Mengambil data room dari booking |
| GET | /api/bookings/{id}/customer | Mengambil data customer dari booking |
| GET | /api/bookings/{id}/detail | Menampilkan detail booking lengkap |

### Payment Service

| Method | Endpoint | Fungsi |
|---|---|---|
| GET | /api/payments | Menampilkan semua pembayaran |
| GET | /api/payments/{id} | Menampilkan detail pembayaran |
| POST | /api/payments | Membuat pembayaran |
| PUT | /api/payments/{id} | Mengubah data pembayaran |
| DELETE | /api/payments/{id} | Menghapus pembayaran |
| POST | /api/payments/{id}/pay | Mengubah status menjadi paid |
| POST | /api/payments/{id}/cancel | Membatalkan pembayaran |
| POST | /api/payments/{id}/refund | Melakukan refund |
| POST | /api/payments/{id}/process | Memproses pembayaran asynchronous |
| GET | /api/bookings/{bookingId}/payments | Menampilkan pembayaran berdasarkan booking |

## Message Broker

Project ini menggunakan RabbitMQ sebagai message broker untuk komunikasi asynchronous.

RabbitMQ digunakan terutama pada Payment Service melalui endpoint:

```bash
POST /api/payments/{id}/process

## JALANKAN 
cd customer-service
docker compose up -d

cd room-service-main
docker compose up -d

cd booking-service
docker compose up -d

cd payment-service
docker compose up -d

## LIHAT DCOKER YANG BERJALAN 
docker ps

## Port Service

| Service | Port |
|---|---|
| Booking Service | 8001 / sesuai docker-compose |
| Customer Service | 8002 / sesuai docker-compose |
| Room Service | 8003 / sesuai docker-compose |
| Payment Service | 8004 / sesuai docker-compose |
| RabbitMQ | 5672 |
| RabbitMQ Management | 15672 |

