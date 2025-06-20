# FixPro API Server

FixPro API Server is a Laravel-based backend application designed to serve as the core API service for the FixPro platform.

## 📦 Requirements

Before you begin, ensure you have the following installed on your system:

- [Docker](https://www.docker.com/)
- [Composer](https://getcomposer.org/)

## 🚀 Setting Up the Server

Follow the steps below to set up the FixPro API Server on your local machine.

### 1. Clone the Repository

```
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

## ⚙️ Server Configuration (Post-Setup)

After the server is running, there are a few configuration steps that the client needs to complete in order to properly set up the server for specific operations (e.g., integrations, services).

### 1. Access the Home Page

After starting the server, navigate to the home page:

```
http://your_application/
```

If you're not authenticated, you'll be redirected to /auth for authentication.

### 2. Authenticate (`/auth`)
1. Find the authentication token in the console output:

    ```
    [AUTH_TOKEN] 12345abcdef-token-value
    ```

2. Copy the token and paste it into the /auth page to authenticate.

    After successful authentication, you'll be redirected to the configuration page.

### 3. Configure Server Settings

Once authenticated, the configuration page will guide you through the following setup steps:

- Define User Data Fields

    Select the types of information you want to collect from users, such as email, phone number, or any other custom fields relevant to your application.

- Integrate Google Calendar

    The page will walk you through enabling the Google Calendar API in the Google Cloud Console and entering the necessary API credentials. This integration allows the server to create and manage calendar events.

- Set Registration Policy

    Choose how new members can join your platform:
    - Open : Anyone can register freely
    - Approval Required : Admin approval is needed for new registrations
    - Closed : New user registration is disabled


### 4. Finalizing
Once setup is complete, the server is ready to use!
