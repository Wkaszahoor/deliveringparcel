<?php

namespace App\Services\Seo;

use App\Models\CmsPost;
use Illuminate\Support\Str;

/**
 * SEO / Content Intelligence Engine (v2).
 *
 * Analyzes content on two levels:
 *  1. FIELD level  — the editable fields (meta title/description, slug, content…)
 *  2. RENDERED level — fetches the public URL (via UrlFetcher) and inspects
 *     the real HTML (title tag, meta description, canonical, robots, JSON-LD).
 *
 * Two entry points feed ONE shared pipeline:
 *  - analyzePost(CmsPost)  — CMS edit panel (analyze() kept as BC alias)
 *  - analyzeHtml(array)    — content/URL mode: raw fields + optional fetched
 *                            page (UrlFetcher result) or raw 'content_html'
 *
 * Every finding carries a 'category' and a 'severity':
 *  critical       — real findability/technical problem          (−20 in its category)
 *  recommendation — established SEO practice worth doing        (−8  in its category)
 *  heuristic      — our own tool's observation, advisory only   (−3  in its category)
 *  pass           — good signal present                         ( +0 )
 *  info           — informational only, never affects scores    ( +0 )
 *
 * Scoring v2: availability-aware and weighted. Categories that could not be
 * measured (e.g. 'Technical SEO' when the rendered page is unreachable) are
 * reported as 'unavailable' and EXCLUDED from the weighted overall score.
 */
class SeoAnalyzerService
{
    /** Site URLs worth internal-linking from blog content (config seo-shield.internal_topic_map is merged over this). */
    protected array $internalTargets = [
        '/shipper-program'   => ['shipper', 'ship for me', 'become a shipper', 'earn'],
        '/services'          => ['service', 'shipping assistance', 'assisted shopping'],
        '/track-order'       => ['track', 'tracking', 'where is my parcel'],
        '/freequote'         => ['quote', 'cost', 'price', 'how much'],
        '/contact-details'   => ['contact', 'support', 'help'],
        '/testimonials'      => ['review', 'testimonial', 'trust'],
        '/blog'              => ['guide', 'blog', 'read more'],
    ];

    protected array $fillerPhrases = [
        "in today's digital world", 'in the fast-paced world', 'in the modern world',
        'look no further', 'unlock the', 'in this article, we will', 'it is important to note',
        'when it comes to', 'at the end of the day', 'in conclusion,',
        'plays a vital role', 'plays a crucial role', 'ever-evolving world',
    ];

    /**
     * Category weights. Listed weights sum to 0.95, so the remainder (0.05)
     * is folded into 'Content Quality' to make the sum exactly 1.0. The
     * overall score is normalized by the weight sum of non-null categories,
     * so availability never skews the result.
     */
    protected array $weights = [
        'Content Quality' => 0.25,
        'On-Page & Meta'  => 0.15,
        'Technical SEO'   => 0.15,
        'Links'           => 0.08,
        'Images'          => 0.05,
        'Trust & Schema'  => 0.12,
        'Spam Shield'     => 0.10,
        'Search Intent'   => 0.05,
        'Originality'     => 0.05,
    ];

    /* ================= public API ================= */

    /** Backward-compatible alias — the CMS edit panel endpoint calls analyze(). */
    public function analyze(CmsPost $post): array
    {
        return $this->analyzePost($post);
    }

    /** Analyze a cms_post (blog post, service, page, product). */
    public function analyzePost(CmsPost $post): array
    {
        $post->loadMissing('author');

        $data = $this->normalizePost($post);
        $data['fetched'] = $this->fetchRendered($data);

        return $this->buildReport($data);
    }

    /**
     * Analyze raw content / a fetched URL (content & URL mode).
     *
     * $input keys: title, meta_title, meta_description, slug, content (required),
     * topic, fetched (UrlFetcher result with html/status/final_url/ttfb_ms/size_bytes),
     * content_html (optional alternative to fetched), featured_image (optional).
     */
    public function analyzeHtml(array $input): array
    {
        return $this->buildReport($this->normalizeInput($input));
    }

    /** Proxy to the hardened fetcher. */
    public function fetchUrl(string $url): array
    {
        return app(UrlFetcher::class)->fetch($url);
    }

    /* ================= normalization (shared context) ================= */

