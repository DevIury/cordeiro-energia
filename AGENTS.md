# Cordeiro Energia - Agent Guide

## Project Overview

Modern, SEO-optimized website for a solar energy company in Brazil. Focuses on lead generation through WhatsApp and contact forms, with comprehensive local SEO and conversion optimization.

**Primary Goals:**
- Showcase solar energy solutions and services
- Generate leads via WhatsApp and contact forms
- Dominate local SEO for "energia solar" + city names
- Build authority through educational content
- Provide bilingual support (Portuguese/English)

## Tech Stack & Dependencies

**Framework:** Astro (static site generator)
**Styling:** Tailwind CSS (utility-first CSS framework)
**Language:** TypeScript
**Deployment:** Cloudflare Pages
**Repository:** [REPOSITORY_URL]

**Key Dependencies:**
- `@astrojs/sitemap` - Sitemap generation
- `@astrojs/tailwind` - Tailwind CSS integration
- `astro` - Core framework
- `tailwindcss` - CSS framework

## Project Structure

```
/
├── public/                    # Static assets
│   ├── fonts/inter/          # Self-hosted Inter font (woff2)
│   ├── *.webp                # Images (logo, favicon, projects)
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

## Important Configuration

**Site Configuration (astro.config.mjs):**
```javascript
site: 'https://cordeiro-energia.pages.dev'  // Temp until domain purchase
// site: 'https://cordeiroenergia.com.br'  // After domain purchase
compressHTML: true,
integrations: [tailwind(), sitemap()]
```

**Environment Variables (.env - gitignored):**
```
PEXELS_API_KEY=your_key_here
```

**Layout.astro Props:**
- `title` - Page title
- `description` - Meta description
- `ogImage` - Open Graph image (default: og-image.svg)
- `ogType` - Open Graph type (default: website)
- `lang` - Language (default: pt-BR)

## Content Structure

**Blog Posts (target: 10+ total):**
- Location: `src/content/blog/*.md`
- Required frontmatter: title, description, pubDate, author, tags
- Cover images: `/public/blog-*.svg`
- All posts 4000+ chars, Brazilian Portuguese
- Internal linking strategy implemented

**Portfolio/Solutions (target: 8+ total):**
- Real projects when available
- Mark completed projects with green badge
- Mark prototypes with orange badge

**Landing Pages (target: 30+ total):**
- City pages: `energia-solar-*.astro`
- Service pages: `servico-*.astro`
- Educational pages: `guia-*.astro`

## Design System

**Color Palette:**
- Dark background: `#0a0a0a`
- Solar accent: `#FFC107` (amber/solar yellow)
- Green accent: `#00E676` (for eco/sustainability)
- Gradient buttons: `#FF8F00` → `#FFC107` → `#FFD54F`

**Typography:**
- Primary: Inter (self-hosted, weights 400-800)
- Mobile breakpoint: 768px
- Line height: 1.9 for blog content

**Components:**
- `.hero-only` class for mobile visibility
- `.testimonial-avatar` for initials
- `.scroll-reveal` for animations
- `.mobile-cta-bar` fixed bottom bar (CSS, not JS)

## SEO & Analytics

**Meta Tags:**
- Dynamic OG tags in Layout.astro
- Geo meta tags for target cities
- Canonical URL structure

**JSON-LD Schema:**
- Organization + LocalBusiness
- Founder with sameAs (WhatsApp, Instagram, LinkedIn)
- AggregateRating (5.0 reviews)
- areaServed (target cities)
- FAQPage schema for FAQ sections
- Service schema for each solar solution

**Analytics:**
- Google Analytics ID: [TO_BE_CONFIGURED]
- Cookie consent gated for LGPD compliance
- Events tracked: whatsapp_click, cta_click, phone_click, email_click, project_view, faq_open, scroll_depth, time_on_page

**Google Services:**
- Search Console: [TO_BE_CONFIGURED]
- Business Profile: [TO_BE_CONFIGURED]
- Sitemap submission: [TO_BE_CONFIGURED]

## Performance & Security

**Images:**
- All images converted to WebP format
- `fetchpriority="high"` on hero images
- Lazy loading implemented
- `width` and `height` attributes on all images

**Fonts:**
- Self-hosted Inter from `/public/fonts/inter/`
- No external font requests

**Security:**
- `rel="noopener noreferrer"` on all `target="_blank"` links
- AI bot blocking in robots.txt (GPTBot, ChatGPT-User, CCBot, ClaudeBot, anthropic-ai)
- Cookie consent banner for LGPD

**View Transitions:**
- Astro ViewTransitions with fade CSS animations
- Scroll reveal re-initializes on `astro:page-load` event

## Important Patterns & Conventions

**Component Patterns:**
- FloatingWhatsApp uses `visible` prop for mobile control
- Analytics component gates GA behind cookie consent
- OptimizedImage component handles lazy loading and fetchpriority

**CSS Patterns:**
- Mobile-first responsive design
- Focus trap for sidebar navigation (Tab cycling, Escape key, focus restore)
- `prefers-reduced-motion` support
- `.sr-only` class for screen reader content

**JavaScript Patterns:**
- IntersectionObserver for scroll animations
- `astro:page-load` event for ViewTransitions re-initialization
- Cookie consent state management

## Deployment

**Current:** Cloudflare Pages (free domain: `cordeiro-energia.pages.dev`)
**Planned:** `cordeiroenergia.com.br` (to be purchased)

**Deploy Process:**
1. Push to main branch
2. Cloudflare Pages auto-deploys
3. Submit sitemap to Google Search Console

**Domain Migration:**
- All code references updated to `.pages.dev` temporarily
- Will need to update to `.com.br` after purchase
- Update canonical URLs, sitemap, analytics

## Known Issues & Workarounds

1. **Domain References:** Currently using placeholder - needs update after domain purchase
2. **OG Image:** Using SVG placeholder (`og-image.svg`)
3. **Mobile CTA Bar:** Watch for CSS conflicts (display: flex vs display: none in same media query)
4. **Blog Encoding:** Ensure UTF-8 encoding to avoid mojibake issues
5. **Scroll Reveal:** Use `astro:page-load` event listener for ViewTransitions

## Future Tasks

**High Priority:**
- Purchase domain and update all references
- Configure Google Search Console and Analytics
- Submit sitemap to Google Search Console
- Register in Brazilian directories per `docs/backlinks-diretorios.md`

**Medium Priority:**
- Create educational blog content about solar energy
- Build out landing pages for target cities
- Add more portfolio/project case studies

**Low Priority:**
- Advanced animations and effects
- A/B testing for conversion optimization
- Multi-language expansion beyond Portuguese/English

## Key Files for Maintenance

1. `src/layouts/Layout.astro` - Base layout, SEO, analytics
2. `src/components/Header.astro` - Navigation, mobile menu
3. `src/components/FloatingWhatsApp.astro` - WhatsApp button
4. `src/pages/index.astro` - Main homepage
5. `astro.config.mjs` - Site configuration
6. `src/content/blog/*.md` - Blog content
7. `public/robots.txt` - SEO bot rules
8. `.env` - API keys (gitignored)

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

## Important Notes

- **Always test on mobile** - 768px breakpoint is critical
- **Maintain Portuguese first** - English pages are secondary
- **Preserve existing SEO** - Don't break canonical URLs or schema
- **Keep WhatsApp visible on homepage** - It's the primary CTA
- **Blog internal linking** - Each post links to 3 related posts
- **Cookie consent required** - GA won't track without consent
- **SVG cover images** - Blog posts use simplified SVGs, not photos
