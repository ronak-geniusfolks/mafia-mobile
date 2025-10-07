<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied • 403</title>
    <style>
        :root {
            --bg: #0f172a;
            --card: #111827;
            --text: #e5e7eb;
            --muted: #9ca3af;
            --accent: #f59e0b; /* amber */
            --danger: #ef4444; /* red */
            --btn: #2563eb; /* blue */
            --btn-hover: #1d4ed8;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: radial-gradient(1200px 600px at 10% 10%, rgba(245, 158, 11, 0.12), transparent 60%),
                        radial-gradient(900px 500px at 90% 20%, rgba(37, 99, 235, 0.10), transparent 60%),
                        var(--bg);
            color: var(--text);
            display: grid;
            place-items: center;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, Apple Color Emoji, Segoe UI Emoji;
        }

        .container {
            width: 100%;
            max-width: 880px;
            padding: 24px;
        }

        .card {
            background: linear-gradient(180deg, rgba(255,255,255,0.04), rgba(255,255,255,0.02));
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.25);
            position: relative;
            overflow: hidden;
        }

        .glow {
            position: absolute;
            inset: -2px;
            background: conic-gradient(from 180deg at 50% 50%, rgba(245, 158, 11, 0.18), rgba(37, 99, 235, 0.18), rgba(239, 68, 68, 0.18), rgba(245, 158, 11, 0.18));
            filter: blur(32px);
            opacity: 0.4;
            animation: rotate 10s linear infinite;
            z-index: 0;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg) scale(1.1); }
            100% { transform: rotate(360deg) scale(1.1); }
        }

        .content { position: relative; z-index: 1; }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(239, 68, 68, 0.12);
            color: #fecaca;
            border: 1px solid rgba(239, 68, 68, 0.35);
            border-radius: 999px;
            padding: 6px 12px;
            font-weight: 600;
            width: fit-content;
            margin-bottom: 16px;
        }

        .icon-wrap {
            display: inline-grid;
            place-items: center;
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: radial-gradient(circle at 30% 30%, rgba(239,68,68,0.25), rgba(239,68,68,0.15));
            border: 1px solid rgba(239,68,68,0.35);
            margin-bottom: 16px;
            position: relative;
        }

        .pulse {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            animation: pulse 2.2s ease-out infinite;
            background: rgba(239,68,68,0.25);
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.7; }
            70% { transform: scale(1.35); opacity: 0; }
            100% { transform: scale(1.35); opacity: 0; }
        }

        h1 {
            margin: 8px 0 6px;
            font-size: 32px;
            line-height: 1.2;
            letter-spacing: -0.02em;
        }

        p.lead {
            margin: 0 0 10px;
            color: var(--muted);
            font-size: 16px;
        }

        .hint {
            margin-top: 16px;
            padding: 12px 14px;
            border: 1px dashed rgba(245, 158, 11, 0.45);
            background: rgba(245, 158, 11, 0.08);
            color: #fde68a;
            border-radius: 12px;
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            border-radius: 10px;
            border: 1px solid rgba(255,255,255,0.12);
            background: linear-gradient(180deg, var(--btn), var(--btn-hover));
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: transform 120ms ease, filter 120ms ease, box-shadow 120ms ease;
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }

        .btn:hover { transform: translateY(-1px); filter: brightness(1.05); }

        .btn.secondary {
            background: transparent;
            color: var(--text);
            border: 1px solid rgba(255,255,255,0.16);
            box-shadow: none;
        }

        .code {
            margin-left: 8px;
            padding: 2px 8px;
            border-radius: 8px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.10);
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 12px;
            color: #c7d2fe;
        }

        .footer {
            margin-top: 22px;
            font-size: 12px;
            color: var(--muted);
        }

        @media (max-width: 640px) {
            .card { padding: 24px; }
            h1 { font-size: 26px; }
        }
    </style>
    <meta name="robots" content="noindex">
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="glow" aria-hidden="true"></div>
            <div class="content">
                <div class="badge">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M12 8v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        <circle cx="12" cy="16" r="1.5" fill="currentColor"/>
                        <path d="M10.29 3.86 1.82 18.14A2 2 0 0 0 3.54 21h16.92a2 2 0 0 0 1.72-2.86L13.71 3.86a2 2 0 0 0-3.42 0z" stroke="currentColor" stroke-width="2" fill="none"/>
                    </svg>
                    Access denied
                </div>

                <div class="icon-wrap" role="img" aria-label="Warning">
                    <div class="pulse" aria-hidden="true"></div>
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 8v5" stroke="#fca5a5" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="12" cy="17" r="1.6" fill="#fca5a5"/>
                    </svg>
                </div>

                <h1>403 • You don’t have permission to access this page</h1>
                <p class="lead">Please contact your administrator to request access. If you believe this is a mistake, try returning to the dashboard or signing in with the correct account.</p>

                <div class="hint">
                    Pro tip: share this code with your admin <span class="code">ERR-403</span> so they can quickly grant the required role or permission.
                </div>

                <div class="actions">
                    <a class="btn" href="{{ url('/') }}">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M3 12l9-9 9 9" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M9 21V9h6v12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Go to Dashboard
                    </a>
                    <a class="btn secondary" href="mailto:ronakrafaliya@geniusfolks.in?subject=Access%20Request%20(403)&body=Hello%2C%0D%0A%0DI%20encountered%20a%20403%20(Access%20Denied)%20error%20on%20the%20application.%20Could%20you%20please%20grant%20me%20the%20required%20permissions%3F%0D%0A%0D%0AThanks.">
                        Contact Administrator
                    </a>
                </div>

                <div class="footer">HTTP 403 • Authorization required</div>
            </div>
        </div>
    </div>
</body>
</html>


