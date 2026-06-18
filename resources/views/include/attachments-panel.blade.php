{{--
    Reusable attachments panel.
    Required variables:
      $attachable      – the Invoice or Purchase model instance
      $attachableType  – 'invoice' or 'purchase'
      $attachableId    – $attachable->id
    Optional:
      $labelOptions    – array of suggested label strings
--}}
@php
    $attachments   = $attachable->attachments()->get();
    $defaultLabels = $attachableType === 'invoice'
        ? ['Aadhaar Card', 'PAN Card', 'Driving Licence', 'Voter ID', 'Passport', 'Other ID Proof']
        : ['Original Bill', 'Device Photo', 'Box Photo', 'Accessories Photo', 'Other Document'];
    $labelOpts   = $labelOptions ?? $defaultLabels;
    $tokenRoute  = route('attachments.token', ['type' => $attachableType, 'id' => $attachableId]);
    $storeRoute  = route('attachments.store');
    $panelId     = $attachableType . '-' . $attachableId;
@endphp

{{-- ── Styles (injected once into <head>) ────────────────────────────────── --}}
@once
@push('styles')
<style>
/* ── Panel header ────────────────────────────────────────────────────────── */
.att-panel-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 1rem;
}
.att-panel-header h5 { margin: 0; font-size: 0.95rem; font-weight: 700; }
.att-btn-group { display: flex; flex-wrap: wrap; gap: 0.35rem; }

/* ── Upload form ─────────────────────────────────────────────────────────── */
.att-upload-box {
    background: #f8f9fc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}
.att-upload-row {
    display: grid;
    grid-template-columns: 1fr 2fr auto;
    gap: 0.6rem;
    align-items: end;
}
@media (max-width: 767.98px) {
    .att-upload-row { grid-template-columns: 1fr; }
    .att-upload-btn { width: 100%; }
}

/* ── QR panel ────────────────────────────────────────────────────────────── */
.att-qr-panel {
    background: #f8f9fc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 1.25rem 1rem;
    margin-bottom: 1rem;
    text-align: center;
}
.att-qr-actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 0.75rem;
}

