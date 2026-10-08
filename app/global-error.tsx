"use client";

/* Shown only if the whole site cannot load (for example the admin / API is down). */
export default function GlobalError({ reset }: { reset: () => void }) {
  return (
    <html lang="en-IN">
      <body style={{ fontFamily: "system-ui, Arial, sans-serif", margin: 0 }}>
        <div style={{ maxWidth: 560, margin: "80px auto", padding: 24, textAlign: "center" }}>
          <h1 style={{ fontSize: "1.4rem", color: "#0f2a52" }}>Website is updating</h1>
          <p style={{ color: "#5b6b7b" }}>Please try again in a moment.</p>
          <button onClick={() => reset()} style={{ marginTop: 12, padding: "12px 22px", borderRadius: 30, border: 0, background: "#2c9a55", color: "#fff", fontWeight: 700, cursor: "pointer" }}>
            Try again
          </button>
        </div>
      </body>
    </html>
  );
}
