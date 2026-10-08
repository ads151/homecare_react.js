import { SiteFooter, SiteHeader, cssVars, tracking } from "@/components/Shell";
import RawCode from "@/components/RawCode";
import SiteBehaviors from "@/components/SiteBehaviors";
import { getSite } from "@/lib/api";
import { helpers } from "@/lib/site";
import "./site.css";

/* Header, footer, popup and floating buttons stay on screen while pages
   change (no reload) — only the content inside <main> is swapped. */
export default async function RootLayout({ children }: LayoutProps<"/">) {
  const h = helpers(await getSite());
  return (
    <html lang="en-IN">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        {/* eslint-disable-next-line @next/next/no-page-custom-font */}
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
        <style dangerouslySetInnerHTML={{ __html: cssVars(h) }} />
      </head>
      <body>
        <div className="hc-progress" aria-hidden="true" />
        <RawCode html={tracking(h, "head")} id="tc-head" />
        <RawCode html={tracking(h, "bodystart")} id="tc-bs" />
        <SiteHeader h={h} />
        <main id="main">{children}</main>
        <SiteFooter h={h} />
        <RawCode html={tracking(h, "bodyend")} id="tc-be" />
        <SiteBehaviors />
      </body>
    </html>
  );
}
