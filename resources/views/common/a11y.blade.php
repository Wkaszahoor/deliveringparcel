{{-- DeliveringParcel accessibility widget (dp-a11y) — text size, underline links, high contrast.
     Preferences persist in localStorage. Include once per layout, right after <body>. --}}
<link rel="stylesheet" href="{{ url('dashbord/css/dp-a11y.css') }}?v=20260928a">
<div id="dpA11yRoot">
    <button type="button" id="dpA11yBtn" class="dp-a11y-btn" aria-label="Accessibility options"
            aria-expanded="false" aria-controls="dpA11yPanel" title="Accessibility options">
        <i class="fas fa-universal-access" aria-hidden="true"></i>
    </button>
    <div id="dpA11yPanel" class="dp-a11y-panel" role="dialog" aria-label="Accessibility options" hidden>
        <div class="dp-a11y-head">
            <strong>Accessibility options</strong>
            <button type="button" class="dp-a11y-close" id="dpA11yClose" aria-label="Close accessibility options">&times;</button>
        </div>
        <div class="dp-a11y-body">
            <div class="dp-a11y-label">Text size</div>
            <div class="dp-a11y-row" role="group" aria-label="Text size">
                <button type="button" id="dpA11ySmaller" aria-label="Decrease text size">A&minus;</button>
                <button type="button" id="dpA11yDefault">Default</button>
                <button type="button" id="dpA11yLarger" aria-label="Increase text size">A+</button>
            </div>
            <div class="dp-a11y-size" id="dpA11ySize" aria-live="polite">Text size: 100%</div>
            <label class="dp-a11y-check"><input type="checkbox" id="dpA11yUnderline"> Underline links</label>
            <label class="dp-a11y-check"><input type="checkbox" id="dpA11yContrast"> High contrast</label>
            <button type="button" id="dpA11yReset" class="dp-a11y-reset">Reset all settings</button>
            <p class="dp-a11y-note">Preferences are saved in this browser. Keyboard focus is always highlighted.</p>
        </div>
    </div>
</div>
<script src="{{ url('dashbord/js/dp-a11y.js') }}?v=20260920a" defer></script>
