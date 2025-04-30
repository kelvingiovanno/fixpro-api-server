# FixPro API Server

FixPro API Server is a Laravel-based backend application designed to serve as the core API service for the FixPro platform.

## 📦 Requirements

Before you begin, ensure you have the following installed on your system:

- [Docker](https://www.docker.com/)
- [Composer](https://getcomposer.org/)

## 🚀 Getting Started

Follow the steps below to set up the FixPro API Server on your local machine.

### 1. Clone the Repository

```bash
git clone https://github.com/your-username/fixpro-api-server.git
```

### 2. Copy the Environment File

```
cp .env.example .env
```
Customize the `.env` file as needed, especially database settings app keys and others.

### 3. Install Composer Dependencies

```
composer install
```

### 4. Start Laravel Sail
Start the Docker containers using Sail:
```
./vendor/bin/sail up
```
The first time you run this, Sail will build your containers and may take a few minutes.

### 5. Run Migrations and Seed the Database (inside Docker Server Container)
Run the following command to create the database tables and populate them with the necessary initial data.
```
php artisan migrate --seed
```