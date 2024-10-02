# Mailbox Client
**Mailbox Client** allows users to register and link their Gmail and Outlook mailboxes seamlessly. Upon linking a mailbox, it downloads all emails from the provider using the IMAP protocol. The client ensures that data remains up to date by reflecting real-time updates through the IMAP IDLE protocol. Additionally, it leverages the EventSource API to synchronize updates on the frontend, providing a smooth user experience.
## Installation
### Clone the Repository
```bash
git clone https://github.com/cmhown/mailbox-client.git
```
### Navigate to the Root Directory
```
cd mailbox-client
```

### Start Docker Containers
Create a .env file
```
cp backend/.env.example backend/.env
```
Start docker conatiners
```
docker-compose up -d
```

### Configuration
Add the following credentials to your .env file. 
```
GOOGLE_CLIENT_ID=<your-google-client-id>
GOOGLE_CLIENT_SECRET=<your-google-client-secret>

MICROSOFT_CLIENT_ID=<your-microsoft-client-id>
MICROSOFT_CLIENT_SECRET=<your-microsoft-client-secret>
```
### Application URLs
1. Your Laravel app should be accessible at:
http://localhost:8000
2. Your React app should run at:
http://localhost:3000

### Docker Setup Issues
Here are some common setup issues that you might face.

**Laravel**

Sometimes docker fails to create a vendor directory and gives error when trying to access Laravel app.

Go inside the container and run the following commands manually
```
docker exec -it laravel bash
```
```
composer install
```
```
composer dump-autoload
```
For permission issues, run
```
chgrp www-data /var/www/html -R
```
```
chmod 755 /var/www/html/storage -R
```
For database migrations
```
php artisan migrate
```
For elasticsearch migrations
```
php artisan elastic:migrate
```
If you change the port of react app from 3000, please also change it in `backend/config/cors.php` line `22`

**React**

If react container fails to start, run `npm install` from host machine in `frontend` directory.
```
cd frontend
```
```
npm install
```
If still not works, run this commond twice
```
npm audit fix --force
```

### Usage
1. Open the React app URL http://localhost:3000/register and register a new user.
2. Log in to your account. You will be redirected to the home page. If it doesn't redirects, try access this url manually http://localhost:3000.
3. Link an email provider from the available options (Google or Microsoft).
4. Your email list will start loading on UI as soon as emails are fetched in the backend.
5. The frontend uses the EventSource API to get real-time updates from the server.

### Email Synchronization Process
Once an account is linked, the Laravel backend saves the linked account information and queues a job `SyncEmailFolders` to sync the folder structure from the provider for the OAuth account.

**This job**
1. Syncs folders from the provider.
2. Sends updates to the frontend via Redis.
3. Stores folder data in a local Elasticsearch index.
4. Creates two additional jobs:

   4.1 `SyncEmailMessages`: Syncs all emails from the folder to Elasticsearch and sends updates to Redis for the frontend.

   4.2 `IdleEmailFolder`: Creates an IMAP IDLE connection for the folder and listens for real-time changes from the provider, such as new messages, flag changes, and deletions.

**Event listeners handle:**
1. New messages
2. Flag changes
3. Message deletions

These events trigger synchronization jobs, which update both the backend and frontend.
### Features
- **Unique Queue Jobs:** Jobs are made unique to avoid unnecessary processing. If a job for a specific folder or message is already in queue, the new job will not create.
- **Elasticsearch Indexes:** Managed using the `babenkoivan/elastic-migrations` package, which handles Elasticsearch indexes similarly to MySQL migrations.
- **OAuth:** Implemented using the `laravel/socialite` package. Microsoft Outlook support is added via the `socialiteproviders/microsoft` package.
- **IMAP Communication:** Managed by the `webklex/laravel-imap` package. A custom wrapper, `CustomImapIdleService`, is used to extend its IDLE functionality for events like flag changes and deletions.
### Scalability & Monitoring
- **Redis:** Used for queuing jobs to ensure scalability and also to send realtime updates to frontend.
- **Supervisord:** Handles processing of queued jobs efficiently and allows horizontal scaling.
- **Command for OAuth Account Synchronization:** A custom command schedules periodic OAuth account syncs to keep email folders up to date. Adjust the frequency of this command as needed.
- **Laravel Horizon:** To monitor jobs http://localhost:8000/horizon
