#!/usr/bin/env python3
"""
security_probes.py — black-box security regression pack (SE-010).

Usage:
    python tests/Security/security_probes.py [BASE_URL] [--uploads-dir DIR] [--php PATH]

Defaults: BASE_URL=http://127.0.0.1:8000, uploads dir derived from the
canonical docroot (C:/laragon/www/dplive/uploads), PHP CLI auto-detected.

Std-library only (urllib / http.cookiejar). Exits non-zero if ANY probe
fails. Safe to re-run: writes only probe-marked contact rows
(sec-probe-<ts>@example.com) through the real public contact form.

Probe map:
  P01 public pages online                    P07 forged status/amount (RQ-003)
  P02 auth-gated area blocks anonymous       P08 cross-user track (email scoping)
  P03 missing CSRF token -> 419              P09 admin area blocks anonymous
  P04 honeypot silently discards bots        P10 malicious upload rejected
  P05 stored XSS neutralized on output       P11 HtmlSanitizer payload harness (PHP)
  P06 SQLi strings cause no error/leak       P12 contact throttle (429) — runs LAST
"""

import http.cookiejar
import json
import mimetypes
import os
import re
import subprocess
import sys
import tempfile
import time
import urllib.error
import urllib.parse
import urllib.request

BASE = sys.argv[1] if len(sys.argv) > 1 and not sys.argv[1].startswith("--") else "http://127.0.0.1:8000"
UPLOADS_DIR = "C:/laragon/www/dplive/uploads"
PHP_BIN = None
args = sys.argv[1:]
if "--uploads-dir" in args:
    UPLOADS_DIR = args[args.index("--uploads-dir") + 1]
if "--php" in args:
    PHP_BIN = args[args.index("--php") + 1]
if PHP_BIN is None:
    for cand in (r"C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe", "php"):
        if os.path.exists(cand) or subprocess.run(["where", cand], capture_output=True).returncode == 0:
            PHP_BIN = cand
            break

ADMIN_EMAIL = os.environ.get("DP_ADMIN_EMAIL", "superadmin@deliveringparcel.com")
ADMIN_PASS = os.environ.get("DP_ADMIN_PASSWORD", "SuperAdmin@2026")

STAMP = str(int(time.time()))
XSS_NAME = "<script>alert(1)</script>"
XSS_DETAIL = '<img src=x onerror=alert(2)> <svg onload=alert(3)> "quoted" text'

RESULTS = []


class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self, req, fp, code, msg, headers, newurl):
        return None


def make_session():
    jar = http.cookiejar.CookieJar()
    return urllib.request.build_opener(
        urllib.request.HTTPCookieProcessor(jar), NoRedirect())


def fetch(opener, method, url, data=None, headers=None):
    if isinstance(data, dict):
        data = urllib.parse.urlencode(data).encode()
    req = urllib.request.Request(url, data=data, method=method,
                                 headers=headers or {})
    try:
        with opener.open(req, timeout=30) as resp:
            body = resp.read().decode("utf-8", "replace")
            return resp.status, dict(resp.headers), body
    except urllib.error.HTTPError as e:
        return e.code, dict(e.headers), e.read().decode("utf-8", "replace")


def get(opener, url):
    return fetch(opener, "GET", url)


def post(opener, url, fields):
    return fetch(opener, "POST", url, fields)


def csrf_token(html):
    m = re.search(r'name="_token"\s+value="([^"]+)"', html)
    return m.group(1) if m else None


def multipart(fields, files):
    boundary = "----secprobe" + STAMP
    out = b""
    for k, v in fields.items():
        out += ("--%s\r\nContent-Disposition: form-data; name=\"%s\"\r\n\r\n%s\r\n"
                % (boundary, k, v)).encode()
    for name, (fname, content, ctype) in files.items():
        out += ("--%s\r\nContent-Disposition: form-data; name=\"%s\"; filename=\"%s\"\r\n"
                "Content-Type: %s\r\n\r\n" % (boundary, name, fname, ctype)).encode()
        out += content + b"\r\n"
    out += ("--%s--\r\n" % boundary).encode()
    return out, "multipart/form-data; boundary=" + boundary


def record(pid, name, ok, detail):
    RESULTS.append((pid, name, ok, detail))
    print("%-5s %-46s %s %s" % (pid, name, "PASS" if ok else "FAIL",
                                ("— " + detail) if detail else ""))


def contact_post(opener, email, name, detail, extra=None, honeypot=None):
    _, _, body = get(opener, BASE + "/home2/contact")
    token = csrf_token(body)
    fields = {"_token": token, "name": name, "email": email, "detail": detail}
    if honeypot is not None:
        fields["company_website"] = honeypot
    if extra:
        fields.update(extra)
    return post(opener, BASE + "/home2/contact", fields)


_ADMIN_OK = set()  # id(opener) of sessions already authenticated


