import type { NextConfig } from "next";

const BACKEND = (process.env.BACKEND_URL || "http://localhost:8000").replace(/\/+$/, "");

const nextConfig: NextConfig = {
  poweredByHeader: false,

  images: {
    // Photos from the admin are resized + converted to WebP, then cached for 30 days
    formats: ["image/webp"],
    minimumCacheTTL: 2592000,
    localPatterns: [{ pathname: "/images/**" }, { pathname: "/uploads/**" }],
  },

  async rewrites() {
    return {
      // The home page is the admin page with the slug "home" (cached like every page)
      beforeFiles: [{ source: "/", destination: "/home" }],
      // Photos uploaded in the admin are served from the backend under this website's address
      afterFiles: [
        { source: "/images/:path*", destination: `${BACKEND}/images/:path*` },
        { source: "/uploads/:path*", destination: `${BACKEND}/uploads/:path*` },
      ],
    };
  },

  async redirects() {
    return [
      { source: "/home", destination: "/", permanent: true },
      { source: "/index", destination: "/", permanent: true },
      // yourdomain.com/admin → admin panel
      { source: "/admin", destination: `${BACKEND}/admin`, permanent: false },
      { source: "/admin/:path*", destination: `${BACKEND}/admin/:path*`, permanent: false },
    ];
  },
};

export default nextConfig;
