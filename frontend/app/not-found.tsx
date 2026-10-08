import { connection } from "next/server";
import { NotFound } from "@/components/Templates";
import { getSite } from "@/lib/api";
import { helpers } from "@/lib/site";

export const metadata = { title: "Page Not Found", robots: { index: false } };

export default async function NotFoundPage() {
  // Render on request (no backend call while building on the server)
  await connection();
  try {
    const site = await getSite();
    return <NotFound h={helpers(site)} />;
  } catch {
    return (
      <div style={{ fontFamily: "system-ui, Arial, sans-serif", textAlign: "center", padding: "80px 16px" }}>
        <h1>Page not found</h1>
        <p>
          <a href="/">Go to Home Page</a>
        </p>
      </div>
    );
  }
}