    /**
     * Map a CmsPost onto the normalized $data shape both modes share.
     * mode: 'post' — full trust/date checks apply.
     */
    protected function normalizePost(CmsPost $post): array
    {
        return [
            'mode'             => 'post',
            'post_id'          => $post->id,
            'post_type'        => (string) $post->post_type,
            'status'           => (string) $post->status,
            'title'            => (string) $post->title,
            'meta_title'       => (string) $post->meta_title,
            'meta_description' => (string) $post->meta_description,
            'slug'             => (string) $post->slug,
            'content'          => (string) $post->content,
            'topic'            => $post->meta_keywords ? trim(explode(',', (string) $post->meta_keywords)[0]) : '',
            'author_name'      => $post->author?->name,
            'has_author'       => $post->author !== null && (int) $post->author_id > 0,
            'published_at'     => $post->published_at,
            'updated_at'       => $post->updated_at,
            'featured_image'   => (string) ($post->featured_image ?? ''),
            'fetched'          => null,
            'content_html'     => null,
        ];
    }

    /**
     * Map raw analyzeHtml() input onto the same normalized shape.
     * mode: 'html' — author/date findings become severity 'info'.
     */
    protected function normalizeInput(array $input): array
    {
        $title = trim((string) ($input['title'] ?? ''));

        return [
            'mode'             => 'html',
            'post_id'          => null,
            'post_type'        => 'html_analysis',
            'status'           => 'published',
            'title'            => $title,
            'meta_title'       => trim((string) ($input['meta_title'] ?? '')),
            'meta_description' => trim((string) ($input['meta_description'] ?? '')),
            'slug'             => trim((string) ($input['slug'] ?? '')),
            'content'          => (string) ($input['content'] ?? ''),
            'topic'            => trim((string) ($input['topic'] ?? '')),
            'author_name'      => null,
            'has_author'       => false,
            'published_at'     => null,
            'updated_at'       => null,
            'featured_image'   => trim((string) ($input['featured_image'] ?? '')),
            'fetched'          => $input['fetched'] ?? null,
            'content_html'     => $input['content_html'] ?? null,
        ];
    }

    /* ================= shared pipeline ================= */