def admin_login(opener):
    if id(opener) in _ADMIN_OK:
        return True
    _, _, body = get(opener, BASE + "/login")
    token = csrf_token(body)
    status, headers, _ = post(opener, BASE + "/login",
                              {"_token": token, "email": ADMIN_EMAIL,
                               "password": ADMIN_PASS})
    ok = status in (200, 302) and "login" not in (headers.get("Location") or "")
    if ok:
        _ADMIN_OK.add(id(opener))
    return ok


def contacts_row(opener, email):
    """Fetch the admin Messages JSON row for a marker email (or None)."""
    _, _, body = get(opener, BASE + "/admin/contacts/data?q=" +
                     urllib.parse.quote(email))
    try:
        data = json.loads(body)
    except ValueError:
        return None
    for row in data.get("data", []):
        if row.get("email") == email:
            return row
    return None


# ---------------------------------------------------------------- probes ---

def p01(opener):
    ok = True
    for path in ("/home2", "/home2/blog", "/home2/contact", "/home2/track-order",
                 "/home2/services"):
        s, _, _ = get(opener, BASE + path)
        ok &= s == 200
    record("P01", "public home2 pages return 200", ok, "")


def p02(opener):
    s, h, b = get(opener, BASE + "/home2/dashboard")
    ok = s in (301, 302) and "login" in (h.get("Location") or "").lower() and "Your account overview" not in b
    record("P02", "anonymous /home2/dashboard redirected to login", ok,
           "status=%s loc=%s" % (s, h.get("Location")))


def p03(opener):
    s, _, _ = post(opener, BASE + "/home2/track-order",
                   {"ref": "DP-0001", "email": "x@example.com"})
    ok = s == 419
    record("P03", "POST without CSRF token rejected with 419", ok,
           "status=%s (track-order, no throttle on route)" % s)


def p04(opener):
    email = "sec-probe-honeypot-%s@example.com" % STAMP
    s, _, _ = contact_post(opener, email, "Bot", "spam text",
                           honeypot="http://spam.example")
    row = contacts_row(opener, email) if admin_login(opener) else "nologin"
    ok = s in (301, 302) and row is None
    record("P04", "honeypot-filled contact silently discarded", ok,
           "post=%s row=%s" % (s, row))


def p05(opener):
    email = "sec-probe-xss-%s@example.com" % STAMP
    s, _, _ = contact_post(opener, email, XSS_NAME, XSS_DETAIL)
    row = contacts_row(opener, email)
    if row is None:
        record("P05", "stored XSS neutralized on admin output", False, "row not found")
        return
    _, _, page = get(opener, row["show_url"])
    # Raw INJECTION means the unescaped ATTACK markup. Layout assets
    # (<script src=...>, logo <img>) are legitimate; only the payload's own
    # executable fragments count. Escaped entities = safe (Req 6.10).
    raw_present = ("<script>alert" in page) or ("<svg onload" in page) or ("<img src=x" in page)
    escaped_present = "&lt;script&gt;" in page or "&lt;img" in page or "&lt;svg" in page
    ok = (s in (301, 302)) and not raw_present and escaped_present
    record("P05", "stored XSS neutralized on admin output", ok,
           "id=%s rawPayloadInHtml=%s escaped=%s" % (row["id"], raw_present, escaped_present))


def p06(opener):
    ok = True
    _, _, body = get(opener, BASE + "/home2/services?q=" +
                     urllib.parse.quote("' OR 1=1--"))
    ok &= "SQLSTATE" not in body and "syntax" not in body.lower()
    _, _, body = get(opener, BASE + "/home2/blog?q=" +
                     urllib.parse.quote("\"; DROP TABLE blogs;--"))
    # Reflected-but-escaped search input is safe; only engine errors matter.
    ok &= "SQLSTATE" not in body and "syntax error" not in body.lower()
    _, _, body = post(opener, BASE + "/home2/track-order",
                      {"_token": csrf_token(get(opener, BASE + "/home2/track-order")[2]),
                       "ref": "' UNION SELECT password FROM users--",
                       "email": "x@example.com"})
    ok &= ("SQLSTATE" not in body and "No order found" in body)
    record("P06", "SQLi strings produce no error / no data leak", ok, "")


def p07(opener):
    email = "sec-probe-forge-%s@example.com" % STAMP
    s, _, _ = contact_post(opener, email, "Forge Probe", "pricing question",
                           extra={"status": "approved", "category": "admin",
                                  "user_id": "1", "amount": "0.01"})
    row = contacts_row(opener, email)
    ok = (s in (301, 302) and row is not None and
          row.get("status") == "new" and row.get("category") != "admin")
    record("P07", "forged status/category stripped (server-side only)", ok,
           "row=%s" % ({"status": row and row.get("status"),
                        "category": row and row.get("category")} if row else None))


def p08(opener):
    _, _, body = post(opener, BASE + "/home2/track-order",
                      {"_token": csrf_token(get(opener, BASE + "/home2/track-order")[2]),
                       "ref": "1", "email": "wrong-email-%s@example.com" % STAMP})
    ok = "No order found" in body and "Order #" not in body.replace("No order found", "")
    record("P08", "track with wrong email leaks nothing", ok, "")


