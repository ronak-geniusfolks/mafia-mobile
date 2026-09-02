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
            padding-bottom: 24px;
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
        .upload-zone.dragover { border-color: #1a56db; background: #ebf5ff; }
        .upload-zone input[type="file"] {
            position: absolute; inset: 0; opacity: 0; cursor: pointer; width: 100%; height: 100%;
        }
        .upload-zone .icon { font-size: 40px; display: block; margin-bottom: 10px; }
        .upload-zone .zone-title { font-size: 15px; font-weight: 600; color: #1a56db; }
        .upload-zone .zone-sub { font-size: 12px; color: #94a3b8; margin-top: 4px; }

        .btn-camera {
            display: block; width: 100%; padding: 14px; border-radius: 10px;
            font-size: 15px; font-weight: 600; cursor: pointer; border: none;
            margin-top: 10px; transition: opacity 0.2s;
        }
        .btn-camera:active { opacity: 0.85; }
        .btn-camera:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-primary-action { background: #1a56db; color: white; }
        .btn-secondary-action { background: #f1f5f9; color: #374151; }
        .btn-continue { background: #16a34a; color: white; }

        .preview-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 16px;
        }
        .preview-item { position: relative; border-radius: 8px; overflow: hidden; aspect-ratio: 1; background: #f1f5f9; }
        .preview-item img { width: 100%; height: 100%; object-fit: cover; }
        .preview-item .pdf-thumb {
            width: 100%; height: 100%; display: flex; flex-direction: column;
            align-items: center; justify-content: center; gap: 4px;
        }
        .preview-item .pdf-thumb .pdf-icon { font-size: 28px; }
        .preview-item .pdf-thumb .pdf-name {
            font-size: 9px; color: #64748b; text-align: center; padding: 0 4px; word-break: break-all;
        }
        .preview-item .remove-btn {
            position: absolute; top: 3px; right: 3px; background: rgba(0,0,0,0.55);
            color: white; border: none; border-radius: 50%; width: 20px; height: 20px;
            font-size: 12px; cursor: pointer; display: flex; align-items: center; justify-content: center; line-height: 1;
        }

        .msg-box {
            padding: 10px 12px; border-radius: 8px; font-size: 13px; font-weight: 600;
            margin-top: 12px; display: none;
        }
        .msg-box.success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
        .msg-box.error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        .progress-track {
            height: 6px; background: #e2e8f0; border-radius: 4px; margin-top: 12px; overflow: hidden; display: none;
        }
        .progress-fill { height: 100%; width: 0%; background: #1a56db; transition: width 0.15s; }

        .uploaded-card {
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px;
            padding: 10px 12px; display: flex; align-items: center; gap: 10px; margin-top: 8px;
        }
        .uploaded-card .name { font-size: 13px; font-weight: 600; flex: 1; word-break: break-all; }
        .uploaded-card .check { color: #16a34a; font-size: 18px; }

        .continue-card {
            background: white; margin: 0 16px 16px; border-radius: 12px; padding: 18px 16px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08); text-align: center;
        }
        .continue-card p { font-size: 13px; color: #64748b; margin-bottom: 12px; line-height: 1.5; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo">📱 Mafia Mobile</div>
        <div class="subtitle">Document Upload</div>
    </div>

    <div class="info-card">
        <span class="badge">{{ $pending ? 'New Invoice · Draft' : ucfirst($type) }}</span>
        <div class="title">{{ $title }}</div>
        @if(!empty($subtitle))
            <div class="meta">{{ $subtitle }}</div>
        @endif
    </div>

    <div class="form-card">
        <div class="section-title">Add a document</div>

        <select class="label-select" id="labelSelect">
            <option value="">— Document type (optional) —</option>
            @foreach($labelOptions as $opt)
                <option value="{{ $opt }}">{{ $opt }}</option>
            @endforeach
        </select>

        <div class="upload-zone" id="uploadZone">
            <input type="file" id="fileInput" accept="image/*,application/pdf" multiple capture="environment">
            <span class="icon">📄</span>
            <div class="zone-title">Tap to choose or take a photo</div>
            <div class="zone-sub">JPG · PNG · PDF · up to 10 MB each</div>
        </div>

        <div class="preview-grid" id="previewGrid"></div>

        <button type="button" class="btn-camera btn-primary-action" id="uploadBtn" disabled>
            Upload Selected Files
        </button>

        <div class="progress-track" id="progressTrack">
            <div class="progress-fill" id="progressFill"></div>
        </div>

        <div class="msg-box" id="msgBox"></div>

        <div id="uploadedList"></div>
    </div>

    <div class="continue-card">
        <p>You can upload as many documents as you like. When you're done — or if you'd rather finish on your
            computer — tap below to open {{ $pending ? 'the invoice form' : 'the invoice' }}.</p>
        <a href="{{ $continueUrl }}" class="btn-camera btn-continue" style="display:block;text-decoration:none;">
            Continue to {{ $pending ? 'Invoice Form' : 'Invoice' }} →
        </a>
    </div>

    <script>
        const uploadUrl   = @json(route('attachments.store'));
        const csrfToken   = @json(csrf_token());
        const isPending   = @json($pending);
        const uploadToken = @json($token);
        const attType     = @json($type);
        const attId       = @json($id ?? null);

        const fileInput   = document.getElementById('fileInput');
        const uploadZone  = document.getElementById('uploadZone');
        const previewGrid = document.getElementById('previewGrid');
        const uploadBtn   = document.getElementById('uploadBtn');
        const labelSelect = document.getElementById('labelSelect');
        const msgBox      = document.getElementById('msgBox');
        const progressTrack = document.getElementById('progressTrack');
        const progressFill  = document.getElementById('progressFill');
        const uploadedList  = document.getElementById('uploadedList');

        let selectedFiles = [];

        fileInput.addEventListener('change', () => {
            selectedFiles = Array.from(fileInput.files);
            renderPreviews();
        });

        ['dragover', 'dragenter'].forEach(evt => uploadZone.addEventListener(evt, e => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        }));
        ['dragleave', 'drop'].forEach(evt => uploadZone.addEventListener(evt, e => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
        }));
        uploadZone.addEventListener('drop', e => {
            if (e.dataTransfer.files.length) {
                selectedFiles = Array.from(e.dataTransfer.files);
                renderPreviews();
            }
        });

        function renderPreviews() {
            previewGrid.innerHTML = '';
            selectedFiles.forEach((file, idx) => {
                const item = document.createElement('div');
                item.className = 'preview-item';
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    item.appendChild(img);
                } else {
                    item.innerHTML = `<div class="pdf-thumb"><span class="pdf-icon">📕</span>
                        <span class="pdf-name">${file.name}</span></div>`;
                }
                const rm = document.createElement('button');
                rm.className = 'remove-btn';
                rm.innerHTML = '✕';
                rm.onclick = () => { selectedFiles.splice(idx, 1); renderPreviews(); };
                item.appendChild(rm);
                previewGrid.appendChild(item);
            });
            uploadBtn.disabled = selectedFiles.length === 0;
        }

        function showMsg(text, type) {
            msgBox.textContent = text;
            msgBox.className = 'msg-box ' + type;
            msgBox.style.display = 'block';
        }

        uploadBtn.addEventListener('click', () => {
            if (!selectedFiles.length) return;

            const fd = new FormData();
            selectedFiles.forEach(f => fd.append('files[]', f));
            if (isPending) {
                fd.append('upload_token', uploadToken);
            } else {
                fd.append('attachable_type', attType);
                fd.append('attachable_id', attId);
            }
            if (labelSelect.value) fd.append('label', labelSelect.value);
            fd.append('_token', csrfToken);

            uploadBtn.disabled = true;
            uploadBtn.textContent = 'Uploading…';
            progressTrack.style.display = 'block';
            progressFill.style.width = '8%';
            msgBox.style.display = 'none';

            const xhr = new XMLHttpRequest();
            xhr.open('POST', uploadUrl);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.upload.onprogress = e => {
                if (e.lengthComputable) progressFill.style.width = Math.round((e.loaded / e.total) * 90) + '%';
            };
            xhr.onload = () => {
                progressFill.style.width = '100%';
                let res = {};
                try { res = JSON.parse(xhr.responseText); } catch (e) {}

                if (xhr.status === 200 && res.success) {
                    showMsg(`✅ ${res.attachments.length} file(s) uploaded successfully.`, 'success');
                    res.attachments.forEach(att => {
                        const card = document.createElement('div');
                        card.className = 'uploaded-card';
                        card.innerHTML = `<span class="check">✔</span>
                            <span class="name">${att.file_name}${att.label ? ' — ' + att.label : ''}</span>`;
                        uploadedList.appendChild(card);
                    });
                    selectedFiles = [];
                    fileInput.value = '';
                    renderPreviews();
                } else {
                    const err = res.errors ? Object.values(res.errors).flat().join(' · ') : (res.message ?? 'Upload failed.');
                    showMsg('❌ ' + err, 'error');
                }
                uploadBtn.textContent = 'Upload Selected Files';
                setTimeout(() => { progressTrack.style.display = 'none'; progressFill.style.width = '0%'; }, 1200);
            };
            xhr.onerror = () => {
                showMsg('❌ Network error. Please try again.', 'error');
                uploadBtn.disabled = false;
                uploadBtn.textContent = 'Upload Selected Files';
                progressTrack.style.display = 'none';
            };
            xhr.send(fd);
        });
    </script>
</body>
</html>