    protected function buildReport(array $data): array
    {
        $content = (string) $data['content'];
        $text = trim(strip_tags($content));
        $words = $text === '' ? 0 : str_word_count($text);

        $publicUrl = $data['mode'] === 'post' ? $this->publicUrl($data) : ($data['fetched']['final_url'] ?? null);

        $report = [
            'post' => [
                'id'    => $data['post_id'],
                'type'  => $data['post_type'],
                'title' => $data['title'],
                'slug'  => $data['slug'],
                'public_url' => $publicUrl,
            ],
            'score'      => 0,
            'grade'      => '',
            'categories' => [],
            'scores'     => [],
            'findings'   => [],
            'priority'   => ['critical' => 0, 'recommendation' => 0, 'heuristic' => 0, 'pass' => 0],
            'stats'      => [],
            'analyzer_version' => (string) config('seo-shield.analyzer_version', '2.0.0'),
            'unavailable' => [],
            'weights'     => $this->weights,
        ];

        $findings = [];

        if ($data['mode'] === 'post') {
            $this->checkDuplicateTitles((int) $data['post_id'], $data['title'], $data['meta_title'], $data['slug'], $findings);
        }

        // ---------------- FIELD-level checks ----------------
        $this->checkTitleMeta($data, $findings);
        $this->checkDescription($data, $findings);
        $this->checkSlug($data, $findings);
        $this->checkHeadings($content, $words, $findings);
        $this->checkTopic($data, $text, $findings);
        $this->checkLinks($content, $findings);
        $this->checkImages($data, $content, $findings);
        $this->checkTrust($data, $findings);
        $this->checkSpamShield($text, $content, $words, $findings);
        $fillerHits = $this->fillerHits($text);
        $this->checkOriginality($text, $fillerHits, $findings);

        // ---------------- RENDERED-level checks ----------------
        // 'content_html' is an alternative to a fetched page (no TTFB/status).
        $fetched = $data['fetched'] ?? null;
        if (!is_array($fetched) && !empty($data['content_html'])) {
            $fetched = [
                'ok' => true, 'url' => null, 'final_url' => null, 'status' => 200,
                'html' => (string) $data['content_html'], 'ttfb_ms' => null,
                'size_bytes' => strlen((string) $data['content_html']),
            ];
        }
        $fetchOk = is_array($fetched) && ($fetched['ok'] ?? false) === true;

        if ($fetchOk) {
            $this->checkRendered($fetched, $findings);

            $ttfb = $fetched['ttfb_ms'];
            $ttfbText = $ttfb !== null ? (int) $ttfb . ' ms' : 'n/a';
            $kb = (int) round((int) $fetched['size_bytes'] / 1024);
            $report['stats']['ttfb_ms'] = $ttfb !== null ? (int) $ttfb : null;
            $report['stats']['html_size_kb'] = $kb;
            $report['stats']['rendered_url'] = $fetched['final_url'];
            $report['stats']['http_status'] = $fetched['status'];
            $findings[] = $this->f('info', 'Fetch Stats',
                "Fetch stats: TTFB {$ttfbText}, HTML {$kb} KB (informational — no performance score is fabricated).",
                null, 'Technical SEO');
        } elseif ($data['mode'] === 'post' && $data['status'] === 'published') {
            $findings[] = $this->f('heuristic', 'Rendered page not reachable',
                'The public URL did not respond locally, so rendered-HTML checks (title tag, canonical, JSON-LD, noindex) were skipped.',
                'Publish/verify the page, then re-run the analysis.', 'Technical SEO');
        }

        // ---------------- theme-fallback intelligence ----------------
        // If the rendered page already outputs a working title/description
        // (theme falls back to the post title/excerpt), the empty FIELD is no
        // longer a critical issue — downgrade to a heuristic advisory.
        $renderedDescOk = collect($findings)->contains(fn ($x) => $x['title'] === 'Rendered Description' && $x['severity'] === 'pass');
        $renderedTitleOk = collect($findings)->contains(fn ($x) => $x['title'] === 'Rendered Title' && $x['severity'] === 'pass');
        foreach ($findings as &$f) {
            if ($f['severity'] === 'critical' && $f['title'] === 'Meta Description' && $renderedDescOk) {
                $f['severity'] = 'heuristic';
                $f['detail'] .= ' The rendered page DOES output a description tag (theme fallback, e.g. excerpt) — set the field to control it.';
            }
            if ($f['severity'] === 'critical' && $f['title'] === 'Meta Title' && $renderedTitleOk) {
                $f['severity'] = 'heuristic';
                $f['detail'] .= ' The rendered <title> looks fine (theme fallback) — set the field to control it.';
            }
        }
        unset($f);

        // ---------------- scoring + grouping ----------------
        $report['findings'] = $findings;
        $infoCount = 0;
        foreach ($findings as $f) {
            if (($f['severity'] ?? '') === 'info') {
                $infoCount++;
                continue;
            }
            if (isset($report['priority'][$f['severity']])) {
                $report['priority'][$f['severity']]++;
            }
        }
        if ($infoCount > 0) {
            $report['priority']['info'] = $infoCount;
        }

        // Availability-aware category scores ('scores' = numeric map only).
        $scores = [];
        foreach ($this->weights as $category => $weight) {
            $score = $this->categoryScore($category, $findings, $data, $text, $fetchOk);
            if ($score !== null) {
                $scores[$category] = $score;
            }
        }
        $report['scores'] = $scores;

        // 'categories' keeps the full map; null categories are surfaced as the
        // string 'unavailable' so the CMS panel never renders NaN bars.
        foreach ($this->weights as $category => $weight) {
            $report['categories'][$category] = $scores[$category] ?? 'unavailable';
        }
        $report['unavailable'] = array_values(array_diff(array_keys($this->weights), array_keys($scores)));

        // Weighted overall (null categories excluded from the average).
        $num = 0.0;
        $den = 0.0;
        foreach ($this->weights as $category => $weight) {
            if (!isset($scores[$category])) {
                continue;
            }
            $num += $scores[$category] * $weight;
            $den += $weight;
        }
        $report['score'] = $den > 0.0 ? max(0, min(100, (int) round($num / $den))) : 0;
        $report['grade'] = $this->grade($report['score']);

        $report['stats'] = array_merge($report['stats'], [
            'words'        => $words,
            'reading_time' => $words > 0 ? max(1, (int) ceil($words / 200)) . ' min' : '—',
            'h1'           => preg_match_all('/<h1[\s>]/i', $content),
            'h2'           => preg_match_all('/<h2[\s>]/i', $content),
            'internal_links' => $this->countLinks($content)['internal'],
            'external_links' => $this->countLinks($content)['external'],
            'mode'         => $data['mode'],
        ]);

        return $report;
    }

    /* ================= individual checks ================= */

