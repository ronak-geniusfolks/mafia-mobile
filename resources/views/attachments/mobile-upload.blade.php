<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
    <title>Upload Documents — Mafia Mobile</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f4f8;
            min-height: 100vh;
            color: #1a202c;
        }

        .header {
            background: linear-gradient(135deg, #1a56db 0%, #1e429f 100%);
            color: white;
            padding: 20px 16px 16px;
            text-align: center;
        }
        .header .logo { font-size: 22px; font-weight: 700; letter-spacing: 0.5px; }
        .header .subtitle { font-size: 13px; opacity: 0.85; margin-top: 4px; }

        .info-card {
            background: white;
            margin: 16px;
            border-radius: 12px;
            padding: 16px;
            border-left: 4px solid #1a56db;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }
        .info-card .badge {
            display: inline-block;
            background: #ebf5ff;
            color: #1a56db;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 20px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .info-card .title { font-size: 16px; font-weight: 700; }
        .info-card .meta { font-size: 13px; color: #64748b; margin-top: 3px; }

        .form-card {
            background: white;
            margin: 0 16px 16px;
            border-radius: 12px;
            padding: 20px 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: #374151;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .label-select {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            margin-bottom: 16px;
            background: #f9fafb;
            appearance: none;
            color: #374151;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%236b7280' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
        }
        .label-select:focus { outline: none; border-color: #1a56db; background-color: white; }

        .upload-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 12px;
            padding: 28px 16px;
            text-align: center;
            transition: all 0.2s;
            cursor: pointer;
            background: #f8fafc;
            position: relative;
        }
        .upload-zone.dragover {
            border-color: #1a56db;
            background: #ebf5ff;
        }
        .upload-zone input[type="file"] {
            position: absolute;
            inset: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
        }
        .upload-zone .icon { font-size: 40px; display: block; margin-bottom: 10px; }
        .upload-zone .zone-title { font-size: 15px; font-weight: 600; color: #1a56db; }
        .upload-zone .zone-sub { font-size: 12px; color: #94a3b8; margin-top: 4px; }

        .btn-camera {
            display: block;
            width: 100%;
            padding: 14px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            margin-top: 10px;
            transition: opacity 0.2s;
        }
        .btn-camera:active { opacity: 0.85; }
        .btn-primary-action {
            background: #1a56db;
            color: white;
        }
        .btn-secondary-action {
            background: #f1f5f9;
            color: #374151;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-top: 16px;
        }
        .preview-item {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            aspect-ratio: 1;
            background: #f1f5f9;
        }
        .preview-item img {
            width: 100%; height: 100%; object-fit: cover;
        }
        .preview-item .pdf-thumb {
            width: 100%; height: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 4px;
        }
        .preview-item .pdf-thumb .pdf-icon { font-size: 28px; }
        .preview-item .pdf-thumb .pdf-name {
            font-size: 9px; color: #64748b; text-align: center;
            padding: 0 4px;
            word-break: break-all;
        }
        .preview-item .remove-btn {
            position: absolute; top: 3px; right: 3px;
            background: rgba(0,0,0,0.55);
            color: white; border: none;
            border-radius: 50%; width: 20px; height: 20px;
            font-size: 12px; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            line-height: 1;
        }

        .selected-count {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            margin-top: 12px;
            display: none;
        }

        .btn-upload {
            display: block;
            width: 100%;
            padding: 16px;
            background: #16a34a;
            color: white;
            font-size: 16px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            margin-top: 16px;
            transition: background 0.2s;
        }
        .btn-upload:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        .btn-upload:not(:disabled):active { background: #15803d; }

        .progress-bar-wrap {
            display: none;
            margin-top: 14px;
            border-radius: 8px;
            overflow: hidden;
            background: #e2e8f0;
            height: 8px;
        }
        .progress-bar-fill {
            height: 100%;
            background: #1a56db;
            width: 0%;
            transition: width 0.3s;
            border-radius: 8px;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            font-size: 14px;
            margin-top: 14px;
            display: none;
        }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .uploaded-section {
            margin: 0 16px 24px;
        }
        .already-uploaded-title {
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .uploaded-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background: white;
            border-radius: 10px;
            padding: 10px 12px;
            margin-bottom: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }
        .uploaded-item .thumb {
            width: 40px; height: 40px;
            border-radius: 6px; overflow: hidden;
            flex-shrink: 0;
            background: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
        }
        .uploaded-item .thumb img { width: 100%; height: 100%; object-fit: cover; }
        .uploaded-item .info { flex: 1; min-width: 0; }
        .uploaded-item .info .name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .uploaded-item .info .sub  { font-size: 11px; color: #94a3b8; margin-top: 2px; }

        .footer-note {
            text-align: center;
            font-size: 11px;
            color: #94a3b8;
            padding: 0 16px 32px;
        }
    </style>
</head>
<body>

<div class="header">
    <div class="logo">📱 Mafia Mobile</div>
    <div class="subtitle">Secure Document Upload</div>
</div>

<div class="info-card">
    <span class="badge">{{ ucfirst($type) }}</span>
    <div class="title">
        @if($type === 'invoice')
            Invoice #{{ $model->invoice_no }}
        @else
            Stock — {{ $model->model }} ({{ $model->imei }})
        @endif
    </div>
    <div class="meta">
        @if($type === 'invoice')
            Customer: {{ $model->customer_name }} &bull; {{ \Carbon\Carbon::parse($model->invoice_date)->format('d M Y') }}
        @else
            {{ $model->brand ?? '' }} {{ $model->storage ?? '' }} &bull; {{ ucfirst($model->color ?? '') }}
        @endif
    </div>
</div>

{{-- Already uploaded files --}}
@php
    $existing = $model->attachments()->latest()->get();
@endphp
@if($existing->count() > 0)
<div class="uploaded-section">
    <div class="already-uploaded-title">✅ Already Uploaded ({{ $existing->count() }})</div>
    @foreach($existing as $att)
    <div class="uploaded-item">
        <div class="thumb">
            @if($att->isImage())
                <img src="{{ $att->url }}" alt="{{ $att->file_name }}">
            @else
                <span style="font-size:20px;">📄</span>
            @endif
        </div>
        <div class="info">
            <div class="name">{{ $att->file_name }}</div>
            <div class="sub">{{ $att->label ? $att->label . ' · ' : '' }}{{ $att->formatted_size }}</div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Upload form --}}
<div class="form-card">
    <div class="section-title">📎 Upload New Document</div>

    <label for="labelSelect" style="font-size:13px;font-weight:600;color:#374151;display:block;margin-bottom:6px;">
        Document Type (optional)
    </label>
    <select id="labelSelect" class="label-select">
        <option value="">-- Select type --</option>
        @if($type === 'invoice')
            <option value="Aadhaar Card">Aadhaar Card</option>
            <option value="PAN Card">PAN Card</option>
            <option value="Driving Licence">Driving Licence</option>
            <option value="Voter ID">Voter ID</option>
            <option value="Passport">Passport</option>
            <option value="Other ID Proof">Other ID Proof</option>
        @else
            <option value="Original Bill">Original Bill</option>
            <option value="Device Photo">Device Photo</option>
            <option value="Box Photo">Box Photo</option>
            <option value="Accessories Photo">Accessories Photo</option>
            <option value="Other Document">Other Document</option>
        @endif
    </select>

    {{-- Camera capture (mobile primary action) --}}
    <label for="cameraInput">
        <div style="background:#1a56db;color:white;text-align:center;padding:14px;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;margin-bottom:10px;">
            📷 Take Photo with Camera
        </div>
    </label>
    <input type="file" id="cameraInput" accept="image/*" capture="environment" multiple style="display:none;">

    {{-- Gallery / file browse --}}
    <div class="upload-zone" id="uploadZone">
        <input type="file" id="fileInput" accept="image/*,application/pdf" multiple>
        <span class="icon">🖼️</span>
        <div class="zone-title">Choose from Gallery or Files</div>
        <div class="zone-sub">Images (JPG, PNG, WEBP) &amp; PDF · Max 10 MB each</div>
    </div>

    <div class="preview-grid" id="previewGrid"></div>
    <div class="selected-count" id="selectedCount"></div>

    <div class="progress-bar-wrap" id="progressWrap">
        <div class="progress-bar-fill" id="progressFill"></div>
    </div>

    <div class="alert alert-success" id="alertSuccess"></div>
    <div class="alert alert-error"   id="alertError"></div>

    <button class="btn-upload" id="uploadBtn" disabled>Upload Files</button>
</div>

<div class="footer-note">
    🔒 This link is valid for 24 hours &amp; is specific to this record only.<br>
    Files are saved securely in Mafia Mobile's system.
</div>

<script>
    const token       = @json($token);
    const uploadUrl   = @json(route('attachments.mobile-store', ['token' => $token]));
    const csrfToken   = @json(csrf_token());

    let selectedFiles = [];

    const fileInput    = document.getElementById('fileInput');
    const cameraInput  = document.getElementById('cameraInput');
    const previewGrid  = document.getElementById('previewGrid');
    const selectedCount= document.getElementById('selectedCount');
    const uploadBtn    = document.getElementById('uploadBtn');
    const progressWrap = document.getElementById('progressWrap');
    const progressFill = document.getElementById('progressFill');
    const alertSuccess = document.getElementById('alertSuccess');
    const alertError   = document.getElementById('alertError');
    const uploadZone   = document.getElementById('uploadZone');

    // ── File selection helpers ─────────────────────────────────────────────
    function addFiles(newFiles) {
        Array.from(newFiles).forEach(f => selectedFiles.push(f));
        renderPreviews();
        updateCounter();
    }

    function removeFile(index) {
        selectedFiles.splice(index, 1);
        renderPreviews();
        updateCounter();
    }

    function renderPreviews() {
        previewGrid.innerHTML = '';
        selectedFiles.forEach((file, i) => {
            const item = document.createElement('div');
            item.className = 'preview-item';

            if (file.type.startsWith('image/')) {
                const img = document.createElement('img');
                const reader = new FileReader();
                reader.onload = e => { img.src = e.target.result; };
                reader.readAsDataURL(file);
                item.appendChild(img);
            } else {
                const pdf = document.createElement('div');
                pdf.className = 'pdf-thumb';
                pdf.innerHTML = `<span class="pdf-icon">📄</span><span class="pdf-name">${file.name}</span>`;
                item.appendChild(pdf);
            }

            const rm = document.createElement('button');
            rm.className = 'remove-btn';
            rm.textContent = '×';
            rm.onclick = (e) => { e.stopPropagation(); removeFile(i); };
            item.appendChild(rm);
            previewGrid.appendChild(item);
        });
    }

    function updateCounter() {
        uploadBtn.disabled = selectedFiles.length === 0;
        if (selectedFiles.length > 0) {
            selectedCount.style.display = 'block';
            selectedCount.textContent = `${selectedFiles.length} file(s) selected — ready to upload`;
        } else {
            selectedCount.style.display = 'none';
        }
    }

    fileInput.addEventListener('change', e => addFiles(e.target.files));
    cameraInput.addEventListener('change', e => addFiles(e.target.files));

    // Drag-and-drop
    uploadZone.addEventListener('dragover', e => { e.preventDefault(); uploadZone.classList.add('dragover'); });
    uploadZone.addEventListener('dragleave', () => uploadZone.classList.remove('dragover'));
    uploadZone.addEventListener('drop', e => {
        e.preventDefault();
        uploadZone.classList.remove('dragover');
        addFiles(e.dataTransfer.files);
    });

    // ── Upload ─────────────────────────────────────────────────────────────
    uploadBtn.addEventListener('click', () => {
        if (!selectedFiles.length) return;

        const label  = document.getElementById('labelSelect').value;
        const formData = new FormData();
        selectedFiles.forEach(f => formData.append('files[]', f));
        if (label) formData.append('label', label);
        formData.append('_token', csrfToken);

        // UI state
        uploadBtn.disabled = true;
        uploadBtn.textContent = 'Uploading…';
        alertSuccess.style.display = 'none';
        alertError.style.display   = 'none';
        progressWrap.style.display = 'block';
        progressFill.style.width   = '10%';

        const xhr = new XMLHttpRequest();
        xhr.open('POST', uploadUrl);

        xhr.upload.onprogress = (e) => {
            if (e.lengthComputable) {
                progressFill.style.width = Math.round((e.loaded / e.total) * 90) + '%';
            }
        };

        xhr.onload = () => {
            progressFill.style.width = '100%';
            const res = JSON.parse(xhr.responseText);

            if (xhr.status === 200 && res.success) {
                alertSuccess.style.display = 'block';
                alertSuccess.innerHTML = `✅ <strong>${res.uploaded} file(s) uploaded successfully!</strong><br><small>You can upload more if needed.</small>`;
                selectedFiles = [];
                renderPreviews();
                updateCounter();
                fileInput.value   = '';
                cameraInput.value = '';
                setTimeout(() => { progressWrap.style.display = 'none'; progressFill.style.width = '0%'; }, 1200);
            } else {
                alertError.style.display = 'block';
                alertError.textContent   = res.error ?? res.message ?? 'Upload failed. Please try again.';
                progressWrap.style.display = 'none';
            }

            uploadBtn.disabled   = false;
            uploadBtn.textContent = 'Upload Files';
        };

        xhr.onerror = () => {
            alertError.style.display = 'block';
            alertError.textContent   = 'Network error. Please check your connection and try again.';
            uploadBtn.disabled   = false;
            uploadBtn.textContent = 'Upload Files';
            progressWrap.style.display = 'none';
        };

        xhr.send(formData);
    });
</script>
</body>
</html>
