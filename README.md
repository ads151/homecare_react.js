# Home Care — Next.js website + Laravel admin

| Folder | What | Runs on |
|---|---|---|
| `frontend/` | Next.js 16 website (what visitors see) | Hostinger Node.js App → `yourdomain.com` |
| `backend/` | Laravel 13 + Filament 5 admin panel and JSON API | Hostinger PHP → `admin.yourdomain.com` |

Work only on the **main** branch. On every push, GitHub Actions publishes:

- `deploy-backend` — Laravel with `vendor/` (no SSH / composer needed on the server)
- `deploy-frontend` — the Next.js app (Hostinger runs `npm install` + `npm run build`)

Connect Hostinger to these two branches. Secrets (`.env`), the database and uploaded photos are never in git.

Setup guide (Hinglish): [INSTALL-HOSTINGER-NEXTJS.txt](INSTALL-HOSTINGER-NEXTJS.txt)

## Local development

```bash
# backend
cd backend && composer install && cp .env.example .env && php artisan key:generate && php artisan serve

# frontend (.env.local: BACKEND_URL + API_SECRET)
cd frontend && npm install && npm run dev
```
