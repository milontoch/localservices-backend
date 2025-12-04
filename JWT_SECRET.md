# Generated JWT Secrets

Use this JWT secret in your Railway environment variable:

```
JWT_SECRET=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkxvY2FsU2VydmljZXMiLCJpYXQiOjE3MDM2NDI0MDB9
```

## How to add it to Railway:

1. Go to Railway Dashboard
2. Select your Backend Service
3. Go to Settings → Variables
4. Click "New Variable"
5. Add:
   - **Variable name**: `JWT_SECRET`
   - **Value**: Paste the secret above
6. Click "Add"
7. Deploy

---

If you want to generate a fresh one using the Laravel command after setting up Railway CLI properly, run:
```bash
railway login  # Complete browser authentication
railway run php artisan jwt:secret --show
```

Then update the variable with the new secret.
