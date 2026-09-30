<?php

namespace App\Services\Seo;

use App\Models\CmsPost;
use Illuminate\Support\Str;

/**
 * Deterministic SEO auto-fix — no external AI required.
 *
 * Every "fix" is transparent content surgery:
 *  - meta title / description are DERIVED from the post's own title, excerpt
 *    and opening sentences (trimmed on word boundaries),
 *  - featured image is the first image found in the content,
 *  - author becomes the logged-in admin,
 *  - filler sentences (config seo-shield.filler_phrases) are REMOVED,
 *  - a "Related" internal-links block is APPENDED from the topic map,
 *  - slugs are de-duplicated with -2/-3 suffixes.
 *
 * propose()  → returns a preview (no DB writes).
 * apply()    → writes only the choices the user ticked.
 */
class SeoAutoFixService
{
    /** Propose fixes for a post without saving anything. */
    public function propose(CmsPost $post, ?int $userId): array
    {
        $content = (string) $post->content;
        $text = trim(strip_tags($content));
        $title = trim((string) $post->title);

        $proposal = [
            'post_id'   => $post->id,
            'duplicates' => $this->findDuplicates($post->id, $title, (string) $post->meta_title, (string) $post->slug),
        ];

        // --- meta title ---
        $current = trim((string) $post->meta_title);
        $candidate = $this->trimToWords($title !== '' ? $title : $current, 65);
        $proposal['meta_title'] = [
            'current'   => $current,
            'proposed'  => $candidate,
            'duplicate' => $this->valueTaken('meta_title', $candidate, $post->id) && $candidate !== $current,
        ];

        // --- meta description ---
        $current = trim((string) $post->meta_description);
        $base = trim((string) $post->excerpt);
        if ($base === '') {
            $base = $text;
        }
        $candidate = $this->trimToWords($base, 158);
        $proposal['meta_description'] = [
            'current'   => $current,
            'proposed'  => $candidate,
        ];

        // --- slug (unique) ---
        $current = (string) $post->slug;
        $candidate = $this->uniqueSlug(Str::slug($title !== '' ? $title : $current), $post->id);
        $proposal['slug'] = [
            'current'   => $current,
            'proposed'  => $candidate,
        ];

        // --- focus topic (meta_keywords) ---
        $current = trim((string) $post->meta_keywords);
        $candidate = $current !== '' ? $current : $this->deriveTopic($title, $text);
        $proposal['meta_keywords'] = [
            'current'   => $current,
            'proposed'  => $candidate,
        ];

        // --- featured image: first image inside the content ---
        $current = (string) $post->featured_image;
        $candidate = $current;
        if ($candidate === '' && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $m)) {
            $src = $m[1];
            if (!preg_match('/^https?:\/\//i', $src)) {
                $src = asset($src);
            }
            // Rewrite dead legacy hosts (e.g. dplive.test) to the current host.
            $legacy = parse_url((string) config('app.url'), PHP_URL_HOST);
            $reqHost = app()->runningInConsole() ? null : request()->getHttpHost();
            if ($legacy && $reqHost && strcasecmp($legacy, $reqHost) !== 0) {
                $src = str_replace('://' . $legacy, '://' . $reqHost, $src);
            }
            $candidate = $src;
        }
        $proposal['featured_image'] = [
            'current'   => $current,
            'proposed'  => $candidate,
        ];

        // --- author ---
        $proposal['author_id'] = [
            'current'   => (int) $post->author_id,
            'proposed'  => $userId,
        ];

        // --- content operations ---
        $filler = $this->findFillerSentences($content);
        $proposal['strip_filler'] = [
            'current'  => count($filler) . ' filler sentence(s)',
            'proposed' => $filler,
            'count'    => count($filler),
        ];

        $links = $this->missingInternalLinks($content);
        $proposal['add_internal_links'] = [
            'current'  => 'no links to ' . implode(', ', array_column($links, 'label')),
            'proposed' => $links,
            'count'    => count($links),
        ];

