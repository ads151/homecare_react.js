import Script from "next/script";
import "./site.css";

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html lang="en-IN">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        {/* eslint-disable-next-line @next/next/no-page-custom-font */}
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800&family=Nunito+Sans:wght@400;600;700&display=swap" rel="stylesheet" />
      </head>
      <body>
        {children}
        {/* Original website script: menu, popup, forms, filters, click tracking */}
        <Script src="/assets/site/main.js" strategy="afterInteractive" />
      </body>
    </html>
  );
}