    /** Titles must be unique site-wide — flag any other post with the same
     *  title, meta title or slug. */
    protected function checkDuplicateTitles(int $excludeId, string $title, string $metaTitle, string $slug, array &$findings): void
    {
        $q = \App\Models\CmsPost::query()->where('id', '!=', $excludeId)
            ->where(function ($w) use ($title, $metaTitle, $slug) {
                if ($title !== '') {
                    $w->orWhere('title', $title);
                }
                if ($metaTitle !== '') {
                    $w->orWhere('meta_title', $metaTitle);
                }
                if ($slug !== '') {
                    $w->orWhere('slug', $slug);
                }
            });

        $dupes = $q->get(['id', 'title', 'post_type', 'slug']);
        if ($dupes->isEmpty()) {
            $findings[] = $this->f('pass', 'Unique Title', 'Title, meta title and slug are unique across the site.', null, 'On-Page & Meta');
            return;
        }
        $list = $dupes->map(fn ($d) => "#{$d->id} {$d->title} (" . $d->post_type . ")")->implode('; ');
        $findings[] = $this->f('critical', 'Duplicate Title',
            'Another page already uses this title/meta title/slug: ' . $list,
            'Give this page a distinct title and slug — duplicate titles compete with each other in search.', 'On-Page & Meta');
    }

    protected function checkTitleMeta(array $data, array &$findings): void
    {
        $t = trim((string) $data['meta_title']);
        if ($t === '') {
            $findings[] = $this->f('critical', 'Meta Title', 'No meta title (SEO title) set — search engines will improvise one from the page.',
                'Add a clear, descriptive meta title in the SEO fields.', 'On-Page & Meta');
            return;
        }
        $len = mb_strlen($t);
        if ($len > 70) {
            $findings[] = $this->f('heuristic', 'Meta Title', "Title is {$len} characters. There is no fixed limit, but very long titles get rewritten in results.",
                'Keep it clear and concise — front-load the main topic.', 'On-Page & Meta');
        } else {
            $findings[] = $this->f('pass', 'Meta Title', "Meta title present ({$len} characters).", null, 'On-Page & Meta');
        }
    }

    protected function checkDescription(array $data, array &$findings): void
    {
        $d = trim((string) $data['meta_description']);
        if ($d === '') {
            $findings[] = $this->f('critical', 'Meta Description', 'No meta description — Google will generate a snippet from page text, which may be weak.',
                'Write a concise summary that gives users a reason to click.', 'On-Page & Meta');
            return;
        }
        $len = mb_strlen($d);
        if ($len > 165) {
            $findings[] = $this->f('heuristic', 'Meta Description', "Description is {$len} characters — likely to be truncated in results.",
                'Consider tightening it while keeping the message.', 'On-Page & Meta');
        } else {
            $findings[] = $this->f('pass', 'Meta Description', "Meta description present ({$len} characters).", null, 'On-Page & Meta');
        }
    }

    protected function checkSlug(array $data, array &$findings): void
    {
        $slug = (string) $data['slug'];
        if ($slug === '') {
            $findings[] = $this->f('critical', 'URL Slug', 'Slug is empty.', null, 'On-Page & Meta');
            return;
        }
        if (preg_match('/\d{4,}|-p-\d+|\?|=/i', $slug)) {
            $findings[] = $this->f('recommendation', 'URL Slug', "Slug \"{$slug}\" contains IDs/dates/parameters — descriptive slugs age better.",
                'Use short, human-readable words.', 'On-Page & Meta');
        } elseif (Str::length($slug) > 75) {
            $findings[] = $this->f('heuristic', 'URL Slug', "Slug is very long (" . Str::length($slug) . " chars).", 'Shorten to the essential topic words.', 'On-Page & Meta');
        } else {
            $findings[] = $this->f('pass', 'URL Slug', "Slug \"{$slug}\" is clean and descriptive.", null, 'On-Page & Meta');
        }
    }

    protected function checkHeadings(string $content, int $words, array &$findings): void
    {
        preg_match_all('/<h([1-6])[^>]*>(.*?)<\/h\1>/is', $content, $m, PREG_SET_ORDER);
        $h1 = count(array_filter($m, fn ($x) => $x[1] === '1'));
        $h2 = count(array_filter($m, fn ($x) => $x[1] === '2'));

        if ($h1 === 0) {
            $findings[] = $this->f('recommendation', 'Heading Structure', 'No H1 found in the content body (the theme may render the title as H1 — check the live page).', null, 'Content Quality');
        } elseif ($h1 > 1) {
            $findings[] = $this->f('recommendation', 'Heading Structure', "{$h1} H1 tags in the body — keep a single main heading per page.", null, 'Content Quality');
        }

        if ($h2 === 0 && $words > 300) {
            $findings[] = $this->f('recommendation', 'Heading Structure', 'Long content with no H2 subheadings — hard to scan for readers.', null, 'Content Quality');
        }

        // duplicate consecutive headings = repetitive structure
        $texts = array_map(fn ($x) => mb_strtolower(trim(strip_tags($x[2]))), $m);
        $dupes = 0;
        for ($i = 1; $i < count($texts); $i++) {
            if ($texts[$i] !== '' && $texts[$i] === $texts[$i - 1]) {
                $dupes++;
            }
        }
        if ($dupes > 0) {
            $findings[] = $this->f('critical', 'Heading Structure', "Repetitive heading structure — {$dupes} consecutive identical headings.", null, 'Content Quality');
        } elseif ($h2 > 0) {
            $findings[] = $this->f('pass', 'Heading Structure', "Logical hierarchy ({$h1} H1, {$h2} H2).", null, 'Content Quality');
        }
    }