/* ── File grid ───────────────────────────────────────────────────────────── */
.att-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 0.75rem;
}
@media (max-width: 400px) {
    .att-grid { grid-template-columns: repeat(2, 1fr); }
}
.att-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 0.6rem;
    background: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    transition: box-shadow 0.15s;
}
.att-card:hover { box-shadow: 0 3px 8px rgba(0,0,0,0.1); }
.att-thumb {
    width: 100%;
    height: 90px;
    object-fit: cover;
    border-radius: 5px;
    margin-bottom: 0.4rem;
    display: block;
}
.att-thumb-pdf {
    height: 68px;
    width: auto;
    margin: 0.2rem auto 0.4rem;
    display: block;
}
.att-label-badge {
    font-size: 9px;
    padding: 2px 6px;
    margin-bottom: 0.3rem;
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.att-filename {
    font-size: 10px;
    color: #6b7280;
    word-break: break-word;
    line-height: 1.3;
    margin-bottom: 0.2rem;
}
.att-filesize { font-size: 10px; color: #9ca3af; margin-bottom: 0.5rem; }
.att-actions {
    display: flex;
    gap: 0.3rem;
    margin-top: auto;
    width: 100%;
    justify-content: center;
}
.att-actions .btn { font-size: 11px; padding: 2px 7px; flex: 1; }
@media (max-width: 575.98px) {
    .att-actions .btn { font-size: 10px; padding: 2px 5px; }
}

/* ── Empty state ─────────────────────────────────────────────────────────── */
.att-empty {
    text-align: center;
    padding: 2rem 1rem;
    color: #9ca3af;
}
.att-empty i { font-size: 2.5rem; display: block; margin-bottom: 0.5rem; }
</style>
@endpush
@endonce

{{-- ── JS (injected once at end of page) ────────────────────────────────── --}}

<div class="card mt-3" id="att-panel-{{ $panelId }}">
    <div class="card-body">

        {{-- ── Header ──────────────────────────────────────────────────── --}}
        <div class="att-panel-header">
            <h5>
                <i class="mdi mdi-paperclip text-primary mr-1"></i>
                {{ $attachableType === 'invoice' ? 'Customer Documents' : 'Stock Documents' }}
                @if($attachments->count() > 0)
                    <span class="badge badge-primary ml-1">{{ $attachments->count() }}</span>
                @endif
            </h5>
            <div class="att-btn-group">
                <button class="btn btn-sm btn-outline-primary" type="button"
                    onclick="attToggleUpload('{{ $panelId }}')">
                    <i class="mdi mdi-upload mr-1"></i><span class="d-none d-sm-inline">Upload from </span>PC
                </button>
                <button class="btn btn-sm btn-outline-info" type="button"
                    onclick="attShowQr('{{ $panelId }}', '{{ $tokenRoute }}')">
                    <i class="mdi mdi-qrcode mr-1"></i><span class="d-none d-sm-inline">QR </span>Mobile
                </button>
            </div>
        </div>

        {{-- ── Upload Form (laptop) ─────────────────────────────────────── --}}
        <div id="att-upload-{{ $panelId }}" class="att-upload-box" style="display:none;">
            <div class="att-upload-row">
                <div>
                    <label class="small font-weight-bold mb-1 d-block">Document Type</label>
                    <select class="form-control form-control-sm" id="att-label-{{ $panelId }}">
                        <option value="">— Select —</option>
                        @foreach($labelOpts as $opt)
                            <option value="{{ $opt }}">{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="small font-weight-bold mb-1 d-block">
                        Files
                        <small class="text-muted font-weight-normal">(JPG · PNG · PDF · max 10 MB)</small>
                    </label>
                    <div class="custom-file">
                        <input type="file" class="custom-file-input att-file-input"
                            id="att-file-{{ $panelId }}"
                            accept="image/*,application/pdf" multiple>
                        <label class="custom-file-label text-truncate" for="att-file-{{ $panelId }}">
                            Choose files…
                        </label>
                    </div>
                </div>
                <div>
                    <button class="btn btn-success btn-sm att-upload-btn" type="button"
                        onclick="attDoUpload('{{ $panelId }}','{{ $storeRoute }}','{{ $attachableType }}','{{ $attachableId }}','{{ csrf_token() }}')">
                        <i class="mdi mdi-cloud-upload mr-1"></i>Upload
                    </button>
                </div>
            </div>
            <div class="progress mt-2" id="att-progress-{{ $panelId }}" style="display:none;height:4px;">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                    id="att-progressbar-{{ $panelId }}" style="width:0%"></div>
            </div>
            <div id="att-msg-{{ $panelId }}" class="mt-2"></div>
        </div>

        {{-- ── QR Code Panel ────────────────────────────────────────────── --}}
        <div id="att-qr-{{ $panelId }}" class="att-qr-panel" style="display:none;">
            <p class="mb-0 font-weight-bold">Scan with phone to upload documents</p>
            <p class="small text-muted mb-3">Valid for 24 hours · No login needed on phone</p>
            <div id="att-qrbox-{{ $panelId }}"
                class="d-inline-block p-2 border rounded bg-white mb-1"
                style="min-width:212px;min-height:212px;"></div>
            <div id="att-qrloading-{{ $panelId }}" class="text-muted small mt-1">
                <i class="mdi mdi-loading mdi-spin mr-1"></i>Generating secure link…
            </div>
            <div class="att-qr-actions">
                <a id="att-qrlink-{{ $panelId }}" href="#" target="_blank"
                    class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-open-in-new mr-1"></i>Open link on phone
                </a>
                <button class="btn btn-sm btn-outline-danger" type="button"
                    onclick="document.getElementById('att-qr-{{ $panelId }}').style.display='none'">
                    Close
                </button>
            </div>
        </div>

        {{-- ── Files Grid ───────────────────────────────────────────────── --}}
        <div id="att-list-{{ $panelId }}">
            @if($attachments->count() > 0)
                <div class="att-grid" id="att-grid-{{ $panelId }}">
                    @foreach($attachments as $att)
                        <div class="att-card" id="att-item-{{ $att->id }}">
                            @if($att->isImage())
                                <a href="{{ $att->url }}" target="_blank" class="d-block w-100">
                                    <img src="{{ $att->url }}" alt="{{ $att->file_name }}" class="att-thumb">
                                </a>
                            @else
                                <a href="{{ $att->url }}" target="_blank">
                                    <img src="{{ asset('assets/images/pdf-file.svg') }}" alt="PDF" class="att-thumb-pdf">
                                </a>
                            @endif
                            @if($att->label)
                                <span class="badge badge-info att-label-badge">{{ $att->label }}</span>
                            @endif
                            <div class="att-filename">{{ Str::limit($att->file_name, 24) }}</div>
                            <div class="att-filesize">
                                {{ $att->formatted_size }}
                                @if(!$att->uploaded_by)
                                    · <span class="badge badge-light border" style="font-size:8px;">Mobile</span>
                                @endif
                            </div>
                            <div class="att-actions">
                                <a href="{{ $att->url }}" target="_blank"
                                    class="btn btn-xs btn-outline-primary mr-1">
                                    <i class="mdi mdi-eye"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-xs btn-outline-danger"
                                    onclick="attDelete({{ $att->id }},'{{ csrf_token() }}')">
                                    <i class="mdi mdi-delete"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="att-empty" id="att-empty-{{ $panelId }}">
                    <i class="mdi mdi-file-outline"></i>
                    <p class="mb-0">No documents attached yet.</p>
                    <small>Upload from PC or scan the QR code on your phone.</small>
                </div>
            @endif
        </div>

    </div>
</div>

@once
@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
window._vmQrCache = window._vmQrCache || {};

function attToggleUpload(pid) {
    const el = document.getElementById('att-upload-' + pid);
    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
    document.getElementById('att-qr-' + pid).style.display = 'none';
}

function attShowQr(pid, tokenRoute) {
    const qrPanel  = document.getElementById('att-qr-' + pid);
    const loading  = document.getElementById('att-qrloading-' + pid);
    const qrBox    = document.getElementById('att-qrbox-' + pid);
    const qrLink   = document.getElementById('att-qrlink-' + pid);

    document.getElementById('att-upload-' + pid).style.display = 'none';
    qrPanel.style.display = 'block';
    qrPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    if (window._vmQrCache[pid]) {
        loading.style.display = 'none';
        return;
    }

    loading.style.display = 'block';
    qrBox.innerHTML = '';

    fetch(tokenRoute, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error('failed');
            loading.style.display = 'none';
            qrLink.href = data.upload_url;
            new QRCode(qrBox, {
                text: data.upload_url,
                width: 200, height: 200,
                colorDark: '#111827', colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
            window._vmQrCache[pid] = data.upload_url;
        })
        .catch(() => {
            loading.innerHTML = '<span class="text-danger"><i class="mdi mdi-alert-circle mr-1"></i>Could not generate link. Please refresh.</span>';
        });
}

function attDoUpload(pid, storeRoute, type, id, csrf) {
    const fileInput  = document.getElementById('att-file-' + pid);
    const labelInput = document.getElementById('att-label-' + pid);
    const progress   = document.getElementById('att-progress-' + pid);
    const bar        = document.getElementById('att-progressbar-' + pid);
    const msg        = document.getElementById('att-msg-' + pid);

    if (!fileInput.files.length) {
        msg.innerHTML = '<div class="alert alert-warning py-1 px-2 small mb-0">Please choose at least one file.</div>';
        return;
    }

    const fd = new FormData();
    Array.from(fileInput.files).forEach(f => fd.append('files[]', f));
    fd.append('attachable_type', type);
    fd.append('attachable_id',   id);
    if (labelInput.value) fd.append('label', labelInput.value);
    fd.append('_token', csrf);

    progress.style.display = 'flex';
    bar.style.width = '8%';
    msg.innerHTML = '';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', storeRoute);
    xhr.upload.onprogress = e => {
        if (e.lengthComputable) bar.style.width = Math.round((e.loaded / e.total) * 88) + '%';
    };
    xhr.onload = () => {
        bar.style.width = '100%';
        const res = JSON.parse(xhr.responseText);
        if (xhr.status === 200 && res.success) {
            msg.innerHTML = `<div class="alert alert-success py-1 px-2 small mb-0">
                ✅ ${res.attachments.length} file(s) uploaded.
                <a href="javascript:location.reload()">Refresh</a> to see all.
            </div>`;
            fileInput.value = '';
            const lbl = document.querySelector(`label[for="att-file-${pid}"]`);
            if (lbl) lbl.textContent = 'Choose files…';

            const empty = document.getElementById('att-empty-' + pid);
            if (empty) empty.style.display = 'none';

            // Update the badge count in the panel header
            const panel = document.getElementById('att-panel-' + pid);
            if (panel) {
                const h5 = panel.querySelector('.att-panel-header h5');
                let badge = h5 ? h5.querySelector('.badge') : null;
                const addedCount = res.attachments.length;
                if (badge) {
                    badge.textContent = (parseInt(badge.textContent, 10) || 0) + addedCount;
                } else if (h5) {
                    const b = document.createElement('span');
                    b.className = 'badge badge-primary ml-1';
                    b.textContent = addedCount;
                    h5.appendChild(b);
                }
            }

            let grid = document.getElementById('att-grid-' + pid);
            if (!grid) {
                document.getElementById('att-list-' + pid).innerHTML =
                    `<div class="att-grid" id="att-grid-${pid}"></div>`;
                grid = document.getElementById('att-grid-' + pid);
            }

            res.attachments.forEach(att => {
                const card = document.createElement('div');
                card.className = 'att-card';
                card.id = 'att-item-' + att.id;
                const fname = att.file_name.length > 24 ? att.file_name.substring(0, 24) + '…' : att.file_name;
                card.innerHTML = `
                    ${att.is_image
                        ? `<a href="${att.url}" target="_blank" class="d-block w-100"><img src="${att.url}" class="att-thumb" alt="${att.file_name}"></a>`
                        : `<a href="${att.url}" target="_blank"><img src="/assets/images/pdf-file.svg" class="att-thumb-pdf" alt="PDF"></a>`}
                    ${att.label ? `<span class="badge badge-info att-label-badge">${att.label}</span>` : ''}
                    <div class="att-filename">${fname}</div>
                    <div class="att-filesize">${att.formatted_size}</div>
                    <div class="att-actions">
                        <a href="${att.url}" target="_blank" class="btn btn-xs btn-outline-primary mr-1">
                            <i class="mdi mdi-eye"></i>
                        </a>
                        <button type="button" class="btn btn-xs btn-outline-danger"
                            onclick="attDelete(${att.id},'${csrf}')">
                            <i class="mdi mdi-delete"></i>
                        </button>
                    </div>`;
                grid.prepend(card);
            });
        } else {
            const err = res.errors
                ? Object.values(res.errors).flat().join(' · ')
                : (res.message ?? 'Upload failed.');
            msg.innerHTML = `<div class="alert alert-danger py-1 px-2 small mb-0">❌ ${err}</div>`;
        }
        setTimeout(() => { progress.style.display = 'none'; bar.style.width = '0%'; }, 1400);
    };
    xhr.onerror = () => {
        msg.innerHTML = '<div class="alert alert-danger py-1 px-2 small mb-0">Network error. Please try again.</div>';
        progress.style.display = 'none';
    };
    xhr.send(fd);
}

function attDelete(attId, csrf) {
    vmConfirm({
        title: 'Delete Document?',
        text: 'This file will be permanently removed and cannot be recovered.',
        confirmText: 'Yes, Delete',
        cancelText: 'Cancel',
        icon: 'warning',
    }).then(confirmed => {
        if (!confirmed) return;

        fetch(`/attachments/${attId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                const el = document.getElementById('att-item-' + attId);
                if (!el) return;

                // Find the parent panel BEFORE removing the element
                const panel = el.closest('[id^="att-panel-"]');
                el.remove();

                // Update the badge count in the panel header
                if (panel) {
                    const badge = panel.querySelector('.att-panel-header h5 .badge');
                    if (badge) {
                        const current = parseInt(badge.textContent, 10) || 0;
                        const next = current - 1;
                        if (next <= 0) {
                            badge.remove();
                        } else {
                            badge.textContent = next;
                        }
                    }

                    // If the grid is now empty, swap it for the empty-state message
                    const pid = panel.id.replace('att-panel-', '');
                    const grid = document.getElementById('att-grid-' + pid);
                    if (grid && grid.children.length === 0) {
                        const list = document.getElementById('att-list-' + pid);
                        if (list) {
                            list.innerHTML = `<div class="att-empty" id="att-empty-${pid}">
                                <i class="mdi mdi-file-outline"></i>
                                <p class="mb-0">No documents attached yet.</p>
                                <small>Upload from PC or scan the QR code on your phone.</small>
                            </div>`;
                        }
                    }
                }

                vmToast('Document deleted successfully.', 'success');
            } else {
                vmToast('Could not delete. Please try again.', 'error');
            }
        })
        .catch(() => vmToast('Network error. Please try again.', 'error'));
    });
}

// Update custom-file-input label text
document.querySelectorAll('.att-file-input').forEach(inp => {
    inp.addEventListener('change', function () {
        const lbl = document.querySelector(`label[for="${this.id}"]`);
        if (!lbl) return;
        lbl.textContent = this.files.length > 1
            ? `${this.files.length} files selected`
            : (this.files[0]?.name ?? 'Choose files…');
    });
});
</script>
@endpush
@endonce
