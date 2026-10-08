import Image from "next/image";
import { img } from "@/lib/site";

/**
 * Photo from the admin. Website photos are resized and converted to WebP by
 * Next.js (faster pages, better Google speed score). Outside links stay as they are.
 */
export default function Img({ src, alt, width, height, sizes, className, priority = false }: { src: unknown; alt: string; width: number; height: number; sizes?: string; className?: string; priority?: boolean }) {
  const s = img(src);
  if (!s) return null;
  if (!/^\/(images|uploads)\//.test(s)) {
    return <img src={s} alt={alt} width={width} height={height} className={className} loading={priority ? undefined : "lazy"} decoding="async" />;
  }
  return <Image src={s} alt={alt} width={width} height={height} sizes={sizes} className={className} priority={priority} />;
}