    protected function checkTopic(array $data, string $text, array &$findings): void
    {
        $topic = $this->resolveTopic($data);
        if ($topic === '' || $text === '') {
            return;
        }
        $lower = mb_strtolower($text);
        $count = substr_count($lower, $topic);
        $total = str_word_count($text);
        $density = $total > 0 ? round(100 * str_word_count($topic) * $count / max(1, $total), 1) : 0.0;

        $inTitle = Str::contains(mb_strtolower(trim((string) ($data['meta_title'] ?: $data['title']))), $topic);
        $inSlug = Str::contains(str_replace('-', ' ', (string) $data['slug']), $topic);
        $first = mb_substr($lower, 0, 400);
        $inIntro = Str::contains($first, $topic);

        if ($total >= 150 && $density > 12) {
            $findings[] = $this->f('critical', 'Focus Topic', "Keyword stuffing risk: \"{$topic}\" density is {$density}% — unnatural repetition.", 'Rewrite for people first; use natural variations.', 'Content Quality');
        } elseif ($total >= 150 && $density > 8) {
            $findings[] = $this->f('recommendation', 'Focus Topic', "\"{$topic}\" density is {$density}% on a long page — check it reads naturally.", 'Vary the wording; write for people first.', 'Content Quality');
        } elseif ($inTitle && ($inIntro || $inSlug)) {
            $findings[] = $this->f('pass', 'Focus Topic', "Topic \"{$topic}\" appears naturally (density {$density}% — informational only).", null, 'Content Quality');
        } else {
            $findings[] = $this->f('recommendation', 'Focus Topic', "Topic \"{$topic}\" is underused — appear in the title, intro and some headings naturally.", null, 'Content Quality');
        }
    }

    protected function checkLinks(string $content, array &$findings): void
    {
        $links = $this->countLinks($content);
        if ($links['internal'] === 0) {
            $suggestions = $this->suggestInternal($content);
            $f = $this->f('recommendation', 'Internal Linking', 'No internal links — readers (and crawlers) have no path to your other pages.',
                $suggestions ? 'Natural fits: ' . implode('; ', array_slice($suggestions, 0, 3)) : 'Link to related services or guides where it helps the reader.', 'Links');
            $f['suggestions'] = $suggestions;
            $findings[] = $f;
        } elseif ($links['internal'] >= 2) {
            $findings[] = $this->f('pass', 'Internal Linking', "{$links['internal']} internal links found — good.", null, 'Links');
        } else {
            $findings[] = $this->f('heuristic', 'Internal Linking', "Only {$links['internal']} internal link — consider one or two more where they genuinely help.", null, 'Links');
        }

        if ($links['external'] > 0) {
            $findings[] = $this->f('pass', 'External Links', "{$links['external']} external reference(s) — fine when relevant and credible.", null, 'Links');
        }
        // Zero external links is NOT penalized — by design.
    }

    protected function checkImages(array $data, string $content, array &$findings): void
    {
        if (trim((string) ($data['featured_image'] ?? '')) === '') {
            $findings[] = $this->f('recommendation', 'Featured Image', 'No featured/OG image — shares and result cards look bare.', 'Add a representative image.', 'Images');
        } else {
            $findings[] = $this->f('pass', 'Featured Image', 'Featured image set.', null, 'Images');
        }

        preg_match_all('/<img[^>]*>/i', $content, $imgs);
        $missing = 0;
        $generic = 0;
        foreach ($imgs[0] as $tag) {
            if (!preg_match('/alt="([^"]*)"/i', $tag, $alt) || trim($alt[1]) === '') {
                $missing++;
            } elseif (preg_match('/^(img|image|photo|picture|dsc\d*)[\.\-\s]/i', $alt[1])) {
                $generic++;
            }
        }
        if ($missing > 0) {
            $findings[] = $this->f('recommendation', 'Image Alt Text', "{$missing} image(s) without alt text.", 'Describe what each image shows.', 'Images');
        } elseif ($generic > 0) {
            $findings[] = $this->f('heuristic', 'Image Alt Text', "{$generic} image(s) with generic alt text (e.g. \"image.jpg\").", 'Describe the actual subject.', 'Images');
        } elseif (count($imgs[0]) > 0) {
            $findings[] = $this->f('pass', 'Image Alt Text', 'All images have descriptive alt text.', null, 'Images');
        }
    }

