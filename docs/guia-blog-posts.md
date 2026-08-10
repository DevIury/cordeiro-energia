# Guia de Escrita e Formatação — Blog DevIury

Este guia documenta os padrões de escrita e formatação utilizados nos posts do blog. Todo novo post deve seguir esses padrões para manter consistência.

---

## 1. Estrutura do Frontmatter

```yaml
---
title: "Título do Post com Palavra-Chave Principal"
description: "Descrição persuasiva de 1-2 frases que inclui a palavra-chave e a localização."
date: 2026-07-10
image: "/blog-nome-do-post.svg"
tags: ["palavra-chave-1", "palavra-chave-2", "palavra-chave-3"]
author: "DevIury"
---
```

| Campo | Obrigatório | Formato |
|-------|-------------|---------|
| `title` | Sim | String, inclui palavra-chave principal |
| `description` | Sim | 1-2 frases persuasivas, termina com "!" ou ". " |
| `date` | Sim | `YYYY-MM-DD` |
| `image` | Sim | `"/blog-nome-do-post.svg"` (SVG simplificado, escuro, com título) |
| `tags` | Sim | 2-4 strings em minúsculas |
| `author` | Opcional | `"DevIury"` |

---

## 2. Estrutura do Artigo

```
[Introdução — 1-3 parágrafos]

## Primeiro tópico
[2-3 parágrafos]

## Segundo tópico
[2-3 parágrafos]

## Terceiro tópico
[2-3 parágrafos]

## Quarto tópico
[2-3 parágrafos]

## Quinto+ tópicos (conforme necessário)
[2-3 parágrafos cada]

## Conclusão
[1-3 parágrafos resumindo + CTA com link]

---

**Leia também:**
- [Post relacionado 1](/blog/slug-1)
- [Post relacionado 2](/blog/slug-2)
- [Post relacionado 3](/blog/slug-3)
```

### Regras de estrutura
- **H1:** Nunca usar no corpo. O `Layout.astro` gera o H1 a partir do `title` do frontmatter.
- **H2 (`##`):** 5-8 seções por post. Usar sentence case (apenas primeira palavra maiúscula).
- **H3 (`###`):** Raramente usar. Apenas para sub-tópicos dentro de um H2 (ex: listar páginas de um site).
- **Título "Conclusão":** Todo post termina com `## Conclusão`.

---

## 3. Tamanho do Artigo

| Métrica | Valor |
|---------|-------|
| Mínimo de caracteres | 4.000 |
| Faixa típica | 4.500 — 7.500 caracteres |
| Média | ~6.100 caracteres |
| Parágrafos | 2-4 frases cada (sem blocos densos) |

---

## 4. Tom e Voz

- **Profissional mas acessível** — consultivo, nunca agressivo.
- **Primeira pessoa** no parágrafo introdutório: "vou explicar", "vou compartilhar".
- **Segunda pessoa** no corpo do texto: "você", "sua empresa", "seu negócio".
- **Listas de três** são frequentes (tricolons): "rápido, otimizado e para SEO local".

### Regras de escrita
- Frases curtas a médias (15-30 palavras).
- Usar ponto e vírgula para conectar cláusulas relacionadas.
- Perguntas retóricas ocasionalmente: "Seu site está funcionando, mas não gera clientes?".
- Nunca usar emojis no corpo do texto.
- Nunca usar bold ou itálico no corpo (após o frontmatter).

---

## 5. Links Internos

### Links inline no corpo
- Inserir 1-3 links por post dentro do texto.
- Usar caminhos relativos (nunca domínio completo).
- Anchor text descritivo, nunca a URL.
- Apontar para: outros posts (`/blog/*`), páginas de serviço (`/servico-*`), landing pages (`/landing-page-*`), páginas de cidade (`/criacao-de-sites-*`).

```markdown
[SEO local](/blog/seo-local-curvelo)
[criação de sites em Curvelo](/criacao-de-sites-curvelo)
[site com WhatsApp integrado](/servico-de-site-para-whatsapp)
```

### Seção "Leia também"
- Sempre no final, após uma linha horizontal (`---`).
- **Exatamente 3 links** para outros posts do blog.
- Formato:

```markdown
---

**Leia também:**
- [Quanto custa um site profissional em Curvelo?](/blog/quanto-custa-site-profissional)
- [Landing Page x Site Institucional: qual escolher?](/blog/landing-page-site-institucional)
- [SEO Local: como aparecer no Google em Curvelo](/blog/seo-local-curvelo)
```

---

## 6. CTAs e WhatsApp

### CTAs no texto
- **Nunca** incluir links `wa.me` ou `api.whatsapp.com` no markdown.
- O WhatsApp é tratado pelo componente `FloatingWhatsApp.astro`.
- Links de serviço aparecem como texto contextual: `[botão de WhatsApp no site](/servico-de-site-para-whatsapp)`.

### CTA na conclusão
Todo post termina com uma frase CTA na conclusão:

```
Quer saber mais sobre [criação de sites em Curvelo](/criacao-de-sites-curvelo)?
Entre em contato e descubra como posso ajudar sua empresa a crescer no digital.
```

Variações:
- "Quer saber mais sobre [X]? Entre em contato..."
- "Precisa de ajuda com [X]? Entre em contato..."
- "Conheça nosso [serviço de X]..."
- "Solicite um [orçamento para X]..."
- "Saiba mais sobre [X] e comece..."

---

## 7. SEO e Palavras-Chave

### Onde inserir a palavra-chave principal
1. **Título** (`title` do frontmatter)
2. **Descrição** (`description` do frontmatter)
3. **Primeira frase** da introdução
4. **Títulos H2** (naturalmente)
5. **Corpo do texto** (3-6 vezes por post, de forma natural)
6. **Conclusão**

### Integração local
- Incluir o nome da cidade na introdução, H2s (quando relevante), corpo e conclusão.
- Usar perguntas que o usuário pesquisaria como títulos H2:
  - "Quanto custa um site profissional?"
  - "Como escolher o melhor web designer em Curvelo?"
  - "Por que um site lento faz você perder clientes?"

### Regras
- Nunca forçar palavras-chave (keyword stuffing).
- Incluir termos semânticos naturalmente:
  - "site profissional" → credibilidade, presença digital, conversão, clientes
  - "SEO local" → Google Meu Negócio, avaliações, palavras-chave, buscas locais
  - "web designer" → portfólio, experiência, processo de trabalho, orçamento

---

## 8. Formatação

| Elemento | Uso |
|----------|-----|
| **Bold (`**`)** | Apenas no header "Leia também:" |
| *Itálico (`*`)* | Não usar |
| Listas com `-` | Sparingly: comparações, features, passos |
| Listas numeradas | Fazer via títulos H2: `## 1. Tópico` (não usar `1.` no markdown) |
| Blockquotes (`>`) | Não usar |
| Imagens inline | Não usar (tratado via frontmatter `image`) |
| Emojis | Não usar |
| Code blocks | Não usar |
| Tabelas | Não usar |
| Linha horizontal (`---`) | Uma vez por post: antes da seção "Leia também" |

---

## 9. Imagens de Capa (SVG)

- Formato: SVG simplificado, fundo escuro (`#0a0a0a`), texto verde (`#00E676`).
- Mostrar apenas o título do post (não usar fotos).
- Salvar em `/public/blog-nome-do-post.svg`.
- Referenciar no frontmatter: `image: "/blog-nome-do-post.svg"`.

---

## 10. Checklist para Novos Posts

- [ ] Frontmatter completo (title, description, date, image, tags)
- [ ] Mínimo 4.000 caracteres
- [ ] 5-8 seções H2 + Conclusão
- [ ] Palavra-chave principal no título, descrição, introdução e conclusão
- [ ] 1-3 links internos inline no corpo
- [ ] 3 links na seção "Leia também"
- [ ] CTA na conclusão com link para página de serviço
- [ ] Tom consultivo, segunda pessoa
- [ ] Sem emojis, bold, itálico ou blockquotes no corpo
- [ ] SVG de capa criado e referenciado
- [ ] Tags (2-4) em minúsculas
