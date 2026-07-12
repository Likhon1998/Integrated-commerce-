<x-app-layout>
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap');

/* ── LIGHT THEME ── */
:root {
    --bg:           #f4f3ef;
    --surface:      #ffffff;
    --surface-2:    #f0eeea;
    --surface-3:    #e8e6e0;
    --border:       #e2e0d8;
    --border-2:     #c9c6bb;
    --text-1:       #141210;
    --text-2:       #5c5954;
    --text-3:       #a09c92;
    --green:        #059669;
    --green-bg:     #ecfdf5;
    --green-border: #a7f3d0;
    --green-dark:   #065f46;
    --red:          #dc2626;
    --red-bg:       #fef2f2;
    --red-border:   #fecaca;
    --amber:        #d97706;
    --amber-bg:     #fffbeb;
    --amber-border: #fde68a;
    --teal:         #0d9488;
    --teal-light:   #14b8a6;
    --teal-bg:      #f0fdfa;
    --teal-border:  #99f6e4;
    --accent:       #141210;
    --radius:       14px;
    --radius-sm:    10px;
    --shadow-sm:    0 1px 3px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.03);
    --shadow:       0 4px 16px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --shadow-lg:    0 20px 60px rgba(0,0,0,0.15), 0 6px 18px rgba(0,0,0,0.08);
    --font:         'Outfit', sans-serif;
    --mono:         'JetBrains Mono', monospace;
}

/* ── DARK THEME ── */
[data-theme="dark"] {
    --bg:           #0d1117;
    --surface:      #161b22;
    --surface-2:    #1c2128;
    --surface-3:    #21262d;
    --border:       #30363d;
    --border-2:     #484f58;
    --text-1:       #e6edf3;
    --text-2:       #8b949e;
    --text-3:       #3d444e;
    --green:        #3fb950;
    --green-bg:     #0a1f14;
    --green-border: #1a4731;
    --green-dark:   #aff5b4;
    --red:          #f85149;
    --red-bg:       #1f0a0a;
    --red-border:   #6e2020;
    --amber:        #e3b341;
    --amber-bg:     #1a1200;
    --amber-border: #4a3300;
    --teal:         #2dd4bf;
    --teal-light:   #5eead4;
    --teal-bg:      #081a18;
    --teal-border:  #0d4a44;
    --shadow-sm:    0 1px 3px rgba(0,0,0,0.4);
    --shadow:       0 4px 16px rgba(0,0,0,0.5);
    --shadow-lg:    0 20px 60px rgba(0,0,0,0.7);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

.pos-root {
    font-family: var(--font);
    background: var(--bg);
    min-height: calc(100vh - 64px);
    padding: 14px;
    display: flex;
    gap: 14px;
    transition: background .2s, color .2s;
}

/* ── ICON BUTTON ── */
.icon-btn {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: var(--surface-2);
    border: 1.5px solid var(--border);
    color: var(--text-2);
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s; flex-shrink: 0;
}
.icon-btn:hover { color: var(--text-1); border-color: var(--border-2); background: var(--surface-3); }
.icon-btn svg { width: 16px; height: 16px; }

/* ── STATUS BADGE ── */
.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 11px;
    border-radius: 8px; border: 1.5px solid;
    font-size: 10.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase;
    transition: all .3s; flex-shrink: 0;
}
.status-badge.online  { background: var(--green-bg);  border-color: var(--green-border); color: var(--green); }
.status-badge.offline { background: var(--red-bg);    border-color: var(--red-border);   color: var(--red); animation: pulse .8s ease-in-out infinite alternate; }
.status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
@keyframes pulse { to { opacity: .5; } }

/* ═══════════════════════════════════════════════
   LEFT PANEL
═══════════════════════════════════════════════ */
.panel-left {
    flex: 1;
    display: flex; flex-direction: column;
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
    min-height: 0;
}

.panel-top {
    padding: 14px 16px;
    border-bottom: 1.5px solid var(--border);
    background: var(--surface);
}

.top-row {
    display: flex; align-items: center; gap: 8px; margin-bottom: 12px;
}

/* Search */
.search-wrap { position: relative; flex: 1; }
.search-icon {
    position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
    color: var(--text-3); width: 15px; height: 15px; pointer-events: none;
}
.search-input {
    width: 100%;
    padding: 10px 14px 10px 38px;
    background: var(--surface-2);
    border: 1.5px solid var(--border);
    border-radius: var(--radius-sm);
    font-family: var(--mono); font-size: 12px;
    color: var(--text-1); outline: none;
    transition: all .18s; letter-spacing: .01em;
}
.search-input::placeholder { font-family: var(--font); font-size: 13px; color: var(--text-3); }
.search-input:focus {
    background: var(--surface); border-color: var(--teal);
    box-shadow: 0 0 0 3px rgba(13,148,136,.12);
}

/* Category pills */
.cat-row { display: flex; gap: 6px; overflow-x: auto; scrollbar-width: none; }
.cat-row::-webkit-scrollbar { display: none; }
.cat-pill {
    padding: 6px 14px; border-radius: 100px;
    font-size: 11.5px; font-weight: 600; white-space: nowrap; cursor: pointer;
    border: 1.5px solid var(--border);
    background: var(--surface); color: var(--text-2);
    transition: all .15s;
}
.cat-pill:hover { border-color: var(--border-2); color: var(--text-1); }
.cat-pill.active {
    background: var(--teal); color: #fff; border-color: var(--teal);
    box-shadow: 0 2px 10px rgba(13,148,136,.25);
}

/* Product Grid */
.product-list {
    flex: 1; overflow-y: auto;
    padding: 12px 14px;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(175px, 1fr));
    gap: 9px;
    background: var(--bg);
    align-content: start;
    scrollbar-width: thin;
    scrollbar-color: var(--border) transparent;
}

.product-card {
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    padding: 12px 12px 11px;
    cursor: pointer;
    display: flex; flex-direction: column; gap: 8px;
    transition: all .16s; position: relative; overflow: hidden;
}
.product-card::before {
    content: ''; position: absolute;
    inset: 0; border-radius: inherit;
    background: linear-gradient(135deg, var(--teal) 0%, transparent 60%);
    opacity: 0; transition: opacity .2s; pointer-events: none;
}
.product-card:hover { border-color: var(--teal); box-shadow: var(--shadow); transform: translateY(-2px); }
.product-card:hover::before { opacity: .05; }
.product-card:active { transform: scale(.98); }
.product-card.out-of-stock { opacity: .45; cursor: not-allowed; pointer-events: none; }

.p-img-wrap {
    width: 100%; height: 90px;
    border-radius: 8px; overflow: hidden;
    background: var(--surface-2); border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
}
.p-img-wrap img { width: 100%; height: 100%; object-fit: cover; }

.p-name { font-size: 13px; font-weight: 700; color: var(--text-1); line-height: 1.35; }
.p-barcode { font-family: var(--mono); font-size: 9.5px; color: var(--text-3); letter-spacing: .07em; }

.p-foot { display: flex; align-items: center; justify-content: space-between; margin-top: auto; }
.p-price { font-family: var(--mono); font-size: 15px; font-weight: 700; color: var(--green); letter-spacing: -.02em; }
.p-price-sym { font-size: 9px; font-weight: 600; margin-right: 1px; opacity: .8; }

.stock-chip {
    font-family: var(--mono); font-size: 9px; font-weight: 700;
    padding: 2px 7px; border-radius: 100px; border: 1px solid;
}
.stock-ok  { background: var(--green-bg); color: var(--green); border-color: var(--green-border); }
.stock-low { background: var(--amber-bg); color: var(--amber); border-color: var(--amber-border); }
.stock-out { background: var(--red-bg);   color: var(--red);   border-color: var(--red-border); }