    protected function checkTrust(array $data, array &$findings): void
    {
        // Content/URL mode: authorship/dates are not judgeable — info only.
        if ($data['mode'] !== 'post') {
            $findings[] = $this->f('info', 'Author', 'Not applicable for this analysis mode.', null, 'Trust & Schema');
            $findings[] = $this->f('info', 'Publication Dates', 'Not applicable for this analysis mode.', null, 'Trust & Schema');
            return;
        }

        if (empty($data['has_author']) || $data['author_name'] === null || $data['author_name'] === '') {
            $findings[] = $this->f('critical', 'Author', 'No author identified — authorship helps trust and Article structured data.', 'Assign an author with a name/bio.', 'Trust & Schema');
        } else {
            $findings[] = $this->f('pass', 'Author', 'Author: ' . $data['author_name'] . '.', null, 'Trust & Schema');
        }

        if (empty($data['published_at'])) {
            $findings[] = $this->f('recommendation', 'Publication Dates', 'No publish date set.', 'Set published_at so dates can be shown honestly.', 'Trust & Schema');
        } else {
            $published = $data['published_at'];
            $updated = $data['updated_at'] ?? null;
            $findings[] = $this->f('pass', 'Publication Dates',
                'Published ' . $published->format('d M Y')
                . (($updated && $updated->gt($published)) ? ' · updated ' . $updated->format('d M Y') : ''), null, 'Trust & Schema');
        }
    }

    protected function checkSpamShield(string $text, string $content, int $words, array &$findings): void
    {
        if ($words > 200) {
            // repeated paragraphs
            $paras = array_map(fn ($p) => trim(preg_replace('/\s+/', ' ', strip_tags($p))), preg_split('/\n{2,}|<\/p>/i', $content) ?: []);
            $paras = array_filter($paras, fn ($p) => mb_strlen($p) > 60);
            $counts = array_count_values(array_map(fn ($p) => mb_strtolower($p), $paras));
            $dup = 0;
            foreach ($counts as $c) {
                if ($c > 1) {
                    $dup += $c - 1;
                }
            }
            if ($dup > 0) {
                $findings[] = $this->f('critical', 'Duplicate Paragraphs', "{$dup} duplicated paragraph(s) — spam-policy risk.", 'Remove the repeats.', 'Spam Shield');
            } else {
                $findings[] = $this->f('pass', 'Duplicate Paragraphs', 'No duplicated paragraphs.', null, 'Spam Shield');
            }

            // hidden text
            if (preg_match('/style="[^"]*(display\s*:\s*none|visibility\s*:\s*hidden|font-size\s*:\s*0|text-indent\s*:\s*-9)/i', $content)) {
                $findings[] = $this->f('critical', 'Hidden Text', 'Hidden text patterns detected — a spam-policy violation.', 'Remove hidden keyword text.', 'Spam Shield');
            } else {
                $findings[] = $this->f('pass', 'Hidden Text', 'No hidden-text patterns.', null, 'Spam Shield');
            }
        }

        // exact-match anchor over-optimization
        preg_match_all('/<a[^>]*href="[^"]*"[^>]*>(.*?)<\/a>/is', $content, $anchors);
        $texts = array_map(fn ($a) => mb_strtolower(trim(strip_tags($a[1]))), $anchors[0] ?? []);
        $over = 0;
        foreach (array_count_values(array_filter($texts)) as $t => $c) {
            if ($c >= 4 && mb_strlen($t) > 3) {
                $over++;
            }
        }
        if ($over > 0) {
            $findings[] = $this->f('recommendation', 'Anchor Over-Optimization', "{$over} anchor text(s) repeated 4+ times — vary the wording naturally.", null, 'Spam Shield');
        } else {
            $findings[] = $this->f('pass', 'Anchor Over-Optimization', 'Anchor usage looks natural.', null, 'Spam Shield');
        }
    }

    protected function checkOriginality(string $text, array $hits, array &$findings): void
    {
        if ($text === '') {
            return;
        }
        if (count($hits) >= 2) {
            $findings[] = $this->f('heuristic', 'Originality Signals', 'Low original-value signals — generic filler phrases: ' . implode(', ', $hits) . '.',
                'Replace with specifics: real examples, data, screenshots, first-hand experience.', 'Content Quality') + ['hits' => count($hits)];
        } elseif (count($hits) === 1) {
            $findings[] = $this->f('heuristic', 'Originality Signals', 'One generic filler phrase found: ' . $hits[0] . '.',
                'Consider a more specific sentence.', 'Content Quality') + ['hits' => 1];
        } else {
            $findings[] = $this->f('pass', 'Originality Signals', 'No generic filler detected.', null, 'Content Quality');
        }
    }