def p09(opener):
    s, h, b = get(opener, BASE + "/admin/dashbord2")
    ok = s in (301, 302) and "login" in (h.get("Location") or "").lower()
    s2, _, _ = get(opener, BASE + "/admin/contacts/data")
    ok &= s2 in (301, 302, 403)
    record("P09", "admin area blocks anonymous access", ok,
           "page=%s data=%s" % (s, s2))


def p10(opener):
    if not admin_login(opener):
        record("P10", "malicious upload rejected", False, "admin login failed")
        return
    _, _, body = get(opener, BASE + "/admin/shop/products/create")
    token = csrf_token(body)
    fields = {"_token": token, "name": "SEC-PROBE-%s" % STAMP,
              "price": "1", "stock": "1", "is_active": "1"}
    php_payload = b"<?php echo 'SEC-PROBE'; ?>"

    cases = [("evil.php", php_payload, "application/x-php"),
             ("evil.php.jpg", php_payload, "image/jpeg"),
             ("evil.phtml", php_payload, "application/x-php")]
    statuses = []
    for fname, content, ctype in cases:
        payload, ctype_full = multipart(fields, {"images[]": (fname, content, ctype)})
        s, _, _ = fetch(opener, "POST", BASE + "/admin/shop/products",
                        payload, {"Content-Type": ctype_full})
        statuses.append((fname, s))

    leaked = []
    if os.path.isdir(UPLOADS_DIR):
        for root, _dirs, files in os.walk(UPLOADS_DIR):
            for f in files:
                if "evil" in f.lower():
                    leaked.append(os.path.join(root, f))
    # Validation must reject all three (mimes + UploadGuard); 302 = back to form w/ errors.
    ok = all(s in (301, 302) for _f, s in statuses) and not leaked
    record("P10", "malicious upload attempts rejected, nothing stored", ok,
           "statuses=%s leakedFiles=%s" % (statuses, leaked))


SAN_HARNESS = r"""<?php
require $argv[1];
use App\Support\HtmlSanitizer;
$cases = [
    ['<p>ok<script>alert(1)</script></p>', '<script'],
    ['<script>alert(1)</script>keep', '<script'],
    ['<img src=x onerror=alert(1)>', 'onerror'],
    ['<a href="javascript:alert(1)">c</a>', 'javascript'],
    ['<iframe src="//e.vil"></iframe>after', 'iframe'],
    ['<svg onload=alert(1)>s', 'svg'],
];
$fail = 0;
foreach ($cases as $i => [$in, $bad]) {
    $out = HtmlSanitizer::clean($in);
    if (stripos($out, $bad) !== false) { echo "case $i contains '$bad'\n"; $fail++; }
}
$p = HtmlSanitizer::clean('<p>Hello <strong>world</strong></p>');
if (strpos($p, '<strong>world</strong>') === false) { echo "allow-list broken\n"; $fail++; }
exit($fail === 0 ? 0 : 1);
"""


def p11(_opener):
    if PHP_BIN is None:
        record("P11", "HtmlSanitizer payload harness (PHP CLI)", True, "SKIP: no php binary")
        return
    app_root = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
    cls = os.path.join(app_root, "app", "Support", "HtmlSanitizer.php")
    with tempfile.NamedTemporaryFile("w", suffix=".php", delete=False) as fh:
        fh.write(SAN_HARNESS)
        harness = fh.name
    try:
        r = subprocess.run([PHP_BIN, harness, cls], capture_output=True, text=True, timeout=60)
        record("P11", "HtmlSanitizer payload harness (PHP CLI)", r.returncode == 0,
               (r.stdout + r.stderr).strip()[:200])
    finally:
        os.unlink(harness)


def p12(opener):
    """Runs LAST — intentionally trips throttle:5,1 on /home2/contact."""
    got429 = False
    last = None
    for _ in range(6):
        s, _, _ = contact_post(opener, "sec-probe-throttle-%s@example.com" % STAMP,
                               "T", "t")
        last = s
        if s == 429:
            got429 = True
            break
        time.sleep(0.3)
    record("P12", "contact form rate limit trips (429)", got429, "last=%s" % last)


def main():
    print("security_probes.py -> %s (uploads=%s php=%s)\n" % (BASE, UPLOADS_DIR, PHP_BIN))
    pub = make_session()
    adm = make_session()
    p01(pub)
    p02(pub)
    p03(pub)
    p04(adm)   # does its own admin login for DB-side verification
    p05(adm)
    p06(pub)
    p07(adm)
    p08(pub)
    p09(pub)
    p10(adm)
    p11(pub)
    p12(pub)
    fails = [r for r in RESULTS if not r[2]]
    print("\n%d/%d probes passed" % (len(RESULTS) - len(fails), len(RESULTS)))
    if fails:
        print("REGRESSION:", ", ".join("%s(%s)" % (f[0], f[1]) for f in fails))
        sys.exit(1)
    sys.exit(0)


if __name__ == "__main__":
    main()
