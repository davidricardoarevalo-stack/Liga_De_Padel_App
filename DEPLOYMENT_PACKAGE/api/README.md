Lumen rewrite (development Docker setup)

This folder contains a minimal scaffold to develop the new Lumen-based API using Docker.

Quick start (requires Docker Desktop):

1. Copy `.env.example` to `.env` and adjust values if needed.
2. Start services:
   docker-compose up --build
3. Lumen app will be available at: http://localhost:8080
4. Adminer (DB UI) at: http://localhost:8081

Developer notes:
- The image build runs `composer create-project` and `composer install` in the builder stage. We added `firebase/php-jwt` to `composer.json` so the app can issue/verify JWTs.
- If you change PHP dependencies locally, rebuild the image with:
   docker-compose build --no-cache app

JWT:
- Set a strong `JWT_SECRET` in `.env` before creating real users. The default TTL is 1 hour.

Notes:
- The image build scaffolds a Lumen project during build time. If you want to customize the app code, edit files in this folder after the first build.
- Production: this is development setup only. For Bluehost production you'll package the app and upload files.
