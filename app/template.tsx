/* Re-mounts on every page change → the page content fades in smoothly. */
export default function Template({ children }: { children: React.ReactNode }) {
  return <div className="hc-page">{children}</div>;
}