        return $proposal;
    }

    /** Apply the ticked fixes. $choices keys map to the proposal keys. */
    public function apply(CmsPost $post, array $choices, ?int $userId): array
    {
        $applied = [];
        $update = [];
        $p = $this->propose($post, $userId);

        if (!empty($choices['meta_title']) && $p['meta_title']['proposed'] !== '') {
            $update['meta_title'] = $p['meta_title']['proposed'];
            $applied[] = 'Meta title';
        }
        if (!empty($choices['meta_description'])) {
            $update['meta_description'] = $p['meta_description']['proposed'];
            $applied[] = 'Meta description';
        }
        if (!empty($choices['slug'])) {
            $update['slug'] = $p['slug']['proposed'];
            $applied[] = 'Slug';
        }
        if (!empty($choices['meta_keywords']) && $p['meta_keywords']['proposed'] !== '') {
            $update['meta_keywords'] = $p['meta_keywords']['proposed'];
            $applied[] = 'Focus topic';
        }
        if (!empty($choices['featured_image']) && $p['featured_image']['proposed'] !== '') {
            $update['featured_image'] = $p['featured_image']['proposed'];
            $applied[] = 'Featured image';
        }
        if (!empty($choices['author_id']) && $userId) {
            $update['author_id'] = $userId;
            $applied[] = 'Author (you)';
        }

        $content = (string) $post->content;
        if (!empty($choices['strip_filler'])) {
            $content = $this->stripFillerSentences($content);
            $applied[] = 'Filler sentences removed';
        }
        if (!empty($choices['add_internal_links'])) {
            $content = $this->appendInternalLinks($content);
            $applied[] = 'Internal links block added';
        }
        if ($content !== (string) $post->content) {
            $update['content'] = $content;
        }

        if ($update !== []) {
            $post->update($update);
        }

        return ['applied' => $applied, 'post' => $post->fresh()];
    }

    /* ================= helpers ================= */

    protected function findDuplicates(int $excludeId, string $title, string $metaTitle, string $slug): array
    {
        return \App\Models\CmsPost::query()->where('id', '!=', $excludeId)
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
            })
            ->get(['id', 'title', 'post_type'])
            ->map(fn ($d) => ['id' => $d->id, 'title' => $d->title, 'type' => $d->post_type])
            ->all();
    }

    protected function valueTaken(string $column, string $value, int $excludeId): bool
    {
        if ($value === '') {
            return false;
        }
        return \App\Models\CmsPost::where($column, $value)->where('id', '!=', $excludeId)->exists();
    }

    protected function uniqueSlug(string $slug, int $excludeId): string
    {
        $base = $slug !== '' ? $slug : 'post';
        $try = $base;
        $i = 2;
        while (\App\Models\CmsPost::where('slug', $try)->where('id', '!=', $excludeId)->exists()) {
            $try = $base . '-' . $i++;
        }
        return $try;
    }

    /** Trim to a maximum length WITHOUT cutting words; adds no ellipsis lies. */
    protected function trimToWords(string $text, int $max): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
        if (mb_strlen($text) <= $max) {
            return $text;
        }
        $cut = mb_substr($text, 0, $max);
        $pos = mb_strrpos($cut, ' ');
        return rtrim($pos !== false ? mb_substr($cut, 0, $pos) : $cut, " \t,;:.-");
    }

    protected function deriveTopic(string $title, string $text): string
    {
        $stop = ['the','and','for','with','from','that','this','your','you','are','was','how','what','why','a','an','to','of','in','on','is','it','at','by','or','as','be','can','we','our','us'];
        $source = mb_strtolower($title !== '' ? $title : mb_substr($text, 0, 200));
        $words = preg_split('/[^a-z0-9]+/u', $source, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $words = array_values(array_filter($words, fn ($w) => !in_array($w, $stop, true) && mb_strlen($w) > 2));
        $topic = implode(' ', array_slice($words, 0, 4));
        return $topic !== '' ? $topic : mb_strtolower($title);
    }

    protected function fillerPhrases(): array
    {
        return array_merge(config('seo-shield.filler_phrases', []), [
            "in today's digital world", 'look no further', 'in conclusion,',
        ]);
    }

    /** Sentences (in <p> blocks) that contain a filler phrase. */
    protected function findFillerSentences(string $content): array
    {
        $hits = [];
        if (preg_match_all('/<p[^>]*>(.*?)<\/p>/is', $content, $paras)) {
            foreach ($paras[1] as $para) {
                $plain = trim(strip_tags($para));
                foreach (preg_split('/(?<=[.!?])\s+/', $plain) ?: [] as $sentence) {
                    $low = mb_strtolower($sentence);
                    foreach ($this->fillerPhrases() as $phrase) {
                        if (Str::contains($low, $phrase)) {
                            $hits[] = trim($sentence);
                            break;
                        }
                    }
                }
            }
        }
        return array_values(array_unique($hits));
    }

    protected function stripFillerSentences(string $content): string
    {
        $phrases = $this->fillerPhrases();
        return preg_replace_callback('/<p[^>]*>(.*?)<\/p>/is', function ($m) use ($phrases) {
            $inner = $m[1];
            $plain = trim(strip_tags($inner));
            $kept = [];
            foreach (preg_split('/(?<=[.!?])\s+/', $plain) ?: [] as $sentence) {
                $low = mb_strtolower($sentence);
                $bad = false;
                foreach ($phrases as $phrase) {
                    if (Str::contains($low, $phrase)) {
                        $bad = true;
                        break;
                    }
                }
                if (!$bad && trim($sentence) !== '') {
                    $kept[] = trim($sentence);
                }
            }
            if ($kept === []) {
                return '';
            }
            return '<p>' . implode(' ', $kept) . '</p>';
        }, $content) ?? $content;
    }

    protected function missingInternalLinks(string $content): array
    {
        $map = array_merge(config('seo-shield.internal_topic_map', []), [
            '/shipper-program'   => ['shipper', 'ship for me', 'become a shipper', 'earn'],
            '/services'          => ['service', 'shipping assistance', 'assisted shopping'],
            '/track-order'       => ['track', 'tracking', 'where is my parcel'],
            '/freequote'         => ['quote', 'cost', 'price', 'how much'],
            '/contact-details'   => ['contact', 'support', 'help'],
            '/testimonials'      => ['review', 'testimonial', 'trust'],
            '/blog'              => ['guide', 'blog', 'read more'],
        ]);
        $lower = mb_strtolower(strip_tags($content));
        $out = [];
        foreach ($map as $url => $needles) {
            foreach ($needles as $n) {
                if (Str::contains($lower, $n)) {
                    $out[] = ['url' => $url, 'needle' => $n, 'label' => ucfirst($n)];
                    break;
                }
            }
        }
        return $out;
    }

    protected function appendInternalLinks(string $content): string
    {
        $links = $this->missingInternalLinks($content);
        // Only link pages that aren't already linked in the content.
        $links = array_values(array_filter($links, fn ($l) => !Str::contains($content, '"' . $l['url'] . '"')));
        if ($links === []) {
            return $content;
        }
        $items = implode('', array_map(
            fn ($l) => '<li><a href="' . $l['url'] . '">' . htmlspecialchars($l['label'], ENT_QUOTES) . '</a></li>',
            array_slice($links, 0, 5)
        ));
        return rtrim($content) . "\n<h2>Related</h2>\n<ul>" . $items . "</ul>\n";
    }
}
