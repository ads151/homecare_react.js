import type { NextConfig } from "next";

const BACKEND = (process.env.BACKEND_URL || "http://localhost:8000").replace(/\/+$/, "");

const nextConfig: NextConfig = {
  poweredByHeader: false,
  async rewrites() {
    // Photos uploaded in the admin are served from the backend, under this website's address
    return [
      { source: "/images/:path*", destination: `${BACKEND}/images/:path*` },
      { source: "/uploads/:path*", destination: `${BACKEND}/uploads/:path*` },
    ];
  },
  async redirects() {
    // yourdomain.com/admin → admin panel
    return [
      { source: "/admin", destination: `${BACKEND}/admin`, permanent: false },
      { source: "/admin/:path*", destination: `${BACKEND}/admin/:path*`, permanent: false },
    ];
  },
};

export default nextConfig;
