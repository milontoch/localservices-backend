# Post-Deployment Checklist & Guide

## 1️⃣ Verify Database Migrations

### Check Migration Status
1. Go to Railway Dashboard → Your Backend Service
2. Click on **Deployments** tab
3. Select the latest deployment
4. Check the **Logs** for migration messages

**Look for:**
```
Migration table created successfully.
Migrated:  2024_01_01_000001_create_users_table
Migrated:  2024_01_01_000002_create_categories_table
... (all migrations should show)
```

**If migrations failed:**
```bash
# Run in Railway CLI:
railway run php artisan migrate:status
railway run php artisan migrate --force
```

---

## 2️⃣ Generate & Set JWT Secret

### Generate JWT Secret
This is required for authentication to work.

**Option A: Using Railway CLI**
```bash
railway run php artisan jwt:secret
# Check the output - it will show: JWT_SECRET=eyJ0eXAiOiJKV1QiLCJhbGc...
```

**Option B: Generate Locally (if you have PHP)**
```bash
php artisan jwt:secret --show
# Copy the output value
```

### Set JWT Secret in Railway
1. Railway Dashboard → Backend Service → Settings → Variables
2. Click **New Variable**
3. Add:
   - **Variable name**: `JWT_SECRET`
   - **Value**: Paste the secret you generated (without "JWT_SECRET=" prefix)
4. Click **Add**
5. Deploy again to apply changes

---

## 3️⃣ Configure External Services

### Cloudinary (Required for file uploads)
1. Go to https://cloudinary.com and sign up
2. In Dashboard, copy:
   - Cloud Name
   - API Key
   - API Secret

3. In Railway → Backend → Settings → Variables, add:
   - `CLOUDINARY_CLOUD_NAME`: your_cloud_name
   - `CLOUDINARY_API_KEY`: your_api_key
   - `CLOUDINARY_API_SECRET`: your_api_secret

### Mapbox (Optional - for location services)
1. Go to https://mapbox.com and sign up
2. Create access token in Account → Tokens
3. Add to Railway variables:
   - `MAPBOX_API_KEY`: your_token

### SMS Provider (Optional)
If you're using SMS functionality:
1. Get API key from your SMS provider
2. Add to Railway:
   - `SMS_PROVIDER_API_KEY`: your_api_key

---

## 4️⃣ Set CORS & URLs

### Get Your Backend URL
1. Railway Dashboard → Backend Service → Settings
2. Copy your **Domain** (e.g., `localservices-backend-prod.up.railway.app`)

### Update Backend Environment Variables
In Railway → Backend → Settings → Variables, update:

1. **APP_URL**
   - Value: `https://your-domain.up.railway.app`
   - Example: `https://localservices-backend-prod.up.railway.app`

2. **FRONTEND_URL** (Important for CORS!)
   - Value: Your frontend URL
   - Example: `https://localservices-frontend.vercel.app`

---

## 5️⃣ Test API Endpoints

### Test Health Check
```bash
curl https://your-backend-domain.up.railway.app/api/health
```

Expected response:
```json
{
  "status": "ok",
  "timestamp": "2025-12-04T10:30:00Z"
}
```

### Test Authentication (if you have a login endpoint)
```bash
curl -X POST https://your-backend-domain.up.railway.app/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

### Test from Browser DevTools
Open the browser console and run:
```javascript
fetch('https://your-backend-domain.up.railway.app/api/health')
  .then(res => res.json())
  .then(data => console.log(data))
  .catch(err => console.error(err))
```

---

## 6️⃣ Check Application Logs

### View Real-time Logs
1. Railway Dashboard → Backend Service
2. Click on **Logs** tab
3. Watch for any errors or warnings

**Common issues to look for:**
- Database connection errors
- Missing environment variables
- JWT secret errors
- CORS errors

### Download Full Logs
Click the **Download** button in the Logs tab for detailed analysis.

---

## 7️⃣ Database Verification

### Connect to Database (Optional)
If you need to inspect the database directly:

```bash
# Get DATABASE_URL from Railway
railway run php artisan tinker
# In tinker, run:
DB::connection()->getPDO()->getAttribute(\PDO::ATTR_DRIVER_NAME);
DB::select('SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = DATABASE()');
```

### Seed Test Data (Optional)
If you want to populate sample data:
```bash
railway run php artisan db:seed
```

---

## 8️⃣ Performance Optimization (Optional)

### Clear All Caches
```bash
railway run php artisan cache:clear
railway run php artisan config:clear
railway run php artisan route:clear
```

### Rebuild Cache
```bash
railway run php artisan config:cache
railway run php artisan route:cache
```

---

## 9️⃣ Security Checks

### Verify Security Settings
- [ ] `APP_ENV` is set to `production`
- [ ] `APP_DEBUG` is set to `false`
- [ ] `JWT_SECRET` is set and strong
- [ ] `FRONTEND_URL` is configured correctly
- [ ] Database credentials are not exposed

### Check `.env` Sensitive Data
Make sure these are NOT committed to git:
```bash
# Should be in .gitignore:
.env
.env.local
composer.lock (if using local development)
```

---

## 🔟 Final Deployment Check

Create a checklist:

- [ ] Database migrations completed successfully
- [ ] JWT_SECRET generated and configured
- [ ] Cloudinary credentials added
- [ ] APP_URL set correctly
- [ ] FRONTEND_URL set correctly
- [ ] Health check API responds
- [ ] No errors in application logs
- [ ] CORS is properly configured

---

## 🚨 Troubleshooting

### Issue: "CORS policy: No 'Access-Control-Allow-Origin' header"
**Solution:** Make sure `FRONTEND_URL` is set correctly in Railway variables

### Issue: "Unauthorized" errors
**Solution:** Make sure `JWT_SECRET` is set in Railway variables

### Issue: File uploads failing
**Solution:** Verify Cloudinary credentials are correct

### Issue: Database connection errors
**Solution:** Check if MySQL database plugin is added to Railway

---

## 📞 Need Help?

If something fails:
1. Check Railway logs for error messages
2. Verify all environment variables are set
3. Redeploy the application
4. Check the application logs again

