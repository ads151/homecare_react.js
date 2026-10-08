import { NotFound } from "@/components/Templates";
import { getSite } from "@/lib/api";
import { helpers } from "@/lib/site";

export const metadata = { title: "Page Not Found", robots: { index: false } };

export default async function NotFoundPage() {
  try {
    const site = await getSite();
    return <NotFound h={helpers(site)} />;
  } catch {
    return (
      <section className="sec">
        <div className="wrap narrow" style={{ textAlign: "center" }}>
          <h1>Page not found</h1>
          <p>
            <a href="/">Go to Home Page</a>
          </p>
        </div>
      </section>
    );
  }
}