    /* ================= rendered-page checks ================= */

    /** Fetch the public URL through the hardened fetcher (null when not possible). */
    protected function fetchRendered(array $data): ?array
    {
        $url = $this->publicUrl($data);
        if ($url === null || ($data['status'] ?? '') !== 'published') {
            return null;
        }
        // Trusted self-fetch: analyzing OUR OWN rendered page is legitimate even
        // on loopback/private hosts, so the SSRF guard is bypassed here only.
        try {
            $result = app(UrlFetcher::class)->fetch($url, true);
        } catch (\Throwable $e) {
            return null;
        }

        return ($result['ok'] ?? false) === true ? $result : null;
    }

    protected function checkRendered(array $live, array &$findings): void
    {
        $html = (string) $live['html'];

        if ((int) $live['status'] !== 200) {
            $findings[] = $this->f('critical', 'Indexability', 'Rendered URL returns HTTP ' . $live['status'] . '.', 'Fix the page before promoting it.', 'Technical SEO');
            return;
        }

        if (preg_match('/<meta[^>]+name=["\']robots["\'][^>]+content=["\'][^"\']*noindex/i', $html)) {
            $findings[] = $this->f('critical', 'Indexability', 'Rendered page contains meta robots noindex — Google cannot index it!', 'Remove the noindex (check the post robots field / theme).', 'Technical SEO');
        } else {
            $findings[] = $this->f('pass', 'Indexability', 'No noindex on the rendered page.', null, 'Technical SEO');
        }

        if (preg_match('/<link[^>]+rel=["\']canonical["\'][^>]+href=["\']([^"\']+)["\']/i', $html, $c)) {
            $findings[] = $this->f('pass', 'Canonical', 'Canonical present: ' . $c[1], null, 'Technical SEO');
        } else {
            $findings[] = $this->f('recommendation', 'Canonical', 'No canonical link on the rendered page.', 'Add a self-referencing canonical.', 'Technical SEO');
        }

        if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $html, $t)) {
            $len = mb_strlen(trim($t[1]));
            $findings[] = $len > 70
                ? $this->f('heuristic', 'Rendered Title', "Rendered <title> is {$len} characters — may be rewritten in results.", null, 'Technical SEO')
                : $this->f('pass', 'Rendered Title', "Rendered <title> looks fine ({$len} characters).", null, 'Technical SEO');
        }

        if (!preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\'][^"\']+["\']/i', $html)) {
            $findings[] = $this->f('critical', 'Rendered Description', 'Rendered page has NO meta description tag — the field may not reach the theme!', 'Check the theme head partial wiring.', 'Technical SEO');
        } else {
            $findings[] = $this->f('pass', 'Rendered Description', 'Meta description tag present in HTML.', null, 'Technical SEO');
        }

        if (preg_match('/application\/ld\+json/i', $html) && preg_match('/"(?:@type|@@type)"\s*:\s*"(?:Article|BlogPosting|NewsArticle|Service|Product|WebPage)"/i', $html)) {
            $findings[] = $this->f('pass', 'Structured Data', 'JSON-LD structured data found on the rendered page.', null, 'Trust & Schema');
        } else {
            $findings[] = $this->f('recommendation', 'Structured Data', 'No Article/Service JSON-LD in the rendered HTML.', 'Add schema markup to the theme template for this post type.', 'Trust & Schema');
        }
    }

    /* ================= scoring ================= */

    /** Score for one category — null when the category is unavailable. */
    protected function categoryScore(string $category, array $findings, array $data, string $text, bool $fetchOk): ?int
    {
        return match ($category) {
            'Technical SEO' => $fetchOk ? $this->catScore($findings, $category) : null,
            'Search Intent' => $this->searchIntentScore($data, $text),
            'Originality'   => $this->originalityScore($text, $findings),
            default         => $this->catScore($findings, $category),
        };
    }

    /** Deduction-based category score: critical −20, recommendation −8, heuristic −3, floor 0. */
    protected function catScore(array $findings, string $category): int
    {
        $score = 100;
        foreach ($findings as $f) {
            if (($f['category'] ?? '') !== $category) {
                continue;
            }
            $score -= match ($f['severity']) {
                'critical' => 20,
                'recommendation' => 8,
                'heuristic' => 3,
                default => 0, // pass and info never deduct
            };
        }

        return max(0, min(100, $score));
    }

    /**
     * Search Intent (0.05 weight): 100 when the focus topic appears naturally
     * in the title + intro/slug (same logic as the Focus Topic pass), 75 when
     * a topic exists but does not match, null when there is no topic at all.
     */
    protected function searchIntentScore(array $data, string $text): ?int
    {
        $topic = $this->resolveTopic($data);
        if ($topic === '' || $text === '') {
            return null;
        }
        $title = mb_strtolower(trim((string) ($data['meta_title'] ?: $data['title'])));
        $inTitle = Str::contains($title, $topic);
        $inSlug = Str::contains(str_replace('-', ' ', (string) $data['slug']), $topic);
        $inIntro = Str::contains(mb_substr(mb_strtolower($text), 0, 400), $topic);

        return ($inTitle && ($inIntro || $inSlug)) ? 100 : 75;
    }

    /**
     * Originality (0.05 weight): derived from the Originality Signals finding —
     * pass → 100, 1 filler hit → 85, ≥2 hits → 70; null with no content.
     */
    protected function originalityScore(string $text, array $findings): ?int
    {
        if ($text === '') {
            return null;
        }
        foreach ($findings as $f) {
            if (($f['title'] ?? '') === 'Originality Signals') {
                if (($f['severity'] ?? '') === 'pass') {
                    return 100;
                }

                return ((int) ($f['hits'] ?? 1)) >= 2 ? 70 : 85;
            }
        }

        return null;
    }

    /* ================= helpers ================= */

    /** Focus topic, lowercased: explicit topic → meta title → title. */
    protected function resolveTopic(array $data): string
    {
        $topic = mb_strtolower(trim((string) ($data['topic'] ?? '')));
        if ($topic === '') {
            $topic = mb_strtolower(trim((string) ($data['meta_title'] ?: $data['title'])));
        }

        return $topic;
    }

    /** Count filler-phrase hits, formatted for the Originality Signals detail. */
    protected function fillerHits(string $text): array
    {
        if ($text === '') {
            return [];
        }
        $lower = mb_strtolower($text);
        $hits = [];
        foreach ($this->fillerPhrases as $p) {
            $c = substr_count($lower, $p);
            if ($c > 0) {
                $hits[] = "\"{$p}\" ×{$c}";
            }
        }

        return $hits;
    }

    /** Internal-link topic map: config seo-shield.internal_topic_map merged over the defaults. */
    protected function internalTopicMap(): array
    {
        return array_merge((array) config('seo-shield.internal_topic_map', []), $this->internalTargets);
    }

    protected function publicUrl(array $data): ?string
    {
        $base = app()->runningInConsole()
            ? rtrim((string) config('app.url'), '/')
            : rtrim(request()->getSchemeAndHttpHost(), '/');
        if ($base === '') {
            $base = rtrim(url('/'), '/');
        }
        $slug = (string) $data['slug'];
        return match ((string) $data['post_type']) {
            'blog_post' => "{$base}/blog/{$slug}",
            'service'   => "{$base}/services/{$slug}",
            'page'      => "{$base}/page/{$slug}",
            default     => null,
        };
    }

    protected function countLinks(string $content): array
    {
        $host = parse_url(app()->runningInConsole() ? (string) config('app.url') : request()->getSchemeAndHttpHost(), PHP_URL_HOST) ?: parse_url(url('/'), PHP_URL_HOST);
        preg_match_all('/<a[^>]+href=["\']([^"\']+)["\']/i', $content, $m);
        $internal = 0;
        $external = 0;
        foreach ($m[1] as $href) {
            if (preg_match('/^(mailto:|tel:|#|javascript:)/i', $href)) {
                continue;
            }
            $h = parse_url($href, PHP_URL_HOST);
            if ($h === null || $h === false || strcasecmp($h, (string) $host) === 0) {
                $internal++;
            } else {
                $external++;
            }
        }
        return ['internal' => $internal, 'external' => $external];
    }

    protected function suggestInternal(string $content): array
    {
        $lower = mb_strtolower(strip_tags($content));
        $out = [];
        foreach ($this->internalTopicMap() as $url => $needles) {
            foreach ((array) $needles as $n) {
                if (Str::contains($lower, $n)) {
                    $out[] = "\"{$n}\" → link to {$url}";
                    break;
                }
            }
        }
        return $out;
    }

    protected function grade(int $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 75 => 'Good — minor improvements recommended',
            $score >= 55 => 'Needs work',
            default => 'Critical problems — fix before publishing',
        };
    }

    protected function f(string $severity, string $title, string $detail, ?string $fix = null, string $category = 'On-Page & Meta'): array
    {
        return ['severity' => $severity, 'title' => $title, 'detail' => $detail, 'fix' => $fix, 'category' => $category];
    }
}