/* ═══════════════════════════════════════════════
   RIGHT PANEL — CART
═══════════════════════════════════════════════ */
.panel-right {
    width: 385px; flex-shrink: 0;
    display: flex; flex-direction: column;
    background: var(--surface);
    border: 1.5px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

/* Cart Header */
.cart-head {
    padding: 13px 16px;
    border-bottom: 1.5px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
    background: var(--surface); flex-shrink: 0;
}
.cart-title {
    display: flex; align-items: center; gap: 8px;
    font-size: 13px; font-weight: 900; color: var(--text-1);
    text-transform: uppercase; letter-spacing: .07em;
}
.cart-badge-icon {
    width: 29px; height: 29px; border-radius: 8px;
    background: var(--teal-bg); border: 1px solid var(--teal-border);
    display: flex; align-items: center; justify-content: center;
}
.cart-badge-icon svg { width: 13px; height: 13px; color: var(--teal); }
.cart-count {
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--teal); color: #fff;
    font-family: var(--mono); font-size: 10px; font-weight: 700;
    min-width: 19px; height: 19px; border-radius: 100px; padding: 0 5px;
}

.cart-head-actions { display: flex; align-items: center; gap: 6px; }
.held-btn {
    display: flex; align-items: center; gap: 4px;
    background: var(--amber-bg); color: var(--amber);
    border: 1.5px solid var(--amber-border);
    padding: 5px 10px; border-radius: 7px;
    font-size: 11px; font-weight: 800; cursor: pointer;
    transition: all .15s; font-family: var(--font);
}
.held-btn:hover { background: var(--amber-border); }
.held-btn svg { width: 13px; height: 13px; }

.clear-btn {
    background: none; border: 1.5px solid transparent;
    font-size: 11px; font-weight: 700; color: var(--text-3);
    cursor: pointer; padding: 5px 9px; border-radius: 7px;
    transition: all .15s; font-family: var(--font);
}
.clear-btn:hover { color: var(--red); background: var(--red-bg); border-color: var(--red-border); }

/* Exchange Banner */
.exchange-banner {
    background: var(--amber-bg); padding: 9px 16px;
    border-bottom: 1.5px solid var(--amber-border);
    display: flex; justify-content: space-between; align-items: center;
    font-size: 10.5px; font-weight: 800; color: var(--amber);
    text-transform: uppercase; letter-spacing: .06em; flex-shrink: 0;
}
.exchange-banner svg { width: 14px; height: 14px; }

/* Customer Section */
.customer-section {
    padding: 10px 14px; background: var(--surface-2);
    border-bottom: 1.5px solid var(--border);
    display: flex; flex-direction: column; gap: 7px; flex-shrink: 0;
}
.section-label {
    font-size: 9.5px; font-weight: 800; color: var(--text-3);
    text-transform: uppercase; letter-spacing: .08em;
}
.cust-input {
    width: 100%; padding: 8px 11px;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-family: var(--font); font-size: 12.5px;
    color: var(--text-1); background: var(--surface); outline: none;
    transition: border-color .18s;
}
.cust-input::placeholder { color: var(--text-3); }
.cust-input:focus { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(13,148,136,.08); }

