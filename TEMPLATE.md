# Portfolio Site Template - Agent Reference

## Overview

This document serves as a comprehensive reference for creating modern, SEO-optimized portfolio websites using Astro + Tailwind CSS. It contains all the patterns, conventions, and configurations needed to build sites similar to the DevIury portfolio.

## Tech Stack

**Core:**
- Framework: Astro (static site generator)
- Styling: Tailwind CSS
- Language: TypeScript
- Deployment: Cloudflare Pages

**Key Dependencies:**
- `@astrojs/sitemap` - SEO sitemap generation
- `@astrojs/tailwind` - Tailwind integration
- `astro` - Core framework
- `tailwindcss` - CSS framework

## Project Structure Template

```
/
├── public/                    # Static assets
│   ├── fonts/[font-name]/    # Self-hosted fonts (woff2)
│   ├── *.webp                # Images (logo, favicon, portfolio)
│   ├── blog-*.svg            # Blog cover images
│   ├── og-image.svg          # Open Graph image
│   ├── robots.txt            # AI bot blocking rules
│   └── google*.html          # Google Search Console verification
├── src/
│   ├── components/           # Reusable UI components
│   │   ├── Header.astro      # Navigation + sidebar + focus trap
│   │   ├── Footer.astro      # Footer with social links
│   │   ├── FloatingWhatsApp.astro  # WhatsApp button (visible prop)
│   │   ├── CtaSection.astro  # Shared CTA section
│   │   ├── WhatsAppIcon.astro # Reusable SVG icon
│   │   ├── OptimizedImage.astro # Image component with lazy loading
│   │   └── Analytics.astro   # GA4 event tracking
│   ├── layouts/
│   │   └── Layout.astro      # Base layout with SEO, JSON-LD, ViewTransitions
│   ├── pages/
│   │   ├── index.astro       # Main homepage
│   │   ├── blog.astro        # Blog listing page
│   │   ├── blog/[slug].astro # Blog post template
│   │   ├── en/               # English pages
│   │   ├── *.astro           # Landing pages and service pages
│   │   └── api/pexels.ts     # Pexels API proxy (API key in .env)
│   ├── content/
│   │   └── blog/             # Markdown blog posts
│   └── content.config.ts     # Content collection config
├── docs/                     # Documentation
│   ├── backlinks-diretorios.md # Brazilian directories for backlinks
│   └── google-meu-negocio.md # Google Business Profile setup guide
├── astro.config.mjs          # Astro configuration
└── package.json
```

## Configuration Template

### astro.config.mjs
```javascript
import { defineConfig } from 'astro/config';
import tailwind from '@astrojs/tailwind';
import sitemap from '@astrojs/sitemap';

export default defineConfig({
  site: 'https://your-domain.pages.dev', // Temp until domain purchase
  // site: 'https://your-domain.com.br', // After domain purchase
  compressHTML: true,
  integrations: [tailwind(), sitemap()],
});
```

### .env (gitignored)
```
PEXELS_API_KEY=your_key_here
```

### Layout.astro Props
```typescript
interface Props {
  title: string;
  description: string;
  ogImage?: string;
  ogType?: string;
  lang?: string;
}

const {
  title,
  description,
  ogImage = '/og-image.svg',
  ogType = 'website',
  lang = 'pt-BR'
} = Astro.props;
```

## Design System

### Color Palette
```javascript
// Tailwind config colors
colors: {
  dark: '#0a0a0a',        // Dark background
  primary: '#00E676',     // Green accent
  gradient: {
    start: '#00C853',
    middle: '#00E676',
    end: '#69F0AE'
  }
}
```

### Typography
- Primary Font: Inter (self-hosted, weights 400-800)
- Mobile Breakpoint: 768px
- Blog Line Height: 1.9

### Key CSS Classes
```css
/* Mobile visibility */
.hero-only { display: block; }
@media (min-width: 768px) { .hero-only { display: none; } }

/* Testimonial avatar */
.testimonial-avatar { /* initials styling */ }

/* Scroll animations */
.scroll-reveal { /* reveal animations */ }

/* Mobile CTA bar */
.mobile-cta-bar { /* fixed bottom bar */ }

/* Screen reader only */
.sr-only { /* accessible hidden content */ }
```

## Component Patterns

### FloatingWhatsApp.astro
```astro
---
interface Props {
  visible?: boolean;
}

const { visible = true } = Astro.props;
---

<a
  href="https://wa.me/[PHONE_NUMBER]"
  class:list={[
    'fixed z-50',
    !visible && 'hero-only' // Hide on mobile when not visible
  ]}
  target="_blank"
  rel="noopener noreferrer"
>
  <!-- WhatsApp icon -->
</a>
```

