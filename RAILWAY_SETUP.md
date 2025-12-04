# Railway Setup Instructions

## Step-by-Step Configuration

Follow these steps in your Railway Dashboard:

### Backend Service Environment Variables

Go to: **Backend Service → Settings → Variables**

Add these variables one by one (click "New Variable" for each):

---

## 1. Essential URLs

| Variable Name | Value |
|---|---|
| `APP_URL` | `https://localservices-backend.railway.internal` |
| `FRONTEND_URL` | `https://localservices-frontend.vercel.app/` |

---

## 2. Cloudinary Configuration

| Variable Name | Value |
|---|---|
| `CLOUDINARY_CLOUD_NAME` | `dst0us8ev` |
| `CLOUDINARY_API_KEY` | `777157591577617` |
| `CLOUDINARY_API_SECRET` | `yvEM1aeBApzanMH-LaGqS_KHlvQ` |

---

## 3. Mapbox Configuration

| Variable Name | Value |
|---|---|
| `MAPBOX_API_KEY` | `pk.eyJ1IjoibWlsb25jb2RlcyIsImEiOiJjbWlyYjB4NHYwZndzM2NzZ2NpM2JqZnM4In0.c5ESEHFhVIKooBD9niZa-w` |

---

## 4. JWT Secret (Generate First)

First, generate JWT secret:

```bash
railway run php artisan jwt:secret
```

This will output something like:
```
JWT_SECRET=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

Copy the entire value (including "eyJ..." part) and add to Railway:

| Variable Name | Value |
|---|---|
| `JWT_SECRET` | `(paste your generated secret here)` |

---

## ✅ Verification Steps

After adding all variables:

1. Click **Deploy** to redeploy with new variables
2. Wait for deployment to complete
3. Check logs for any errors
4. Test API endpoint:

```bash
curl https://localservices-backend.railway.internal/api/health
```

Or from your frontend:
```javascript
fetch('https://localservices-backend.railway.internal/api/health')
  .then(r => r.json())
  .then(data => console.log(data))
```

---

## Notes

- Railway should already have `DATABASE_URL` set from MySQL plugin
- All other variables can stay at their defaults
- If you get CORS errors, verify `FRONTEND_URL` is exactly correct
- If authentication fails, verify `JWT_SECRET` is set