/* Discount Section */
.discount-section {
    padding: 9px 14px; background: var(--surface);
    border-bottom: 1.5px solid var(--border); flex-shrink: 0;
}
.discount-row { display: flex; align-items: center; gap: 7px; margin-bottom: 6px; }
.type-toggle {
    display: flex; border: 1.5px solid var(--border);
    border-radius: 8px; overflow: hidden; flex-shrink: 0;
}
.tt-btn {
    padding: 6px 11px; font-family: var(--mono); font-size: 12px; font-weight: 700;
    background: var(--surface-2); color: var(--text-3);
    border: none; cursor: pointer; transition: all .15s; line-height: 1;
}
.tt-btn.active { background: var(--teal); color: #fff; }

.discount-input {
    flex: 1; padding: 7px 11px;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-family: var(--mono); font-size: 14px; font-weight: 700;
    color: var(--text-1); background: var(--surface); outline: none;
    transition: border-color .18s; min-width: 0;
}
.discount-input:focus { border-color: var(--teal); }
.discount-input::placeholder { font-family: var(--font); font-size: 12px; font-weight: 400; color: var(--text-3); }

.discount-badge {
    font-family: var(--mono); font-size: 11px; font-weight: 700;
    color: var(--green); white-space: nowrap; flex-shrink: 0;
}

.coupon-row { display: flex; gap: 6px; }
.coupon-input {
    flex: 1; padding: 7px 10px;
    border: 1.5px solid var(--border); border-radius: 8px;
    font-family: var(--mono); font-size: 11.5px;
    color: var(--text-1); background: var(--surface-2); outline: none;
    text-transform: uppercase; letter-spacing: .08em;
    transition: all .18s;
}
.coupon-input::placeholder { text-transform: none; letter-spacing: 0; font-family: var(--font); font-size: 12px; color: var(--text-3); }
.coupon-input:focus { border-color: var(--teal); background: var(--surface); }

.coupon-apply-btn {
    padding: 7px 12px; border-radius: 8px;
    background: var(--surface-2); border: 1.5px solid var(--border);
    color: var(--text-2); font-size: 12px; font-weight: 700;
    cursor: pointer; transition: all .15s; font-family: var(--font); white-space: nowrap;
}
.coupon-apply-btn:hover { background: var(--teal-bg); border-color: var(--teal-border); color: var(--teal); }

.coupon-applied {
    display: flex; align-items: center; justify-content: space-between;
    margin-top: 5px; padding: 5px 9px;
    background: var(--green-bg); border: 1px solid var(--green-border);
    border-radius: 7px; font-size: 11px; font-weight: 700; color: var(--green);
}
.coupon-remove { background: none; border: none; cursor: pointer; color: var(--red); font-size: 11px; font-weight: 700; }
.coupon-remove:hover { text-decoration: underline; }

/* Cart Items */
.cart-items {
    flex: 1; overflow-y: auto;
    padding: 10px 12px;
    display: flex; flex-direction: column; gap: 6px;
    scrollbar-width: thin; scrollbar-color: var(--border) transparent;
    min-height: 0;
}

.cart-item {
    background: var(--surface-2);
    border: 1.5px solid var(--border); border-radius: 10px;
    padding: 9px 11px;
    display: flex; align-items: center; gap: 9px;
    animation: ci-in .16s ease;
    transition: border-color .15s;
}
.cart-item:hover { border-color: var(--border-2); }
.cart-item.at-limit { border-color: var(--amber-border); background: var(--amber-bg); }

@keyframes ci-in {
    from { opacity: 0; transform: translateX(12px); }
    to   { opacity: 1; transform: translateX(0); }
}

.ci-info { flex: 1; min-width: 0; }
.ci-name { font-size: 12.5px; font-weight: 700; color: var(--text-1); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ci-unit { font-family: var(--mono); font-size: 10px; color: var(--teal); margin-top: 1px; }
.ci-sub  { font-family: var(--mono); font-size: 10px; color: var(--text-2); margin-top: 1px; font-weight: 600; }

.qty-ctrl {
    display: flex; align-items: center;
    background: var(--surface); border: 1.5px solid var(--border);
    border-radius: 8px; padding: 2px;
}
.qty-btn {
    width: 27px; height: 27px; border: none; background: none;
    color: var(--text-2); font-size: 16px; font-weight: 700;
    cursor: pointer; border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    transition: all .12s;
}
.qty-btn:hover { background: var(--border); color: var(--text-1); }
.qty-num { width: 28px; text-align: center; font-family: var(--mono); font-size: 13px; font-weight: 700; color: var(--text-1); }

.ci-remove {
    width: 29px; height: 29px; border-radius: 7px;
    display: flex; align-items: center; justify-content: center;
    color: var(--text-3); cursor: pointer;
    background: transparent; border: none; transition: all .15s; flex-shrink: 0;
}
.ci-remove:hover { color: var(--red); background: var(--red-bg); }

/* Empty Cart */
.cart-empty {
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    flex: 1; padding: 32px 20px; text-align: center;
}
.empty-ring {
    width: 62px; height: 62px;
    background: var(--surface-2);
    border: 2px dashed var(--border-2); border-radius: 50%;
    display: flex; align-items: center; justify-content: center; margin-bottom: 12px;
}
.empty-ring svg { width: 26px; height: 26px; color: var(--text-3); }
.empty-title { font-size: 13px; font-weight: 700; color: var(--text-2); }
.empty-sub  { font-size: 11.5px; color: var(--text-3); margin-top: 3px; }
.empty-hint {
    margin-top: 10px;
    display: inline-flex; align-items: center; gap: 4px;
    background: var(--surface-2); border: 1px solid var(--border);
    border-radius: 100px; padding: 4px 11px;
    font-size: 10.5px; font-weight: 700; color: var(--text-3); font-family: var(--mono);
}

/* Cart Footer */
.cart-foot {
    padding: 11px 14px 14px;
    border-top: 1.5px solid var(--border);
    background: var(--surface); flex-shrink: 0;
}
.summary-rows { display: flex; flex-direction: column; gap: 4px; margin-bottom: 8px; }
.sum-row {
    display: flex; justify-content: space-between; align-items: baseline;
    font-size: 11.5px; color: var(--text-2);
}
.sum-label { font-weight: 500; }
.sum-val { font-family: var(--mono); font-weight: 600; }
.sum-row.discount .sum-val { color: var(--green); }
.sum-row.exchange .sum-val { color: var(--amber); }

.total-row {
    display: flex; justify-content: space-between; align-items: baseline;
    padding: 9px 0 12px;
    border-top: 2px dashed var(--border-2);
}
.total-label { font-size: 12px; font-weight: 900; color: var(--text-1); text-transform: uppercase; letter-spacing: .08em; }
.total-val { font-family: var(--mono); font-size: 24px; font-weight: 700; color: var(--text-1); letter-spacing: -.03em; }
.total-sym { font-size: 13px; color: var(--text-3); margin-right: 2px; font-weight: 600; }

.cart-actions { display: flex; gap: 8px; }

.hold-btn {
    width: 36%; padding: 12px;
    background: var(--amber-bg); color: var(--amber);
    border: 1.5px solid var(--amber-border); border-radius: var(--radius-sm);
    font-family: var(--font); font-size: 12.5px; font-weight: 800;
    cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 5px;
    transition: all .18s;
}
.hold-btn:hover { background: var(--amber-border); }
.hold-btn:disabled { opacity: .4; cursor: not-allowed; }
.hold-btn svg { width: 15px; height: 15px; }

.pay-btn {
    flex: 1; padding: 12px;
    background: var(--teal); color: #fff; border: none;
    border-radius: var(--radius-sm);
    font-family: var(--font); font-size: 13.5px; font-weight: 800;
    cursor: pointer; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 2px;
    transition: all .18s; box-shadow: 0 4px 16px rgba(13,148,136,.3); letter-spacing: .02em;
}
.pay-btn:hover { background: #0f766e; box-shadow: 0 6px 22px rgba(13,148,136,.4); transform: translateY(-1px); }
.pay-btn:active { transform: scale(.98); }
.pay-btn:disabled { background: var(--border); color: var(--text-3); cursor: not-allowed; box-shadow: none; transform: none; }
.pay-btn .pay-hint { font-size: 9.5px; font-weight: 700; opacity: .7; background: rgba(255,255,255,.15); padding: 1px 6px; border-radius: 4px; font-family: var(--mono); }

/* ═══════════════════════════════════════════════
   MODALS
═══════════════════════════════════════════════ */
.modal-overlay {
    position: fixed; inset: 0; z-index: 50;
    display: flex; align-items: center; justify-content: center; padding: 20px;
}
.modal-bg {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.6); backdrop-filter: blur(10px);
}
.modal {
    position: relative; z-index: 10;
    background: var(--surface); border: 1.5px solid var(--border);
    border-radius: 18px; width: 100%; max-width: 455px;
    overflow: hidden; box-shadow: var(--shadow-lg);
}
.modal-head {
    padding: 20px 24px 16px;
    border-bottom: 1.5px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.modal-title { font-size: 16px; font-weight: 900; color: var(--text-1); letter-spacing: -.02em; }
.modal-subtitle { font-size: 11.5px; color: var(--text-3); margin-top: 2px; font-weight: 500; }
.modal-close {
    width: 30px; height: 30px; border-radius: 8px;
    background: var(--surface-2); border: 1.5px solid var(--border);
    color: var(--text-2); cursor: pointer;
    display: flex; align-items: center; justify-content: center; transition: all .15s;
}
.modal-close:hover { background: var(--red-bg); color: var(--red); border-color: var(--red-border); }
.modal-close svg { width: 14px; height: 14px; }

.modal-body { padding: 20px 24px; display: flex; flex-direction: column; gap: 15px; }

/* Amount display */
.amount-display {
    background: var(--surface-2); border: 1.5px solid var(--border);
    border-radius: 13px; padding: 16px; text-align: center;
}
.amount-label { font-size: 10px; font-weight: 800; color: var(--text-3); text-transform: uppercase; letter-spacing: .08em; }
.amount-value { font-family: var(--mono); font-size: 38px; font-weight: 700; color: var(--text-1); line-height: 1.1; margin-top: 3px; letter-spacing: -.04em; }
.amount-note { font-size: 11px; font-weight: 700; margin-top: 5px; }

/* Stock warning */
.warning-box {
    display: flex; align-items: flex-start; gap: 9px;
    background: var(--amber-bg); border: 1.5px solid var(--amber-border);
    border-radius: 10px; padding: 10px 13px;
}
.warning-box svg { width: 17px; height: 17px; color: var(--amber); flex-shrink: 0; margin-top: 1px; }
.warning-text { font-size: 11.5px; font-weight: 600; color: var(--amber); line-height: 1.5; }

/* Split payment */
.field-label { font-size: 10px; font-weight: 800; color: var(--text-2); text-transform: uppercase; letter-spacing: .07em; display: block; margin-bottom: 7px; }
.split-grid { display: flex; flex-direction: column; gap: 5px; }
.sp-row {
    display: flex; align-items: center; justify-content: space-between;
    background: var(--surface); border: 1.5px solid var(--border);
    border-radius: 9px; padding: 8px 13px; transition: all .15s;
}
.sp-row:focus-within { border-color: var(--teal); box-shadow: 0 0 0 3px rgba(13,148,136,.1); }
.sp-label { font-size: 13px; font-weight: 700; color: var(--text-1); display: flex; align-items: center; gap: 8px; }
.sp-input {
    width: 108px; text-align: right;
    font-family: var(--mono); font-size: 17px; font-weight: 700; color: var(--text-1);
    border: none; background: transparent; outline: none; padding: 2px;
}
.sp-input::-webkit-inner-spin-button, .sp-input::-webkit-outer-spin-button { display: none; }

.qc-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px; margin-top: 7px; }
.qc-btn {
    padding: 9px 4px; border: 1.5px solid var(--border-2); border-radius: 8px;
    background: var(--surface); color: var(--text-2);
    font-family: var(--mono); font-size: 11.5px; font-weight: 700;
    cursor: pointer; transition: all .15s;
}
.qc-btn:hover { border-color: var(--teal); color: var(--teal); background: var(--teal-bg); }
.qc-btn.exact { background: var(--text-1); color: var(--surface); border-color: var(--text-1); }
.qc-btn.exact:hover { opacity: .85; }

.change-box {
    display: flex; justify-content: space-between; align-items: center;
    padding: 12px 15px; border-radius: 10px; border: 1.5px solid; transition: all .2s;
}
.change-box.ok  { background: var(--green-bg); border-color: var(--green-border); }
.change-box.err { background: var(--red-bg);   border-color: var(--red-border); }
.change-lbl { font-size: 12px; font-weight: 700; letter-spacing: .02em; }
.change-box.ok  .change-lbl { color: var(--green-dark); }
.change-box.err .change-lbl { color: var(--red); }
.change-val { font-family: var(--mono); font-size: 21px; font-weight: 700; }
.change-box.ok  .change-val { color: var(--green); }
.change-box.err .change-val { color: var(--red); }
.err-hint { font-size: 11px; color: var(--red); text-align: center; font-weight: 700; letter-spacing: .03em; margin-top: -6px; }

.modal-foot { padding: 0 24px 20px; display: flex; gap: 9px; }
.modal-cancel {
    flex: 1; padding: 12px; background: var(--surface-2); border: 1.5px solid var(--border);
    border-radius: var(--radius-sm); font-family: var(--font); font-size: 13px; font-weight: 700;
    color: var(--text-2); cursor: pointer; transition: all .15s;
    display: flex; align-items: center; justify-content: center; gap: 6px;
}
.modal-cancel:hover { color: var(--text-1); border-color: var(--border-2); }
.modal-confirm {
    flex: 1; padding: 12px; background: var(--green); border: none;
    border-radius: var(--radius-sm); font-family: var(--font); font-size: 13.5px; font-weight: 800;
    color: #fff; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: all .18s; box-shadow: 0 4px 14px rgba(5,150,105,.3); letter-spacing: .02em;
}
.modal-confirm:hover { background: #047857; box-shadow: 0 6px 20px rgba(5,150,105,.4); }
.modal-confirm:disabled { background: var(--border); color: var(--text-3); cursor: not-allowed; box-shadow: none; }
.modal-confirm svg { width: 16px; height: 16px; }

/* ═══════════════════════════════════════════════
   KEYBOARD SHORTCUT CHEATSHEET
═══════════════════════════════════════════════ */
.kb-overlay {
    position: fixed; inset: 0; z-index: 100;
    background: rgba(0,0,0,.65); backdrop-filter: blur(12px);
    display: flex; align-items: center; justify-content: center; padding: 20px;
}
.kb-modal {
    background: var(--surface); border: 1.5px solid var(--border);
    border-radius: 20px; width: 100%; max-width: 480px;
    box-shadow: var(--shadow-lg); overflow: hidden;
}
.kb-head {
    padding: 20px 24px; border-bottom: 1.5px solid var(--border);
    display: flex; align-items: center; justify-content: space-between;
}
.kb-title {
    font-size: 16px; font-weight: 900; color: var(--text-1);
    display: flex; align-items: center; gap: 9px;
}
.kb-title svg { width: 18px; height: 18px; color: var(--teal); }
.kb-grid { padding: 16px 20px; display: flex; flex-direction: column; gap: 6px; }
.kb-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 9px 12px; border-radius: 9px;
    border: 1px solid var(--border); background: var(--surface-2);
}
.kb-desc { font-size: 12.5px; font-weight: 600; color: var(--text-1); }
.key {
    font-family: var(--mono); font-size: 10.5px; font-weight: 700;
    background: var(--surface); border: 1.5px solid var(--border-2);
    border-radius: 6px; padding: 3px 8px; color: var(--text-1);
    box-shadow: 0 2px 0 var(--border-2);
}

/* ═══════════════════════════════════════════════
   TOAST NOTIFICATIONS
═══════════════════════════════════════════════ */
.toast-dock {
    position: fixed; bottom: 18px; right: 18px; z-index: 200;
    display: flex; flex-direction: column-reverse; gap: 7px;
    pointer-events: none; max-width: 310px;
}
.toast {
    display: flex; align-items: center; gap: 9px;
    background: var(--surface); border: 1.5px solid var(--border);
    border-radius: 11px; padding: 10px 14px;
    box-shadow: var(--shadow-lg);
    animation: toast-in .2s ease;
    pointer-events: all;
}
.toast-bar { width: 3px; height: 32px; border-radius: 2px; flex-shrink: 0; }
.toast-msg { font-size: 12.5px; font-weight: 600; color: var(--text-1); line-height: 1.4; }
.toast.success .toast-bar { background: var(--green); }
.toast.error   .toast-bar { background: var(--red); }
.toast.warning .toast-bar { background: var(--amber); }
.toast.info    .toast-bar { background: var(--teal); }

@keyframes toast-in {
    from { opacity: 0; transform: translateY(8px) scale(.97); }
    to   { opacity: 1; transform: translateY(0) scale(1); }
}

/* ═══════════════════════════════════════════════
   RESPONSIVE
═══════════════════════════════════════════════ */
@media (max-width: 1100px) {
    .pos-root { flex-direction: column; }
    .panel-right { width: 100%; }
    .product-list { grid-template-columns: repeat(auto-fill, minmax(155px, 1fr)); }
}
@media (max-width: 640px) {
    .pos-root { padding: 9px; gap: 9px; }
    .product-list { grid-template-columns: repeat(2, 1fr); }
    .p-img-wrap { height: 72px; }
}
</style>

<div class="pos-root"
     x-data="posSystem()"
     :data-theme="darkMode ? 'dark' : 'light'"
     @keydown.window="handleKeydown($event)">

    <!-- ══════════════════════════════════════
         LEFT PANEL — Products
    ══════════════════════════════════════ -->
    <div class="panel-left">
        <div class="panel-top">

            <div class="top-row">
                <!-- Status badge -->
                <div class="status-badge" :class="isOnline ? 'online' : 'offline'">
                    <span class="status-dot"></span>
                    <span x-text="isOnline ? 'Online' : 'Offline'"></span>
                </div>

                <!-- Search -->
                <div class="search-wrap">
                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" x-model="search" x-ref="searchInput" autofocus
                        placeholder="Scan barcode or search product…"
                        class="search-input">
                </div>

                <!-- Shortcut help -->
                <button @click="kbOpen = true" class="icon-btn" title="Keyboard shortcuts (?)">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </button>

                <!-- Dark mode toggle -->
                <button @click="toggleDark()" class="icon-btn" :title="darkMode ? 'Switch to light' : 'Switch to dark'">
                    <svg x-show="!darkMode" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                    <svg x-show="darkMode" style="display:none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </button>
            </div>

            <!-- Category Pills -->
            <div class="cat-row">
                <button @click="selectedCategory = 'all'"
                    :class="selectedCategory == 'all' ? 'active' : ''"
                    class="cat-pill">All Items</button>
                @foreach($categories as $cat)
                    <button @click="selectedCategory = '{{ $cat->id }}'"
                        :class="selectedCategory == '{{ $cat->id }}' ? 'active' : ''"
                        class="cat-pill">{{ $cat->name }}</button>
                @endforeach
            </div>
        </div>

        <!-- Product Grid -->
        <div class="product-list">
            <template x-for="product in filteredProducts()" :key="product.id">
                <div @click="addToCart(product)"
                     class="product-card"
                     :class="product.stock_quantity < 1 ? 'out-of-stock' : ''">

                    <div class="p-img-wrap">
                        <img :src="product.image ? '/storage/' + product.image : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(product.name) + '&background=e2e0d8&color=141210&bold=true&size=128'"
                             :alt="product.name">
                    </div>

                    <div>
                        <div class="p-name" x-text="product.name"></div>
                        <div class="p-barcode" x-text="product.barcode"></div>
                    </div>

                    <div class="p-foot">
                        <div class="p-price">
                            <span class="p-price-sym">৳</span><span x-text="formatNumber(product.selling_price)"></span>
                        </div>
                        <div class="stock-chip"
                             :class="product.stock_quantity < 1 ? 'stock-out' : product.stock_quantity < 5 ? 'stock-low' : 'stock-ok'"
                             x-text="product.stock_quantity < 1 ? 'Out' : product.stock_quantity + ' left'">
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         RIGHT PANEL — Cart
    ══════════════════════════════════════ -->
    <div class="panel-right">

        <!-- Cart Header -->
        <div class="cart-head">
            <div class="cart-title">
                <div class="cart-badge-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                Order
                <span class="cart-count" x-show="cart.length > 0" x-text="cart.length"></span>
            </div>
            <div class="cart-head-actions">
                <button @click="holdCartsModalOpen = true"
                    x-show="heldCarts.length > 0"
                    style="display:none"
                    class="held-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-text="heldCarts.length"></span> Held
                </button>
                <button @click="cart = []" x-show="cart.length > 0" class="clear-btn">Clear</button>
            </div>
        </div>

        <!-- Exchange Mode Banner -->
        <div x-show="isExchangeMode" style="display:none" class="exchange-banner">
            <div style="display:flex;align-items:center;gap:7px">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Exchange Mode
            </div>
            <span>Credit: ৳<span x-text="formatNumber(exchangeCredit)"></span></span>
        </div>

        <!-- Customer Section -->
        <div class="customer-section">
            <div class="section-label">Customer (Optional)</div>
            <div style="position:relative">
                <input type="text" x-model="customerPhone"
                    @input.debounce.500ms="searchCustomer()"
                    placeholder="📱 Mobile number"
                    class="cust-input">
                <div x-show="isSearchingCustomer"
                    style="position:absolute;right:10px;top:50%;transform:translateY(-50%)">
                    <svg class="animate-spin" style="width:15px;height:15px;color:var(--teal)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                    </svg>
                </div>
            </div>
            <input type="text" x-model="customerName" placeholder="👤 Customer name" class="cust-input">
        </div>

        <!-- Discount Section -->
        <div class="discount-section">
            <div class="section-label" style="margin-bottom:7px">Discount</div>
            <div class="discount-row">
                <div class="type-toggle">
                    <button @click="discountType = 'percent'" class="tt-btn" :class="discountType === 'percent' ? 'active' : ''">%</button>
                    <button @click="discountType = 'flat'"    class="tt-btn" :class="discountType === 'flat'    ? 'active' : ''">৳</button>
                </div>
                <input type="number" x-model.number="discountValue"
                    class="discount-input"
                    :placeholder="discountType === 'percent' ? 'Enter %' : 'Flat amount'"
                    min="0" :max="discountType === 'percent' ? 100 : getTotal()">
                <span x-show="getDiscount() > 0" class="discount-badge">
                    -৳<span x-text="formatNumber(getDiscount())"></span>
                </span>
            </div>
            <div class="coupon-row">
                <input type="text" x-model="couponCode"
                    @keyup.enter="applyCoupon()"
                    placeholder="Coupon code"
                    class="coupon-input">
                <button @click="applyCoupon()" class="coupon-apply-btn">Apply</button>
            </div>
            <div x-show="appliedCoupon" style="display:none" class="coupon-applied">
                <span>✓ Coupon "<span x-text="appliedCoupon?.code"></span>" applied</span>
                <button @click="removeCoupon()" class="coupon-remove">✕ Remove</button>
            </div>
        </div>

        <!-- Cart Items -->
        <div class="cart-items">
            <template x-for="(item, index) in cart" :key="index">
                <div class="cart-item" :class="item.qty >= item.max_stock ? 'at-limit' : ''">
                    <div class="ci-info">
                        <div class="ci-name" x-text="item.name"></div>
                        <div class="ci-unit">৳<span x-text="formatNumber(item.price)"></span> each</div>
                        <div class="ci-sub">= ৳<span x-text="formatNumber(item.price * item.qty)"></span></div>
                    </div>
                    <div class="qty-ctrl">
                        <button @click="updateQty(index, -1)" class="qty-btn">−</button>
                        <div class="qty-num" x-text="item.qty"></div>
                        <button @click="updateQty(index, 1)" class="qty-btn">+</button>
                    </div>
                    <button @click="removeItem(index)" class="ci-remove" title="Remove">
                        <svg style="width:15px;height:15px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>

            <template x-if="cart.length === 0">
                <div class="cart-empty">
                    <div class="empty-ring">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="empty-title">Cart is empty</div>
                    <div class="empty-sub">Click a product or scan a barcode to add</div>
                    <div class="empty-hint">
                        <svg style="width:12px;height:12px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01"/>
                        </svg>
                        Press <span class="key" style="font-size:9px;padding:1px 5px">?</span> for shortcuts
                    </div>
                </div>
            </template>
        </div>

        <!-- Cart Footer -->
        <div class="cart-foot">
            <div class="summary-rows">
                <div class="sum-row">
                    <span class="sum-label">Subtotal (<span x-text="cart.reduce((s,i) => s + i.qty, 0)"></span> items)</span>
                    <span class="sum-val">৳<span x-text="formatNumber(getTotal())"></span></span>
                </div>
                <div class="sum-row discount" x-show="getDiscount() > 0">
                    <span class="sum-label">Discount</span>
                    <span class="sum-val">-৳<span x-text="formatNumber(getDiscount())"></span></span>
                </div>
                <div class="sum-row exchange" x-show="isExchangeMode && exchangeCredit > 0" style="display:none">
                    <span class="sum-label">Exchange Credit</span>
                    <span class="sum-val">-৳<span x-text="formatNumber(exchangeCredit)"></span></span>
                </div>
            </div>

            <div class="total-row">
                <div class="total-label">Total Due</div>
                <div class="total-val">
                    <span class="total-sym">৳</span><span x-text="formatNumber(getPayableTotal())"></span>
                </div>
            </div>

            <div class="cart-actions">
                <button @click="suspendCurrentCart()" :disabled="cart.length === 0" class="hold-btn">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Hold
                </button>
                <button @click="openCheckout()" :disabled="!canProceedToCheckout()" class="pay-btn">
                    <div style="display:flex;align-items:center;gap:6px">
                        <svg style="width:16px;height:16px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Proceed to Payment
                    </div>
                    <span class="pay-hint">F2</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         CHECKOUT MODAL
    ══════════════════════════════════════ -->
    <div x-show="checkoutModalOpen" style="display:none" class="modal-overlay"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        <div class="modal-bg" @click="checkoutModalOpen = false"></div>

        <div class="modal"
             x-show="checkoutModalOpen"
             x-transition:enter="ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-3" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">

            <div class="modal-head">
                <div>
                    <div class="modal-title">Complete Payment</div>
                    <div class="modal-subtitle" x-text="cart.length + ' item(s)  ·  ' + (customerName || 'Walk-in Customer')"></div>
                </div>
                <button @click="checkoutModalOpen = false" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="modal-body">
                <!-- Amount -->
                <div class="amount-display">
                    <div class="amount-label">Total Amount Due</div>
                    <div class="amount-value">
                        <span style="font-size:20px;color:var(--text-3);margin-right:3px;font-weight:600">৳</span><span x-text="formatNumber(getPayableTotal())"></span>
                    </div>
                    <div x-show="getDiscount() > 0" class="amount-note" style="color:var(--green)">
                        🎉 Saved ৳<span x-text="formatNumber(getDiscount())"></span>
                    </div>
                    <div x-show="isExchangeMode" style="display:none" class="amount-note" style="color:var(--amber)">
                        Exchange credit: -৳<span x-text="formatNumber(exchangeCredit)"></span>
                    </div>
                </div>

                <!-- Stock Warning -->
                <div x-show="hasLowStockItems()" style="display:none" class="warning-box">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="warning-text">One or more items are at their stock limit. Verify availability before confirming.</div>
                </div>

                <!-- Split Payment -->
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px">
                        <label class="field-label" style="margin-bottom:0">Payment Method</label>
                        <span style="font-size:9.5px;font-weight:800;color:var(--teal);background:var(--teal-bg);padding:2px 8px;border-radius:100px;border:1px solid var(--teal-border)">Split Pay</span>
                    </div>
                    <div class="split-grid">
                        <div class="sp-row">
                            <div class="sp-label"><span>💵</span> Cash</div>
                            <input type="number" x-model.number="payCash" class="sp-input" placeholder="0">
                        </div>
                        <div class="sp-row">
                            <div class="sp-label"><span>💳</span> Card</div>
                            <input type="number" x-model.number="payCard" class="sp-input" placeholder="0">
                        </div>
                        <div class="sp-row">
                            <div class="sp-label"><span>📱</span> bKash</div>
                            <input type="number" x-model.number="payBkash" class="sp-input" placeholder="0">
                        </div>
                    </div>
                    <div class="qc-grid">
                        <button @click="payCash = getPayableTotal(); payCard = 0; payBkash = 0;" class="qc-btn exact">Exact</button>
                        <button @click="payCash = 500"  class="qc-btn">৳500</button>
                        <button @click="payCash = 1000" class="qc-btn">৳1K</button>
                        <button @click="payCash = 2000" class="qc-btn">৳2K</button>
                    </div>
                </div>

                <!-- Change -->
                <div class="change-box" :class="getChange() >= 0 ? 'ok' : 'err'">
                    <span class="change-lbl" x-text="getChange() >= 0 ? 'Change to Return' : 'Amount Still Owed'"></span>
                    <span class="change-val">৳<span x-text="formatNumber(Math.abs(getChange()))"></span></span>
                </div>
                <p x-show="getChange() < 0" class="err-hint">Received amount is less than total due</p>
            </div>

            <div class="modal-foot">
                <button @click="checkoutModalOpen = false" class="modal-cancel">
                    Cancel <span class="key" style="font-size:9px">ESC</span>
                </button>
                <button @click="submitOrder()"
                        :disabled="getChange() < 0 || isProcessing"
                        class="modal-confirm">
                    <svg x-show="!isProcessing" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span x-show="!isProcessing" style="display:flex;align-items:center;gap:6px">
                        Confirm Sale <span class="key" style="font-size:9px;background:rgba(255,255,255,.2);border-color:rgba(255,255,255,.3);color:#fff;box-shadow:none">ENTER</span>
                    </span>
                    <span x-show="isProcessing">Processing…</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         HELD CARTS MODAL
    ══════════════════════════════════════ -->
    <div x-show="holdCartsModalOpen" style="display:none" class="modal-overlay"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="modal-bg" @click="holdCartsModalOpen = false"></div>
        <div class="modal" style="max-width:440px"
             x-show="holdCartsModalOpen"
             x-transition:enter="ease-out duration-250" x-transition:enter-start="opacity-0 scale-95 translate-y-3" x-transition:enter-end="opacity-100 scale-100 translate-y-0">
            <div class="modal-head">
                <div>
                    <div class="modal-title" style="display:flex;align-items:center;gap:8px">
                        <svg style="width:18px;height:18px;color:var(--amber)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Suspended Carts
                    </div>
                    <div class="modal-subtitle">Tap Resume to continue a suspended order</div>
                </div>
                <button @click="holdCartsModalOpen = false" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div style="max-height:400px;overflow-y:auto;background:var(--bg)">
                <template x-if="heldCarts.length === 0">
                    <div style="padding:36px;text-align:center;font-size:13px;font-weight:600;color:var(--text-3)">No suspended carts.</div>
                </template>
                <template x-for="(hCart, index) in heldCarts" :key="hCart.id">
                    <div style="padding:13px 18px;display:flex;justify-content:space-between;align-items:center;background:var(--surface);border-bottom:1px solid var(--border)">
                        <div>
                            <div style="font-weight:700;font-size:13px;color:var(--text-1)">
                                Held at <span style="color:var(--teal);font-family:var(--mono)" x-text="hCart.time"></span>
                            </div>
                            <div style="font-size:11px;color:var(--text-2);margin-top:3px;font-family:var(--mono)">
                                <span x-text="hCart.cartData.length"></span> items · ৳<span x-text="formatNumber(hCart.total)"></span>
                            </div>
                            <div x-show="hCart.customerName || hCart.customerPhone"
                                 style="font-size:11px;color:var(--text-3);margin-top:2px">
                                <span x-text="hCart.customerName || 'Guest'"></span>
                                <span x-show="hCart.customerPhone" x-text="' · ' + hCart.customerPhone"></span>
                            </div>
                        </div>
                        <div style="display:flex;flex-direction:column;gap:6px">
                            <button @click="resumeHeldCart(index)"
                                style="background:var(--teal-bg);color:var(--teal);border:1.5px solid var(--teal-border);padding:7px 16px;border-radius:8px;font-size:12px;font-weight:800;cursor:pointer;font-family:var(--font)">
                                Resume
                            </button>
                            <button @click="deleteHeldCart(index)"
                                style="background:var(--red-bg);color:var(--red);border:1.5px solid var(--red-border);padding:7px 16px;border-radius:8px;font-size:12px;font-weight:800;cursor:pointer;font-family:var(--font)">
                                Drop
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         KEYBOARD SHORTCUT CHEATSHEET
    ══════════════════════════════════════ -->
    <div x-show="kbOpen" style="display:none" class="kb-overlay"
         @click.self="kbOpen = false"
         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="kb-modal"
             x-transition:enter="ease-out duration-250" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="kb-head">
                <h2 class="kb-title">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Keyboard Shortcuts
                </h2>
                <button @click="kbOpen = false" class="modal-close">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="kb-grid">
                <div class="kb-row">
                    <span class="kb-desc">Proceed to Payment</span>
                    <span class="key">F2</span>
                </div>
                <div class="kb-row">
                    <span class="kb-desc">Confirm Sale (when modal open)</span>
                    <span class="key">Enter</span>
                </div>
                <div class="kb-row">
                    <span class="kb-desc">Close modal / Clear search</span>
                    <span class="key">Esc</span>
                </div>
                <div class="kb-row">
                    <span class="kb-desc">Auto-add single barcode result</span>
                    <span class="key">Enter</span>
                </div>
                <div class="kb-row">
                    <span class="kb-desc">Show this cheatsheet</span>
                    <span class="key">?</span>
                </div>
                <div class="kb-row">
                    <span class="kb-desc">Toggle dark / light mode</span>
                    <span class="key">D</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════
         TOAST NOTIFICATIONS
    ══════════════════════════════════════ -->
    <div class="toast-dock">
        <template x-for="toast in toasts" :key="toast.id">
            <div class="toast" :class="toast.type">
                <div class="toast-bar"></div>
                <span class="toast-msg" x-text="toast.msg"></span>
            </div>
        </template>
    </div>

</div>

<script>
function posSystem() {
    return {
        /* ── Core ── */
        isOnline: navigator.onLine,
        isSyncing: false,
        darkMode: localStorage.getItem('nexa_dark') === 'true',

        search: '',
        selectedCategory: 'all',
        products: @json($products),
        cart: [],

        /* ── Customer ── */
        customerName: '',
        customerPhone: '',
        isSearchingCustomer: false,

        /* ── Discount ── */
        discountType: 'percent',
        discountValue: 0,
        couponCode: '',
        appliedCoupon: null,

        /* ── Checkout ── */
        checkoutModalOpen: false,
        isProcessing: false,
        payCash: 0,
        payCard: 0,
        payBkash: 0,

        /* ── UI State ── */
        heldCarts: JSON.parse(localStorage.getItem('nexa_held_carts')) || [],
        holdCartsModalOpen: false,
        kbOpen: false,
        toasts: [],

        /* ── Exchange Mode ── */
        isExchangeMode: {{ $exchangeOrder ? 'true' : 'false' }},
        exchangeOrderId: {{ $exchangeOrder ?? 'null' }},
        returnProductId: {{ $returnProduct ?? 'null' }},
        returnQty: {{ $returnQty ?? 0 }},
        exchangeCredit: {{ $credit ?? 0 }},

        /* ───────────────────────────────────
           INIT
        ─────────────────────────────────── */
        init() {
            window.addEventListener('online',  () => {
                this.isOnline = true;
                this.showToast('Connection restored. Syncing orders…', 'success');
                this.syncOfflineOrders();
            });
            window.addEventListener('offline', () => {
                this.isOnline = false;
                this.showToast('Offline mode — orders are saved locally', 'warning');
            });
            if (this.isOnline) this.syncOfflineOrders();
        },

        /* ───────────────────────────────────
           DARK MODE
        ─────────────────────────────────── */
        toggleDark() {
            this.darkMode = !this.darkMode;
            localStorage.setItem('nexa_dark', this.darkMode);
        },

        /* ───────────────────────────────────
           TOAST SYSTEM
        ─────────────────────────────────── */
        showToast(msg, type = 'info', ms = 3200) {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, msg, type });
            setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, ms);
        },

        /* ───────────────────────────────────
           BEEP FEEDBACK
        ─────────────────────────────────── */
        playBeep(ok = true) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = ok ? 1200 : 380;
                osc.type = ok ? 'sine' : 'square';
                gain.gain.setValueAtTime(0.22, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + (ok ? 0.13 : 0.22));
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + (ok ? 0.13 : 0.22));
            } catch(e) {}
        },

        /* ───────────────────────────────────
           KEYBOARD SHORTCUTS
        ─────────────────────────────────── */
        handleKeydown(e) {
            // Inside checkout modal
            if (this.checkoutModalOpen) {
                if (e.key === 'Enter')  { e.preventDefault(); if (this.getChange() >= 0 && !this.isProcessing) this.submitOrder(); }
                if (e.key === 'Escape') { e.preventDefault(); this.checkoutModalOpen = false; }
                return;
            }
            // Shortcut overlay open
            if (this.kbOpen) {
                if (e.key === 'Escape') { this.kbOpen = false; }
                return;
            }
            // Global
            if (e.key === 'F2')    { e.preventDefault(); if (this.canProceedToCheckout()) this.openCheckout(); }
            if (e.key === 'Escape'){ this.search = ''; this.$refs.searchInput.focus(); }
            if (e.key === '?')     { e.preventDefault(); this.kbOpen = true; }
            if ((e.key === 'd' || e.key === 'D') && document.activeElement.tagName !== 'INPUT') this.toggleDark();
            if (e.key === 'Enter' && document.activeElement === this.$refs.searchInput) {
                e.preventDefault();
                const filtered = this.filteredProducts();
                if (filtered.length === 1) this.addToCart(filtered[0]);
            }
        },

        /* ───────────────────────────────────
           PRODUCT FILTER
        ─────────────────────────────────── */
        filteredProducts() {
            return this.products.filter(p => {
                const matchSearch = p.name.toLowerCase().includes(this.search.toLowerCase()) || p.barcode.includes(this.search);
                const matchCat    = this.selectedCategory === 'all' || p.category_id == this.selectedCategory;
                return matchSearch && matchCat;
            });
        },

        /* ───────────────────────────────────
           CUSTOMER LOOKUP
        ─────────────────────────────────── */
        async searchCustomer() {
            if (this.customerPhone.length < 9) {
                if (!this.customerPhone) this.customerName = '';
                return;
            }
            if (!this.isOnline) return;
            this.isSearchingCustomer = true;
            try {
                const res = await fetch(`{{ route('pos.customer-lookup') }}?phone=${this.customerPhone}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    const data = await res.json();
                    if (data.found) {
                        this.customerName = data.name;
                        this.showToast('Customer found: ' + data.name, 'success');
                    }
                }
            } catch(e) {}
            this.isSearchingCustomer = false;
        },

        /* ───────────────────────────────────
           CART
        ─────────────────────────────────── */
        addToCart(product) {
            if (product.stock_quantity < 1) {
                this.playBeep(false);
                this.showToast(product.name + ' is out of stock!', 'error');
                return;
            }
            const existing = this.cart.find(i => i.id === product.id);
            if (existing) {
                if (existing.qty < product.stock_quantity) {
                    existing.qty++;
                    this.playBeep(true);
                } else {
                    this.playBeep(false);
                    this.showToast('Maximum stock reached for ' + product.name, 'warning');
                }
            } else {
                this.cart.push({
                    id: product.id,
                    name: product.name,
                    price: product.selling_price,
                    qty: 1,
                    max_stock: product.stock_quantity
                });
                this.playBeep(true);
            }
            this.search = '';
            this.$refs.searchInput.focus();
        },

        updateQty(index, amount) {
            const item   = this.cart[index];
            const newQty = item.qty + amount;
            if (newQty <= 0) {
                this.cart.splice(index, 1);
            } else if (newQty <= item.max_stock) {
                item.qty = newQty;
            } else {
                this.playBeep(false);
                this.showToast('Cannot exceed available stock for ' + item.name, 'warning');
            }
        },

        removeItem(index) { this.cart.splice(index, 1); },

        /* ───────────────────────────────────
           DISCOUNT & COUPONS
        ─────────────────────────────────── */
        getDiscount() {
            let disc = 0;
            // Coupon takes priority over manual discount
            if (this.appliedCoupon) {
                disc = this.appliedCoupon.type === 'percent'
                    ? (this.getTotal() * this.appliedCoupon.value / 100)
                    : this.appliedCoupon.value;
            } else if (this.discountValue > 0) {
                disc = this.discountType === 'percent'
                    ? (this.getTotal() * Math.min(this.discountValue, 100) / 100)
                    : this.discountValue;
            }
            return Math.max(0, Math.min(disc, this.getTotal()));
        },

        applyCoupon() {
            const code = this.couponCode.trim().toUpperCase();
            if (!code) return;
            // Demo coupons — validate server-side in production
            const coupons = {
                'SAVE10':  { code: 'SAVE10',  type: 'percent', value: 10 },
                'FLAT50':  { code: 'FLAT50',  type: 'flat',    value: 50 },
                'WELCOME': { code: 'WELCOME', type: 'percent', value: 5  }
            };
            if (coupons[code]) {
                this.appliedCoupon = coupons[code];
                this.discountValue = 0;
                this.showToast('Coupon applied! ' + (coupons[code].type === 'percent' ? coupons[code].value + '% off' : '৳' + coupons[code].value + ' off'), 'success');
            } else {
                this.playBeep(false);
                this.showToast('Invalid coupon code: ' + code, 'error');
            }
        },

        removeCoupon() {
            this.appliedCoupon = null;
            this.couponCode    = '';
            this.showToast('Coupon removed', 'info');
        },

        /* ───────────────────────────────────
           TOTALS
        ─────────────────────────────────── */
        getTotal() {
            return this.cart.reduce((s, i) => s + (i.price * i.qty), 0);
        },
        getPayableTotal() {
            return Math.max(0, this.getTotal() - this.getDiscount() - this.exchangeCredit);
        },
        getPaidAmount() {
            return (Number(this.payCash) || 0) + (Number(this.payCard) || 0) + (Number(this.payBkash) || 0);
        },
        getChange() {
            return this.getPaidAmount() - this.getPayableTotal();
        },
        canProceedToCheckout() {
            if (this.cart.length === 0) return false;
            if (this.isExchangeMode && this.getTotal() < this.exchangeCredit) return false;
            return true;
        },
        hasLowStockItems() {
            return this.cart.some(i => i.qty >= i.max_stock);
        },
        formatNumber(n) {
            return parseFloat(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },
        getPaymentMethodString() {
            const methods = [];
            if (this.payCash   > 0) methods.push('Cash');
            if (this.payCard   > 0) methods.push('Card');
            if (this.payBkash  > 0) methods.push('bKash');
            if (methods.length > 1) return methods.join(' + ');
            if (methods.length === 1) return methods[0].toLowerCase();
            return 'cash';
        },

        /* ───────────────────────────────────
           HOLD / RESUME CARTS
        ─────────────────────────────────── */
        suspendCurrentCart() {
            if (!this.cart.length) return;
            const rec = {
                id:           Date.now(),
                time:         new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                cartData:     JSON.parse(JSON.stringify(this.cart)),
                customerName: this.customerName,
                customerPhone:this.customerPhone,
                total:        this.getTotal()
            };
            this.heldCarts.push(rec);
            localStorage.setItem('nexa_held_carts', JSON.stringify(this.heldCarts));
            this.cart = [];
            this.customerName = '';
            this.customerPhone = '';
            this.showToast('Cart suspended. Ready for next customer.', 'info');
        },
        resumeHeldCart(index) {
            const rec = this.heldCarts[index];
            if (this.cart.length > 0 && !confirm('Resume this cart? Your current cart will be cleared.')) return;
            this.cart          = JSON.parse(JSON.stringify(rec.cartData));
            this.customerName  = rec.customerName  || '';
            this.customerPhone = rec.customerPhone || '';
            this.heldCarts.splice(index, 1);
            localStorage.setItem('nexa_held_carts', JSON.stringify(this.heldCarts));
            this.holdCartsModalOpen = false;
            this.showToast('Cart resumed!', 'success');
        },
        deleteHeldCart(index) {
            if (confirm('Permanently delete this suspended cart?')) {
                this.heldCarts.splice(index, 1);
                localStorage.setItem('nexa_held_carts', JSON.stringify(this.heldCarts));
            }
        },

        /* ───────────────────────────────────
           CHECKOUT
        ─────────────────────────────────── */
openCheckout() {
            if (!this.canProceedToCheckout()) {
                this.showToast('Cart must total ৳' + this.formatNumber(this.exchangeCredit) + ' or more for exchange.', 'error');
                return;
            }
            if (this.hasLowStockItems()) this.showToast('⚠️ Some items are at stock limit — check before confirming', 'warning');
            this.payCash  = this.getPayableTotal();
            this.payCard  = 0;
            this.payBkash = 0;
            this.checkoutModalOpen = true;
        },

        async submitOrder() {
            if (this.getChange() < 0) return;
            this.isProcessing = true;

            const payload = {
                cart:                    this.cart,
                items:                   this.cart,
                total_amount:            this.getTotal(),
                discount_amount:         this.getDiscount(),
                coupon_code:             this.appliedCoupon?.code || null,
                payment_method:          this.getPaymentMethodString(),
                paid_amount:             this.getPaidAmount(),
                customer_name:           this.customerName,
                customer_phone:          this.customerPhone,
                created_at:              new Date().toISOString(),
                is_exchange:             this.isExchangeMode,
                exchange_for_order_id:   this.exchangeOrderId,
                return_product_id:       this.returnProductId,
                return_qty:              this.returnQty,
                exchange_credit:         this.exchangeCredit
            };

            if (this.isOnline) {
                try {
                    const res  = await fetch('{{ route('pos.checkout') }}', {
                        method:  'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body:    JSON.stringify(payload)
                    });
                    const data = await res.json();

                    if (res.ok && data.success) {
                        this.playBeep(true);
                        
                        // 1. Instantly pop open the receipt window
                        window.open(`/pos/receipt/${data.order_id}`, 'ReceiptWindow', 'width=400,height=620');
                        
                        // 2. Clear the cart and reset the UI instantly
                        this.cart = []; this.customerName = ''; this.customerPhone = '';
                        this.discountValue = 0; this.appliedCoupon = null; this.couponCode = '';
                        this.checkoutModalOpen = false;
                        
                        // 3. Show a nice success message without reloading the page!
                        this.showToast('Payment successful! Change: ৳' + this.formatNumber(data.change), 'success');
                        
                    } else {
                        this.playBeep(false);
                        this.showToast('Error: ' + (data.message || 'Something went wrong'), 'error');
                    }
                } catch(e) {
                    this.playBeep(false);
                    this.showToast('Server error. Please check your connection.', 'error');
                }
            } else {
                if (this.isExchangeMode) {
                    this.showToast('Cannot process exchanges while offline.', 'error');
                    this.isProcessing = false; return;
                }
                const offline = JSON.parse(localStorage.getItem('nexa_offline_orders')) || [];
                offline.push(payload);
                localStorage.setItem('nexa_offline_orders', JSON.stringify(offline));
                this.showToast('Offline order saved. Will auto-sync when online.', 'warning');
                this.cart = []; this.customerName = ''; this.customerPhone = '';
                this.discountValue = 0; this.appliedCoupon = null; this.couponCode = '';
                this.checkoutModalOpen = false;
            }

            this.isProcessing = false;
        },

        /* ───────────────────────────────────
           OFFLINE SYNC
        ─────────────────────────────────── */
        async syncOfflineOrders() {
            const offline = JSON.parse(localStorage.getItem('nexa_offline_orders')) || [];
            if (!offline.length || this.isSyncing) return;
            this.isSyncing = true;
            try {
                const res  = await fetch('{{ route('pos.sync') }}', {
                    method:  'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body:    JSON.stringify({ orders: offline })
                });
                const data = await res.json();
                if (data.success) {
                    localStorage.removeItem('nexa_offline_orders');
                    this.showToast(`✅ ${data.synced} offline order(s) synced!`, 'success');
                    setTimeout(() => window.location.reload(), 1800);
                }
            } catch(e) {}
            this.isSyncing = false;
        }
    };
}
</script>
</x-app-layout>