### OptimizedImage.astro
```astro
---
interface Props {
  src: string;
  alt: string;
  width: number;
  height: number;
  loading?: 'lazy' | 'eager';
  fetchpriority?: 'high' | 'low' | 'auto';
  decoding?: 'async' | 'sync' | 'auto';
}

const {
  src,
  alt,
  width,
  height,
  loading = 'lazy',
  fetchpriority = 'auto',
  decoding = 'async'
} = Astro.props;
---

<img
  {src}
  {alt}
  {width}
  {height}
  {loading}
  {fetchpriority}
  {decoding}
  class="w-full h-auto"
/>
```

### Analytics.astro
```astro
---
// Cookie consent check
const hasConsent =Astro.cookies.get('cookie-consent')?.boolean();
---

{hasConsent && (
  <script>
    // Google Analytics initialization
    // Event tracking functions
  </script>
)}
```

## SEO Template

### Layout.astro Head
```astro
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{title}</title>
  <meta name="description" content={description} />

  <!-- Open Graph -->
  <meta property="og:title" content={title} />
  <meta property="og:description" content={description} />
  <meta property="og:image" content={ogImage} />
  <meta property="og:type" content={ogType} />

  <!-- Geo Meta Tags -->
  <meta name="geo.region" content="BR-MG" />
  <meta name="geo.placename" content="Curvelo" />

  <!-- Canonical -->
  <link rel="canonical" href={Astro.url.href} />

  <!-- JSON-LD Schema -->
  <script type="application/ld+json" set:html={JSON.stringify({
    "@context": "https://schema.org",
    "@type": "ProfessionalService",
    "name": "Your Business Name",
    "description": description,
    "url": Astro.url.href,
    "logo": "/logo.webp",
    "sameAs": [
      "https://www.instagram.com/your-profile/",
      "https://www.linkedin.com/in/your-profile/"
    ],
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "Curvelo",
      "addressRegion": "MG",
      "addressCountry": "BR"
    },
    "aggregateRating": {
      "@type": "AggregateRating",
      "ratingValue": "5.0",
      "reviewCount": "8"
    }
  })} />
</head>
```

## Performance Patterns

### Image Optimization
- Convert all images to WebP format
- Use `fetchpriority="high"` on hero images
- Implement lazy loading with `loading="lazy"`
- Always include `width` and `height` attributes

### Font Optimization
- Self-host fonts in `/public/fonts/`
- Use `woff2` format
- Include multiple weights (400-800)
- Use `font-display: swap` in @font-face

### Security
- Add `rel="noopener noreferrer"` to all external links
- Implement AI bot blocking in robots.txt
- Add cookie consent banner for LGPD

## Content Structure

### Blog Posts
```markdown
---
title: "Post Title"
description: "Post description with CTA"
pubDate: 2024-01-15
author: "Author Name"
tags: ["tag1", "tag2", "tag3"]
---

Content here with 4000+ characters...
```

### Portfolio Items
- Real projects: No badge
- New projects: Green badge with "Novo"
- Prototypes: Orange badge with "Protótipo"

### Landing Pages
- City-specific pages: `service-city-name.astro`
- Service pages: `service-type.astro`
- Educational content: `guide-topic.astro`

## Deployment

### Cloudflare Pages Setup
1. Push to main branch
2. Auto-deploys to Cloudflare Pages
3. Submit sitemap to Google Search Console

### Domain Migration
1. Purchase domain
2. Update `site` in astro.config.mjs
3. Update canonical URLs
4. Update sitemap
5. Update analytics
6. Update all internal references

## Development Commands

```bash
# Start dev server
npm run dev

# Build for production
npm run build

# Preview production build
npm run preview

# Check for broken links
npm run build  # (sitemap will show all pages)
```

## Checklist for New Projects

- [ ] Set up Astro project with Tailwind
- [ ] Configure astro.config.mjs
- [ ] Create Layout.astro with SEO
- [ ] Build Header component with mobile menu
- [ ] Build Footer component
- [ ] Create FloatingWhatsApp component
- [ ] Implement Analytics component
- [ ] Create OptimizedImage component
- [ ] Set up blog system with content collections
- [ ] Create portfolio/projects section
- [ ] Build landing pages for target cities
- [ ] Implement SEO meta tags and JSON-LD
- [ ] Add cookie consent for LGPD
- [ ] Configure Google Analytics
- [ ] Set up Google Search Console
- [ ] Create robots.txt
- [ ] Generate sitemap
- [ ] Test on mobile devices
- [ ] Optimize images to WebP
- [ ] Self-host fonts
- [ ] Deploy to Cloudflare Pages

## Common Patterns to Reuse

1. **Mobile-first responsive design** with 768px breakpoint
2. **Focus trap** for sidebar navigation
3. **Scroll reveal animations** with IntersectionObserver
4. **ViewTransitions** with `astro:page-load` re-initialization
5. **Cookie consent** gating for analytics
6. **WhatsApp integration** with mobile visibility control
7. **JSON-LD schema** for SEO
8. **Self-hosted fonts** for performance
9. **WebP images** with lazy loading
10. **AI bot blocking** in robots.txt
