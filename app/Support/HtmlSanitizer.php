<?php

namespace App\Support;

/**
 * Allow-list HTML sanitizer for admin-authored rich text (blog body,
 * service description, future custom-HTML widgets).
 *
 * Strategy (SE-005): admin trust is NOT a sanitizer. Every unescaped
 * render ({!! ... !!}) of stored HTML MUST pass through HtmlSanitizer::clean().
 *
 * - Parses with DOMDocument (libxml), never regex-over-HTML.
 * - Drops <script>, <iframe>, <object>, <embed>, <link>, <meta>, <style>,
 *   <form> controls, inline event handlers (on*) and javascript:/vbscript:/
 *   data: URLs (except safe raster data:image types on img src).
 * - Keeps only the allow-listed tags/attributes below; everything else is
 *   unwrapped (children preserved) or dropped outright for dangerous ones.
 * - Pure PHP, no external dependencies.
 */
class HtmlSanitizer
{
    /** tag => allowed attributes (lowercase) */
    private const ALLOWED = [
        'p'          => [],
        'br'         => [],
        'hr'         => [],
        'strong'     => [],
        'b'          => [],
        'em'         => [],
        'i'          => [],
        'u'          => [],
        's'          => [],
        'ul'         => [],
        'ol'         => ['start'],
        'li'         => [],
        'a'          => ['href', 'title', 'target', 'rel'],
        'img'        => ['src', 'srcset', 'sizes', 'alt', 'title', 'width', 'height', 'loading', 'decoding'],
        'h1'         => [],
        'h2'         => [],
        'h3'         => [],
        'h4'         => [],
        'h5'         => [],
        'h6'         => [],
        'blockquote' => ['cite'],
        'code'       => [],
        'pre'        => [],
        'figure'     => [],
        'figcaption' => [],
        'table'      => [],
        'thead'      => [],
        'tbody'      => [],
        'tfoot'      => [],
        'tr'         => [],
        'td'         => ['colspan', 'rowspan'],
        'th'         => ['colspan', 'rowspan', 'scope'],
        'span'       => [],
        'div'        => ['class'],
    ];

    /** Removed together with their entire subtree. */
    private const DROP_WITH_CHILDREN = [
        'script', 'style', 'iframe', 'object', 'embed', 'applet', 'noscript',
        'template', 'svg', 'math', 'link', 'meta', 'base', 'form', 'input',
        'button', 'textarea', 'select', 'option', 'audio', 'video', 'source',
        'track', 'canvas', 'frame', 'frameset', 'portal',
    ];

    /** URL-bearing attributes the scheme check applies to. */
    private const URL_ATTRS = ['href', 'src', 'cite'];

    /**
     * Sanitize an HTML fragment for safe unescaped output.
     */
    public static function clean(?string $html): string
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }

        $dom = new \DOMDocument();
        $prior = libxml_use_internal_errors(true);
        libxml_clear_errors();

        // The XML prolog forces libxml to treat the input as UTF-8, preventing
        // multibyte payloads from smuggling tags through encoding confusion.
        $wrapped = '<?xml encoding="utf-8"?><!DOCTYPE html><html><body>' . $html . '</body></html>';
        $parsed = $dom->loadHTML($wrapped, LIBXML_NOXMLDECL);
        libxml_clear_errors();
        libxml_use_internal_errors($prior);

        // Drop the synthetic prolog node libxml creates for the encoding hint.
        foreach (iterator_to_array($dom->childNodes) as $child) {
            if ($child instanceof \DOMProcessingInstruction) {
                $dom->removeChild($child);
            }
        }

        if (!$parsed) {
            // Unparseable input: return fully escaped text, never raw.
            return e($html);
        }

        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body === null) {
            return e($html);
        }

        self::walk($body);

        $out = '';
        foreach (iterator_to_array($body->childNodes) as $child) {
            $out .= $dom->saveHTML($child);
        }

        return $out;
    }

    private static function walk(\DOMNode $node): void
    {
        // Iterate a snapshot: we mutate the tree while walking.
        foreach (iterator_to_array($node->childNodes ?? []) as $child) {
            if ($child instanceof \DOMElement) {
                $tag = strtolower($child->tagName);

                if (in_array($tag, self::DROP_WITH_CHILDREN, true)) {
                    self::remove($child);
                    continue;
                }

                if (!isset(self::ALLOWED[$tag])) {
                    // Not allow-listed: unwrap (replace with its children).
                    self::unwrap($child);
                    continue;
                }

                self::filterAttributes($child, $tag);
                self::walk($child);
            } elseif ($child->nodeType === XML_COMMENT_NODE) {
                // Comments can carry IE/conditional payloads.
                self::remove($child);
            } else {
                self::walk($child);
            }
        }
    }

    private static function filterAttributes(\DOMElement $el, string $tag): void
    {
        $allowed = self::ALLOWED[$tag];

        foreach (iterator_to_array($el->attributes) as $attr) {
            $name  = strtolower($attr->nodeName);
            $value = (string) $attr->nodeValue;

            $drop = str_starts_with($name, 'on')                       // on* event handlers
                || str_starts_with(trim($value), 'javascript:')         // inline JS URLs
                || str_starts_with(trim($value), 'vbscript:')
                || str_starts_with(trim($value), 'data:text')
                || str_starts_with($value, '#{')                        // JS template injection into frameworks
                || !in_array($name, $allowed, true);

            // href="#" / src="#" are harmless; javascript: handled above.
            if ($drop) {
                $el->removeAttribute($attr->nodeName);
                continue;
            }

            if (in_array($name, self::URL_ATTRS, true) && !self::safeUrl($value, $name === 'src' && $tag === 'img')) {
                $el->removeAttribute($attr->nodeName);
            }
        }

        // Force safe link semantics on anchors that kept a target.
        if ($tag === 'a' && $el->getAttribute('target') === '_blank') {
            $el->setAttribute('rel', trim(($el->getAttribute('rel') ?: '') . ' noopener noreferrer'));
        }
    }

    /**
     * Allow http(s), protocol-relative, site-relative, fragment and mailto/tel.
     * For <img src> additionally allow raster data: URIs (data:image/png|jpeg|gif|webp).
     */
    private static function safeUrl(string $url, bool $isImgSrc = false): bool
    {
        $url = trim($url);
        if ($url === '' || $url === '#') {
            return true;
        }

        // Control chars / whitespace tricks inside the scheme.
        if (preg_match('/[\x00-\x1f\x7f]/', $url)) {
            return false;
        }

        if (preg_match('#^([a-z][a-z0-9+.\-]*)://#i', $url, $m)) {
            $scheme = strtolower($m[1]);

            return $scheme === 'http' || $scheme === 'https' || $scheme === 'mailto' || $scheme === 'tel';
        }

        if (preg_match('/^data:/i', $url)) {
            return $isImgSrc && (bool) preg_match('#^data:image/(png|jpe?g|gif|webp)(;|,)#i', $url);
        }

        // Relative, protocol-relative (//host/...), anchor or query-only.
        return true;
    }

    private static function remove(\DOMNode $node): void
    {
        if ($node->parentNode !== null) {
            $node->parentNode->removeChild($node);
        }
    }

    private static function unwrap(\DOMElement $el): void
    {
        $parent = $el->parentNode;
        if ($parent === null) {
            return;
        }

        while ($el->firstChild !== null) {
            $parent->insertBefore($el->firstChild, $el);
        }

        $parent->removeChild($el);
    }
}
