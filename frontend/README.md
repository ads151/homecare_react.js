# Home Care — Next.js website (frontend)

The public website. All text, photos, pages, services, SEO and settings come
from the Laravel admin panel (backend) through its JSON API.

- Next.js 16 (App Router) + React 19, TypeScript
- Same design as the original site (`app/site.css`, `public/assets/site/main.js`)
- Pages are rendered on request; content is cached and refreshed automatically
  when something is saved in the admin (`/api/revalidate`)
- Enquiry forms post to `/api/enquiry`, which passes them to the backend

## Settings (.env)

```
BACKEND_URL=https://admin.yourdomain.com
API_SECRET=<same as FRONTEND_SECRET in the backend>
```

Admin → Site Settings → **Website (Next.js)** shows both values.

## Run

```bash
npm install
npm run build
npm start
```

## Where things are

| Path | What |
|---|---|
| `app/page.tsx`, `app/[slug]/page.tsx` | Every page (from Admin → Pages) |
| `components/Templates.tsx` | Page designs: Home, About, Contact, Services, Legal, Thank You, Page Builder |
| `components/Blocks.tsx` | Page Builder sections |
| `components/Shell.tsx` | Header, footer, popup, floating buttons, mobile bar |
| `components/Sections.tsx` | Cards, FAQ, testimonials, stats… |
| `components/EnquiryForm.tsx` | Enquiry form |
| `lib/api.ts` | Calls to the backend API |
| `app/api/enquiry`, `app/api/revalidate` | Form submit, cache refresh |
