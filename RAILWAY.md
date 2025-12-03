# Railway Deployment Guide

## Prerequisites
1. Create a Railway account at https://railway.app
2. Install Railway CLI (optional): `npm i -g @railway/cli`

## Deployment Steps

### Option 1: Deploy via Railway Dashboard
1. Go to https://railway.app/new
2. Click "Deploy from GitHub repo"
3. Select this repository
4. Railway will auto-detect the project and deploy

### Option 2: Deploy via Railway CLI
```bash
railway login
railway init
railway up
```

## Required Environment Variables

Set these in Railway dashboard (Settings → Variables):

### Essential Variables
```
APP_NAME=LocalServices
APP_ENV=production
APP_KEY=base64:YOUR_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-app.railway.app
```

### Database (Railway MySQL Plugin)
Railway provides `DATABASE_URL` automatically when you add MySQL plugin.
```
DB_CONNECTION=mysql
```

### JWT Secret
Generate with: `php artisan jwt:secret`
```
JWT_SECRET=your_jwt_secret_here
```

### Cloudinary (Required for file uploads)
```
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret
```

### Mapbox (Optional - for location services)
```
MAPBOX_API_KEY=your_mapbox_key
```

### CORS Settings
```
FRONTEND_URL=https://your-frontend-url.com
```

### Optional Services
```
SMS_PROVIDER_API_KEY=your_sms_key
MAIL_MAILER=smtp
MAIL_HOST=your_mail_host
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

## Adding MySQL Database

1. In Railway dashboard, click "New" → "Database" → "Add MySQL"
2. Railway will automatically provide `DATABASE_URL` environment variable
3. The application will use this automatically

## Post-Deployment

After deployment:
1. Verify migrations ran successfully in Railway logs
2. Test API endpoints
3. Check application logs for any errors

## Custom Domain (Optional)

1. Go to Settings → Domains
2. Click "Generate Domain" or add custom domain
3. Update `APP_URL` and `FRONTEND_URL` accordingly

## Troubleshooting

### Migrations not running
Check Railway logs and manually run:
```bash
railway run php artisan migrate --force
```

### App key not set
Generate locally and add to Railway:
```bash
php artisan key:generate --show
```

### Storage issues
Files are stored in Cloudinary. Ensure credentials are set correctly.

## Monitoring

- View logs: Railway Dashboard → Deployments → Logs
- Monitor metrics: Railway Dashboard → Metrics
- Check health: `https://your-app.railway.app/api/health`
